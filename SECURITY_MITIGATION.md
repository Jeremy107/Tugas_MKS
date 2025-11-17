# Laporan Mitigasi Keamanan

Tanggal: 2025-11-17

## Ringkasan

Aplikasi menampilkan daftar sekolah dan mengizinkan unduhan PDF setelah memasukkan kode verifikasi (4 digit). Versi awal melakukan verifikasi sepenuhnya di sisi klien (JavaScript) dan mengekspos kode verifikasi melalui HTML/JSON, sehingga kontrol dapat dengan mudah dibypass dan file PDF dapat diunduh tanpa verifikasi. Perbaikan berfokus pada pemindahan verifikasi ke sisi server, memblokir akses langsung ke `pdf_files/`, dan mencegah eksposur data sensitif.

## Temuan Utama

- Informasi sensitif terekspos:
  - `data_sekolah.json` dapat diakses langsung via HTTP dan memuat `verification_codes` (plaintext). Siapa pun bisa membaca kode.
- Verifikasi di sisi klien:
  - `index.php` menyisipkan nilai kode verifikasi ke input tersembunyi dan memvalidasi di JavaScript. Hal ini dapat dibypass (misal via DevTools, custom fetch, atau permintaan langsung ke URL PDF).
- Akses langsung ke PDF:
  - File pada `pdf_files/` dapat diakses langsung tanpa kontrol (hotlinking/bypass), karena tidak ada aturan pembatasan server.
- Potensi injeksi/rekayasa DOM:
  - Parameter yang disisipkan ke handler JS tidak diamankan dengan cukup ketat.
- Validasi input terbatas:
  - Tidak ada validasi server-side terhadap input (sekolah/pendamping/kode), tidak ada logging, dan tidak ada pembatasan jalur file (path traversal mitigasi).

## Perbaikan yang Diterapkan

1. Verifikasi dipindahkan ke sisi server:
   - Tambah endpoint `download.php` yang memverifikasi triplet `(school, pendamping, code)` terhadap data server, lalu mengalirkan PDF jika valid.
   - Validasi input (format kode 4 digit) dan pencocokan data case-insensitive dengan index sejajar antara `pendamping` dan `verification_codes`.
2. Blokir akses langsung:
   - Tambah `.htaccess` di root untuk memblokir akses HTTP ke `data_sekolah.json`.
   - Tambah `.htaccess` di `pdf_files/` dengan `Require all denied` untuk mencegah akses langsung ke PDF.
3. Hardening `index.php`:
   - Hapus penyisipan `expected_code`/verifikasi sisi klien dan proses fetch langsung ke PDF.
   - Form verifikasi sekarang `POST` ke `download.php` dan tangani unduhan dari server.
   - Gunakan `htmlspecialchars` dan `json_encode` (HEX flags) untuk menyisipkan nilai dengan aman ke atribut/onlick JS.
4. Validasi & Keamanan File:
   - `download.php` menggunakan `realpath` dan pemeriksaan prefix untuk mencegah path traversal.
   - Set header paksa unduhan (`Content-Disposition`) dan `X-Content-Type-Options: nosniff`.
5. Logging server-side:
   - Catat event sukses/gagal ke `logs/access.log` (timestamp, IP, sekolah, pendamping, status).

## Dampak Perubahan

- UI tetap sama (modal verifikasi). Alur unduh berubah: form dikirim ke server, bukan lagi fetch file di klien.
- Pengguna tidak lagi melihat kode verifikasi tersisip di HTML/JS.
- Upaya akses langsung ke PDF akan ditolak (403), sehingga unduhan harus melalui `download.php`.

## Rekomendasi Lanjutan

- Hashing kode verifikasi:
  - Simpan `verification_codes` sebagai hash (mis. bcrypt/argon2) dan verifikasi dengan `password_verify` agar plaintext tidak tersimpan.
- Pindahkan data ke luar webroot:
  - Letakkan data (JSON/konfigurasi) di luar direktori yang dapat diakses web, atau gunakan database.
- Rate limiting / throttling:
  - Terapkan pembatasan percobaan dan cooldown per IP/ sesi untuk mencegah brute force 4 digit.
- Audit & monitoring:
  - Putar log secara berkala, pantau percobaan gagal yang berulang.
- CSRF token (opsional):
  - Walau operasi ini non-idempotent terhadap data, token CSRF dapat ditambahkan untuk mengurangi eksploitasi form lintas situs.
- Validasi ketat input:
  - Terapkan whitelist karakter untuk `school` dan `pendamping` atau gunakan ID internal untuk menghindari ketergantungan pada string bebas.
- Header keamanan tambahan:
  - Tambah `Content-Security-Policy` dan `Referrer-Policy` pada aplikasi untuk memperkuat mitigasi XSS/klikjacking.

## File yang Diubah/Ditambahkan

- Diubah: `index.php` – hilangkan verifikasi klien, kirim POST ke server, sanitasi output.
- Ditambah: `download.php` – verifikasi server-side dan unduh PDF aman.
- Ditambah: `.htaccess` (root) – blokir `data_sekolah.json`, nonaktifkan directory listing.
- Ditambah: `pdf_files/.htaccess` – blokir akses langsung ke PDF.
- Ditambah: `logs/access.log` (terbuat saat runtime) – log akses unduhan.

## Cara Uji Cepat

1. Buka `index.php`, pilih pendamping, masukkan kode 4 digit yang benar → file terunduh.
2. Coba akses `pdf_files/NAMA.pdf` langsung di browser → ditolak.
3. Coba buka `data_sekolah.json` langsung → ditolak.
4. Masukkan kode salah → mendapat pesan gagal (HTTP 401).
