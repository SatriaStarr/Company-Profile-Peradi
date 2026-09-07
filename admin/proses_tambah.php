<?php
include 'koneksi.php';

// --- FUNGSI UPLOAD FILE ---
// Agar kodingan rapi, kita buat fungsi khusus upload
function uploadFile($nama_input) {
    $nama_file   = $_FILES[$nama_input]['name'];
    $ukuran_file = $_FILES[$nama_input]['size'];
    $error       = $_FILES[$nama_input]['error'];
    $tmp_name    = $_FILES[$nama_input]['tmp_name'];

    // Cek apakah ada file yang diupload
    if ($error === 4) {
        return null; // Tidak ada file
    }

    // Generate nama file baru yang unik (agar tidak bentrok)
    // Contoh: 170923221_foto.jpg
    $ekstensi_valid = ['jpg', 'jpeg', 'png', 'pdf'];
    $ekstensi_file  = explode('.', $nama_file);
    $ekstensi_file  = strtolower(end($ekstensi_file));

    // Cek ekstensi (sederhana)
    if (!in_array($ekstensi_file, $ekstensi_valid)) {
        return false;
    }

    // Nama baru
    $nama_file_baru = uniqid() . '.' . $ekstensi_file;
    
    // Pindahkan file ke folder 'uploads'
    move_uploaded_file($tmp_name, 'uploads/' . $nama_file_baru);

    return $nama_file_baru;
}

// --- AMBIL DATA TEXT DARI FORM ---
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

// --- PROSES UPLOAD FILE ---
$foto_profil = uploadFile('foto_profil');
$file_ktpa   = uploadFile('file_ktpa');
// Kalau gagal upload (format salah), set null saja biar tidak error
if($foto_profil === false) $foto_profil = null; 
if($file_ktpa === false) $file_ktpa = null;

// --- SIMPAN KE DATABASE ---
$query = "INSERT INTO data_advokat (
            nia, nama_lengkap, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, 
            agama, alamat_domisili, nama_kantor_hukum, alamat_kantor, no_hp, 
            email, status_keanggotaan, universitas_asal, nomor_bas, 
            tanggal_sumpah_pt, foto_profil, file_ktpa
          ) VALUES (
            '$nia', '$nama_lengkap', '$nik', '$tempat_lahir', '$tanggal_lahir', '$jenis_kelamin',
            '$agama', '$alamat_domisili', '$nama_kantor', '$alamat_kantor', '$no_hp',
            '$email', '$status', '$univ', '$nomor_bas',
            '$tgl_sumpah', '$foto_profil', '$file_ktpa'
          )";

if (mysqli_query($conn, $query)) {
    // Berhasil
    header("Location: data_advokat.php?pesan=sukses");
} else {
    // Gagal (Biasanya karena NIA kembar atau error SQL)
    echo "Gagal: " . mysqli_error($conn);
}
?>