-- =============================================================================
-- Migrasi: tambah kolom sertifikat.tanggal_terbit
-- Tanggal: 2026-09-07
-- =============================================================================
-- Kode admin/detail_pendaftaran.php sudah menyimpan & menampilkan
-- "tanggal_terbit" (dijaga columnExists), tetapi kolomnya belum ada di tabel
-- `sertifikat`. Tambahkan agar tanggal terbit sertifikat bisa dicatat terpisah
-- dari created_at (waktu unggah).
-- =============================================================================

USE db_peradi;

ALTER TABLE `sertifikat`
  ADD COLUMN `tanggal_terbit` DATE NULL AFTER `nomor_sertifikat`;
