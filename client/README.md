# Spiders API Client

Aplikasi Laravel terpisah yang mengelola **member** Bandung Spiders sepenuhnya lewat REST API proyek utama (`/api/v1`). Client ini tidak punya tabel member sendiri; setiap halaman memanggil API dengan `Illuminate\Support\Facades\Http`, sama seperti tutorial *REST API Task Management — Client*.

| Halaman | Request ke API |
|---|---|
| Masuk | `POST /masuk` (token disimpan di session) |
| Daftar member | `GET /member` |
| Detail member | `GET /member/{id}` |
| Tambah member | `POST /member` |
| Ubah member | `PUT /member/{id}` |
| Tombol Aktifkan/Nonaktifkan | `PATCH /member/{id}` |
| Tombol Hapus | `DELETE /member/{id}` |
| Keluar | `POST /keluar` |

## Menjalankan

Terminal 1, di folder proyek utama (`spiders-web`):

```bash
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2, di folder `client`:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --host=127.0.0.1 --port=8001
```

Buka http://127.0.0.1:8001 lalu masuk dengan `admin@spiders.test` / `password`. Akun coach atau orang tua juga bisa masuk, tapi hanya bisa melihat member miliknya (aturan dari API).

Alamat API diatur di `.env`: `API_SERVER_URL=http://127.0.0.1:8000/api/v1`. Setelah mengubahnya jalankan `php artisan config:clear`.

Kode utama: `app/Services/SpidersApiService.php`, `app/Http/Controllers/MemberController.php`, `resources/views/member/`.

## Tes

`php artisan test` (memakai `Http::fake`, tidak perlu server API hidup).
