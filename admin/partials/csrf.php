<?php
/*
|--------------------------------------------------------------------------
| Helper CSRF (dipakai admin_auth.php dan halaman admin yang punya guard sendiri)
|--------------------------------------------------------------------------
*/
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('csrf_check')) {
    /** Panggil di awal handler POST. Menghentikan request jika token tidak valid. */
    function csrf_check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $sent = $_POST['csrf_token'] ?? '';
        if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
            http_response_code(419);
            die('Sesi tidak valid atau kedaluwarsa. Silakan muat ulang halaman dan coba lagi.');
        }
    }
}
