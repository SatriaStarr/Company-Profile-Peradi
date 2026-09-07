<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: peraturan.php');
    exit;
}
csrf_check();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: peraturan.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT file_pdf FROM peraturan WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$file = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['file_pdf'] ?? '';
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, 'DELETE FROM peraturan WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($file) {
    hapus_upload('uploads/peraturan/' . basename($file));
}

header('Location: peraturan.php?pesan=hapus_sukses');
exit;
