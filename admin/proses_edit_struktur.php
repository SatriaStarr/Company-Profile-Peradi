<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: struktur_pengurus.php');
    exit;
}
csrf_check();

$id      = (int) ($_POST['id'] ?? 0);
$nama    = trim($_POST['nama'] ?? '');
$jabatan = trim($_POST['jabatan'] ?? '');
if ($id <= 0 || $nama === '' || $jabatan === '') {
    header('Location: struktur_pengurus.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'SELECT foto FROM struktur_pengurus WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['foto'] ?? '';
mysqli_stmt_close($stmt);

$foto = $lama;
$baru = null;
if (($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    try {
        $baru = simpan_upload($_FILES['foto'], 'struktur', ['image'], 3145728, false);
        $foto = basename($baru);
    } catch (RuntimeException $e) {
        die(e($e->getMessage()));
    }
}

$stmt = mysqli_prepare($conn, 'UPDATE struktur_pengurus SET nama = ?, jabatan = ?, foto = ? WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'sssi', $nama, $jabatan, $foto, $id);

if (mysqli_stmt_execute($stmt)) {
    if ($baru && $lama && $lama !== $foto) {
        hapus_upload('uploads/struktur/' . $lama);
    }
    header('Location: struktur_pengurus.php?pesan=edit_sukses');
    exit;
}
if ($baru) {
    hapus_upload($baru);
}
die('Gagal memperbarui data struktur.');
