# MX100 — Perencanaan & Spesifikasi API

## Gambaran umum

MX100 adalah portal pekerjaan yang menghubungkan perusahaan (pemberi kerja) dengan freelancer. API ini menyediakan:

- **Pemberi kerja (employer)**: membuat lowongan, menyimpan sebagai **draft** atau **published**, dan melihat **CV/lamaran** per lowongan.
- **Freelancer**: melihat lowongan yang **sudah dipublish**, mengirim **satu CV per lowongan** (satu kali per kombinasi freelancer + job).

## Stack & asumsi

- **Framework**: Laravel 10, PHP 8.1+
- **Autentikasi API**: Laravel Sanctum (Bearer token)
- **Database**: **MySQL** untuk lingkungan lokal/produksi (lihat `.env`); migrasi pakai Eloquent schema builder
- **CV**: teks wajib `cover_letter`; opsional unggah file `cv` (pdf/doc/docx) disimpan di `storage/app/public/cvs` (akses via `php artisan storage:link`)

## Skema database

### `users`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | PK |
| name | string | |
| email | string | unique |
| password | string | di-hash |
| role | enum | `employer` \| `freelancer` |
| timestamps | | |

### `jobs`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | PK |
| employer_id | FK → users | pemilik lowongan |
| title | string | |
| description | text | |
| status | enum | `draft` \| `published` |
| published_at | timestamp nullable | diisi saat publish |
| timestamps | | |

### `job_applications`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | PK |
| job_id | FK → jobs | |
| freelancer_id | FK → users | |
| cover_letter | text | isi CV utama |
| cv_file_path | string nullable | path relatif disk `public` |
| timestamps | | |
| **UNIQUE** | (job_id, freelancer_id) | maksimal satu lamaran per freelancer per lowongan |

## Aturan bisnis (ringkas)

1. Hanya lowongan `published` yang tampil di daftar & detail freelancer; lowongan `draft` tidak boleh dilihat freelancer (404 pada detail).
2. Freelancer hanya boleh mengirim lamaran jika job `published`, dan **tidak boleh** mengirim kedua kalinya untuk job yang sama (validasi + unique DB).
3. Employer hanya mengelola job miliknya dan hanya melihat lamaran untuk job miliknya.

## Endpoint REST (`/api/v1`)

Prefix global dari Laravel: `/api` (lihat `app/Providers/RouteServiceProvider.php`); grup versi: `/v1`.

### Publik

| Metode | Path | Deskripsi |
|--------|------|-----------|
| POST | `/api/v1/register` | Body: `name`, `email`, `password`, `password_confirmation`, `role` (`employer` \| `freelancer`) |
| POST | `/api/v1/login` | Body: `email`, `password` → mengembalikan token Sanctum |

### Butuh `Authorization: Bearer {token}`

| Metode | Path | Peran | Deskripsi |
|--------|------|--------|-----------|
| POST | `/api/v1/logout` | semua | Cabut token saat ini |
| GET | `/api/v1/user` | semua | Profil + role |

#### Employer

| Metode | Path | Deskripsi |
|--------|------|-----------|
| GET | `/api/v1/employer/jobs` | Daftar semua job milik employer (draft + published) |
| POST | `/api/v1/employer/jobs` | Buat job; opsional `status` draft/published |
| GET | `/api/v1/employer/jobs/{job}` | Detail job milik sendiri |
| PUT/PATCH | `/api/v1/employer/jobs/{job}` | Update |
| DELETE | `/api/v1/employer/jobs/{job}` | Hapus |
| POST | `/api/v1/employer/jobs/{job}/publish` | Set status published + `published_at` |
| GET | `/api/v1/employer/jobs/{job}/applications` | Daftar lamaran/CV untuk job tersebut |

#### Freelancer

| Metode | Path | Deskripsi |
|--------|------|-----------|
| GET | `/api/v1/jobs` | Hanya job `published` (paginasi) |
| GET | `/api/v1/jobs/{job}` | Detail job jika published; jika tidak → 404 |
| POST | `/api/v1/jobs/{job}/applications` | Body: `cover_letter` (wajib), `cv` (opsional file) |

### Format respons (umum)

- Sukses: `message` + `data` (objek atau paginasi Laravel: `data`, `links`, `meta`).
- Validasi gagal: HTTP 422 + `errors` per field.
- Terlarang policy: HTTP 403.
- Tidak ditemukan: HTTP 404.

## Dokumentasi tambahan

- Koleksi Postman: [postman/MX100.postman_collection.json](postman/MX100.postman_collection.json) dan environment [postman/MX100.postman_environment.json](postman/MX100.postman_environment.json)
- README proyek: [README.md](README.md) (setup, migrate, seed, pengujian)

## Sample data

Jalankan `php artisan db:seed`. Akun contoh (password `password`):

- `employer@mx100.test`, `hr@startup.test` — employer  
- `budi@freelancer.test`, `siti@freelancer.test` — freelancer  

Tersedia job draft, job published, dan lamaran contoh.
