<?php
/*
|--------------------------------------------------------------------------
| KLIEN MIDTRANS (Snap API) — implementasi cURL ringan, tanpa Composer
|--------------------------------------------------------------------------
| Fungsi:
|   midtrans_config()            -> array kredensial
|   midtrans_aktif()             -> bool, apakah server_key sudah diisi
|   midtrans_base_snap()         -> URL Snap sesuai mode
|   midtrans_base_api()          -> URL API v2 sesuai mode
|   midtrans_client_key()
|   midtrans_snap_url()          -> URL Snap.js (untuk popup, opsional)
|   midtrans_create_transaction($params)      -> ['token'=>..., 'redirect_url'=>...]
|   midtrans_get_status($orderId)             -> array status transaksi
|   midtrans_verify_signature($notif)         -> bool (sha512)
|   midtrans_map_status($transaction_status, $fraud_status) -> status pembayaran lokal
*/

function midtrans_config(): array
{
    static $c = null;
    if ($c !== null) {
        return $c;
    }
    $file = __DIR__ . '/midtrans_keys.local.php';
    $c = is_file($file) ? (array) require $file : [];
    $c += ['is_production' => false, 'server_key' => '', 'client_key' => '', 'merchant_id' => ''];
    return $c;
}

function midtrans_aktif(): bool
{
    $c = midtrans_config();
    return trim((string) $c['server_key']) !== '';
}

function midtrans_is_production(): bool
{
    return (bool) (midtrans_config()['is_production'] ?? false);
}

function midtrans_client_key(): string
{
    return (string) (midtrans_config()['client_key'] ?? '');
}

function midtrans_server_key(): string
{
    return (string) (midtrans_config()['server_key'] ?? '');
}

function midtrans_base_snap(): string
{
    return midtrans_is_production()
        ? 'https://app.midtrans.com/snap/v1'
        : 'https://app.sandbox.midtrans.com/snap/v1';
}

function midtrans_base_api(): string
{
    return midtrans_is_production()
        ? 'https://api.midtrans.com/v2'
        : 'https://api.sandbox.midtrans.com/v2';
}

function midtrans_snap_js_url(): string
{
    return midtrans_is_production()
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
}

/**
 * Panggilan HTTP dasar ke Midtrans (Basic auth = server_key + ':').
 * @return array [int $httpCode, array|null $json, string $raw]
 */
function midtrans_request(string $method, string $url, ?array $body = null): array
{
    $ch = curl_init($url);
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(midtrans_server_key() . ':'),
    ];
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw  = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException('Gagal menghubungi Midtrans: ' . $err);
    }
    $json = json_decode($raw, true);
    return [$code, is_array($json) ? $json : null, (string) $raw];
}

/**
 * Buat transaksi Snap.
 * @return array ['token' => string, 'redirect_url' => string, 'raw' => array]
 */
function midtrans_create_transaction(array $params): array
{
    [$code, $json, $raw] = midtrans_request('POST', midtrans_base_snap() . '/transactions', $params);
    if ($code >= 400 || !$json || empty($json['token'])) {
        $msg = $json['error_messages'][0] ?? ('HTTP ' . $code);
        throw new RuntimeException('Midtrans menolak transaksi: ' . $msg);
    }
    return [
        'token'        => (string) $json['token'],
        'redirect_url' => (string) ($json['redirect_url'] ?? ''),
        'raw'          => $json,
    ];
}

/** Ambil status transaksi terbaru dari Midtrans (sumber kebenaran). */
function midtrans_get_status(string $orderId): array
{
    [$code, $json, $raw] = midtrans_request('GET', midtrans_base_api() . '/' . rawurlencode($orderId) . '/status');
    if (!$json) {
        throw new RuntimeException('Respons status Midtrans tidak valid (HTTP ' . $code . ').');
    }
    return $json;
}

/**
 * Verifikasi signature_key dari notifikasi webhook.
 * signature_key = sha512(order_id + status_code + gross_amount + server_key)
 */
function midtrans_verify_signature(array $notif): bool
{
    $expected = hash(
        'sha512',
        ($notif['order_id'] ?? '')
        . ($notif['status_code'] ?? '')
        . ($notif['gross_amount'] ?? '')
        . midtrans_server_key()
    );
    return isset($notif['signature_key'])
        && hash_equals($expected, (string) $notif['signature_key']);
}

/**
 * Petakan status Midtrans -> status kolom pembayaran.status lokal.
 * @return string salah satu: pending | lunas | ditolak | kadaluarsa | dibatalkan
 */
function midtrans_map_status(?string $trxStatus, ?string $fraudStatus = null): string
{
    switch ($trxStatus) {
        case 'capture':
            return ($fraudStatus === 'challenge') ? 'pending' : 'lunas';
        case 'settlement':
            return 'lunas';
        case 'pending':
            return 'pending';
        case 'deny':
        case 'failure':
            return 'ditolak';
        case 'cancel':
            return 'dibatalkan';
        case 'expire':
            return 'kadaluarsa';
        case 'refund':
        case 'partial_refund':
        case 'chargeback':
            return 'ditolak';
        default:
            return 'pending';
    }
}
