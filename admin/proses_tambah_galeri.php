<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php");
    exit;
}

$judul = mysqli_real_escape_string($conn, $_POST['judul']);

// ===== PROSES FILE FOTO =====
$file_name  = $_FILES['foto']['name'];
$file_tmp   = $_FILES['foto']['tmp_name'];
$file_error = $_FILES['foto']['error'];

$ekstensi_valid = ['jpg', 'jpeg', 'png'];
$ekstensi = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if ($file_error !== 0) {
    die("Upload foto gagal.");
}

if (!in_array($ekstensi, $ekstensi_valid)) {
    die("Format foto tidak valid.");
}

// Rename unik
$nama_file_baru = uniqid('galeri_') . '.' . $ekstensi;
$upload_path = '../uploads/galeri/' . $nama_file_baru;

move_uploaded_file($file_tmp, $upload_path);

// ===== SIMPAN DB =====
$query = mysqli_query($conn, "
    INSERT INTO galeri (judul, foto)
    VALUES ('$judul', '$nama_file_baru')
");

if ($query) {
    header("Location: galeri.php?pesan=sukses");
} else {
    echo "Gagal menyimpan galeri.";
}
