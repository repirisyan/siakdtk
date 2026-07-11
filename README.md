Sistem Informasi Akademik TK (SIAKDTK)

Sistem Informasi Akademik berbasis Laravel, Vue 3, Inertia.js, dan MySQL yang dirancang untuk membantu pengelolaan administrasi, akademik, keuangan, dan komunikasi antara sekolah dengan orang tua siswa.

Fitur Utama

Manajemen Pengguna

* Login dan autentikasi pengguna
* Verifikasi email
* Reset password melalui email
* Upload foto profil
* Kelola User (Admin)
* Role Based Access Control

Role yang tersedia:

* Admin
* Staff Akademik
* Staff Administrasi
* Guru
* Kepala Sekolah
* Orangtua Siswa

⸻

Pendaftaran Siswa Online

* Registrasi siswa secara online
* Multi Step Form (Stepper)
* Upload dokumen pendukung
* Status pendaftaran:
    * Menunggu Verifikasi
    * Disetujui
    * Ditolak
* Approval oleh Admin atau Staff Akademik
* Penempatan kelas setelah proses approval

⸻

Kelola Data Siswa

* Data siswa lengkap
* Data orang tua
* Data wali
* Riwayat kelas
* Mutasi kelas
* Pemindahan siswa antar kelas
* Status aktif dan nonaktif

⸻

Kelola Data Guru

* Data guru lengkap
* NIP
* NUPTK
* Pendidikan
* Status kepegawaian
* Status aktif dan nonaktif

⸻

Kelola Kelas

* Kelola kelas
* Tahun ajaran
* Status aktif dan nonaktif
* Pemindahan seluruh siswa ke kelas lain

⸻

Kelola Tema Pembelajaran

* Kelola tema pembelajaran
* Status aktif dan nonaktif
* Digunakan pada jadwal dan rapor

⸻

Kelola Jadwal

* Jadwal belajar
* Guru pengajar
* Tema pembelajaran
* Kelas

⸻

Absensi Siswa

* Absensi per jadwal
* Status:
    * Hadir
    * Izin
    * Sakit
    * Alfa
* Catatan ketidakhadiran
* Riwayat absensi

⸻

Penilaian Harian

* Penilaian berdasarkan absensi
* Hanya siswa hadir yang dapat dinilai
* Catatan perkembangan siswa
* Dokumentasi kegiatan

⸻

Rapor Siswa

* Penilaian per tema
* Catatan perkembangan siswa
* Validasi rapor oleh Kepala Sekolah
* Validasi:
    * Per siswa
    * Massal
* Riwayat rapor

⸻

Keuangan dan SPP

Kelola SPP

* Generate tagihan berdasarkan:
    * Kelas
    * Tahun ajaran
* Nominal fleksibel
* Cicilan pembayaran
* Riwayat pembayaran
* Status pembayaran
* Sisa tagihan

Pembayaran SPP

Metode pembayaran:

* Upload bukti transfer
* Midtrans (Sandbox)

Bukti Pembayaran

* PDF
* JPG
* JPEG
* PNG
* WEBP

File gambar akan:

* Dikompresi
* Dikonversi ke format WEBP

Notifikasi Tagihan

* Email per siswa
* Email massal
* Berdasarkan filter
* Menggunakan Queue Laravel

⸻

Portal Orang Tua

Satu akun orang tua dapat memiliki lebih dari satu siswa.

Fitur:

* Melihat seluruh anak yang terdaftar
* Melihat rapor anak
* Melihat tagihan anak
* Melakukan pembayaran
* Melihat riwayat pembayaran
* Menambah data anak

⸻

Kelola Konten Website

* Berita
* Galeri
* Event
* Pengumuman

Konten ditampilkan pada landing page sekolah.

⸻

Landing Page Sekolah

* Responsive
* Dark Mode
* Light Mode
* Data sekolah dinamis
* Berita terbaru
* Event sekolah
* Galeri kegiatan
* Informasi pendaftaran

⸻

Pengaturan Sekolah

Dapat diakses oleh Admin.

Pengaturan meliputi:

* Nama sekolah
* Logo
* Tagline
* Visi
* Misi
* Sejarah sekolah
* Alamat
* Kontak
* Website
* Status pendaftaran

⸻

Dashboard

Admin

* Total siswa aktif
* Total guru aktif
* Total kelas aktif
* Total jadwal hari ini
* Total tema aktif
* Statistik siswa per tahun ajaran
* Statistik keuangan

Staff Akademik

* Total siswa aktif
* Total kelas aktif
* Total guru aktif
* Total jadwal hari ini

Staff Administrasi

* Statistik pembayaran
* Statistik tagihan
* Monitoring keuangan

Kepala Sekolah

* Statistik akademik
* Statistik keuangan
* Validasi rapor

Orang Tua

* Total anak
* Total tagihan
* Total pembayaran
* Total rapor tersedia

⸻

Teknologi

Backend

* Laravel 12
* PHP 8.3+
* MySQL 8+

Frontend

* Vue 3
* TypeScript
* Inertia.js
* Tailwind CSS
* Shadcn Vue
* ApexCharts

Integrasi

* Midtrans Sandbox
* Laravel Queue
* Laravel Mail
* Storage Public

⸻

Instalasi

Clone repository:

git clone https://github.com/username/siakdtk.git
cd siakdtk

Install dependency:

composer install
npm install

Copy environment:

cp .env.example .env

Generate key:

php artisan key:generate

Konfigurasi database pada file .env.

Jalankan migrasi:

php artisan migrate --seed

Buat symbolic link storage:

php artisan storage:link

Jalankan aplikasi:

php artisan serve
npm run dev

⸻

Queue Worker

Sistem menggunakan Queue Laravel untuk:

* Verifikasi email
* Reset password
* Notifikasi SPP
* Email massal
* Notifikasi sistem

Jalankan worker:

php artisan queue:work

⸻

Default Roles

* Admin
* Staff Akademik
* Staff Administrasi
* Guru
* Kepsek
* Orangtua Siswa

⸻

Security

* Password hashing
* Email verification
* CSRF protection
* Role-based authorization
* File validation
* Queue-based email delivery

⸻

License

Project ini dikembangkan untuk kebutuhan Sistem Informasi Akademik Taman Kanak-Kanak (TK) dan dapat disesuaikan sesuai kebutuhan institusi pendidikan masing-masing.
