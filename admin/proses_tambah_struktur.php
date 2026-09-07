<?php
session_start();
include 'koneksi.php';

$nama    = mysqli_real_escape_string($conn, $_POST['nama']);
$jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);

$ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
$nama_file = uniqid('struktur_').'.'.$ext;

move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/struktur/'.$nama_file);

mysqli_query($conn, "
INSERT INTO struktur_pengurus (nama, jabatan, foto)
VALUES ('$nama','$jabatan','$nama_file')
");

header("Location: struktur_pengurus.php");
