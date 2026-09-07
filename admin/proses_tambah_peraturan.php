<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_peraturan.php');
    exit;
}
csrf_check();

$judul = trim($_POST['judul'] ?? '');
$isi   = trim($_POST['isi'] ?? '');
if ($judul === '' || $isi === '') {
    header('Location: tambah_peraturan.php?pesan=kosong');
    exit;
}

try {
    $path = simpan_upload($_FILES['file_pdf'] ?? [], 'peraturan', ['pdf'], 10485760, true);
} catch (RuntimeException $e) {
    die(e($e->getMessage()));
}
$file = basename($path);

$stmt = mysqli_prepare($conn, 'INSERT INTO peraturan (judul, isi, file_pdf) VALUES (?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'sss', $judul, $isi, $file);

if (mysqli_stmt_execute($stmt)) {
    header('Location: peraturan.php?pesan=sukses');
    exit;
}
hapus_upload($path);
die('Gagal menyimpan peraturan.');
