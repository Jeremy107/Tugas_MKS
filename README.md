# Aplikasi Web Verifikasi Unduhan PDF Bebras Challenge 2025

Aplikasi web sederhana berbasis HTML, CSS, dan JavaScript (dengan PHP opsional untuk server-side, namun implementasi utama hanya client-side) yang digunakan untuk mengunduh file PDF daftar peserta Bebras Challenge 2025 secara aman berdasarkan verifikasi kode 4 digit dari nomor kontak pendamping.

## Fitur

*   **Tampilan Daftar Sekolah:** Menampilkan daftar sekolah dari file `data_sekolah.json`.
*   **Daftar Pendamping:** Menampilkan daftar pendamping per sekolah.
*   **Verifikasi Kode:** Meminta pengguna memasukkan 4 digit terakhir nomor kontak pendamping sebelum mengunduh file PDF.
*   **Unduhan Langsung:** File PDF diunduh langsung ke komputer pengguna setelah verifikasi berhasil, tanpa membuka PDF di tab baru.
*   **Responsive Design:** Antarmuka yang relatif mudah digunakan di berbagai ukuran layar.

## Struktur Proyek

*   `index.php`: Halaman utama web yang menampilkan daftar sekolah dan pendamping.
*   `style.css`: File stylesheet untuk tampilan halaman web.
*   `data_sekolah.json`: File data berisi informasi sekolah, pendamping, kode verifikasi, dan nama file PDF.
*   `pdf_files/`: Folder yang berisi file-file PDF yang siap diunduh. *(Harus diisi secara manual)*
*   `README.md`: File dokumentasi ini.

## Prasyarat

*   Server web yang mendukung PHP (jika ingin menggunakan versi yang mengakses file lokal langsung dari server, meskipun versi utama tidak memerlukannya).
*   Browser web modern di sisi klien untuk menjalankan JavaScript dan memicu unduhan.

## Instalasi

1.  **Unggah File:** Unggah semua file ke folder root hosting web Anda (misalnya, `public_html`).
2.  **Upload PDF:** Letakkan semua file PDF hasil dari Google Colab ke dalam folder `pdf_files/`. Pastikan nama file PDF **sama persis** dengan yang tercantum di `data_sekolah.json` pada kunci `pdf_file`.
3.  **Sesuaikan Data (Opsional):** Jika nama file PDF atau data lainnya berubah, sesuaikan file `data_sekolah.json` secara manual.
4.  **Akses Web:** Buka URL hosting Anda (misalnya, `https://namahosting.com/`) di browser.

## Cara Kerja

1.  Pengguna mengunjungi halaman web.
2.  Mereka melihat daftar sekolah dan pendamping yang terkait.
3.  Pengguna memilih tombol "Download PDF" di samping nama pendamping yang relevan.
4.  Modal verifikasi muncul, meminta 4 digit terakhir nomor kontak pendamping tersebut.
5.  Pengguna memasukkan kode 4 digit yang benar.
6.  Jika kode benar, browser secara otomatis memulai proses pengunduhan file PDF untuk sekolah tersebut.
7.  Jika kode salah, muncul pesan kesalahan.

## Catatan Penting

*   **Kode Verifikasi:** Kode verifikasi didasarkan pada 4 digit terakhir dari kolom `No Kontak Pendamping` di file Excel sumber.
*   **Keamanan Client-Side:** Implementasi ini menggunakan verifikasi di sisi klien (JavaScript). Artinya, kode verifikasi sebenarnya *dapat dilihat* oleh pengguna dalam source code halaman web. Ini cocok untuk kontrol akses dasar, bukan keamanan ketat.
*   **Nama File PDF:** Sangat penting bahwa nama file PDF dalam folder `pdf_files/` **sama persis** dengan nama yang tercantum di `data_sekolah.json`.
*   **Format JSON:** File `data_sekolah.json` harus dalam format JSON yang valid.
*   **Fetch API:** Fungsi unduhan bergantung pada `fetch` API JavaScript, yang didukung oleh sebagian besar browser modern.

## Pembuatan File PDF (Google Colab)

File `index.php` dan `data_sekolah.json` dirancang untuk bekerja dengan pasangan file PDF yang dihasilkan oleh skrip Google Colab. Skrip Colab membaca file Excel `Manajemen Siswa - BebrasWeb (1).xlsx`, mengelompokkan data berdasarkan `Sekolah`, dan membuat satu file PDF per sekolah, mencantumkan semua pendamping dan kontak yang terkait dalam satu file tersebut. File `data_sekolah.json` juga dihasilkan untuk mencocokkan struktur ini.

## Lisensi

Copyright (c) 2025 [Niskarto / Bebras Biro USU]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
