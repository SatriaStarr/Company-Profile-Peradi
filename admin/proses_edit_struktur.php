<?php
include 'koneksi.php';

$id = intval($_POST['id']);
$nama = $_POST['nama'];
$jabatan = $_POST['jabatan'];

if (!empty($_FILES['foto']['name'])) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $foto = uniqid('struktur_').'.'.$ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/struktur/'.$foto);
    mysqli_query($conn,"UPDATE struktur_pengurus SET foto='$foto' WHERE id=$id");
}

mysqli_query($conn,"
UPDATE struktur_pengurus
SET nama='$nama', jabatan='$jabatan'
WHERE id=$id
");

header("Location: struktur_pengurus.php");
