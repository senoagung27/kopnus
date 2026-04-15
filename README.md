# MX100 — Job Portal API (Laravel)

REST API untuk portal pekerjaan MX100: perusahaan memposting lowongan (draft atau published), freelancer melihat lowongan yang dipublish dan mengirim CV (maksimal sekali per lowongan), perusahaan melihat lamaran per lowongan.

## Persyaratan

- PHP 8.1+
- Composer
- Ekstensi PHP umum Laravel (openssl, pdo, mbstring, tokenizer, xml, ctype, json)
- MySQL 8.x (atau kompatibel MariaDB)
- Ekstensi PHP `pdo_mysql`

## Instalasi cepat

```bash
composer install
cp .env.example .env   # jika belum ada .env
php artisan key:generate
```

Sesuaikan `DB_*` di `.env` (lihat [.env.example](.env.example)). Default: **MySQL** — buat database kosong terlebih dahulu, misalnya:

```sql
CREATE DATABASE mx100 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Pastikan layanan MySQL berjalan dan kredensial (`DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`) benar.

Jalankan migrasi dan seeder:

```bash
php artisan migrate --seed
php artisan storage:link
```

Jalankan server pengembangan:

```bash
php artisan serve
```

Basis URL API: `http://localhost:8000/api` (sesuaikan host/port).

## Autentikasi

API memakai **Laravel Sanctum** (token Bearer).

1. `POST /api/v1/register` atau `POST /api/v1/login`
2. Ambil `data.token` dari respons JSON
3. Kirim header: `Authorization: Bearer {token}` dan `Accept: application/json`

## Peran pengguna

- **employer** — akses route di bawah `/api/v1/employer/...`
- **freelancer** — akses `/api/v1/jobs` dan submit lamaran

## Dokumentasi endpoint

Ringkasan lengkap skema DB, aturan bisnis, dan daftar endpoint ada di [planning.md](planning.md).

## Postman

1. Impor [postman/MX100.postman_collection.json](postman/MX100.postman_collection.json)
2. Impor environment [postman/MX100.postman_environment.json](postman/MX100.postman_environment.json) (sesuaikan `base_url`)
3. Setelah login/register, salin token ke variabel environment `token` (atau isi manual di header koleksi)

## Sample data (seeder)

Setelah `php artisan db:seed`, gunakan akun berikut (password **`password`**):

| Email | Role |
|-------|------|
| employer@mx100.test | employer |
| hr@startup.test | employer |
| budi@freelancer.test | freelancer |
| siti@freelancer.test | freelancer |

## Pengujian

```bash
./vendor/bin/phpunit
```

Pengujian PHPUnit memakai **SQLite in-memory** lewat [phpunit.xml](phpunit.xml) agar tidak perlu database MySQL khusus untuk tes; aplikasi berjalan tetap memakai MySQL dari `.env`.

## Catatan queue

Tabel `jobs` dipakai untuk **lowongan MX100**. Driver antrian default di `.env` adalah `sync`. Jika Anda beralih ke `database`, migrasi bawaan Laravel untuk antrian juga bernama `jobs` dan akan bentrok — gunakan `redis` / `sqs`, atau sesuaikan nama tabel antrian di konfigurasi.

## Struktur kode (utama)

- `app/Http/Controllers/Api/` — controller API (auth, employer, freelancer)
- `app/Services/` — logika bisnis (job publish, submit lamaran)
- `app/Policies/` — otorisasi per model
- `app/Http/Requests/Api/` — validasi input
- `app/Http/Resources/` — format JSON respons
- `routes/api.php` — definisi route `/api/v1/...`

## Lisensi

MIT (kerangka Laravel; tambahkan catatan proyek sesuai kebutuhan Anda).
