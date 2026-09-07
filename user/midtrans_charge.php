<?php
session_start();
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/midtrans.php';
require_user();

$uid = user_id();
$id_pendaftaran = (int) ($_POST['id_pendaftaran'] ?? $_GET['id'] ?? 0);

if ($id_pendaftaran <= 0) {
    header('Location: dashboard.php');
    exit;
}
if (!midtrans_aktif()) {
    header('Location: pembayaran.php?id=' . $id_pendaftaran . '&pesan=gateway_off');
    exit;
}

/* ---------- ambil pendaftaran + pembayaran milik user ---------- */
$st = $conn->prepare(
    'SELECT p.id_pendaftaran, p.jenis_pendaftaran,
            pb.id_pembayaran, pb.jumlah, pb.status, pb.order_id, pb.snap_token,
            u.nama_lengkap, u.email
     FROM pendaftaran p
     JOIN pembayaran pb ON pb.id_pendaftaran = p.id_pendaftaran
     JOIN users u ON u.id_user = p.id_user
     WHERE p.id_pendaftaran = ? AND p.id_user = ?
     LIMIT 1'
);
$st->bind_param('ii', $id_pendaftaran, $uid);
$st->execute();
$data = $st->get_result()->fetch_assoc();
$st->close();

if (!$data) {
    header('Location: dashboard.php');
    exit;
}
if ($data['status'] === 'lunas') {
    header('Location: detail_pendaftaran.php?id=' . $id_pendaftaran);
    exit;
}

$gross = (int) round((float) $data['jumlah']);
if ($gross <= 0) {
    header('Location: pembayaran.php?id=' . $id_pendaftaran . '&pesan=gagal');
    exit;
}

/*
 * order_id: dipakai ulang selama transaksi belum final, supaya tidak dobel.
 * Jika sebelumnya sudah pernah dibuat & masih pending, buat order_id baru
 * (Midtrans menolak order_id yang sama untuk transaksi baru).
 */
$order_id = 'PG-' . $data['jenis_pendaftaran'] . '-' . $id_pendaftaran . '-' . time();

$params = [
    'transaction_details' => [
        'order_id'     => $order_id,
        'gross_amount' => $gross,
    ],
    'item_details' => [[
        'id'       => 'REG-' . $id_pendaftaran,
        'price'    => $gross,
        'quantity' => 1,
        'name'     => 'Pendaftaran ' . $data['jenis_pendaftaran'] . ' #' . $id_pendaftaran,
    ]],
    'customer_details' => [
        'first_name' => mb_substr((string) $data['nama_lengkap'], 0, 50) ?: 'Peserta',
        'email'      => filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? $data['email'] : null,
    ],
    'callbacks' => [
        'finish' => rtrim(base_url(), '/') . '/user/midtrans_status.php?id=' . $id_pendaftaran,
    ],
    'expiry' => [
        'unit'     => 'hours',
        'duration' => 24,
    ],
];

try {
    $trx = midtrans_create_transaction($params);
} catch (Throwable $e) {
    header('Location: pembayaran.php?id=' . $id_pendaftaran
        . '&pesan=gateway_err&detail=' . urlencode($e->getMessage()));
    exit;
}

/* simpan ke pembayaran (metode midtrans, status pending) */
$st = $conn->prepare(
    "UPDATE pembayaran SET
        metode = 'midtrans',
        order_id = ?,
        snap_token = ?,
        gross_amount = ?,
        transaction_status = 'pending',
        status = 'pending',
        midtrans_response = ?
     WHERE id_pembayaran = ? LIMIT 1"
);
$rawjson = json_encode($trx['raw'], JSON_UNESCAPED_UNICODE);
$st->bind_param('ssdsi', $order_id, $trx['token'], $gross, $rawjson, $data['id_pembayaran']);
$st->execute();
$st->close();

/* arahkan user ke halaman pembayaran Midtrans */
header('Location: ' . $trx['redirect_url']);
exit;


/* ---------- helper base URL ---------- */
function base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/WEB-DPC-PERADI-PONTIANAK';
}
