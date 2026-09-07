<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_advokat.php');
    exit;
}
csrf_check();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

/* ambil file untuk dibersihkan */
$data = null;
if ($st = mysqli_prepare($conn, 'SELECT foto_profil, file_ktpa, file_bas FROM data_advokat WHERE id_advokat = ? LIMIT 1')) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
}
if (!$data) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

$st = mysqli_prepare($conn, 'DELETE FROM data_advokat WHERE id_advokat = ? LIMIT 1');
mysqli_stmt_bind_param($st, 'i', $id);
$ok = mysqli_stmt_execute($st);
mysqli_stmt_close($st);

if ($ok) {
    foreach (['foto_profil', 'file_ktpa', 'file_bas'] as $k) {
        $v = $data[$k] ?? '';
        if ($v && $v !== 'default.jpg' && str_starts_with(str_replace('\\', '/', $v), 'uploads/')) {
            hapus_upload($v);
        }
    }
    header('Location: data_advokat.php?pesan=sukses_hapus');
    exit;
}

header('Location: data_advokat.php?pesan=gagal');
exit;
