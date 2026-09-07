-- =============================================================================
-- Migrasi: lengkapi kolom tabel pendaftaran_upa sesuai formulir UPA
-- Tanggal: 2026-09-07
-- =============================================================================
-- MASALAH: formulir pendaftaran_upa.php mengumpulkan ~32 field data + 5 file,
-- tetapi tabel `pendaftaran_upa` hanya punya 11 kolom. Akibatnya
-- proses_pendaftaran_upa.php INSERT ke kolom yang tidak ada -> pendaftaran UPA
-- SELALU GAGAL (rollback). Tabel diselaraskan dengan formulir.
--
-- Kolom `nik` dibuat NULL (identitas dipakai `nomor_identitas`; `nik` diisi
-- sama dengan nomor_identitas oleh kode saat jenis = KTP).
-- =============================================================================

USE db_peradi;

ALTER TABLE `pendaftaran_upa`
  MODIFY `nik` VARCHAR(30) NULL,
  MODIFY `pekerjaan` VARCHAR(100) NULL,
  ADD COLUMN `nama_lengkap_gelar` VARCHAR(200) NULL AFTER `nama_lengkap`,
  ADD COLUMN `jenis_identitas` VARCHAR(20) NULL AFTER `nama_lengkap_gelar`,
  ADD COLUMN `nomor_identitas` VARCHAR(30) NULL AFTER `jenis_identitas`,
  ADD COLUMN `masa_berakhir_identitas` VARCHAR(30) NULL AFTER `nomor_identitas`,
  ADD COLUMN `jenis_kelamin` VARCHAR(20) NULL AFTER `masa_berakhir_identitas`,
  ADD COLUMN `email` VARCHAR(150) NULL AFTER `no_hp`,
  ADD COLUMN `rt` VARCHAR(10) NULL AFTER `alamat`,
  ADD COLUMN `rw` VARCHAR(10) NULL AFTER `rt`,
  ADD COLUMN `no_rumah` VARCHAR(20) NULL AFTER `rw`,
  ADD COLUMN `kelurahan` VARCHAR(100) NULL AFTER `no_rumah`,
  ADD COLUMN `kecamatan` VARCHAR(100) NULL AFTER `kelurahan`,
  ADD COLUMN `kota` VARCHAR(100) NULL AFTER `kecamatan`,
  ADD COLUMN `provinsi` VARCHAR(100) NULL AFTER `kota`,
  ADD COLUMN `kode_pos` VARCHAR(10) NULL AFTER `provinsi`,
  ADD COLUMN `kota_ujian` VARCHAR(100) NULL AFTER `kode_pos`,
  ADD COLUMN `dpc_dipilih` VARCHAR(150) NULL AFTER `kota_ujian`,
  ADD COLUMN `kebutuhan_khusus` VARCHAR(20) NULL AFTER `dpc_dipilih`,
  ADD COLUMN `keterangan_kebutuhan` TEXT NULL AFTER `kebutuhan_khusus`,
  ADD COLUMN `tahun_lulus` VARCHAR(4) NULL AFTER `keterangan_kebutuhan`,
  ADD COLUMN `no_ijazah` VARCHAR(100) NULL AFTER `tahun_lulus`,
  ADD COLUMN `fakultas` VARCHAR(150) NULL AFTER `no_ijazah`,
  ADD COLUMN `nama_universitas` VARCHAR(200) NULL AFTER `fakultas`,
  ADD COLUMN `nama_sertifikat_pkpa` VARCHAR(200) NULL AFTER `nama_universitas`,
  ADD COLUMN `nomor_sertifikat_pkpa` VARCHAR(100) NULL AFTER `nama_sertifikat_pkpa`,
  ADD COLUMN `tanggal_sertifikat_pkpa` DATE NULL AFTER `nomor_sertifikat_pkpa`,
  ADD COLUMN `pelaksana_pkpa` VARCHAR(200) NULL AFTER `tanggal_sertifikat_pkpa`,
  ADD COLUMN `foto` VARCHAR(255) NULL AFTER `pelaksana_pkpa`,
  ADD COLUMN `foto_ijazah` VARCHAR(255) NULL AFTER `foto`,
  ADD COLUMN `sertifikat_pkpa` VARCHAR(255) NULL AFTER `foto_ijazah`;
