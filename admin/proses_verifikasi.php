<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../notifikasi_helper.php';

if (!$is_super_admin) {
    header('Location: dashboard.php?pesan=no_access');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: verifikasi_admin.php');
    exit;
}
csrf_check();

$id   = (int) ($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';

if ($id <= 0 || $id === $admin_id) {
    header('Location: verifikasi_admin.php?notif=gagal');
    exit;
}

if ($aksi === 'terima') {
    $st = mysqli_prepare($conn,
        "UPDATE users SET status = 'active' WHERE id_user = ? AND role = 'admin' AND status = 'pending' LIMIT 1");
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $st->close();
    kirim_notifikasi($conn, $id, 'Akun admin disetujui',
        'Akun admin Anda telah disetujui. Anda sekarang dapat masuk ke sistem.');
    header('Location: verifikasi_admin.php?notif=sukses_terima');
    exit;
}

if ($aksi === 'tolak') {
    $st = mysqli_prepare($conn,
        "DELETE FROM users WHERE id_user = ? AND role = 'admin' AND status = 'pending' LIMIT 1");
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $st->close();
    header('Location: verifikasi_admin.php?notif=sukses_tolak');
    exit;
}

header('Location: verifikasi_admin.php?notif=gagal');
exit;
