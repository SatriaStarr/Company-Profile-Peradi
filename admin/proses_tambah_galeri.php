<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_galeri.php');
    exit;
}
csrf_check();

$judul = trim($_POST['judul'] ?? '');
if ($judul === '') {
    header('Location: tambah_galeri.php?pesan=kosong');
    exit;
}

try {
    $path = simpan_upload($_FILES['foto'] ?? [], 'galeri', ['image'], 3145728, true);
} catch (RuntimeException $e) {
    die(e($e->getMessage()));
}
$foto = basename($path);

$stmt = mysqli_prepare($conn, 'INSERT INTO galeri (judul, foto) VALUES (?, ?)');
mysqli_stmt_bind_param($stmt, 'ss', $judul, $foto);

if (mysqli_stmt_execute($stmt)) {
    header('Location: galeri.php?pesan=sukses');
    exit;
}
hapus_upload($path);
die('Gagal menyimpan galeri.');
