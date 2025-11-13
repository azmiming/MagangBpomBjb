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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class AttendanceDetailExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithDrawings,
    WithEvents,
    ShouldAutoSize,
    WithCustomStartCell
{
    protected $attendances;
    protected $present;
    protected $rowNum = 0;

    public function __construct($attendances, $present = null)
    {
        $this->attendances = $attendances;
        $this->present = $present;
    }

    public function collection()
    {
        return $this->attendances;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP / NIK',
            'Nama',
            'Jabatan',
            'Divisi',
            'Unit Kerja',
            'Status Kehadiran',
            'Bukti (Daduk)',
            'TTD',
            'Tanggal Submit',
        ];
    }

    public function map($attendance): array
    {
        return [
            '', // Kolom No dikosongkan, akan diisi otomatis di registerEvents
            $attendance->nip ?? '-', // NIP/NIK akan diformat di registerEvents
            $attendance->nama ?? '-',
            $attendance->jabatan ?? '-',
            $attendance->divisi ?? '-',
            $attendance->unit_kerja ?? 'Badan POM Banjarbaru',
            $this->getStatusBadge($attendance->kehadiran_status ?? ''),
            '', // Kolom untuk gambar bukti
            '', // Kolom untuk gambar TTD
            $attendance->submitted_at
                ? Carbon::parse($attendance->submitted_at)->format('d/m/Y H:i')
                : '-',
        ];
    }

    protected function getStatusBadge($status)
    {
        $statusMap = [
            'hadir' => 'Hadir',
            'tidak_hadir' => 'Tidak Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'dinas' => 'Dinas',
            'Daring-WFH' => 'Hadir (WFH)',
            'Daring-WFO' => 'Hadir (WFO)',
            'hadir(daring)' => 'Hadir (Daring)',
        ];

        return $statusMap[$status] ?? $status;
    }

    public function drawings()
    {
        $drawings = [];

        // Logo lembaga (jika ada)
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

        // Gambar Bukti & TTD
        $row = 7;
        foreach ($this->attendances as $attendance) {
            
            // ===== GAMBAR BUKTI (DADUK) - Kolom H (8) =====
            if (!empty($attendance->bukti_path)) {
                $path = storage_path('app/public/' . $attendance->bukti_path);
                if (file_exists($path)) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $img = new Drawing();
                        $img->setPath($path);
                        $img->setHeight(60);
                        $img->setCoordinates('H' . $row);
                        $drawings[] = $img;
                    }
                }
            }

            // ===== GAMBAR TTD (TANDA TANGAN) - Kolom I (9) =====
            if (!empty($attendance->signature)) {
                $sigPath = null;

                // Cek apakah signature adalah base64
                if (strpos($attendance->signature, 'data:image') === 0) {
                    $data = explode(',', $attendance->signature)[1] ?? null;
                    if ($data) {
                        $tempPath = storage_path('app/public/temp_ttd_' . time() . '_' . $row . '.png');
                        file_put_contents($tempPath, base64_decode($data));
                        $sigPath = $tempPath;
                    }
                } else {
                    // Path file biasa
                    $sigPath = storage_path('app/public/' . $attendance->signature);
                    if (!file_exists($sigPath)) {
                        // Coba path alternatif
                        $sigPath = public_path($attendance->signature);
                    }
                }

                if ($sigPath && file_exists($sigPath)) {
                    $ext = strtolower(pathinfo($sigPath, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $sig = new Drawing();
                        $sig->setPath($sigPath);
                        $sig->setHeight(45);
                        $sig->setCoordinates('I' . $row);
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
                $sheet->mergeCells('B2:J2');
                $sheet->setCellValue('B2', 'LAPORAN DETAIL KEHADIRAN');
                $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('B2')->getAlignment()->setHorizontal('center');

                // Subjudul acara
                $acara = $this->present->acara ?? 'Laporan Kehadiran';
                $sheet->mergeCells('B3:J3');
                $sheet->setCellValue('B3', strtoupper($acara));
                $sheet->getStyle('B3')->getFont()->setBold(true)->setItalic(true)->setSize(13);
                $sheet->getStyle('B3')->getAlignment()->setHorizontal('center');

                // Informasi acara
                $sheet->mergeCells('B4:J4');
                $tanggal = $this->present->tanggal 
                    ? Carbon::parse($this->present->tanggal)->format('d/m/Y')
                    : '-';
                $lokasi = $this->present->lokasi ?? '-';
                $sheet->setCellValue('B4', 'Tanggal: ' . $tanggal . ' | Lokasi: ' . $lokasi);
                $sheet->getStyle('B4')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B4')->getFont()->setItalic(true)->setSize(11);

                // Tanggal cetak
                $sheet->mergeCells('B5:J5');
                $sheet->setCellValue('B5', 'Dicetak: ' . Carbon::now()->format('d/m/Y H:i'));
                $sheet->getStyle('B5')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B5')->getFont()->setItalic(true)->setSize(10);

                // Header tabel styling
                $sheet->getStyle('A6:J6')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '366092']
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                    ]
                ]);

                // Masukan nomor urut di kolom A
                $row = 7;
                $highestRow = $sheet->getHighestRow();
                
                for ($i = $row; $i <= $highestRow; $i++) {
                    $sheet->setCellValue('A' . $i, ($i - 6));
                }

                // ===== FORMAT KOLOM B (NIP/NIK) SEBAGAI TEXT TANPA APOSTROF =====
                for ($i = 7; $i <= $highestRow; $i++) {
                    $cellValue = $sheet->getCell('B' . $i)->getValue();
                    if ($cellValue && $cellValue !== '-') {
                        // Set format teks
                        $sheet->getStyle('B' . $i)->getNumberFormat()->setFormatCode('@');
                        // Set value kembali untuk memastikan format text
                        $sheet->getCell('B' . $i)->setValueExplicit($cellValue, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                }

                // Border dan alignment data
                $sheet->getStyle('A6:J' . $highestRow)->applyFromArray([
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

                // Set lebar kolom
                $sheet->getColumnDimension('A')->setWidth(4);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(12);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(12);
                $sheet->getColumnDimension('I')->setWidth(12);
                $sheet->getColumnDimension('J')->setWidth(15);
            },
        ];
    }
}