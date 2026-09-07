<?php
include 'koneksi.php';

$id = $_POST['id'];
$judul = trim($_POST['judul']);
$foto_lama = $_POST['foto_lama'];

$foto_baru = $foto_lama;

if (!empty($_FILES['foto']['name'])) {
    $foto_baru = time().'_'.$_FILES['foto']['name'];
    move_uploaded_file($_FILES['foto']['tmp_name'], "../uploads/galeri/".$foto_baru);

    if ($foto_lama && file_exists("../uploads/galeri/".$foto_lama)) {
        unlink("../uploads/galeri/".$foto_lama);
    }
}

mysqli_query($conn, "
    UPDATE galeri 
    SET judul='$judul', foto='$foto_baru'
    WHERE id='$id'
");

header("Location: galeri.php");
exit;
