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

Sesuaikan `DB_*` di **`.env`** (bukan hanya `.env.example`). Default di contoh: user **`mx100`**, bukan `root`.

Buat database dan user (jalankan di klien `mysql` — di Mac/Homebrew coba `mysql -u root` atau `/opt/homebrew/opt/mysql/bin/mysql -u root`; di Linux sering `sudo mysql -u root`):

```sql
CREATE DATABASE IF NOT EXISTS mx100 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- PHP dengan DB_HOST=127.0.0.1 menghubungi MySQL sebagai host 127.0.0.1 (bukan "localhost"). Buat user untuk kedua host:
CREATE USER 'mx100'@'127.0.0.1' IDENTIFIED BY 'ganti_sandi_anda';
CREATE USER 'mx100'@'localhost' IDENTIFIED BY 'ganti_sandi_anda';
GRANT ALL PRIVILEGES ON mx100.* TO 'mx100'@'127.0.0.1';
GRANT ALL PRIVILEGES ON mx100.* TO 'mx100'@'localhost';
FLUSH PRIVILEGES;
```

(Jika `CREATE USER` gagal karena user sudah ada, lanjut ke `GRANT` saja atau `ALTER USER ... IDENTIFIED BY ...` untuk menyamakan sandi.)

```env
DB_HOST=127.0.0.1
DB_DATABASE=mx100
DB_USERNAME=mx100
DB_PASSWORD=ganti_sandi_anda
```

#### SQLSTATE 1698 — `Access denied for user 'root'@'localhost'`

Artinya Laravel masih membaca **`DB_USERNAME=root`** di `.env`, atau `root` di server Anda **tidak boleh** login dari PHP (plugin `unix_socket` / MariaDB).

**Yang wajib:** ubah `.env` ke user khusus (mis. `mx100`) + sandi seperti di atas — **jangan memakai `root` untuk aplikasi**.

Setelah mengubah `.env`, jalankan lagi:

```bash
php artisan config:clear
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
| superadmin@mx100.test | superadmin |
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
