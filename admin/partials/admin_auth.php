<?php
/*
|--------------------------------------------------------------------------
| GUARD & HELPER BERSAMA UNTUK SEMUA HALAMAN ADMIN
|--------------------------------------------------------------------------
| Sertakan file ini di baris paling atas setiap halaman admin:
|
|     require_once __DIR__ . '/partials/admin_auth.php';
|
| Setelah include ini tersedia:
|   $conn            -> koneksi mysqli
|   $role            -> 'admin' | 'super_admin'
|   $is_super_admin  -> bool
|   $admin_nama, $admin_email, $admin_role (label), $admin_id
|   e($v)            -> escape HTML
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../koneksi.php';

/* ---------- CEK LOGIN ---------- */
if (empty($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header('Location: index.php?pesan=belum_login');
    exit;
}

/* ---------- CEK ROLE ---------- */
$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'super_admin'], true)) {
    header('Location: index.php?pesan=no_access');
    exit;
}

$is_super_admin = ($role === 'super_admin');

/* ---------- DATA ADMIN ---------- */
$admin_id    = (int) ($_SESSION['id_user'] ?? 0);
$admin_nama  = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Administrator';
$admin_email = $_SESSION['email'] ?? '';
$admin_role  = $is_super_admin ? 'SUPER ADMIN' : 'ADMIN';

/* ---------- HELPER ESCAPE ---------- */
if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

/* ---------- HELPER CSRF ---------- */
require_once __DIR__ . '/csrf.php';
