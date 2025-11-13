<?php

namespace App\Exports;

use App\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class AttendanceReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithDrawings,
    WithEvents,
    ShouldAutoSize,
    WithCustomStartCell
{
    protected $attendances;
    protected $filters;

    public function __construct($attendances, $filters = [])
    {
        $this->attendances = $attendances;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function startCell(): string
    {
        return 'A6'; // Header tabel mulai di baris 6
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP / NIK',
            'Nama',
            'Jabatan',
            'Divisi / Bagian',
            'Unit Kerja',
            'Jenis Kelamin',
            'Peserta',
            'Status Kehadiran',
            'Acara',
            'Tanggal Acara',
            'Tipe Verifikasi',
            'Selfie',
            'Tanda Tangan',
            'Tanggal Submit',
        ];
    }

    public function map($attendance): array
    {
        $buktiPath = $attendance->bukti_path
            ? storage_path('app/public/' . $attendance->bukti_path)
            : null;

        $selfieText = '-';
        if ($buktiPath && file_exists($buktiPath)) {
            $ext = strtolower(pathinfo($buktiPath, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $selfieText = ''; // kosong, nanti ditimpa gambar
            }
            // Untuk PDF, biarkan kosong ('-') — tidak dimasukkan ke Excel
        }

        return [
            '',
            $attendance->nip ?? '-',
            $attendance->nama ?? '-',
            $attendance->jabatan ?? '-',
            $attendance->divisi ?? '-',
            $attendance->unit_kerja ?? 'Badan POM Banjarbaru',
            $attendance->jenis_kelamin ?? '-',
            ucfirst($attendance->status ?? '-'),
            ucfirst($attendance->kehadiran_status ?? '-'),
            optional($attendance->present)->acara ?? '-',
            optional($attendance->present)->tanggal
                ? Carbon::parse($attendance->present->tanggal)->format('d/m/Y')
                : '-',
            $this->getTipeVerifikasi($attendance),
            $selfieText, // ← Untuk PDF, tetap '-'
            '',
            $attendance->submitted_at
                ? Carbon::parse($attendance->submitted_at)->format('d/m/Y H:i')
                : '-',
        ];
    }

    protected function getTipeVerifikasi($attendance)
    {
        $tipeList = is_array($attendance->present->tipe ?? null)
            ? $attendance->present->tipe
            : explode(',', $attendance->present->tipe ?? '');

        if (in_array('selfie', $tipeList) && in_array('tanda_tangan', $tipeList)) {
            return 'Selfie + Tanda Tangan';
        } elseif (in_array('selfie', $tipeList)) {
            return 'Selfie';
        } elseif (in_array('tanda_tangan', $tipeList)) {
            return 'Tanda Tangan';
        }
        return '-';
    }

    public function drawings()
    {
        $drawings = [];

        // Logo lembaga
        $logoPath = public_path('images/logo_bpom.png');
        if (file_exists($logoPath)) {
            $logo = new Drawing();
            $logo->setName('Logo');
            $logo->setDescription('Logo Instansi');
            $logo->setPath($logoPath);
            $logo->setHeight(80);
            $logo->setCoordinates('A1');
            $drawings[] = $logo;
        }

        // Gambar Selfie & Tanda Tangan
        $row = 7;
        foreach ($this->attendances as $attendance) {
            /** ✅ Selfie (cek dulu apakah file valid dan berformat gambar) */
            if (!empty($attendance->bukti_path)) {
                $path = storage_path('app/public/' . $attendance->bukti_path);
                if (file_exists($path)) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $img = new Drawing();
                        $img->setPath($path);
                        $img->setHeight(60);
                        $img->setCoordinates('M' . $row);
                        $drawings[] = $img;
                    }
                    // Kalau bukan gambar (misal PDF), tidak akan error — biarkan kosong
                }
            }

            /** ✅ Tanda tangan (bisa base64 atau file path) */
            if (!empty($attendance->signature)) {
                $sigPath = null;

                if (strpos($attendance->signature, 'data:image') === 0) {
                    $data = explode(',', $attendance->signature)[1] ?? null;
                    if ($data) {
                        $tempPath = storage_path('app/public/temp_ttd_' . $row . '.png');
                        file_put_contents($tempPath, base64_decode($data));
                        $sigPath = $tempPath;
                    }
                } else {
                    $sigPath = storage_path('app/public/' . $attendance->signature);
                }

                if ($sigPath && file_exists($sigPath)) {
                    $ext = strtolower(pathinfo($sigPath, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $sig = new Drawing();
                        $sig->setPath($sigPath);
                        $sig->setHeight(45);
                        $sig->setCoordinates('N' . $row);
                        $drawings[] = $sig;
                    }
                }
            }

            $row++;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul utama
                $sheet->mergeCells('B2:O2');
                $sheet->setCellValue('B2', 'LAPORAN KEHADIRAN PEGAWAI');
                $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('B2')->getAlignment()->setHorizontal('center');

                // Subjudul acara
                $acara = $this->filters['acara'] ?? 'Semua Acara';
                $sheet->mergeCells('B3:O3');
                $sheet->setCellValue('B3', strtoupper($acara));
                $sheet->getStyle('B3')->getFont()->setBold(true)->setItalic(true)->setSize(13);
                $sheet->getStyle('B3')->getAlignment()->setHorizontal('center');

                // Tanggal cetak
                $sheet->mergeCells('B4:O4');
                $sheet->setCellValue('B4', 'Dicetak: ' . Carbon::now()->format('d/m/Y H:i'));
                $sheet->getStyle('B4')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B4')->getFont()->setItalic(true)->setSize(11);

                // Header tabel
                $sheet->getStyle('A6:O6')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'D9E1F2']
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                    ]
                ]);

                // Border seluruh data
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A6:O' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'vertical' => 'center',
                        'horizontal' => 'center',
                        'wrapText' => true,
                    ],
                ]);

                // Tinggi baris agar muat gambar
                for ($i = 7; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(65);
                }
            },
        ];
    }
}
