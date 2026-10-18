# Website Bandung Spiders Basketball

Website akademi basket Bandung Spiders. Dikerjakan oleh Kelompok 2, mata kuliah Pemrograman Web Lanjut:
Reza Zesariana Raihansyah (231131005), Hasya Nanda Azmi (231171003), Fadilah Gahara Putra (231171001), Putri Amalia (231111006).

- **Backend:** Laravel 12 (PHP 8.2+), MySQL 8 / MariaDB 10.11
- **Tampilan:** Blade, Tailwind CSS 4, Alpine.js
- **Dokumen:** Dokumen Kebutuhan v2 dan Desain Database v1. ERD dan SQL ada di folder `docs/`.

## Menjalankan di komputer sendiri

Yang perlu ada: PHP 8.2+, Composer, Node.js 20+, dan MySQL/MariaDB (misalnya lewat Laragon atau XAMPP).

```bash
composer install
npm install
cp .env.example .env          # di Windows: copy .env.example .env
php artisan key:generate
```

Buat database kosong bernama `spiders`, lalu isi `DB_USERNAME` dan `DB_PASSWORD` di file `.env` sesuai MySQL di komputer kamu.

```bash
php artisan migrate --seed    # membuat 17 tabel + data contoh
npm run dev                   # jalankan di terminal pertama
php artisan serve             # jalankan di terminal kedua, lalu buka http://localhost:8000
```

Akun contoh (kata sandi `password`, hanya untuk uji coba lokal):

| Peran | Email |
|---|---|
| Manajemen | admin@spiders.test |
| Coach | dita@spiders.test |
| Orang tua | rina.pratama@gmail.com (atau No. HP 081234567890) |

Seeder juga mengisi data contoh untuk Raka (anak Bu Rina), dihitung mundur dari tanggal hari ini: 20 latihan terakhir dengan kehadiran 85%, rapor bulan lalu, dan tiga tagihan (lunas, perlu unggah ulang, belum bayar). Lihat `database/seeders/DataDemoSeeder.php`.

## Halaman yang sudah jadi

| Layar mockup | Alamat | Isi |
|---|---|---|
| L0 Masuk | /masuk | Masuk dengan email atau No. HP, satu akun untuk semua peran |
| L16 Lupa kata sandi | /lupa-kata-sandi | Kirim tautan atur ulang lewat email, berlaku 30 menit |
| L1 Beranda orang tua | /orang-tua | Rapor terbaru, kehadiran 3 bulan, tagihan yang perlu dibayar |
| L2 Rapor, L17 Absensi | /orang-tua/rapor, /orang-tua/absensi | 5 kategori dengan catatan coach, riwayat latihan |
| L3 Tagihan, L4 Unggah bukti, L26 Bukti terkirim | /orang-tua/tagihan | Rekening klub, status tiap bulan, unggah bukti JPG/PNG/PDF maks. 2 MB |
| L19 Profil | /orang-tua/profil | Data akun, WhatsApp admin, keluar |

Halaman coach dan manajemen masih berupa halaman sementara.

## Test

```bash
php artisan test
```

Test memakai SQLite di memori, jadi tidak butuh MySQL. Aturan CHECK (contoh: file maks. 2 MB) hanya aktif di MySQL/MariaDB.

## Struktur database

Semua tabel memakai nama bahasa Indonesia, sesuai Desain Database v1. Karena itu setiap model menulis `$table` secara eksplisit, misalnya `Anak` memakai tabel `anak`. Migration dan model dibuat dari satu sumber yang sama dengan dokumen desain database, jadi kalau struktur tabel berubah, ubah dokumennya juga.
