# Lightweight CMS

Content Management System (CMS) sederhana dan ringan, dibangun dengan **PHP native** tanpa framework.

> ⚠️ **Status:** Proyek masih dalam tahap awal pengembangan.

## Teknologi

| Komponen  | Teknologi                       |
|-----------|---------------------------------|
| Backend   | PHP Native (tanpa framework)    |
| Database  | MySQL / MariaDB                 |
| Tampilan  | Bootstrap                       |

## Rencana Fitur

- [ ] Login dan logout admin
- [ ] Kelola artikel/halaman (tambah, ubah, hapus)
- [ ] Kelola kategori
- [ ] Upload gambar
- [ ] Halaman publik untuk menampilkan konten
- [ ] Pagination di artikel/post

## Kebutuhan Sistem

- PHP 8.0 atau lebih baru (dengan ekstensi `mysqli` atau `pdo_mysql`)
- MySQL 5.7+ atau MariaDB 10.3+
- Web server: Apache, Nginx, atau server bawaan PHP

## Cara Instalasi

1. **Clone repository**

   ```bash
   git clone <url-repository> lightweight-cms
   cd lightweight-cms
   ```

2. **Buat database**

   Buat database baru, misalnya `lightweight_cms`, lewat phpMyAdmin atau terminal:

   ```sql
   CREATE DATABASE lightweight_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Lalu import file struktur tabel (jika sudah tersedia di folder `database/`).

3. **Atur koneksi database**

   Salin file contoh konfigurasi, lalu isi dengan data database Anda:

   ```bash
   cp config.example.php config.php
   ```

   > File `config.php` sudah masuk `.gitignore`, jadi password database tidak akan ikut ter-upload.

4. **Jalankan aplikasi**

   Menggunakan server bawaan PHP:

   ```bash
   php -S localhost:8000
   ```

   Lalu buka <http://localhost:8000> di browser.

   Atau letakkan folder proyek di `htdocs` (XAMPP/Laragon) dan akses lewat `http://localhost/lightweight-cms`.

## Struktur Folder

```
lightweight-cms/
├── index.php        # Halaman utama
├── .gitignore       # Daftar file yang tidak dilacak Git
└── README.md        # Dokumentasi proyek
```

> Struktur akan diperbarui seiring perkembangan proyek.

## Lisensi

Belum ditentukan.
