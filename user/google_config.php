<?php
/*
|--------------------------------------------------------------------------
| Konfigurasi Google OAuth 2.0
|--------------------------------------------------------------------------
| Kredensial dibaca dari config/google_keys.local.php (gitignored).
| Kompatibel dengan kode lama: konstanta GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET
| tetap tersedia.
*/

function google_config(): array
{
    static $c = null;
    if ($c !== null) {
        return $c;
    }
    $file = __DIR__ . '/../config/google_keys.local.php';
    $c = is_file($file) ? (array) require $file : [];
    $c += ['client_id' => '', 'client_secret' => '', 'redirect_uri' => ''];
    return $c;
}

function google_aktif(): bool
{
    $c = google_config();
    return trim((string) $c['client_id']) !== '' && trim((string) $c['client_secret']) !== '';
}

function google_client_id(): string
{
    return (string) google_config()['client_id'];
}

function google_client_secret(): string
{
    return (string) google_config()['client_secret'];
}

function google_redirect_uri(): string
{
    $manual = trim((string) google_config()['redirect_uri']);
    if ($manual !== '') {
        return $manual;
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/WEB-DPC-PERADI-PONTIANAK/user/google_callback.php';
}

/* kompatibilitas mundur */
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', google_client_id());
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', google_client_secret());
}
