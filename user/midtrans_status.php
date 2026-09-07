<?php
session_start();
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../pembayaran_helper.php';
require_user();

$uid = user_id();
$id_pendaftaran = (int) ($_GET['id'] ?? 0);
if ($id_pendaftaran <= 0) {
    header('Location: dashboard.php');
    exit;
}

/* pembayaran milik user */
$st = $conn->prepare(
    'SELECT pb.order_id, pb.status, pb.metode, pb.transaction_status, pb.payment_type,
            p.jenis_pendaftaran
     FROM pendaftaran p
     JOIN pembayaran pb ON pb.id_pendaftaran = p.id_pendaftaran
     WHERE p.id_pendaftaran = ? AND p.id_user = ? LIMIT 1'
);
$st->bind_param('ii', $id_pendaftaran, $uid);
$st->execute();
$data = $st->get_result()->fetch_assoc();
$st->close();

if (!$data) {
    header('Location: dashboard.php');
    exit;
}

/* sinkronkan dari Midtrans (best-effort) */
$sync_pesan = '';
if ($data['metode'] === 'midtrans' && !empty($data['order_id']) && midtrans_aktif()) {
    $r = pembayaran_sinkron_dari_midtrans($conn, $data['order_id']);
    $sync_pesan = $r['pesan'];
    // ambil status terkini
    $st = $conn->prepare('SELECT status, transaction_status, payment_type FROM pembayaran WHERE order_id = ? LIMIT 1');
    $st->bind_param('s', $data['order_id']);
    $st->execute();
    $fresh = $st->get_result()->fetch_assoc();
    $st->close();
    if ($fresh) {
        $data = array_merge($data, $fresh);
    }
}

$map = [
    'lunas'      => ['ok',   'Pembayaran Berhasil', 'Pembayaran Anda sudah diterima. Anda resmi terdaftar.'],
    'pending'    => ['wait', 'Menunggu Pembayaran', 'Transaksi Anda belum selesai. Selesaikan pembayaran sesuai instruksi, lalu cek kembali.'],
    'ditolak'    => ['bad',  'Pembayaran Gagal', 'Transaksi ditolak / gagal. Silakan ulangi pembayaran.'],
    'kadaluarsa' => ['bad',  'Pembayaran Kedaluwarsa', 'Batas waktu pembayaran habis. Silakan buat pembayaran baru.'],
    'dibatalkan' => ['bad',  'Pembayaran Dibatalkan', 'Transaksi dibatalkan. Silakan ulangi pembayaran.'],
];
$view = $map[$data['status']] ?? ['wait', 'Status Pembayaran', 'Status pembayaran Anda sedang diproses.'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Pembayaran - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { box-sizing: border-box; }
body { font-family: 'Montserrat', sans-serif; background: #f4f6f9; margin: 0; color: #333; }
.wrap { max-width: 520px; margin: 60px auto; padding: 20px; }
.card { background: #fff; border-radius: 18px; padding: 38px 30px; box-shadow: 0 12px 40px rgba(0,0,0,.08); text-align: center; }
.ic { width: 76px; height: 76px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 34px; margin: 0 auto 18px; }
.ic.ok { background: #dcfce7; color: #16a34a; }
.ic.wait { background: #fef3c7; color: #d97706; }
.ic.bad { background: #fee2e2; color: #dc2626; }
h1 { color: #1e3a8a; font-size: 22px; margin: 0 0 8px; }
p { color: #64748b; font-size: 14px; line-height: 1.6; }
.meta { font-size: 12px; color: #94a3b8; margin-top: 10px; }
.btn { display: inline-block; margin-top: 22px; background: #1e3a8a; color: #fff; text-decoration: none; padding: 13px 22px; border-radius: 10px; font-weight: 700; font-size: 14px; }
.btn.ghost { background: #e5e7eb; color: #333; margin-left: 8px; }
</style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="ic <?= e($view[0]) ?>">
            <i class="fa-solid <?= $view[0] === 'ok' ? 'fa-check' : ($view[0] === 'bad' ? 'fa-xmark' : 'fa-hourglass-half') ?>"></i>
        </div>
        <h1><?= e($view[1]) ?></h1>
        <p><?= e($view[2]) ?></p>
        <div class="meta">
            <?= e(strtoupper($data['jenis_pendaftaran'])) ?> &middot;
            Status: <?= e($data['status']) ?>
            <?= $data['payment_type'] ? ' &middot; ' . e($data['payment_type']) : '' ?>
        </div>

        <a class="btn" href="detail_pendaftaran.php?id=<?= e($id_pendaftaran) ?>">Lihat Detail Pendaftaran</a>
        <?php if (in_array($data['status'], ['pending', 'ditolak', 'kadaluarsa', 'dibatalkan'], true)): ?>
            <a class="btn ghost" href="pembayaran.php?id=<?= e($id_pendaftaran) ?>">Kembali ke Pembayaran</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
