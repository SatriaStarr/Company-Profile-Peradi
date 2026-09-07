<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header('Location: login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'super_admin') {
    header('Location: ../dashboard.php?pesan=no_access');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$aksi = $_GET['aksi'] ?? '';

if ($id <= 0) {
    header('Location: ../verifikasi_admin.php?notif=gagal');
    exit;
}

if ($aksi === 'terima') {
    mysqli_query($conn, "UPDATE users SET status = 'active' WHERE id_user = $id AND role = 'admin'");
    header('Location: ../verifikasi_admin.php?notif=sukses_terima');
    exit;
}

if ($aksi === 'tolak') {
    mysqli_query($conn, "DELETE FROM users WHERE id_user = $id AND role = 'admin'");
    header('Location: ../verifikasi_admin.php?notif=sukses_tolak');
    exit;
}

header('Location: ../verifikasi_admin.php?notif=gagal');
exit;
?>
