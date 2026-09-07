<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: galeri.php');
    exit;
}
csrf_check();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: galeri.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT foto FROM galeri WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$foto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['foto'] ?? '';
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, 'DELETE FROM galeri WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($foto) {
    hapus_upload('uploads/galeri/' . basename($foto));
}

header('Location: galeri.php?pesan=hapus_sukses');
exit;
