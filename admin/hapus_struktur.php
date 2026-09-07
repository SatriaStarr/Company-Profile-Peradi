<?php
include 'koneksi.php';

$id = intval($_GET['id']);
$q = mysqli_fetch_assoc(mysqli_query($conn,"SELECT foto FROM struktur_pengurus WHERE id=$id"));

if ($q && file_exists('../uploads/struktur/'.$q['foto'])) {
    unlink('../uploads/struktur/'.$q['foto']);
}

mysqli_query($conn,"DELETE FROM struktur_pengurus WHERE id=$id");
header("Location: struktur_pengurus.php");
