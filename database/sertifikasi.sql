CREATE DATABASE IF NOT EXISTS db_peradi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_peradi;

CREATE TABLE IF NOT EXISTS pendaftaran (
  id_pendaftaran INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  jenis ENUM('PKPA','UPA') NOT NULL,
  tanggal DATE NOT NULL,
  status ENUM('menunggu_verifikasi','berkas_diperiksa','menunggu_pembayaran','pembayaran_diperiksa','terdaftar','selesai','ditolak') NOT NULL DEFAULT 'menunggu_verifikasi',
  catatan_admin TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX(id_user), INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS berkas (
  id_berkas INT AUTO_INCREMENT PRIMARY KEY,
  id_pendaftaran INT NOT NULL,
  jenis_berkas VARCHAR(100) NOT NULL,
  file VARCHAR(255) NOT NULL,
  status ENUM('menunggu','valid','ditolak') NOT NULL DEFAULT 'menunggu',
  keterangan TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pendaftaran) REFERENCES pendaftaran(id_pendaftaran) ON DELETE CASCADE,
  INDEX(id_pendaftaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pembayaran (
  id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
  id_pendaftaran INT NOT NULL,
  metode VARCHAR(50) NOT NULL DEFAULT 'QRIS',
  jumlah DECIMAL(15,2) NOT NULL DEFAULT 0,
  bukti_bayar VARCHAR(255) NULL,
  status ENUM('belum_bayar','menunggu_verifikasi','lunas','ditolak') NOT NULL DEFAULT 'belum_bayar',
  tanggal_bayar DATETIME NULL,
  catatan TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pendaftaran) REFERENCES pendaftaran(id_pendaftaran) ON DELETE CASCADE,
  UNIQUE KEY uniq_pendaftaran(id_pendaftaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS hasil (
  id_hasil INT AUTO_INCREMENT PRIMARY KEY,
  id_pendaftaran INT NOT NULL,
  nilai DECIMAL(5,2) NULL,
  status_lulus ENUM('Lulus','Tidak Lulus','Belum Diumumkan') NOT NULL DEFAULT 'Belum Diumumkan',
  catatan TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pendaftaran) REFERENCES pendaftaran(id_pendaftaran) ON DELETE CASCADE,
  UNIQUE KEY uniq_hasil(id_pendaftaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sertifikat (
  id_sertifikat INT AUTO_INCREMENT PRIMARY KEY,
  id_pendaftaran INT NOT NULL,
  nomor_sertifikat VARCHAR(100) NOT NULL,
  file_sertifikat VARCHAR(255) NOT NULL,
  tanggal_terbit DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pendaftaran) REFERENCES pendaftaran(id_pendaftaran) ON DELETE CASCADE,
  UNIQUE KEY uniq_sertifikat(id_pendaftaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifikasi (
  id_notifikasi INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  judul VARCHAR(150) NOT NULL,
  pesan TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(id_user, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jika tabel users sudah ada dari project lama, pastikan memiliki kolom email/status/created_at.
-- Jalankan ALTER berikut hanya jika kolom tersebut belum ada.
-- ALTER TABLE users ADD COLUMN email VARCHAR(150) NULL AFTER username;
-- ALTER TABLE users ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER role;
-- ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
