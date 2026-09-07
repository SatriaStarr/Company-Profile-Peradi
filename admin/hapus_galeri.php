<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Ambil nama file
$data = mysqli_query($conn, "SELECT foto FROM galeri WHERE id=$id");
$row = mysqli_fetch_assoc($data);

if ($row) {
    $file = '../uploads/galeri/' . $row['foto'];

    // Hapus file fisik
    if (file_exists($file)) {
        unlink($file);
    }

    // Hapus DB
    mysqli_query($conn, "DELETE FROM galeri WHERE id=$id");
}

header("Location: galeri.php");
