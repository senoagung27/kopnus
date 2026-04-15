# MX100 — Job Portal API (Laravel)

REST API untuk portal pekerjaan MX100: perusahaan memposting lowongan (draft atau published), freelancer melihat lowongan yang dipublish dan mengirim lamaran (maksimal sekali per lowongan), perusahaan melihat lamaran per lowongan. Autentikasi memakai **Laravel Sanctum** (Bearer token).

## Fitur singkat

- Registrasi / login dengan peran **employer** atau **freelancer**
- CRUD lowongan (employer), publish, daftar lamaran per lowongan
- Daftar & detail lowongan published (freelancer), kirim lamaran
- Akun **superadmin** (seed) untuk uji akses penuh ke route employer & freelancer (lihat bagian **Peran & otorisasi** di bawah)

## Persyaratan

| Komponen | Catatan |
|----------|---------|
| PHP | **8.1+** (`composer.json`). Image Docker memakai **PHP 8.4** FPM. |
| Composer | 2.x |
| Ekstensi | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, dll. (standar Laravel) |
| Database | MySQL 8.x (atau kompatibel) untuk produksi/lokal; PHPUnit memakai SQLite in-memory |

## Instalasi

Pilih salah satu metode:

- **A. Install via Docker Compose** (direkomendasikan untuk environment konsisten)
- **B. Install tanpa Docker (Composer + MySQL lokal)** (jika ingin jalan langsung di host)

### A. Install via Docker Compose

Stack: **PHP-FPM** (`app`), **Nginx**, **MySQL 8**, **Adminer** (opsional UI database).

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

| Akses | URL / koneksi |
|-------|----------------|
| API (Nginx) | `http://localhost:8080` — prefix API: `/api` → contoh `http://localhost:8080/api/v1/login` |
| Port API | Ubah mapping dengan `APP_PORT` di `.env` (default **8080**) |
| Adminer | `http://localhost:8082` (default) — server: **`mysql`** atau **`db`**, kredensial = `DB_*` yang dipakai Compose |
| MySQL dari host (TablePlus, dll.) | Host `127.0.0.1`, port **`33060`** (default `MYSQL_PORT`) |

Di dalam kontainer `app`, `DB_HOST` biasanya **`mysql`** (service Compose). Alias jaringan **`db`** disediakan agar cocok dengan konvensi nama host seperti Laravel Sail.

File terkait: [`Dockerfile`](Dockerfile), [`docker-compose.yml`](docker-compose.yml), [`docker/nginx/default.conf`](docker/nginx/default.conf).

### B. Install tanpa Docker (Composer + MySQL lokal)

```bash
composer install
cp .env.example .env   # jika belum ada .env
php artisan key:generate
```

Sesuaikan `DB_*` di **`.env`**. Default di `.env.example` memakai user **`mx100`**, bukan `root`.

Buat database dan user MySQL (contoh di klien `mysql`):

```sql
CREATE DATABASE IF NOT EXISTS mx100 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- PHP dengan DB_HOST=127.0.0.1 memakai TCP; buat user untuk host yang dipakai:
CREATE USER 'mx100'@'127.0.0.1' IDENTIFIED BY 'ganti_sandi_anda';
CREATE USER 'mx100'@'localhost' IDENTIFIED BY 'ganti_sandi_anda';
GRANT ALL PRIVILEGES ON mx100.* TO 'mx100'@'127.0.0.1';
GRANT ALL PRIVILEGES ON mx100.* TO 'mx100'@'localhost';
FLUSH PRIVILEGES;
```

```env
DB_HOST=127.0.0.1
DB_DATABASE=mx100
DB_USERNAME=mx100
DB_PASSWORD=ganti_sandi_anda
```

#### SQLSTATE 1698 — `Access denied for user 'root'@'localhost'`

Laravel masih memakai **`DB_USERNAME=root`** atau user `root` tidak cocok untuk koneksi dari PHP. **Gunakan user aplikasi khusus** (mis. `mx100`) seperti di atas — jangan memakai `root` untuk aplikasi.

#### Artisan di host + MySQL di Docker

Jika MySQL jalan di **Docker Compose** (port host biasanya **33060**), set di `.env`:

- `DB_HOST=127.0.0.1`
- `DB_PORT=33060` (bukan `3306` kecuali MySQL lokal Anda memang di 3306)
- `DB_PASSWORD` harus sama dengan sandi user DB yang dipakai container saat volume pertama kali diinisialisasi

```bash
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
```

Jalankan API lokal:

```bash
php artisan serve
```

Basis URL: **`http://localhost:8000/api`** (sesuaikan host/port jika berbeda).

## Autentikasi

1. `POST /api/v1/register` atau `POST /api/v1/login`
2. Ambil `data.token` dari respons JSON
3. Header: `Authorization: Bearer {token}` dan `Accept: application/json`

## Peran & otorisasi

| Peran | Akses utama |
|-------|-------------|
| **employer** | Prefix `/api/v1/employer/...` (CRUD lowongan, publish, lamaran) |
| **freelancer** | `/api/v1/jobs`, lamaran ke lowongan published |
| **superadmin** | Boleh memanggil route employer dan freelancer (middleware + policy). Daftar lowongan di `GET /api/v1/employer/jobs` tetap difilter `employer_id` = user yang login — akun superadmin seed biasanya **tanpa lowongan**, jadi daftar bisa kosong; untuk melihat data contoh gunakan login **employer@mx100.test**. |

Registrasi publik hanya untuk peran **employer** atau **freelancer** (bukan superadmin).

## Ringkasan endpoint (`/api` + `/v1/...`)

Semua route JSON di bawah memerlukan header `Accept: application/json` jika diuji manual.

**Publik**

| Method | Path | Keterangan |
|--------|------|------------|
| POST | `/v1/register` | Daftar |
| POST | `/v1/login` | Login |

**Perlu `auth:sanctum`**

| Method | Path | Middleware peran |
|--------|------|-------------------|
| POST | `/v1/logout` | — |
| GET | `/v1/user` | — |
| * | `/v1/employer/jobs` … | `employer` |
| * | `/v1/jobs` … (listing freelancer) | `freelancer` |

Detail lengkap skema DB, aturan bisnis, dan urutan endpoint: [planning.md](planning.md).

## Postman

1. Impor [postman/MX100.postman_collection.json](postman/MX100.postman_collection.json)
2. Impor [postman/MX100.postman_environment.json](postman/MX100.postman_environment.json) — default `base_url` = **`http://localhost:8080/api`** (Docker). Untuk `php artisan serve` ganti ke **`http://localhost:8000/api`**.
3. Pilih environment **MX100 Local** (atau sesuaikan `base_url`).
4. **Dokumentasi lengkap** ada di koleksi: di Postman buka koleksi MX100 lalu tab **Documentation** / panel overview — deskripsi koleksi berisi tabel endpoint, kode status, contoh respons; setiap folder (Auth / Employer / Freelancer) dan setiap request punya penjelasan method, body, dan error umum. Request **Login** dan **Register** menyertakan skrip **Tests** yang menyalin `data.token` ke variabel `token` (environment + collection).
5. Set `job_id` ke ID lowongan nyata (seed atau hasil **Create job**) sebelum memanggil path `.../jobs/{{job_id}}/...`.

## Sample data (seeder)

```bash
php artisan db:seed
```

Password semua akun contoh: **`password`**.

| Email | Role |
|-------|------|
| superadmin@mx100.test | superadmin |
| employer@mx100.test | employer |
| hr@startup.test | employer |
| budi@freelancer.test | freelancer |
| siti@freelancer.test | freelancer |

Untuk **employer@mx100.test** seeder menambah **10 lowongan dummy** bertanda **`[Seed]`** (8 published, 2 draft) selain contoh bawaan — berguna untuk mengisi `GET /api/v1/employer/jobs`.

## Troubleshooting singkat

| Gejala | Penyebab umum |
|--------|----------------|
| `ECONNREFUSED` ke `localhost:8000` | Tidak ada `php artisan serve`, atau Anda memakai Docker — gunakan **`http://localhost:8080`**. |
| `403` + `"Forbidden."` | Token valid tetapi peran salah (mis. freelancer memanggil route employer). Superadmin sudah diizinkan melewati pembatasan peran; pastikan token terbaru. |
| Daftar employer kosong saat login superadmin | Perilaku normal: query memakai `employer_id` user yang login; pakai **employer@mx100.test** untuk melihat lowongan seed. |

## Pengujian

```bash
./vendor/bin/phpunit
```

PHPUnit memakai **SQLite in-memory** lewat [phpunit.xml](phpunit.xml). Aplikasi berjalan normal memakai MySQL dari `.env`.

## Catatan queue

Tabel `jobs` dipakai untuk **lowongan MX100**. Driver antrian default adalah `sync`. Jika Anda memakai driver `database` untuk queue, migrasi bawaan Laravel untuk tabel antrian juga bernama `jobs` dan dapat bentrok — gunakan **redis** / **sqs**, atau sesuaikan nama tabel antrian di konfigurasi.

## Struktur kode (utama)

- `app/Http/Controllers/Api/` — controller API (auth, employer, freelancer)
- `app/Services/` — logika bisnis (publish job, submit lamaran)
- `app/Policies/` — otorisasi per model
- `app/Http/Requests/Api/` — validasi input
- `app/Http/Resources/` — format JSON respons
- `routes/api.php` — definisi route `/api/v1/...`

## Lisensi

MIT (kerangka Laravel; tambahkan catatan proyek sesuai kebutuhan Anda).
