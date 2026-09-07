<?php
/*
|--------------------------------------------------------------------------
| WEBHOOK / HTTP NOTIFICATION MIDTRANS
|--------------------------------------------------------------------------
| Set URL ini di Dashboard Midtrans (Settings -> Configuration -> Payment
| Notification URL):
|     https://<domain-anda>/WEB-DPC-PERADI-PONTIANAK/midtrans_notify.php
|
| Alur:
|   1. Terima JSON dari Midtrans.
|   2. Verifikasi signature_key (sha512).
|   3. Untuk keamanan ekstra: tarik ulang status resmi via API /status
|      (tidak langsung percaya body notifikasi).
|   4. Terapkan ke DB secara idempoten (tidak dobat/dobel).
*/

header('Content-Type: application/json');

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/pembayaran_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'method_not_allowed']);
    exit;
}

if (!midtrans_aktif()) {
    http_response_code(503);
    echo json_encode(['status' => 'gateway_disabled']);
    exit;
}

$raw   = file_get_contents('php://input');
$notif = json_decode($raw, true);

if (!is_array($notif) || empty($notif['order_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'bad_request']);
    exit;
}

/* 1) verifikasi signature */
if (!midtrans_verify_signature($notif)) {
    http_response_code(403);
    echo json_encode(['status' => 'invalid_signature']);
    exit;
}

/* 2) tarik status resmi dari Midtrans (jangan percaya body mentah) */
$order_id = (string) $notif['order_id'];
try {
    $resmi = midtrans_get_status($order_id);
} catch (Throwable $e) {
    // fallback: pakai body notifikasi yang sudah lolos signature
    $resmi = $notif;
}

/* 3) terapkan ke DB (idempoten, transaksi + FOR UPDATE) */
try {
    $hasil = pembayaran_terapkan_notif($conn, $resmi);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'pesan' => $e->getMessage()]);
    exit;
}

/* Midtrans hanya butuh HTTP 200 untuk berhenti retry */
http_response_code($hasil['ok'] ? 200 : 422);
echo json_encode([
    'status'       => $hasil['ok'] ? 'ok' : 'ignored',
    'order_id'     => $order_id,
    'status_lokal' => $hasil['status_lokal'],
    'changed'      => $hasil['changed'],
    'pesan'        => $hasil['pesan'],
]);
