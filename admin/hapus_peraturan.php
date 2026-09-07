<?php
include 'koneksi.php';

$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT file_pdf FROM peraturan WHERE id='$id'");
$row  = mysqli_fetch_assoc($data);

if ($row['file_pdf'] && file_exists("../uploads/peraturan/".$row['file_pdf'])) {
    unlink("../uploads/peraturan/".$row['file_pdf']);
}

mysqli_query($conn, "DELETE FROM peraturan WHERE id='$id'");

header("Location: peraturan.php");
