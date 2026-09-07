<?php
/*
|--------------------------------------------------------------------------
| Helper pembayaran gateway — dipakai webhook & halaman cek status
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/midtrans.php';
require_once __DIR__ . '/notifikasi_helper.php';

/**
 * Terapkan hasil transaksi Midtrans ke DB secara idempoten.
 * $notif = array dari webhook ATAU dari midtrans_get_status().
 *
 * @return array ['ok'=>bool, 'status_lokal'=>string, 'changed'=>bool, 'pesan'=>string]
 */
function pembayaran_terapkan_notif(mysqli $conn, array $notif): array
{
    $order_id = (string) ($notif['order_id'] ?? '');
    if ($order_id === '') {
        return ['ok' => false, 'status_lokal' => '', 'changed' => false, 'pesan' => 'order_id kosong'];
    }

    /* ambil baris pembayaran + pendaftaran terkait (lock) */
    $conn->begin_transaction();

    $st = $conn->prepare(
        'SELECT p.id_pembayaran, p.id_pendaftaran, p.status, p.metode, p.gross_amount,
                d.id_user, d.jenis_pendaftaran
         FROM pembayaran p
         JOIN pendaftaran d ON d.id_pendaftaran = p.id_pendaftaran
         WHERE p.order_id = ? LIMIT 1 FOR UPDATE'
    );
    $st->bind_param('s', $order_id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$row) {
        $conn->rollback();
        return ['ok' => false, 'status_lokal' => '', 'changed' => false, 'pesan' => 'order_id tidak dikenal'];
    }

    $trx_status   = $notif['transaction_status'] ?? null;
    $fraud_status = $notif['fraud_status'] ?? null;
    $status_baru  = midtrans_map_status($trx_status, $fraud_status);

    /* proteksi: kalau sudah lunas, jangan turunkan lagi */
    if ($row['status'] === 'lunas' && $status_baru !== 'lunas') {
        $conn->rollback();
        return ['ok' => true, 'status_lokal' => 'lunas', 'changed' => false, 'pesan' => 'sudah lunas, diabaikan'];
    }

    /* validasi nominal bila tersedia */
    if (isset($notif['gross_amount']) && $row['gross_amount'] !== null) {
        if (abs((float) $notif['gross_amount'] - (float) $row['gross_amount']) > 0.01) {
            $conn->rollback();
            return ['ok' => false, 'status_lokal' => $row['status'], 'changed' => false, 'pesan' => 'nominal tidak cocok'];
        }
    }

    $changed = ($row['status'] !== $status_baru);

    $paid_at = ($status_baru === 'lunas') ? date('Y-m-d H:i:s') : null;

    $st = $conn->prepare(
        'UPDATE pembayaran SET
            status = ?,
            transaction_id = ?,
            payment_type = ?,
            transaction_status = ?,
            fraud_status = ?,
            gross_amount = COALESCE(gross_amount, ?),
            midtrans_response = ?,
            paid_at = COALESCE(paid_at, ?),
            tanggal_bayar = COALESCE(tanggal_bayar, ?)
         WHERE id_pembayaran = ? LIMIT 1'
    );
    $trx_id   = $notif['transaction_id'] ?? null;
    $pay_type = $notif['payment_type'] ?? null;
    $gross    = $notif['gross_amount'] ?? null;
    $rawjson  = json_encode($notif, JSON_UNESCAPED_UNICODE);
    $id_pemb = (int) $row['id_pembayaran'];
    $st->bind_param(
        'sssssdsssi',
        $status_baru, $trx_id, $pay_type, $trx_status, $fraud_status,
        $gross, $rawjson, $paid_at, $paid_at, $id_pemb
    );
    $st->execute();
    $st->close();

    /* kalau lunas: dorong status pendaftaran + notifikasi (sekali saja) */
    if ($status_baru === 'lunas' && $changed) {
        $conn->query(
            "UPDATE pendaftaran SET status = 'terdaftar'
             WHERE id_pendaftaran = " . (int) $row['id_pendaftaran'] . "
             AND status IN ('menunggu_verifikasi','berkas_diperiksa','menunggu_pembayaran','pembayaran_diperiksa')"
        );
        kirim_notifikasi($conn, (int) $row['id_user'], 'Pembayaran berhasil',
            'Pembayaran ' . $row['jenis_pendaftaran'] . ' via payment gateway berhasil. Anda resmi terdaftar.');
        kirim_notifikasi_admin($conn, 'Pembayaran gateway berhasil',
            'Pembayaran ' . $row['jenis_pendaftaran'] . ' (pendaftaran #' . $row['id_pendaftaran']
            . ') LUNAS via Midtrans (' . ($pay_type ?: '-') . ').');
    } elseif (in_array($status_baru, ['ditolak', 'kadaluarsa', 'dibatalkan'], true) && $changed) {
        kirim_notifikasi($conn, (int) $row['id_user'], 'Pembayaran tidak berhasil',
            'Transaksi pembayaran ' . $row['jenis_pendaftaran'] . ' berstatus "' . $status_baru
            . '". Silakan ulangi pembayaran.');
    }

    $conn->commit();

    return [
        'ok'           => true,
        'status_lokal' => $status_baru,
        'changed'      => $changed,
        'pesan'        => 'ok',
    ];
}

/** Tarik status terbaru dari Midtrans lalu terapkan (dipakai halaman "cek status"). */
function pembayaran_sinkron_dari_midtrans(mysqli $conn, string $orderId): array
{
    try {
        $status = midtrans_get_status($orderId);
    } catch (Throwable $e) {
        return ['ok' => false, 'status_lokal' => '', 'changed' => false, 'pesan' => $e->getMessage()];
    }
    return pembayaran_terapkan_notif($conn, $status);
}
