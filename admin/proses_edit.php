<?php
include 'koneksi.php';

// 1. Ambil ID & Data Teks
$id               = $_POST['id'];
$nia              = $_POST['nia'];
$nama_lengkap     = $_POST['nama_lengkap'];
$nik              = $_POST['nik'];
$tempat_lahir     = $_POST['tempat_lahir'];
$tanggal_lahir    = $_POST['tanggal_lahir'];
$jenis_kelamin    = $_POST['jenis_kelamin'];
$agama            = $_POST['agama'];
$alamat_domisili  = $_POST['alamat_domisili'];
$nama_kantor      = $_POST['nama_kantor_hukum'];
$alamat_kantor    = $_POST['alamat_kantor'];
$no_hp            = $_POST['no_hp'];
$email            = $_POST['email'];
$status           = $_POST['status_keanggotaan'];
$univ             = $_POST['universitas_asal'];
$nomor_bas        = $_POST['nomor_bas'];
$tgl_sumpah       = $_POST['tanggal_sumpah_pt'];

// Ambil Nama File Lama (Dikirim via input hidden)
$foto_lama        = $_POST['foto_lama'];
$ktpa_lama        = $_POST['ktpa_lama'];

// --- FUNGSI UPLOAD (Sama kayak tambah data) ---
function uploadFileBaru($nama_input) {
    $nama_file   = $_FILES[$nama_input]['name'];
    $tmp_name    = $_FILES[$nama_input]['tmp_name'];
    $ekstensi    = explode('.', $nama_file);
    $ekstensi    = strtolower(end($ekstensi));
    $nama_baru   = uniqid() . '.' . $ekstensi;
    
    move_uploaded_file($tmp_name, 'uploads/' . $nama_baru);
    return $nama_baru;
}

// 2. LOGIKA GANTI FOTO PROFIL
// Cek apakah user upload foto baru? (Error 4 artinya tidak ada file)
if ($_FILES['foto_profil']['error'] === 4) {
    // Jika tidak upload, pakai nama file lama
    $foto_final = $foto_lama;
} else {
    // Jika upload baru, proses upload & pakai nama file baru
    $foto_final = uploadFileBaru('foto_profil');
}

// 3. LOGIKA GANTI FILE KTPA
if ($_FILES['file_ktpa']['error'] === 4) {
    $ktpa_final = $ktpa_lama;
} else {
    $ktpa_final = uploadFileBaru('file_ktpa');
}

// 4. UPDATE DATABASE
$query = "UPDATE data_advokat SET
            nia = '$nia',
            nama_lengkap = '$nama_lengkap',
            nik = '$nik',
            tempat_lahir = '$tempat_lahir',
            tanggal_lahir = '$tanggal_lahir',
            jenis_kelamin = '$jenis_kelamin',
            agama = '$agama',
            alamat_domisili = '$alamat_domisili',
            nama_kantor_hukum = '$nama_kantor',
            alamat_kantor = '$alamat_kantor',
            no_hp = '$no_hp',
            email = '$email',
            status_keanggotaan = '$status',
            universitas_asal = '$univ',
            nomor_bas = '$nomor_bas',
            tanggal_sumpah_pt = '$tgl_sumpah',
            foto_profil = '$foto_final',
            file_ktpa = '$ktpa_final'
          WHERE id_advokat = '$id'";

if (mysqli_query($conn, $query)) {
    // Sukses, kembali ke data advokat
    header("Location: data_advokat.php?pesan=sukses_edit");
} else {
    echo "Gagal mengupdate data: " . mysqli_error($conn);
}
?>