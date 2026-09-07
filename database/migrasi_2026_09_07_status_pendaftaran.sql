-- =============================================================================
-- Migrasi: selaraskan enum pendaftaran.status dengan alur kerja aplikasi
-- Tanggal: 2026-09-07
-- =============================================================================
-- MASALAH:
--   Kolom `pendaftaran`.`status` sebelumnya = ENUM('menunggu','diproses',
--   'diterima','ditolak','selesai'), TAPI seluruh aplikasi (admin & user:
--   dashboard, data_pendaftaran, detail_pendaftaran, user/functions.php)
--   memakai alur 7 status: menunggu_verifikasi -> berkas_diperiksa ->
--   menunggu_pembayaran -> pembayaran_diperiksa -> terdaftar -> selesai
--   (atau ditolak). Akibatnya saat admin mengubah status ke nilai yang tidak
--   ada di enum, MySQL menyimpannya sebagai '' (string kosong) -> alur kerja
--   pendaftaran tidak bisa berjalan.
--
-- SOLUSI:
--   Ubah enum agar memuat seluruh status alur kerja. 'menunggu' dipertahankan
--   sebagai alias lama supaya baris/insert lama tidak error; kode insert
--   pendaftaran diubah memakai 'menunggu_verifikasi'.
--
-- DAMPAK:
--   - Tidak menghapus kolom apa pun.
--   - Baris dengan status '' diperbaiki menjadi 'menunggu_verifikasi'.
--   - Kompatibel dengan pembayaran manual maupun payment gateway.
-- =============================================================================

USE db_peradi;

ALTER TABLE `pendaftaran`
  MODIFY `status` ENUM(
    'menunggu',
    'menunggu_verifikasi',
    'berkas_diperiksa',
    'menunggu_pembayaran',
    'pembayaran_diperiksa',
    'terdaftar',
    'selesai',
    'ditolak'
  ) NOT NULL DEFAULT 'menunggu_verifikasi';

-- Perbaiki baris yang statusnya kosong akibat bug lama
UPDATE `pendaftaran`
SET `status` = 'menunggu_verifikasi'
WHERE `status` = '' OR `status` IS NULL;

-- Normalisasi 'menunggu' lama -> 'menunggu_verifikasi' (opsional, aman)
UPDATE `pendaftaran`
SET `status` = 'menunggu_verifikasi'
WHERE `status` = 'menunggu';
