<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir - {{ $present->acara }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        @media print {
            body {
                background-color: white;
            }
            .no-print {
                display: none !important;
            }
            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
            }
        }

        .container {
            width: 100%;
            padding: 20px;
            background-color: white;
            min-height: 100vh;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
        }

        .header .logo-area {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .header .logo {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border-radius: 3px;
        }

        .header .title-section h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .header .title-section h2 {
            font-size: 14px;
            font-weight: bold;
            margin: 2px 0;
        }

        .header .title-section p {
            font-size: 12px;
            margin: 2px 0;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .info-row {
            display: flex;
            margin-bottom: 5px;
        
            padding-bottom: 3px;
        }

        .info-label {
            width: 120px;
            font-weight: bold;
        }

        .info-value {
            flex: 1;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 11px;
        }

        table thead {
            background-color: #2c3e50;
            color: white;
        }

        table th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2c3e50;
            word-wrap: break-word;
        }

        table td {
            padding: 8px 5px;
            border: 1px solid #ddd;
            word-wrap: break-word;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

        /* Column Width */
        .col-no { width: 4%; }
        .col-nama { width: 16%; }
        .col-nip { width: 12%; }
        .col-jabatan { width: 16%; }
        .col-jk { width: 7%; }
        .col-status { width: 16%; }
        .col-ttd { width: 12%; }

        /* TTD Image */
        .ttd-image {
            max-width: 100%;
            height: 40px;
            object-fit: contain;
            border: 1px solid #ddd;
            padding: 2px;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }

        .status-hadir {
            background-color: #d4edda;
            color: #155724;
        }

        .status-tidak-hadir {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-dinas {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-luring {
            background-color: #cfe2ff;
            color: #084298;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        .signature-box {
            text-align: center;
            width: 22%;
        }

        .signature-box .title {
            font-weight: bold;
            margin-bottom: 30px;
            text-decoration: underline;
        }

        .signature-box .name {
            margin-top: 5px;
        }

        .signature-box .position {
            font-size: 10px;
            margin-top: 2px;
        }

        /* Print Control */
        .print-controls {
            margin-bottom: 15px;
            text-align: center;
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }

        .print-controls button,
        .print-controls a {
            padding: 8px 16px;
            margin: 0 5px;
            font-size: 13px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background-color: #007bff;
            color: white;
        }

        .btn-print:hover {
            background-color: #0056b3;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        /* Catatan */
        .catatan {
            margin-top: 15px;
            font-size: 10px;
            color: #666;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #007bff;
        }

        /* No Data */
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            background-color: #fafafa;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Tombol Print (Tidak akan tercetak) -->
    <div class="print-controls no-print">
        <button class="btn-print" onclick="window.print()">
            🖨️ Cetak / Save as PDF
        </button>
        <a href="{{ route('report.detail', ['token' => $present->token]) }}" class="btn-back">
            ← Kembali ke Detail
        </a>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-area">
                <div class="title-section">
                    <h1>DAFTAR HADIR KEGIATAN</h1>
                    <h2>BALAI BESAR POM DI BANJARBARU</h2>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-row">
                <div class="info-label">Kegiatan</div>
                <div class="info-value">: {{ $present->acara }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Lokasi</div>
                <div class="info-value">: {{ $present->lokasi ?? 'Ruang Rapat Kantor BBPOM di Banjarbaru' }}</div>
            </div>
          <div class="info-row">
    <div class="info-label">Tanggal</div>
    <div class="info-value">: {{
        \Carbon\Carbon::parse($present->tanggal)
            ->locale('id')
            ->isoFormat('dddd, D MMMM Y')
    }}</div>
</div>
            

        <!-- Tabel Daftar Hadir -->
        @if($attendances->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th class="col-no text-center">No</th>
                        <th class="col-nama">Nama</th>
                        <th class="col-nip">NIP/NIK</th>
                        <th class="col-jabatan">Jabatan</th>
                        <th class="col-jk text-center">Jenis Kelamin</th>
                        <th class="col-status text-center">Kehadiran</th>
                        <th class="col-ttd text-center">TTD</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $attendance->nama ?? '-' }}</td>
                            <td>{{ $attendance->nip ?? '-' }}</td>
                            <td>{{ $attendance->jabatan ?? '-' }}</td>
                            <td class="text-center">
                                @php
                                    $jk = strtolower($attendance->jenis_kelamin ?? '');
                                    if ($jk == 'wanita' || $jk == 'perempuan') {
                                        echo 'P';
                                    } elseif ($jk == 'pria' || $jk == 'laki-laki') {
                                        echo 'L';
                                    } else {
                                        echo $attendance->jenis_kelamin ?? '-';
                                    }
                                @endphp
                            </td>
                            <td class="text-center">
                                @php
                                    $status = $attendance->kehadiran_status ?? '';
                                @endphp
                                @if($status === 'hadir' || in_array($status, ['Daring-WFH', 'Daring-WFO', 'hadir(daring)']))
                                    <span class="status-badge status-hadir">Hadir</span>
                                @elseif($status === 'tidak_hadir')
                                    <span class="status-badge status-tidak-hadir">Tidak Hadir</span>
                                @elseif($status === 'dinas')
                                    <span class="status-badge status-dinas">Dinas</span>
                                @elseif($status === 'izin')
                                    <span class="status-badge status-luring">Izin</span>
                                @elseif($status === 'sakit')
                                    <span class="status-badge status-luring">Sakit</span>
                                @elseif($status === 'cuti')
                                    <span class="status-badge status-luring">Cuti</span>
                                @else
                                    <span class="status-badge" style="background-color: #e9ecef; color: #495057;">{{ $status }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!empty($attendance->signature))
                                    @if(strpos($attendance->signature, 'data:image') === 0)
                                        <img src="{{ $attendance->signature }}" alt="TTD" class="ttd-image">
                                    @else
                                        <img src="{{ asset('storage/' . $attendance->signature) }}" alt="TTD" class="ttd-image">
                                    @endif
                                @else
                                    <span style="color: #ccc;">___________</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Summary -->
            <div class="catatan">
                <strong>Total Peserta:</strong> {{ $attendances->count() }} orang 
             
            </div>
        @else
            <div class="no-data">
                ℹ️ Tidak ada data kehadiran untuk acara ini
            </div>
        @endif

       
</body>
</html>