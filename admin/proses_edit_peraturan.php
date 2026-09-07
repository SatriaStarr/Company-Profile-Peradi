<?php
include 'koneksi.php';

$id         = $_POST['id'];
$judul      = $_POST['judul'];
$isi        = $_POST['isi'];
$file_lama  = $_POST['file_lama'];

function uploadPDF($input) {
    $nama = $_FILES[$input]['name'];
    $tmp  = $_FILES[$input]['tmp_name'];
    $ext  = strtolower(pathinfo($nama, PATHINFO_EXTENSION));

    $nama_baru = uniqid() . '.' . $ext;
    move_uploaded_file($tmp, '../uploads/peraturan/' . $nama_baru);
    return $nama_baru;
}

// Jika tidak upload file baru
if ($_FILES['file_pdf']['error'] === 4) {
    $file_final = $file_lama;
} else {
    $file_final = uploadPDF('file_pdf');
    if ($file_lama && file_exists('../uploads/peraturan/' . $file_lama)) {
        unlink('../uploads/peraturan/' . $file_lama);
    }
}

$query = "UPDATE peraturan SET
            judul='$judul',
            isi='$isi',
            file_pdf='$file_final'
          WHERE id='$id'";

if (mysqli_query($conn, $query)) {
    header("Location: peraturan.php?pesan=edit_sukses");
} else {
    echo "Gagal update: " . mysqli_error($conn);
}
