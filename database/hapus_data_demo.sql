-- ============================================================================
-- HAPUS DATA DEMO
-- ============================================================================
-- Data contoh (peserta ahmad.fauzi, siti.nurhaliza, rendi.pratama,
-- maya.anggraini beserta pendaftaran, pembayaran, hasil, sertifikat,
-- notifikasi) dan 3 pengumuman contoh dimasukkan untuk keperluan
-- dokumentasi/skenario demo pada Laporan Tugas Akhir (BAB IV).
--
-- Jalankan berkas ini bila ingin mengembalikan basis data ke kondisi bersih
-- sebelum sistem digunakan secara nyata:
--   mysql -u root db_peradi < database/hapus_data_demo.sql
-- ============================================================================

SET @demo := (SELECT GROUP_CONCAT(id_user) FROM users
    WHERE username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini'));

-- turunan pendaftaran milik user demo
DELETE h FROM hasil h
    JOIN pendaftaran p ON p.id_pendaftaran = h.id_pendaftaran
    JOIN users u ON u.id_user = p.id_user
    WHERE u.username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini');

DELETE s FROM sertifikat s
    JOIN pendaftaran p ON p.id_pendaftaran = s.id_pendaftaran
    JOIN users u ON u.id_user = p.id_user
    WHERE u.username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini');

DELETE pb FROM pembayaran pb
    JOIN pendaftaran p ON p.id_pendaftaran = pb.id_pendaftaran
    JOIN users u ON u.id_user = p.id_user
    WHERE u.username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini');

DELETE pk FROM pendaftaran_pkpa pk
    JOIN pendaftaran p ON p.id_pendaftaran = pk.id_pendaftaran
    JOIN users u ON u.id_user = p.id_user
    WHERE u.username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini');

DELETE pu FROM pendaftaran_upa pu
    JOIN pendaftaran p ON p.id_pendaftaran = pu.id_pendaftaran
    JOIN users u ON u.id_user = p.id_user
    WHERE u.username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini');

DELETE p FROM pendaftaran p
    JOIN users u ON u.id_user = p.id_user
    WHERE u.username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini');

DELETE n FROM notifikasi n
    JOIN users u ON u.id_user = n.id_user
    WHERE u.username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini');

DELETE FROM users
    WHERE username IN ('ahmad.fauzi','siti.nurhaliza','rendi.pratama','maya.anggraini');

-- pengumuman contoh
DELETE FROM pengumuman WHERE judul IN (
    'Jadwal Pelaksanaan PKPA Angkatan XII Tahun 2026',
    'Pengumuman Hasil Ujian Profesi Advokat (UPA) Periode Agustus 2026',
    'Pemeliharaan Sistem Terjadwal'
);
