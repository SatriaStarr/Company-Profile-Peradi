-- =============================================================================
-- Migrasi: dukungan Payment Gateway (Midtrans) pada tabel pembayaran
-- Tanggal: 2026-09-07
-- =============================================================================
-- Menambah kolom untuk menyimpan data transaksi gateway TANPA menghapus kolom
-- pembayaran manual (bukti_pembayaran, status, jumlah, tanggal_bayar).
--
-- metode:            'manual'  = transfer bank + upload bukti (alur lama, tetap ada)
--                    'midtrans'= via payment gateway
-- order_id:          ID order unik yang dikirim ke Midtrans (UNIQUE)
-- transaction_id:    ID transaksi dari Midtrans
-- payment_type:      jenis pembayaran (bank_transfer, gopay, qris, credit_card, ...)
-- transaction_status:status mentah dari Midtrans (pending/settlement/capture/
--                    deny/cancel/expire/failure/refund)
-- fraud_status:      accept/deny/challenge
-- gross_amount:      nominal yang ditagih gateway
-- snap_token:        token Snap (opsional, untuk popup)
-- midtrans_response: JSON respons terakhir (audit/debug)
-- paid_at:           waktu pembayaran sukses menurut gateway
-- =============================================================================

USE db_peradi;

ALTER TABLE `pembayaran`
  ADD COLUMN `metode` ENUM('manual','midtrans') NOT NULL DEFAULT 'manual' AFTER `id_pendaftaran`,
  ADD COLUMN `order_id` VARCHAR(100) NULL AFTER `metode`,
  ADD COLUMN `transaction_id` VARCHAR(100) NULL AFTER `order_id`,
  ADD COLUMN `payment_type` VARCHAR(50) NULL AFTER `transaction_id`,
  ADD COLUMN `transaction_status` VARCHAR(30) NULL AFTER `payment_type`,
  ADD COLUMN `fraud_status` VARCHAR(20) NULL AFTER `transaction_status`,
  ADD COLUMN `gross_amount` DECIMAL(15,2) NULL AFTER `fraud_status`,
  ADD COLUMN `snap_token` VARCHAR(255) NULL AFTER `gross_amount`,
  ADD COLUMN `midtrans_response` TEXT NULL AFTER `snap_token`,
  ADD COLUMN `paid_at` DATETIME NULL AFTER `midtrans_response`,
  ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
  ADD UNIQUE KEY `uniq_order_id` (`order_id`);

-- Tambah status 'kadaluarsa' & 'dibatalkan' agar bisa memetakan expire/cancel Midtrans.
-- 'menunggu_verifikasi' dipakai juga untuk status "pending" gateway.
ALTER TABLE `pembayaran`
  MODIFY `status` ENUM(
    'belum_bayar',
    'menunggu_verifikasi',
    'pending',
    'lunas',
    'ditolak',
    'kadaluarsa',
    'dibatalkan'
  ) NOT NULL DEFAULT 'belum_bayar';
