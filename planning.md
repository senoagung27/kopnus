# MX100 — Perencanaan & dokumentasi API

Dokumen ini melengkapi [README.md](README.md): skema data, aturan bisnis, dan kontrak REST (request/response). Koleksi Postman: [`postman/MX100.postman_collection.json`](postman/MX100.postman_collection.json).

**Basis URL:** `{APP_URL}/api` — contoh `http://localhost:8000/api` atau `http://localhost:8080/api` (Docker).

Semua endpoint JSON disarankan memakai header `Accept: application/json`. Route yang memerlukan login memakai `Authorization: Bearer {token}` (Laravel Sanctum).

---

## Skema database

### `users`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| name | string | |
| email | string, unique | |
| email_verified_at | timestamp, nullable | |
| password | string | di-hash |
| role | enum | `employer`, `freelancer`, `superadmin` (nilai terakhir dari migrasi tambahan) |
| remember_token | string, nullable | |
| created_at / updated_at | timestamps | |

**Relasi:** employer `hasMany` `jobs` (`employer_id`); freelancer `hasMany` `job_applications` (`freelancer_id`).

### `jobs` (lowongan MX100, bukan antrian Laravel)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| employer_id | FK → users | on delete cascade |
| title | string | |
| description | text | |
| status | enum | `draft`, `published` |
| published_at | timestamp, nullable | diisi saat status published |
| created_at / updated_at | timestamps | |

**Indeks / constraint:** FK `employer_id`.

### `job_applications` (lamaran / CV per lowongan)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| job_id | FK → jobs | on delete cascade |
| freelancer_id | FK → users | on delete cascade |
| cover_letter | text | wajib |
| cv_file_path | string, nullable | path relatif storage `public` jika upload file |
| created_at / updated_at | timestamps | |

**Constraint unik:** `(job_id, freelancer_id)` — maksimal satu lamaran per freelancer per lowongan.

### `personal_access_tokens` (Sanctum)

Tabel bawaan Sanctum untuk token API.

---

## Aturan bisnis

1. **Pemberi kerja (employer)** dapat membuat/mengubah/menghapus lowongan miliknya. Status **`draft`** atau **`published`**. Simpan draft = `status: draft` (tanpa `published_at`). Publish = set `status: published` dan `published_at`, baik lewat body update maupun endpoint `POST .../publish`.
2. **Freelancer** hanya melihat lowongan **`published`** (daftar & detail). Detail lowongan draft → **404**.
3. **Freelancer** hanya dapat mengirim **satu** lamaran per lowongan published (unik DB + validasi di `ApplicationService`). Percobaan kedua → **422** dengan error pada field `job`.
4. **Employer** melihat lamaran/CV hanya untuk lowongan yang **employer_id**-nya sama dengan user yang login (`GET .../employer/jobs/{job}/applications`).
5. **superadmin** (seed): middleware peran mengizinkan akses route employer & freelancer; `Gate::before` mengizinkan semua policy. Daftar lowongan employer tetap difilter `employer_id` = user login (lihat README).

---

## Autentikasi

| Method | Path | Auth |
|--------|------|------|
| POST | `/v1/register` | Tidak |
| POST | `/v1/login` | Tidak |
| POST | `/v1/logout` | Sanctum |
| GET | `/v1/user` | Sanctum |

### Register — request

```json
{
  "name": "Nama",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "employer"
}
```

`role` wajib `employer` atau `freelancer`.

### Register / Login — response (contoh)

```json
{
  "message": "Registered successfully.",
  "data": {
    "user": { "id": 1, "name": "...", "email": "...", "role": "employer" },
    "token": "1|xxxxxxxx"
  }
}
```

Login mengembalikan struktur serupa dengan `token` untuk header Bearer.

---

## Employer (`role:employer`, prefix `/v1/employer`)

Middleware: `auth:sanctum` + `role:employer` (superadmin dikecualikan dari pembatasan peran).

| Method | Path | Keterangan |
|--------|------|------------|
| GET | `/employer/jobs` | Daftar lowongan milik user (paginate) |
| POST | `/employer/jobs` | Buat lowongan |
| GET | `/employer/jobs/{id}` | Detail |
| PUT/PATCH | `/employer/jobs/{id}` | Update |
| DELETE | `/employer/jobs/{id}` | Hapus |
| POST | `/employer/jobs/{id}/publish` | Publish |
| GET | `/employer/jobs/{id}/applications` | Daftar lamaran (CV + cover letter + data freelancer ringkas) |

### POST `/employer/jobs` — body (contoh draft)

```json
{
  "title": "Backend Developer",
  "description": "Membangun API dengan Laravel.",
  "status": "draft"
}
```

`status` opsional; default `draft`. Untuk langsung published: `"status": "published"` ( `published_at` di-set otomatis).

### Respons sukses create (201)

```json
{
  "message": "Job created.",
  "data": {
    "id": 1,
    "title": "...",
    "description": "...",
    "status": "draft",
    "published_at": null,
    "employer": { "id": 1, "name": "...", "email": "..." },
    "created_at": "...",
    "updated_at": "..."
  }
}
```

### GET `/employer/jobs/{id}/applications` — response (ringkas)

```json
{
  "message": "OK",
  "data": [
    {
      "id": 1,
      "cover_letter": "...",
      "cv_file_path": "cvs/xxx.pdf",
      "created_at": "...",
      "freelancer": { "id": 2, "name": "...", "email": "..." }
    }
  ],
  "meta": { "total": 1, ...pagination }
}
```

**403** jika lowongan bukan milik employer tersebut.

---

## Freelancer (`role:freelancer`)

Middleware: `auth:sanctum` + `role:freelancer`.

| Method | Path | Keterangan |
|--------|------|------------|
| GET | `/v1/jobs` | Hanya lowongan **published** |
| GET | `/v1/jobs/{id}` | Detail published; draft → **404** |
| POST | `/v1/jobs/{id}/applications` | Kirim lamaran (sekali per lowongan) |

### POST `/v1/jobs/{id}/applications`

- **JSON:** `Content-Type: application/json`, body `{ "cover_letter": "..." }`
- **Multipart (opsional CV file):** `cover_letter` + file field `cv` → disimpan di disk `public` (`storage/app/public/cvs/...`); jalankan `php artisan storage:link`.

**422** jika sudah pernah apply atau lowongan tidak published.

---

## Pemisahan logika bisnis

- **`App\Services\JobService`:** create, update, publish (status + `published_at`).
- **`App\Services\ApplicationService`:** validasi published + unik satu kali apply, lalu create `JobApplication`.
- **Policies:** `JobPolicy`, `JobApplicationPolicy` (+ `Gate::before` untuk superadmin).
- **Controllers:** tipis — validasi Form Request, authorize, panggil service, Resource JSON.

---

## Sample data

Jalankan `php artisan migrate --seed`. Akun contoh (sandi: `password`) dan skenario draft/published/lamaran dijelaskan di README.

---

## Pengujian otomatis

```bash
./vendor/bin/phpunit
```

Berkas utama: `tests/Feature/Mx100ApiTest.php` (auth, listing published, apply sekali, employer melihat lamaran, publish, otorisasi).
