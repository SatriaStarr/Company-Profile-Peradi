<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: peraturan.php');
    exit;
}
csrf_check();

$id    = (int) ($_POST['id'] ?? 0);
$judul = trim($_POST['judul'] ?? '');
$isi   = trim($_POST['isi'] ?? '');
if ($id <= 0 || $judul === '' || $isi === '') {
    header('Location: peraturan.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT file_pdf FROM peraturan WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['file_pdf'] ?? '';
mysqli_stmt_close($stmt);

$file = $lama;
$baru = null;
if (($_FILES['file_pdf']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    try {
        $baru = simpan_upload($_FILES['file_pdf'], 'peraturan', ['pdf'], 10485760, false);
        $file = basename($baru);
    } catch (RuntimeException $e) {
        die(e($e->getMessage()));
    }
}

$stmt = mysqli_prepare($conn, 'UPDATE peraturan SET judul = ?, isi = ?, file_pdf = ? WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'sssi', $judul, $isi, $file, $id);

if (mysqli_stmt_execute($stmt)) {
    if ($baru && $lama && $lama !== $file) {
        hapus_upload('uploads/peraturan/' . $lama);
    }
    header('Location: peraturan.php?pesan=edit_sukses');
    exit;
}
if ($baru) {
    hapus_upload($baru);
}
die('Gagal memperbarui peraturan.');
