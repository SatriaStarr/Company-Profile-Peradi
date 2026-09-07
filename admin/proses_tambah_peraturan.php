<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php?pesan=belum_login");
    exit;
}

// Ambil data
$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$isi   = mysqli_real_escape_string($conn, $_POST['isi']);

// ===== PROSES FILE PDF =====
$file_name = $_FILES['file_pdf']['name'];
$file_tmp  = $_FILES['file_pdf']['tmp_name'];
$file_size = $_FILES['file_pdf']['size'];
$file_error= $_FILES['file_pdf']['error'];

$ekstensi_valid = ['pdf'];
$ekstensi = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

if ($file_error !== 0) {
    die("Upload file gagal.");
}

if (!in_array($ekstensi, $ekstensi_valid)) {
    die("File harus PDF.");
}

// Rename file agar unik
$nama_file_baru = uniqid('peraturan_') . '.pdf';

// Path upload
$upload_path = '../uploads/peraturan/' . $nama_file_baru;

// Pindahkan file
move_uploaded_file($file_tmp, $upload_path);

// ===== SIMPAN KE DATABASE =====
$query = mysqli_query($conn, "
    INSERT INTO peraturan (judul, isi, file_pdf, created_at)
    VALUES ('$judul', '$isi', '$nama_file_baru', NOW())
");

if ($query) {
    header("Location: peraturan.php?pesan=sukses");
} else {
    echo "Gagal menyimpan data.";
}
