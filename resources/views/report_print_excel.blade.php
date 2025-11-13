<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Daftar Hadir - {{ $present->acara }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }

        .header-section {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-section h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-item label {
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
        }

        .info-item value {
            color: #333;
            font-size: 1rem;
            margin-top: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .action-buttons button,
        .action-buttons a {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-export-excel {
            background-color: #28a745;
            color: white;
        }

        .btn-export-excel:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-print {
            background-color: #007bff;
            color: white;
        }

        .btn-print:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .content-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background-color: #333;
            color: white;
        }

        thead th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #333;
        }

        tbody td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
        }

        tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .badge-primary {
            background-color: #007bff;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }

        .image-cell {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        /* Print styles */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }

            .header-section {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .action-buttons {
                display: none;
            }

            .content-section {
                box-shadow: none;
            }

            table {
                page-break-inside: avoid;
            }

            tbody tr {
                page-break-inside: avoid;
            }
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .filter-info {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading.show {
            display: flex;
        }

        .spinner {
            color: white;
            text-align: center;
        }

        .spinner i {
            font-size: 3rem;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
            display: block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading" id="loadingSpinner">
        <div class="spinner">
            <i class="fas fa-spinner"></i>
            <p>Sedang membuat file Excel...</p>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Header Section -->
        <div class="header-section">
            <h2>📊 Cetak Daftar Hadir</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Nama Acara:</label>
                    <value>{{ $present->acara }}</value>
                </div>
                <div class="info-item">
                    <label>Tanggal:</label>
                    <value>{{ \Carbon\Carbon::parse($present->tanggal)->format('d/m/Y') }}</value>
                </div>
                <div class="info-item">
                    <label>Lokasi:</label>
                    <value>{{ $present->lokasi ?? '-' }}</value>
                </div>
                <div class="info-item">
                    <label>Metode:</label>
                    <value>
                        @if($present->metode_kehadiran === 'luring')
                            <span class="badge badge-primary">Luring</span>
                        @else
                            <span class="badge badge-info">Daring</span>
                        @endif
                    </value>
                </div>
            </div>

            <!-- Filter Info -->
            @if($filter1 || $filter2)
                <div class="filter-info">
                    <strong>Filter diterapkan:</strong>
                    @if($filter1)
                        Jenis Kehadiran: <strong>{{ ucfirst(str_replace('_', ' ', $filter1)) }}</strong>
                    @endif
                    @if($filter2)
                        | Divisi: <strong>{{ $filter2 }}</strong>
                    @endif
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn-export-excel" onclick="exportToExcelWithImages()">
                    <i class="fas fa-file-excel"></i> Export ke Excel (dengan Gambar)
                </button>
                <button type="button" class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print / Simpan PDF
                </button>
                <a href="{{ route('report.detail', ['token' => $present->token]) }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- Content Section -->
        <div class="content-section">
            @if(isset($attendances) && $attendances->isNotEmpty())
                <table id="attendanceTable">
                    <thead>
                        <tr>
                            <th style="width: 3%;">No</th>
                            <th style="width: 8%;">NIP/NIK</th>
                            <th style="width: 12%;">Nama</th>
                            <th style="width: 12%;">Jabatan</th>
                            <th style="width: 11%;">Divisi</th>
                            <th style="width: 8%;">Unit Kerja</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 10%;">Bukti (Daduk)</th>
                            <th style="width: 10%;">Tanda Tangan (TTD)</th>
                            <th style="width: 12%;">Tanggal & Jam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                            <tr>
                                <td class="text-center"><strong>{{ $loop->iteration }}</strong></td>
                                <td>{{ $attendance->nip ?? '-' }}</td>
                                <td><strong>{{ $attendance->nama ?? '-' }}</strong></td>
                                <td>{{ $attendance->jabatan ?? '-' }}</td>
                                <td>{{ $attendance->divisi ?? '-' }}</td>
                                <td>{{ $attendance->unit_kerja ?? 'Badan POM Banjarbaru' }}</td>
                                <td class="text-center">
                                    @php
                                        $status = $attendance->kehadiran_status;
                                    @endphp
                                    @if($status === 'hadir')
                                        <span class="badge badge-success">Hadir</span>
                                    @elseif($status === 'izin')
                                        <span class="badge badge-warning">Izin</span>
                                    @elseif($status === 'sakit')
                                        <span class="badge badge-info">Sakit</span>
                                    @elseif($status === 'cuti')
                                        <span class="badge badge-primary">Cuti</span>
                                    @elseif($status === 'dinas')
                                        <span class="badge badge-secondary">Dinas</span>
                                    @elseif(in_array($status, ['Daring-WFH', 'Daring-WFO', 'hadir(daring)']))
                                        <span class="badge badge-success">Hadir</span>
                                    @elseif($status === 'tidak_hadir')
                                        <span class="badge badge-danger">Tidak Hadir</span>
                                    @else
                                        <span class="badge" style="background-color: #ddd; color: #333;">{{ $status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!empty($attendance->bukti_path))
                                        <img src="{{ asset('storage/' . $attendance->bukti_path) }}" 
                                             alt="Bukti" 
                                             class="image-cell"
                                             data-type="bukti"
                                             data-path="{{ $attendance->bukti_path }}">
                                    @else
                                        <span style="color: #999;">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!empty($attendance->signature))
                                        @if(Str::startsWith($attendance->signature, 'data:image'))
                                            <img src="{{ $attendance->signature }}" 
                                                 alt="TTD" 
                                                 class="image-cell"
                                                 data-type="signature"
                                                 data-is-base64="true">
                                        @else
                                            <img src="{{ $attendance->signature }}" 
                                                 alt="TTD" 
                                                 class="image-cell"
                                                 data-type="signature"
                                                 data-path="{{ $attendance->signature }}">
                                        @endif
                                    @else
                                        <span style="color: #999;">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($attendance->submitted_at)
                                        <div>{{ \Carbon\Carbon::parse($attendance->submitted_at)->format('d/m/Y') }}</div>
                                        <div style="color: #007bff; font-weight: 500; font-size: 0.9rem;">
                                            {{ \Carbon\Carbon::parse($attendance->submitted_at)->format('H:i:s') }}
                                        </div>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #ddd; margin-bottom: 15px; display: block;"></i>
                    <h5>Tidak ada data kehadiran</h5>
                    <p>Belum ada data yang sesuai dengan filter yang diterapkan.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Convert image URL to base64
        async function urlToBase64(url) {
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                return new Promise((resolve) => {
                    const reader = new FileReader();
                    reader.onloadend = () => resolve(reader.result);
                    reader.readAsDataURL(blob);
                });
            } catch (error) {
                console.error('Error converting image:', error);
                return null;
            }
        }

        // Export to Excel with images
        async function exportToExcelWithImages() {
            const loadingSpinner = document.getElementById('loadingSpinner');
            loadingSpinner.classList.add('show');

            try {
                const table = document.getElementById('attendanceTable');
                if (!table) {
                    alert('Tidak ada data untuk diexport');
                    return;
                }

                const eventName = "{{ $present->acara }}".replace(/[^a-zA-Z0-9_-]/g, '_');
                const fileName = `Daftar_Hadir_${eventName}_${new Date().getTime()}.xlsx`;

                // Get all image data
                const imageElements = document.querySelectorAll('img.image-cell');
                const imageMap = {}; // Store base64 images

                for (let img of imageElements) {
                    const src = img.getAttribute('src');
                    const isBase64 = img.getAttribute('data-is-base64') === 'true';
                    
                    if (isBase64) {
                        // Already base64
                        imageMap[src] = src;
                    } else if (src) {
                        // Convert to base64
                        const base64 = await urlToBase64(src);
                        if (base64) {
                            imageMap[src] = base64;
                        }
                    }
                }

                // Create workbook
                const ws = XLSX.utils.table_to_sheet(table);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Daftar Hadir");

                // Set column widths
                ws['!cols'] = [
                    { wch: 4 },   // No
                    { wch: 10 },  // NIP
                    { wch: 15 },  // Nama
                    { wch: 14 },  // Jabatan
                    { wch: 14 },  // Divisi
                    { wch: 10 },  // Unit Kerja
                    { wch: 12 },  // Status
                    { wch: 12 },  // Bukti
                    { wch: 15},  // TTD
                    { wch: 15 }   // Submit Time
                ];

                // Add images to worksheet
                ws['!drawing'] = [];
                imageElements.forEach((img, index) => {
                    const src = img.getAttribute('src');
                    const base64 = imageMap[src];
                    
                    if (base64) {
                        ws['!drawing'].push({
                            type: 'picture',
                            src: base64,
                            row: index + 1,
                            col: img.getAttribute('data-type') === 'bukti' ? 7 : 8
                        });
                    }
                });

                // Download file
                XLSX.writeFile(wb, fileName);
                
                // Show success message
                alert('File Excel berhasil diunduh dengan gambar!');
            } catch (error) {
                console.error('Error exporting:', error);
                alert('Terjadi kesalahan saat membuat file Excel: ' + error.message);
            } finally {
                loadingSpinner.classList.remove('show');
            }
        }
    </script>
</body>
</html>