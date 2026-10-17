-- Desain Database v1 · Website Bandung Spiders Basketball
-- Kelompok 2 PWL · 17 Oktober 2026
-- MySQL 8.0+ (InnoDB, utf8mb4). Struktur ini nantinya ditulis ulang sebagai migration Laravel.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
CREATE DATABASE IF NOT EXISTS spiders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spiders;

-- Akun login untuk 3 peran. Orang tua dibuat saat pendaftaran diterima; coach dan manajemen dibuat oleh manajemen.
CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  peran ENUM('orang_tua','coach','manajemen') NOT NULL,
  alamat VARCHAR(255),
  aktif BOOLEAN NOT NULL DEFAULT 1,
  remember_token VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_no_hp (no_hp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Token lupa kata sandi (tabel bawaan Laravel). Masa berlaku 30 menit diatur di config/auth.php.
CREATE TABLE password_reset_tokens (
  email VARCHAR(100) NOT NULL,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP,
  PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tiga kelompok usia beserta iuran bulanannya.
CREATE TABLE kelompok_usia (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(20) NOT NULL,
  usia_min TINYINT UNSIGNED NOT NULL,
  usia_max TINYINT UNSIGNED NOT NULL,
  iuran_bulanan INT UNSIGNED NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kelompok_usia_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kelompok usia yang dipegang tiap coach. Satu coach bisa memegang lebih dari satu KU.
CREATE TABLE coach_kelompok (
  coach_id BIGINT UNSIGNED NOT NULL,
  kelompok_usia_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (coach_id, kelompok_usia_id),
  CONSTRAINT fk_coach_kelompok_coach_id FOREIGN KEY (coach_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_coach_kelompok_kelompok_usia_id FOREIGN KEY (kelompok_usia_id) REFERENCES kelompok_usia (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data anak yang sudah resmi menjadi member.
CREATE TABLE anak (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  orang_tua_id BIGINT UNSIGNED NOT NULL,
  kelompok_usia_id BIGINT UNSIGNED NOT NULL,
  nama VARCHAR(100) NOT NULL,
  tanggal_lahir DATE NOT NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  sekolah VARCHAR(100),
  hubungan_orang_tua VARCHAR(30) NOT NULL,
  status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  tanggal_masuk DATE NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_anak_orang_tua_id FOREIGN KEY (orang_tua_id) REFERENCES users (id) ON DELETE RESTRICT,
  CONSTRAINT fk_anak_kelompok_usia_id FOREIGN KEY (kelompok_usia_id) REFERENCES kelompok_usia (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Formulir pendaftaran calon member (3 langkah) sampai diterima atau ditolak manajemen.
CREATE TABLE pendaftaran (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kelompok_usia_id BIGINT UNSIGNED NOT NULL,
  nama_anak VARCHAR(100) NOT NULL,
  tanggal_lahir DATE NOT NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  sekolah VARCHAR(100) NOT NULL,
  nama_orang_tua VARCHAR(100) NOT NULL,
  hubungan VARCHAR(30) NOT NULL,
  no_whatsapp VARCHAR(20) NOT NULL,
  email VARCHAR(100) NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  status ENUM('menunggu_verifikasi','diterima','ditolak') NOT NULL DEFAULT 'menunggu_verifikasi',
  alasan_tolak VARCHAR(255),
  diverifikasi_oleh BIGINT UNSIGNED,
  diverifikasi_pada DATETIME,
  anak_id BIGINT UNSIGNED,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pendaftaran_anak_id (anak_id),
  CHECK (status <> 'ditolak' OR alasan_tolak IS NOT NULL),
  CONSTRAINT fk_pendaftaran_kelompok_usia_id FOREIGN KEY (kelompok_usia_id) REFERENCES kelompok_usia (id) ON DELETE RESTRICT,
  CONSTRAINT fk_pendaftaran_diverifikasi_oleh FOREIGN KEY (diverifikasi_oleh) REFERENCES users (id) ON DELETE SET NULL,
  CONSTRAINT fk_pendaftaran_anak_id FOREIGN KEY (anak_id) REFERENCES anak (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tiga dokumen wajib per pendaftaran (langkah 3). File disimpan di storage, database hanya menyimpan path.
CREATE TABLE dokumen_pendaftaran (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pendaftaran_id BIGINT UNSIGNED NOT NULL,
  jenis ENUM('foto_3x4','akte','kk') NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_tipe ENUM('jpg','png','pdf') NOT NULL,
  ukuran_kb INT UNSIGNED NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dok (pendaftaran_id, jenis),
  CHECK (ukuran_kb <= 2048),
  CONSTRAINT fk_dokumen_pendaftaran_pendaftaran_id FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jadwal mingguan tetap per kelompok usia.
CREATE TABLE jadwal (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kelompok_usia_id BIGINT UNSIGNED NOT NULL,
  hari TINYINT UNSIGNED NOT NULL,
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  lokasi VARCHAR(100) NOT NULL,
  berlaku_mulai DATE NOT NULL,
  aktif BOOLEAN NOT NULL DEFAULT 1,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  CHECK (hari BETWEEN 1 AND 7),
  CHECK (jam_selesai > jam_mulai),
  CONSTRAINT fk_jadwal_kelompok_usia_id FOREIGN KEY (kelompok_usia_id) REFERENCES kelompok_usia (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Satu latihan pada tanggal tertentu. Dibuat dari jadwal; dipakai untuk absensi dan untuk perubahan jadwal per tanggal.
CREATE TABLE sesi_latihan (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kelompok_usia_id BIGINT UNSIGNED NOT NULL,
  jadwal_id BIGINT UNSIGNED,
  tanggal DATE NOT NULL,
  jam_mulai TIME NOT NULL,
  jam_selesai TIME NOT NULL,
  lokasi VARCHAR(100) NOT NULL,
  status ENUM('terjadwal','diubah','batal') NOT NULL DEFAULT 'terjadwal',
  alasan_perubahan VARCHAR(255),
  diubah_oleh BIGINT UNSIGNED,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sesi (kelompok_usia_id, tanggal),
  CHECK (status = 'terjadwal' OR alasan_perubahan IS NOT NULL),
  CONSTRAINT fk_sesi_latihan_kelompok_usia_id FOREIGN KEY (kelompok_usia_id) REFERENCES kelompok_usia (id) ON DELETE RESTRICT,
  CONSTRAINT fk_sesi_latihan_jadwal_id FOREIGN KEY (jadwal_id) REFERENCES jadwal (id) ON DELETE SET NULL,
  CONSTRAINT fk_sesi_latihan_diubah_oleh FOREIGN KEY (diubah_oleh) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kehadiran satu anak di satu sesi latihan.
CREATE TABLE absensi (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sesi_latihan_id BIGINT UNSIGNED NOT NULL,
  anak_id BIGINT UNSIGNED NOT NULL,
  status ENUM('hadir','izin','alpa') NOT NULL DEFAULT 'hadir',
  dicatat_oleh BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_absensi (sesi_latihan_id, anak_id),
  CONSTRAINT fk_absensi_sesi_latihan_id FOREIGN KEY (sesi_latihan_id) REFERENCES sesi_latihan (id) ON DELETE CASCADE,
  CONSTRAINT fk_absensi_anak_id FOREIGN KEY (anak_id) REFERENCES anak (id) ON DELETE RESTRICT,
  CONSTRAINT fk_absensi_dicatat_oleh FOREIGN KEY (dicatat_oleh) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lima kategori rapor. Dibuat tabel agar urutan dan nama bisa diubah tanpa mengubah struktur.
CREATE TABLE kategori_rapor (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama VARCHAR(30) NOT NULL,
  urutan TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_kategori_rapor_nama (nama)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rapor bulanan satu anak. Hanya satu rapor per anak per bulan.
CREATE TABLE rapor (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  anak_id BIGINT UNSIGNED NOT NULL,
  coach_id BIGINT UNSIGNED NOT NULL,
  periode DATE NOT NULL,
  status ENUM('draf','terbit') NOT NULL DEFAULT 'draf',
  terbit_pada DATETIME,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rapor (anak_id, periode),
  CHECK (DAY(periode) = 1),
  CONSTRAINT fk_rapor_anak_id FOREIGN KEY (anak_id) REFERENCES anak (id) ON DELETE RESTRICT,
  CONSTRAINT fk_rapor_coach_id FOREIGN KEY (coach_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nilai dan catatan coach untuk satu kategori dalam satu rapor (5 baris per rapor terbit).
CREATE TABLE nilai_rapor (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  rapor_id BIGINT UNSIGNED NOT NULL,
  kategori_rapor_id BIGINT UNSIGNED NOT NULL,
  nilai ENUM('A+','A','A-','B+','B','B-','C+','C','C-') NOT NULL,
  catatan VARCHAR(300),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_nilai (rapor_id, kategori_rapor_id),
  CONSTRAINT fk_nilai_rapor_rapor_id FOREIGN KEY (rapor_id) REFERENCES rapor (id) ON DELETE CASCADE,
  CONSTRAINT fk_nilai_rapor_kategori_rapor_id FOREIGN KEY (kategori_rapor_id) REFERENCES kategori_rapor (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tagihan iuran bulanan per anak, dibuat sistem setiap tanggal 1.
CREATE TABLE tagihan (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  anak_id BIGINT UNSIGNED NOT NULL,
  periode DATE NOT NULL,
  nominal INT UNSIGNED NOT NULL,
  jatuh_tempo DATE NOT NULL,
  status ENUM('belum_bayar','menunggu_verifikasi','lunas','ditolak') NOT NULL DEFAULT 'belum_bayar',
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tagihan (anak_id, periode),
  CHECK (DAY(periode) = 1),
  CONSTRAINT fk_tagihan_anak_id FOREIGN KEY (anak_id) REFERENCES anak (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Setiap unggahan bukti transfer. Bukti yang ditolak tetap disimpan sebagai riwayat.
CREATE TABLE bukti_transfer (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tagihan_id BIGINT UNSIGNED NOT NULL,
  diunggah_oleh BIGINT UNSIGNED NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  file_tipe ENUM('jpg','png','pdf') NOT NULL,
  ukuran_kb INT UNSIGNED NOT NULL,
  tanggal_transfer DATE NOT NULL,
  nominal_transfer INT UNSIGNED NOT NULL,
  status ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  alasan_tolak VARCHAR(255),
  diverifikasi_oleh BIGINT UNSIGNED,
  diverifikasi_pada DATETIME,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  PRIMARY KEY (id),
  CHECK (ukuran_kb <= 2048),
  CHECK (status <> 'ditolak' OR alasan_tolak IS NOT NULL),
  CONSTRAINT fk_bukti_transfer_tagihan_id FOREIGN KEY (tagihan_id) REFERENCES tagihan (id) ON DELETE RESTRICT,
  CONSTRAINT fk_bukti_transfer_diunggah_oleh FOREIGN KEY (diunggah_oleh) REFERENCES users (id) ON DELETE RESTRICT,
  CONSTRAINT fk_bukti_transfer_diverifikasi_oleh FOREIGN KEY (diverifikasi_oleh) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifikasi di dalam website untuk orang tua (ikon lonceng).
CREATE TABLE notifikasi (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  jenis ENUM('tagihan_terbit','bukti_ditolak','iuran_lunas','rapor_terbit','jadwal_berubah') NOT NULL,
  judul VARCHAR(100) NOT NULL,
  isi VARCHAR(255) NOT NULL,
  tautan VARCHAR(255),
  dibaca_pada DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_notifikasi_user_id FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nilai yang bisa diubah manajemen tanpa mengubah kode: rekening tujuan, No. WhatsApp admin, alamat GOR, tanggal jatuh tempo.
CREATE TABLE pengaturan (
  kunci VARCHAR(50) NOT NULL,
  nilai VARCHAR(255) NOT NULL,
  updated_at TIMESTAMP,
  PRIMARY KEY (kunci)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------
-- Data awal (dari mockup v2). Akun contoh memakai kata sandi "password",
-- hanya untuk uji coba lokal; ganti sebelum dipakai klub.
-- ---------------------------------------------------------------
INSERT INTO kelompok_usia (id, nama, usia_min, usia_max, iuran_bulanan) VALUES
  (1, 'KU 6–8', 6, 8, 200000),
  (2, 'KU 9–12', 9, 12, 250000),
  (3, 'KU 13–16', 13, 16, 300000);

INSERT INTO kategori_rapor (id, nama, urutan) VALUES
  (1, 'Fundamental', 1), (2, 'Strategi', 2), (3, 'Mental', 3), (4, 'IQ Basket', 4), (5, 'Fisik', 5);

INSERT INTO pengaturan (kunci, nilai) VALUES
  ('rekening_bank', 'BCA'),
  ('rekening_nomor', '1234567890'),
  ('rekening_atas_nama', 'Bandung Spiders Basketball'),
  ('whatsapp_admin', '081234560000'),
  ('alamat_gor', 'GOR Spiders, Jl. Pajajaran No. 88, Bandung'),
  ('tanggal_jatuh_tempo', '10'),
  ('batas_verifikasi_hari_kerja', '2');

INSERT INTO users (id, nama, email, no_hp, password, peran, alamat) VALUES
  (1, 'Sari', 'admin@spiders.test', '081234560000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manajemen', NULL),
  (2, 'Dita', 'dita@spiders.test', '081200000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', NULL),
  (3, 'Rina Pratama', 'rina.pratama@gmail.com', '081234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'orang_tua', 'Jl. Setiabudi No. 21, Bandung');

INSERT INTO coach_kelompok (coach_id, kelompok_usia_id) VALUES (2, 1), (2, 2);

INSERT INTO anak (id, orang_tua_id, kelompok_usia_id, nama, tanggal_lahir, jenis_kelamin, sekolah, hubungan_orang_tua, tanggal_masuk) VALUES
  (1, 3, 2, 'Raka Pratama', '2016-03-14', 'L', 'SDN 12 Bandung', 'Ibu', '2025-01-11');

INSERT INTO jadwal (kelompok_usia_id, hari, jam_mulai, jam_selesai, lokasi, berlaku_mulai) VALUES
  (1, 6, '07:00', '08:30', 'GOR Spiders, Lapangan A', '2026-10-01'),
  (2, 6, '08:30', '10:00', 'GOR Spiders, Lapangan A', '2026-10-01'),
  (2, 7, '08:30', '10:00', 'GOR Spiders, Lapangan A', '2026-10-01'),
  (3, 6, '15:00', '17:00', 'GOR Spiders, Lapangan A', '2026-10-01'),
  (3, 7, '15:00', '17:00', 'GOR Spiders, Lapangan A', '2026-10-01');
