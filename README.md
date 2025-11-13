# MagangBpomBjb

Sistem manajemen kehadiran modern dengan fitur lengkap untuk mencatat dan melaporkan absensi peserta acara. Dibangun menggunakan Laravel 7 dengan teknologi terdepan untuk pengalaman pengguna yang optimal.

Fitur Utama dari web ini :
1. Generate Link Absensi
- Buat link unik untuk setiap acara
- Support tipe acara: Luring (offline) & Daring (online)
- Verifikasi dual-method: Selfie + Tanda Tangan Digital
- Kustomisasi status kehadiran dinamis
- Generate QR Code otomatis
- Download QR Code dalam format PDF
---------------------------------------------
2. Pengisian Absensi
- Form absensi user-friendly dengan 2 opsi:
Pegawai: Lookup otomatis dari database
Non-Pegawai: Input data manual
- Real-time validasi data pegawai 
- Upload bukti selfi/dokumen (JPG, PNG, PDF, DOC, XLS)
- Digital signature pad dengan canvas
- Modal konfirmasi sebelum submit
- Error messaging yang jelas
----------------------------------------------
3. Dashboard Laporan
- Filter laporan berdasarkan:
Nama acara (search)
Rentang waktu (per tahun, bulan, tanggal)
- Daftar semua acara dengan status
- Akses cepat ke detail kehadiran
- Pagination otomatis
- -----------------------------------------------
4. Detail Kehadiran
- Tabel komprehensif dengan 10+ kolom data
- Filter berdasarkan:
Jenis kehadiran (Hadir, Sakit, Izin, dll)
Divisi/Departemen
- Sorting by NIP/Nama (click header)
- Preview & download bukti (selfie)
- Preview & download tanda tangan digital
- Tampil gambar thumbnail in-line
- Sticky header table saat scroll
  ----------------------------------------------
5. Export & Cetak
- Cetak PDF: Format formal dengan header/footer
- Export Excel: File XLSX dengan embedded images
- Print Langsung: Dari browser (Ctrl+P)
- Filter dan sort terbawa saat export
  ----------------------------------------------
6. Profil Pegawai
- Pencarian pegawai dengan autocomplete
- Real-time AJAX search (min 2 karakter)
- Tampil biodata lengkap pegawai
- Riwayat kehadiran dengan pagination
- Export profil + kehadiran ke Excel
- Excel styling dengan tema corporate
------------------------------------------------
Teknologi yang Digunakan :
A.Backend
-Laravel 7/8 - PHP Web Framework
-MySQL/MariaDB - Database
-Laravel Eloquent ORM - Database abstraction
-Blade Templating - View rendering
------------------------------------------------
B.Frontend
-Bootstrap 5.3 - UI Framework
-jQuery 3.6 - JavaScript library
-Select2 4.0 - Enhanced select
-Font Awesome 6.0 - Icons
-Signature Pad 4.0 - Digital signature
--------------------------------------------------
C.Export & Report
-XLSX (SheetJS) - Excel export
-jsPDF 2.5 - PDF generation
-QRCode.js 1.0 - QR Code generation
-JSZip 3.10 - ZIP compression
---------------------------------------------------
Step 1: Clone Repository
1) git clone https://github.com/bbpom/sistem-absensi.git
cd sistem-absensi
--------------------------------------------------
Step 2: Install Dependencies
2) composer install
npm install
---------------------------------------------------
Step 3: Setup Environment
3) cp .env.example .env
php artisan key:generate

konfigura .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_bbpom
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://localhost:8000
-------------------------------------------------
Step 4: Database migration 
4) php artisan migrate --seed
-------------------------------------------------
Step 5: Buat Symbolic Link Storage
5) php artisan storage:link
----------------------------------------------
Step 6: Jalankan server
6) php artisan serve
Akses : http://localhost:8000
---------------------------------------------------
STRUKTUR FILE :
sistem-absensi/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PresentController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── ReportController.php
│   │   │   ├── PegawaiController.php
│   │   │   └── ExportController.php
│   │   └── Requests/
│   │       ├── StorePresent.php
│   │       ├── SubmitAttendance.php
│   │       └── SearchPegawai.php
│   ├── Models/
│   │   ├── Present.php
│   │   ├── Attendance.php
│   │   ├── Pegawai.php
│   │   ├── Jabatan.php
│   │   └── Divisi.php
│   └── Exports/
│       ├── AttendanceExport.php
│       └── PegawaiExport.php
│
├── resources/
│   └── views/
│       ├── present.blade.php          # Generate link
│       ├── show-present.blade.php     # Form absensi
│       ├── report/
│       │   ├── report.blade.php        # Dashboard
│       │   ├── report_detail.blade.php # Detail table
│       │   ├── report_print_pdf.blade.php
│       │   └── report_print_excel.blade.php
│       ├── profil.blade.php           # Profil pegawai
│       └── layouts/
│           └── app.blade.php
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
│   ├── app/
│   │   └── public/
│   │       ├── bukti/        # Selfie uploads
│   │       └── signatures/   # Digital signatures
│   └── logs/
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── public/
│   ├── css/
│   ├── js/
│   └── images/
│
└── .env

1.) Generate link absen :
Login → Present Page
  ↓
Input: Nama Acara, Tanggal, Lokasi
  ↓
Pilih: Tipe Acara (Luring/Daring)
  ↓
Pilih: Verifikasi (Selfie & TTD)
  ↓
Pilih: Status Kehadiran tersedia
  ↓
Klik: Generate Link Absensi
  ↓
Generate → QR Code + Link unik
  ↓
Tampilkan hasil + Tombol Download QR PDF

2.) User isi absen
Buka link/scan QR code
  ↓
Cek Status: Terbuka/Belum/Tertutup?
  ↓
Pilih: Pegawai atau Non-Pegawai
  ↓
Jika Pegawai:
  ├─ Input NIP → Search AJAX
  ├─ Tampil data dari database
  └─ Validate otomatis
  
Jika Non-Pegawai:
  ├─ Input manual: Nama, NIK, Instansi
  └─ Pilih Jenis Kelamin
  ↓
Pilih: Status Kehadiran (Hadir/Sakit/dll)
  ↓
Upload: Bukti selfie (jika required)
  ↓
Tanda Tangan: Digital signature di canvas
  ↓
Validasi semua field
  ↓
Modal Konfirmasi
  ↓
Submit → Simpan ke database

3.) Lihat Laporan 
Login → Report Dashboard
  ↓
Cari nama acara atau pilih rentang waktu
  ↓
Terapkan filter
  ↓
Lihat daftar acara hasil filter
  ↓
Klik Detail pada acara
  ↓
Lihat tabel kehadiran dengan 10+ kolom
  ↓
Filter berdasarkan: Status / Divisi
  ↓
Sort by NIP/Nama (click header)
  ↓
Preview bukti/TTD (thumbnail)
  ↓
Download bukti/TTD
  ↓
Cetak PDF atau Export Excel



DEVELOPER 
NAMA : MUHAMMAD AZMI ANSHARI 
ASAL : Universitas Islam Kadiri di Kediri atau Universitas Islam Kalimantan Muhammad Arsyad Al Banjari di Banjarmasin
