@extends('layouts.app')

@section('title', 'Present')

@section('content')
<div class="container mt-4">
    <h2>Generate Link Absen</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Present</li>
        </ol>
    </nav>

    <!-- Form Generate -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Buat Link Absen Baru</h5>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('present.generate') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="acara" class="form-label">Acara :</label>
                    <input type="text" name="acara" id="acara" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal :</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" required>
                </div>
                <div class="mb-3">
    <label for="lokasi" class="form-label">Lokasi :</label>
    <input type="text" name="lokasi" id="lokasi" class="form-control" placeholder="Contoh: Aula Utama" value="{{ old('lokasi') }}">
</div>


                <!-- Jam Buka Absen (Full 24-jam) -->
                <div class="mb-3">
                    <label class="form-label">Jam Buka Absen:</label>
                    <div class="row g-2">
                        <div class="col-auto">
                            <select name="jam_buka_jam" class="form-select" required>
                                @for($h = 0; $h <= 23; $h++)
                                    <option value="{{ $h }}" {{ old('jam_buka_jam', 10) == $h ? 'selected' : '' }}>
                                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="jam_buka_menit" class="form-select" required>
                                @for($m = 0; $m < 60; $m++)
                                    <option value="{{ $m }}" {{ old('jam_buka_menit', 0) == $m ? 'selected' : '' }}>
                                        {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <small class="text-muted">Format 24-jam: Jam 1 siang = <strong>13 : 00</strong></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status :</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="status" id="open" value="open" required checked>
                        <label class="form-check-label" for="open">Open</label>
                    </div>
                    
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipe Verifikasi :</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="tipe[]" id="selfie" value="selfie" checked>
                        <label class="form-check-label" for="selfie">Upload Daduk</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="tipe[]" id="tanda_tangan" value="tanda_tangan">
                        <label class="form-check-label" for="tanda_tangan">Tanda Tangan</label>
                    </div>
                </div>

                <!-- Tipe Acara -->
                <div class="mb-3">
                    <label class="form-label">Tipe Acara :</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="metode_kehadiran" id="luring" value="luring" required {{ old('metode_kehadiran', 'luring') == 'luring' ? 'checked' : '' }}>
                        <label class="form-check-label" for="luring">Luring</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="metode_kehadiran" id="daring" value="daring" required {{ old('metode_kehadiran') == 'daring' ? 'checked' : '' }}>
                        <label class="form-check-label" for="daring">Daring</label>
                    </div>
                </div>

                <!-- Status Kehadiran - SEMUA OPSI DITAMPILKAN -->
               <!-- Status Kehadiran -->
<div class="mb-3">
    <label class="form-label">Status Kehadiran:</label><br>

    <!-- Grup Luring -->
    <div id="status-luring" class="mb-2" style="display: none;">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="status_kehadiran[]" id="hadir" value="hadir" checked>
            <label class="form-check-label" for="hadir">Hadir</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="status_kehadiran[]" id="sakit" value="sakit">
            <label class="form-check-label" for="sakit">Sakit</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="status_kehadiran[]" id="cuti" value="cuti">
            <label class="form-check-label" for="cuti">Cuti</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="status_kehadiran[]" id="dinas" value="dinas">
            <label class="form-check-label" for="dinas">Dinas</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="status_kehadiran[]" id="izin" value="izin">
            <label class="form-check-label" for="izin">Izin</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="status_kehadiran[]" id="hadir_daring" value="hadir(daring)">
            <label class="form-check-label" for="hadir_daring">Hadir (Daring)</label>
        </div>
    </div>

    <!-- Grup Daring -->
    <div id="status-daring" class="mb-2" style="display: none;">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="status_kehadiran[]" id="daring_wfh" value="Daring-WFH" checked>
            <label class="form-check-label" for="daring_wfh">Daring-WFH</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="status_kehadiran[]" id="daring_wfo" value="Daring-WFO" checked>
            <label class="form-check-label" for="daring_wfo">Daring-WFO</label>
        </div>
    </div>
</div>

                <button type="submit" class="btn btn-success">Generate Link Absen</button>
            </form>
        </div>
    </div>

    <!-- Daftar Presentasi -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Daftar Link Absen</h5>
        </div>
        <div class="card-body">
            @if($presents->isEmpty())
                <div class="alert alert-info">Belum ada link absen yang dibuat.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Acara</th>
                                <th>Tanggal</th> 
                                <th>Lokasi</th>
                                <th>Jam Buka</th>
                                <th>Tipe Verifikasi</th>
                                <th>Status Kehadiran</th>
                                <th>Tipe Acara</th>
                                <th>Status</th>
                                <th>Link</th>
                                <th>QR Code</th>
                                <th>Aksi</th>
                                
                             
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($presents as $present)
                                @php
                                    $tipeList = is_array($present->tipe) ? $present->tipe : explode(',', $present->tipe);
                                    $statusKehadiranList = is_array($present->status_kehadiran) ? $present->status_kehadiran : explode(',', $present->status_kehadiran ?? '');
                                    $statusKehadiranList = array_map('trim', $statusKehadiranList);

                                    $labelList = array_map(function($t){
                                        return $t === 'selfie' 
                                            ? 'Upload Doduk' 
                                            : ($t === 'tanda_tangan' 
                                                ? 'Tanda Tangan' 
                                                : ucfirst($t));
                                    }, $tipeList);

                                    $statusLabelList = array_map(function($s) {
                                        return $s === 'hadir(daring)' ? 'Hadir (Daring)' : $s;
                                    }, $statusKehadiranList);

                                    $isOpen = $present->status === 'open';
                                    $btnClass = $isOpen ? 'btn-warning' : 'btn-success';
                                    $btnText = $isOpen ? 'Tutup' : 'Buka';
                                    $confirmAction = $isOpen ? 'menutup' : 'membuka';
                                    
                                    $attendanceUrl = route('present.show', $present->token);
                                @endphp
                                <tr>
                                    <td>{{ $present->acara }}</td>
                                    <td>{{ \Carbon\Carbon::parse($present->tanggal)->format('d/m/Y') }}</td>
                                    <td>{{ $present->lokasi ?? '—' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($present->jam_buka)->format('H:i') }}</td>
                                    <td>{{ implode(' & ', $labelList) }}</td>
                                    <td>{{ implode(', ', $statusLabelList) }}</td>
                                    <td>
                                        @if($present->metode_kehadiran === 'luring')
                                            <span class="badge bg-primary">Luring</span>
                                        @else
                                            <span class="badge bg-info text-dark">Daring</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isOpen)
                                            <span class="badge bg-success">Terbuka</span>
                                        @else
                                            <span class="badge bg-danger">Tertutup</span>
                                        @endif
                                    </td>
                                  
                                    <td>
                                        <a href="{{ $attendanceUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            Lihat
                                        </a>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="copyLink('{{ $attendanceUrl }}')">
                                            <i class="bi bi-clipboard"></i> Salin
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info text-white" 
                                                data-url="{{ $attendanceUrl }}" 
                                                data-acara="{{ $present->acara }}" 
                                                data-tanggal="{{ \Carbon\Carbon::parse($present->tanggal)->format('d/m/Y') }}"
                                                onclick="showQRModalFromButton(this)">
                                            <i class="bi bi-qr-code"></i> Lihat QR
                                        </button>
                                    </td>
                                   <td>
    <!-- Tombol Edit -->
    <a href="{{ route('present.edit', $present->id) }}" class="btn btn-sm btn-outline-primary mb-1 w-100">
        <i class="bi bi-pencil"></i> Edit
    </a>

    <!-- Tombol Buka/Tutup -->
    <form action="{{ route('present.toggle-status', $present->token) }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-sm {{ $btnClass }} w-100 mt-1"
                onclick="return confirm('Yakin ingin {{ $confirmAction }} absen ini?')">
            {{ $btnText }}
        </button>
    </form>
</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="qrModalLabel">QR Code Absensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-info" class="mb-3">
                    <h6 id="qr-acara" class="fw-bold"></h6>
                    <p id="qr-tanggal" class="text-muted mb-0"></p>
                </div>
                <div id="qrcode-container" class="d-flex justify-content-center mb-3">
                    <!-- QR Code akan muncul di sini -->
                </div>
                <p class="small text-muted">Scan QR Code untuk mengisi absensi</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" onclick="downloadQRPDF()">
                    <i class="bi bi-download"></i> Download PDF
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<!-- jsPDF Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<script>
let currentQRData = null;

function copyLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        alert('Link berhasil disalin ke clipboard!');
    }, function(err) {
        console.error('Gagal menyalin link: ', err);
        alert('Gagal menyalin link');
    });
}

function showQRModalFromButton(button) {
    const url = button.getAttribute('data-url');
    const acara = button.getAttribute('data-acara');
    const tanggal = button.getAttribute('data-tanggal');
    showQRModal(url, acara, tanggal);
}

function showQRModal(url, acara, tanggal) {
    document.getElementById('qr-acara').textContent = acara;
    document.getElementById('qr-tanggal').textContent = 'Tanggal: ' + tanggal;
    const container = document.getElementById('qrcode-container');
    container.innerHTML = '';
    const qrcode = new QRCode(container, {
        text: url,
        width: 256,
        height: 256,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    currentQRData = { url, acara, tanggal };
    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
    modal.show();
}

function downloadQRPDF() {
    if (!currentQRData) return;
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const canvas = document.querySelector('#qrcode-container canvas');
    if (!canvas) {
        alert('QR Code tidak ditemukan!');
        return;
    }
    const qrImageData = canvas.toDataURL('image/png');
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    doc.setFontSize(20);
    doc.setFont(undefined, 'bold');
    doc.text('QR Code Absensi', pageWidth / 2, 20, { align: 'center' });
    doc.setLineWidth(0.5);
    doc.line(20, 25, pageWidth - 20, 25);
    doc.setFontSize(14);
    doc.setFont(undefined, 'bold');
    doc.text('Acara:', 20, 40);
    doc.setFont(undefined, 'normal');
    doc.text(currentQRData.acara, 20, 48);
    doc.setFont(undefined, 'bold');
    doc.text('Tanggal:', 20, 58);
    doc.setFont(undefined, 'normal');
    doc.text(currentQRData.tanggal, 20, 66);
    const qrSize = 100;
    const qrX = (pageWidth - qrSize) / 2;
    const qrY = 80;
    doc.addImage(qrImageData, 'PNG', qrX, qrY, qrSize, qrSize);
    doc.setFontSize(11);
    doc.setFont(undefined, 'italic');
    doc.text('Scan QR Code di atas untuk mengisi absensi', pageWidth / 2, qrY + qrSize + 15, { align: 'center' });
    const urlText = doc.splitTextToSize(currentQRData.url, pageWidth - 40);
    doc.text(urlText, pageWidth / 2, pageHeight - 20, { align: 'center' });
    doc.setFontSize(9);
    doc.setTextColor(100);
    doc.text('Generated: ' + new Date().toLocaleString('id-ID'), pageWidth / 2, pageHeight - 10, { align: 'center' });
    const filename = 'QR_Absensi_' + currentQRData.acara.replace(/[^a-z0-9]/gi, '_') + '.pdf';
    doc.save(filename);
}
// Toggle Status Kehadiran Berdasarkan Tipe Acara
document.addEventListener('DOMContentLoaded', function () {
    const radioLuring = document.getElementById('luring');
    const radioDaring = document.getElementById('daring');
    const luringGroup = document.getElementById('status-luring');
    const daringGroup = document.getElementById('status-daring');

    function updateStatusKehadiran() {
        if (radioLuring.checked) {
            luringGroup.style.display = 'block';
            daringGroup.style.display = 'none';

            // Pastikan 'hadir' dicentang (jika tidak ada nilai lama)
            const hadirCheckbox = document.getElementById('hadir');
            if (!hadirCheckbox.checked) {
                hadirCheckbox.checked = true;
            }

        } else if (radioDaring.checked) {
            luringGroup.style.display = 'none';
            daringGroup.style.display = 'block';

            // Pastikan kedua checkbox Daring dicentang
            const wfhCheckbox = document.getElementById('daring_wfh');
            const wfoCheckbox = document.getElementById('daring_wfo');
            wfhCheckbox.checked = true;
            wfoCheckbox.checked = true;
        }
    }

    radioLuring.addEventListener('change', updateStatusKehadiran);
    radioDaring.addEventListener('change', updateStatusKehadiran);

    // Inisialisasi awal
    updateStatusKehadiran();
});
</script>

<style>
#qrcode-container {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    display: inline-block;
}
#qrcode-container canvas,
#qrcode-container img {
    border: 3px solid #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
}
.modal-content {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}
#qr-info h6 {
    color: #1f2937;
    margin-bottom: 5px;
}
.btn {
    transition: all 0.3s ease;
}
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
</style>
@endsection