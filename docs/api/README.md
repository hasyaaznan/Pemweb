# REST API Bandung Spiders (v1)

API JSON untuk aplikasi mobile atau client lain. Dibuat dengan pola yang sama seperti tutorial *REST API Task Management — Server*: `Route::apiResource`, Form Request untuk validasi, API Resource untuk membungkus data dalam `{"data": ...}`. Bedanya, API ini memakai login token (Laravel Sanctum) karena isinya data anak.

## Menjalankan

```bash
composer install
php artisan migrate --seed      # tabel personal_access_tokens ikut dibuat
php artisan serve --host=127.0.0.1 --port=8000
```

Base URL: `http://127.0.0.1:8000/api/v1`

Cek daftar rute: `php artisan route:list --path=api`

## Mencoba di Postman

1. Import `docs/api/spiders-api.postman_collection.json`.
2. Jalankan **0. Masuk → Masuk sebagai Admin**. Token tersimpan otomatis.
3. Jalankan request lain dari atas ke bawah. POST menyimpan id data baru, jadi GET/PUT/PATCH/DELETE berikutnya memakai data itu.

Akun contoh (kata sandi `password`): `admin@spiders.test` (Admin), `dita@spiders.test` (coach), `rina.pratama@gmail.com` (orang tua).

## Login

| Metode | Alamat | Keterangan |
|---|---|---|
| POST | `/masuk` | Body `{"login": "email atau No. HP", "password": "...", "perangkat": "Postman"}`. Jawaban berisi `token`. |
| GET | `/saya` | Akun yang sedang masuk |
| POST | `/keluar` | Mencabut token yang dipakai |

Setiap request lain mengirim header `Authorization: Bearer <token>`. Token berlaku 30 hari.

## Data master

| Data | Alamat | GET (baca) | POST, PUT, PATCH, DELETE |
|---|---|---|---|
| Member (anak + orang tua) | `/member`, `/member/{id}` | Admin: semua. Coach: anak di kelompoknya. Orang tua: anaknya sendiri | Admin |
| Kelompok usia | `/kelompok-usia`, `/kelompok-usia/{id}` | Semua peran | Admin |
| Jadwal latihan | `/jadwal`, `/jadwal/{id}` | Semua peran | Admin |
| Coach | `/coach`, `/coach/{id}` | Admin | Admin |

Filter daftar:
- `/member?status=aktif&kelompok_usia_id=2&cari=raka&per_page=20` (daftar member memakai halaman: `data`, `links`, `meta`)
- `/jadwal?kelompok_usia_id=1&aktif=semua`

### PUT dan PATCH

- **PUT** mengganti seluruh data. Semua kolom wajib dikirim.
- **PATCH** mengubah sebagian. Kirim kolom yang berubah saja, misalnya `{"status": "nonaktif"}`.

### Contoh tambah member

```bash
curl -X POST http://127.0.0.1:8000/api/v1/member \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer <token>" \
  -d '{
    "nama": "Bima Saputra", "tanggal_lahir": "2015-05-02", "jenis_kelamin": "L",
    "sekolah": "SDN 5 Bandung", "kelompok_usia_id": 2, "hubungan_orang_tua": "Ayah",
    "orang_tua": {"nama": "Andi Saputra", "email": "andi@contoh.test", "no_hp": "081299990001", "password": "rahasia123"}
  }'
```

Jika email atau No. HP orang tua sudah terdaftar, akun itu dipakai ulang (kakak-adik satu akun). `orang_tua.password` boleh dikosongkan; orang tua lalu menerima tautan email untuk membuat kata sandi sendiri, sama seperti di website.

Kolom member: `nama`, `tanggal_lahir` (YYYY-MM-DD), `jenis_kelamin` (L/P), `sekolah`, `kelompok_usia_id`, `hubungan_orang_tua` (Ibu/Ayah/Wali), `status` (aktif/nonaktif), `tanggal_masuk`, `orang_tua{nama, email, no_hp, alamat, password}`.

Kolom coach: `nama`, `email`, `no_hp`, `jabatan`, `lisensi`, `tampil_di_landing`, `aktif`, `kelompok_usia_ids` (array id), `password` (opsional). Coach yang dinonaktifkan langsung keluar dari semua perangkat.

Kolom jadwal: `kelompok_usia_id`, `hari` (1 = Senin sampai 7 = Minggu), `jam_mulai` dan `jam_selesai` (HH:MM), `lokasi`, `berlaku_mulai`, `aktif`.

## Kode jawaban

| Kode | Arti |
|---|---|
| 200 | Berhasil (GET, PUT, PATCH, DELETE) |
| 201 | Data baru dibuat (POST) |
| 401 | Belum masuk atau token tidak berlaku |
| 403 | Peran akun tidak boleh melakukan ini |
| 404 | Data tidak ditemukan, atau bukan milik akun ini |
| 405 | Metode tidak tersedia untuk alamat itu |
| 409 | Data sudah punya riwayat sehingga tidak dihapus. Nonaktifkan dengan PATCH |
| 422 | Validasi gagal. Lihat `errors` per kolom |
| 429 | Terlalu banyak permintaan |

Contoh 422:

```json
{
  "message": "nama wajib diisi. (and 6 more errors)",
  "errors": { "nama": ["nama wajib diisi."], "tanggal_lahir": ["tanggal lahir wajib diisi."] }
}
```

## Client

Folder `client/` berisi aplikasi Laravel terpisah (port 8001) yang tidak punya database member sendiri: semua data diambil dan dikirim lewat API ini, sesuai tutorial *REST API Task Management — Client*. Lihat `client/README.md`.

## Tes

`php artisan test --filter=ApiTest`
