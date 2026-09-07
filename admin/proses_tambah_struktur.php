<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_struktur.php');
    exit;
}
csrf_check();

$nama    = trim($_POST['nama'] ?? '');
$jabatan = trim($_POST['jabatan'] ?? '');
if ($nama === '' || $jabatan === '') {
    header('Location: tambah_struktur.php?pesan=kosong');
    exit;
}

try {
    $path = simpan_upload($_FILES['foto'] ?? [], 'struktur', ['image'], 3145728, true);
} catch (RuntimeException $e) {
    die(e($e->getMessage()));
}
$foto = basename($path);

$stmt = mysqli_prepare($conn, 'INSERT INTO struktur_pengurus (nama, jabatan, foto) VALUES (?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'sss', $nama, $jabatan, $foto);

if (mysqli_stmt_execute($stmt)) {
    header('Location: struktur_pengurus.php?pesan=sukses');
    exit;
}
hapus_upload($path);
die('Gagal menyimpan data struktur.');
