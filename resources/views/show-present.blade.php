
@extends('layouts.guest')
@section('content')
<style>
    /* HANYA CSS YANG DIUBAH — SEMUA LOGIKA TETAP SAMA */
    body {
        background-color: #f5f7fa;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .container {
        max-width: 800px;
        width: 100%;
    }

    .card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
    }

    .page-header {
        text-align: center;
        margin-bottom: 1.5rem;
        padding: 1.25rem;
        background: white;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .page-header h3 {
        margin: 0.5rem 0;
        font-size: 1.4rem;
        color: #1e293b;
    }

    .text-muted {
        color: #64748b;
        font-size: 0.9rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }

    .btn-primary {
        background: #4f46e5;
        color: white;
    }

    .btn-primary:hover {
        background: #4338ca;
    }

    .btn-success {
        background: #10b981;
        color: white;
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-success:disabled {
        background: #00ff00ff;
        cursor: not-allowed;
    }

    .btn-outline-danger {
        border: 1px solid #df0a0aff;
        color: #df0a0aff;
        background: transparent;
    }

    .btn-outline-danger:hover {
        background: #0ff507ff;
        color: white;
    }

    .form-label {
        display: block;
        margin: 0.75rem 0 0.25rem;
        font-weight: 500;
        color: #334155;
    }

    .form-control {
        width: 100%;
        padding: 0.625rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 1rem;
    }

    .form-check-inline {
        display: inline-block;
        margin-right: 1.5rem;
        margin-top: 0.5rem;
    }

    .form-check-input {
        margin-right: 0.5rem;
    }

    #employee-info {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    #employee-info p {
        margin: 0.3rem 0;
        color: #1e40af;
    }

    #employee-info strong {
        min-width: 130px;
        display: inline-block;
        font-weight: 600;
    }

    #signature-pad {
        width: 100%;
        height: 200px;
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        background: white;
        cursor: crosshair;
        touch-action: none;
    }

    /* Basis umum untuk alert */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        font-weight: 500;
    }

    /* Untuk sukses: warna hijau */
    .alert-success {
        background: #d1fae5; /* Hijau muda */
        border: 1px solid #10b981; /* Hijau gelap */
        color: #065f46; /* Teks hijau gelap */
    }

    /* Untuk error: warna merah */
    .alert-error {
        background: #fef2f2; /* Merah muda */
        border: 1px solid #ef4444; /* Merah */
        color: #dc2626; /* Teks merah gelap */
    }

    .status-selection {
        background: #fffbeb;
        border: 1px solid #fbbf24;
    }

    input[type="file"] {
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .card, .page-header {
            padding: 1rem;
        }
        .btn-success {
            width: 100%;
            padding: 0.75rem;
        }
        .form-check-inline {
            display: block;
            margin-right: 0;
            margin-bottom: 0.5rem;
        }
    }



    /* Modal Custom */
    .modal-content {
        border-radius: 12px;
        overflow: hidden;
    }
    .modal-header .btn-close {
        margin: -0.5rem -0.5rem -0.5rem auto;
    }
    #modalBody {
        font-size: 1rem;
        color: #334155;
    }
    .modal-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }

    /* Notifikasi di atas */
    .status-alert {
        position: sticky;
        top: 1rem;
        z-index: 1050;
        margin-bottom: 1rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        animation: slideDown 0.4s ease-out;
    }

    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

@php
    $decodeJson = function($value) {
        if (is_array($value)) return $value;
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            return array_map('trim', explode(',', $value));
        }
        return [];
    };

    $tipeList = $decodeJson($present->tipe);
    $statusKehadiranList = $decodeJson($present->status_kehadiran ?? []);
    
    if (empty($statusKehadiranList)) {
        $statusKehadiranList = ['hadir'];
    }
@endphp

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- HEADER -->
            <div class="page-header text-center">
                <h3 class="fw-bold">Absensi: {{ $present->acara }}</h3>
                <p class="text-muted mb-0">Tanggal: {{ \Carbon\Carbon::parse($present->tanggal)->translatedFormat('d F Y') }}</p>
            </div>

            <!-- NOTIFIKASI STATUS ABSEN (DI ATAS) -->
            @if (session('success'))
                <div class="alert alert-success status-alert text-center">
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger status-alert text-center">
                    <strong>Gagal!</strong> {{ session('error') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning status-alert text-center" style="background:#fffbeb; border:1px solid #fbbf24; color:#92400e;">
                    <strong>Perhatian!</strong> {{ session('warning') }}
                </div>
            @endif

            <!-- MODAL NOTIFIKASI (untuk validasi & konfirmasi) -->
            <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="modalTitle">Konfirmasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-2" id="modalBody"></div>
                        <div class="modal-footer border-0" id="modalFooter"></div>
                    </div>
                </div>
            </div>

            <!-- STATUS LINK -->
            @if ($accessStatus === 'closed_manually')
                <div class="alert alert-danger text-center">
                    <h4 class="mb-0">Link ini sudah ditutup. Terima kasih.</h4>
                </div>
            @elseif ($accessStatus === 'not_yet_open')
                <div class="alert alert-warning text-center">
                    <h4 class="mb-0">Link ini akan dibuka pada pukul {{ \Carbon\Carbon::parse($waktuBuka)->format('H:i') }} WIB.</h4>
                    <p class="mb-0 mt-2">Silakan kembali lagi saat waktu absensi telah tiba.</p>
                </div>
            @else
                <!-- FORM ABSENSI -->
                <form id="attendance-form" action="{{ route('present.submit', $present->token) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="status" id="status-field" value="pegawai">

                    <!-- PILIH STATUS -->
                    <div class="card mb-4 status-selection">
                        <div class="card-body text-center">
                            <h5 class="mb-3 fw-semibold">Pilih Status Kepegawaian</h5>
                            <div class="form-check form-check-inline me-4">
                                <input class="form-check-input" type="radio" name="statusRadio" id="pegawai" value="pegawai" checked>
                                <label class="form-check-label" for="pegawai">Pegawai</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="statusRadio" id="non_pegawai" value="non_pegawai">
                                <label class="form-check-label" for="non_pegawai">Non-Pegawai</label>
                            </div>
                        </div>
                    </div>

                    <!-- FORM PEGAWAI -->
                    <div id="form-pegawai">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Data Pegawai</h5>
                                <label for="nip" class="form-label">Cari Pegawai (NIP)</label>
                                <div class="input-group mb-3">
                                    <input type="text" id="nip" class="form-control" placeholder="Contoh: 197410171997031001">
                                    <button type="button" class="btn btn-primary" id="lookup-btn">Cari</button>
                                </div>

                                <div id="loading" class="text-center" style="display:none;">
                                    <small class="text-muted">Sedang mencari data...</small>
                                </div>

                                <div id="employee-info" class="mt-3" style="display:none;">
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-4 fw-bold">NIP / No Pegawai:</div>
                                        <div class="col-12 col-md-8" id="nip-pegawai"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-4 fw-bold">Nama:</div>
                                        <div class="col-12 col-md-8" id="nama-pegawai"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-4 fw-bold">Jabatan:</div>
                                        <div class="col-12 col-md-8" id="jabatan-pegawai"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-4 fw-bold">Bagian/Fungsi:</div>
                                        <div class="col-12 col-md-8" id="substansi-pegawai"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-4 fw-bold">Unit Kerja:</div>
                                        <div class="col-12 col-md-8" id="unitkerja-pegawai">Badan POM Banjarbaru</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12 col-md-4 fw-bold">Jenis Kelamin:</div>
                                        <div class="col-12 col-md-8" id="jk-pegawai"></div>
                                    </div>
                                </div>

                                <input type="hidden" id="nip-field" name="nip">
                                <input type="hidden" id="nama-field" name="nama">
                            </div>
                        </div>
                    </div>

                    <!-- FORM NON PEGAWAI -->
                    <div id="form-non-pegawai" style="display:none;">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Data Non-Pegawai</h5>
                                <div class="mb-3">
                                    <label for="nama-non-pegawai" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="nama-non-pegawai" class="form-control" placeholder="Masukkan nama lengkap">
                                </div>
                                <div class="mb-3">
                                    <label for="nik-non-pegawai" class="form-label">NIK / NIP</label>
                                    <input type="text" id="nik-non-pegawai" class="form-control" placeholder="Masukkan NIK atau NIP jika ada">
                                </div>
                                <div class="mb-3">
                                 <label for="instansi-non-pegawai" class="form-label">Asal Instansi / Perusahaan</label>
<input type="text" id="instansi-non-pegawai" name="instansi_non_pegawai" class="form-control" placeholder="Masukkan nama instansi atau perusahaan">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label d-block">Jenis Kelamin</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="jkel" id="jkel-laki" value="Laki-laki">
                                        <label class="form-check-label" for="jkel-laki">Laki-laki</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="jkel" id="jkel-perempuan" value="Perempuan">
                                        <label class="form-check-label" for="jkel-perempuan">Perempuan</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TIPE KEHADIRAN -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <label class="card-title mb-3">Pilih Tipe Kehadiran</label>
                            <div>
                                @foreach ($statusKehadiranList as $status)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input"
                                               type="radio"
                                               name="kehadiran_status"
                                               id="status_{{ $status }}"
                                               value="{{ $status }}"
                                               {{ $loop->first ? 'checked' : '' }}>
                                        <label class="form-check-label text-capitalize" for="status_{{ $status }}">
                                            {{ ucfirst($status) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if (in_array('selfie', $tipeList))
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h5 class="card-title">Upload Daduk</h5>
                                <input type="file" name="bukti" id="bukti-file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" class="form-control">
                                <small class="text-muted d-block mt-2">
                                    Format: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX (maks 10MB)
                                </small>
                                <small class="text-danger d-block mt-1 fw-semibold">
                                    Jika Sakit / Lainnya: Upload Dokumen Pendukung
                                </small>
                            </div>
                        </div>
                    @endif

                    @if (in_array('tanda_tangan', $tipeList))
                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h5 class="card-title">Tanda Tangan Digital</h5>
                                <canvas id="signature-pad"></canvas>
                                <input type="hidden" name="signature" id="signature-data">
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-danger" id="clear-signature">Hapus Tanda Tangan</button>
                                </div>
                                <small class="text-muted mt-2 d-block">Tanda tangan di area putih di atas</small>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Perbaiki kesalahan berikut:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="text-center mt-4">
                        <button type="submit" id="submit-btn" class="btn btn-success btn-lg">
                            Submit Kehadiran
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script type="application/json" id="tipeListJson">@json($tipeList)</script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.1/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('attendance-form');
    if (!form) return;

    let tipeList = [];
    try {
        const el = document.getElementById('tipeListJson');
        if (el && el.textContent) tipeList = JSON.parse(el.textContent);
    } catch (err) { console.warn("Gagal parse tipeList:", err); }

    const submitBtn = document.getElementById('submit-btn');
    const radioPegawai = document.getElementById('pegawai');
    const radioNonPegawai = document.getElementById('non_pegawai');
    const nipInput = document.getElementById('nip');
    const lookupBtn = document.getElementById('lookup-btn');
    const loading = document.getElementById('loading');
    const employeeInfo = document.getElementById('employee-info');
    const nipField = document.getElementById('nip-field');
    const namaField = document.getElementById('nama-field');

    // Signature Pad
    let signaturePad = null;
    const canvas = document.getElementById('signature-pad');
    const clearBtn = document.getElementById('clear-signature');

    if (canvas && tipeList.includes('tanda_tangan')) {
        const ctx = canvas.getContext('2d');
        function resizeCanvas() {
            const ratio = window.devicePixelRatio || 1;
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = 200 * ratio;
            ctx.scale(ratio, ratio);
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                signaturePad.clear();
                document.getElementById('signature-data').value = '';
            });
        }
    }

    // Lookup Pegawai
    if (lookupBtn) {
        lookupBtn.addEventListener('click', async function () {
            const nip = nipInput.value.trim();
            if (!nip) {
                showNotificationModal({ title: 'Peringatan', body: 'Masukkan No Pegawai terlebih dahulu', type: 'error' });
                return;
            }

            loading.style.display = 'block';
            employeeInfo.style.display = 'none';

            try {
                const res = await fetch(`/pegawai/${encodeURIComponent(nip)}`);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();

                loading.style.display = 'none';

                if (data.success && data.data) {
                    const d = data.data;
                    document.getElementById('nip-pegawai').textContent = d.no_pegawai || nip;
                    document.getElementById('nama-pegawai').textContent = d.nama || '-';
                    document.getElementById('jabatan-pegawai').textContent = d.jabatan || (d.jabasn?.nama_jabatan ?? '-');
                    document.getElementById('substansi-pegawai').textContent = d.divisi?.nama || d.substansi || '-';
                    document.getElementById('jk-pegawai').textContent = d.jk || d.jenis_kelamin || '-';

                    nipField.value = d.no_pegawai || nip;
                    namaField.value = d.nama || '';

                    employeeInfo.style.display = 'block';
                    showNotificationModal({ title: 'Berhasil!', body: `Data <strong>${d.nama}</strong> ditemukan.`, type: 'success' });
                } else {
                    showNotificationModal({ title: 'Tidak Ditemukan', body: 'Pegawai tidak ditemukan.', type: 'error' });
                }
            } catch (err) {
                loading.style.display = 'none';
                showNotificationModal({ title: 'Kesalahan', body: 'Gagal mengambil data. Coba lagi.', type: 'error' });
            }
        });
    }

    // Toggle Form
    const formPegawai = document.getElementById('form-pegawai');
    const formNonPegawai = document.getElementById('form-non-pegawai');
    const statusField = document.getElementById('status-field');

    function toggleForm() {
        if (radioPegawai.checked) {
            formPegawai.style.display = 'block';
            formNonPegawai.style.display = 'none';
            statusField.value = 'pegawai';
        } else {
            formPegawai.style.display = 'none';
            formNonPegawai.style.display = 'block';
            statusField.value = 'non_pegawai';
        }
    }
    radioPegawai.addEventListener('change', toggleForm);
    radioNonPegawai.addEventListener('change', toggleForm);
    toggleForm();

    // Submit dengan Modal
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        let errorMessages = [];

        if (radioPegawai.checked) {
            if (!nipField.value || !namaField.value) {
                errorMessages.push('Silakan cari data pegawai terlebih dahulu');
            }
        } else {
            const namaNon = document.getElementById('nama-non-pegawai').value.trim();
            const nikNon = document.getElementById('nik-non-pegawai').value.trim();
            const instansiNon = document.getElementById('instansi-non-pegawai').value.trim();
            const jkelNon = document.querySelector('input[name="jkel"]:checked');

            if (!namaNon) errorMessages.push('Nama lengkap harus diisi');
            if (!nikNon) errorMessages.push('NIK/NIP harus diisi');
            if (!instansiNon) errorMessages.push('Asal instansi harus diisi');
            if (!jkelNon) errorMessages.push('Jenis kelamin harus dipilih');

            if (errorMessages.length === 0) {
                namaField.value = namaNon;
                nipField.value = nikNon;
            }
        }

        if (tipeList.includes('selfie')) {
            const fileInput = document.getElementById('bukti-file');
            if (!fileInput?.files.length) {
                errorMessages.push('Bukti selfie harus diunggah');
            }
        }

        if (tipeList.includes('tanda_tangan')) {
            if (!signaturePad || signaturePad.isEmpty()) {
                errorMessages.push('Tanda tangan harus diisi');
            } else {
                document.getElementById('signature-data').value = signaturePad.toDataURL();
            }
        }

        if (errorMessages.length > 0) {
            showNotificationModal({
                title: 'Data Belum Lengkap',
                body: '<ul class="text-start mb-0 ps-3">' + errorMessages.map(m => `<li>${m}</li>`).join('') + '</ul>',
                type: 'error'
            });
            return;
        }

        const namaDisplay = radioPegawai.checked ? namaField.value : document.getElementById('nama-non-pegawai').value;
        const statusDisplay = document.querySelector('input[name="kehadiran_status"]:checked').nextElementSibling.textContent.trim();

        showNotificationModal({
            title: 'Konfirmasi Kehadiran',
            body: `
                <div class="text-center">
                    <div class="modal-icon text-primary">Check</div>
                    <p class="mb-2"><strong>${namaDisplay}</strong></p>
                    <p class="text-muted small mb-3">${statusDisplay} • ${new Date().toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })}</p>
                    <p class="mb-0">Apakah data sudah benar?</p>
                </div>
            `,
            type: 'confirm',
            onConfirm: () => {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...';
                form.submit();
            }
        });
    });

    // Fungsi Modal
    function showNotificationModal({ title = 'Notifikasi', body = '', type = 'info', onConfirm = null }) {
        const modalEl = document.getElementById('notificationModal');
        const modal = new bootstrap.Modal(modalEl, { backdrop: 'static' });

        const modalTitle = modalEl.querySelector('#modalTitle');
        const modalBody = modalEl.querySelector('#modalBody');
        const modalFooter = modalEl.querySelector('#modalFooter');

        modalTitle.textContent = title;
        modalBody.innerHTML = body;

        let iconClass = 'text-info';
        if (type === 'success') iconClass = 'text-success';
        else if (type === 'error') iconClass = 'text-danger';
        else if (type === 'confirm') iconClass = 'text-primary';

        if (!body.includes('modal-icon') && type !== 'confirm') {
            modalBody.insertAdjacentHTML('afterbegin', `<div class="modal-icon ${iconClass} text-center mb-3"></div>`);
        }

        modalFooter.innerHTML = type === 'confirm' ? `
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-success" id="confirmSubmit">Ya, Submit</button>
        ` : `<button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>`;

        if (type === 'confirm') {
            modalEl.querySelector('#confirmSubmit').onclick = () => {
                modal.hide();
                if (onConfirm) onConfirm();
            };
        }

        modal.show();
    }
});
</script>
@endsection