<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';
require_once __DIR__ . '/partials/advokat_lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_advokat.php');
    exit;
}
csrf_check();

/* ---------- kumpulkan & validasi ---------- */
[$f, $errors] = advokat_input($conn);

if ($errors) {
    $_SESSION['adv_errors'] = $errors;
    $_SESSION['adv_old']    = $f;
    header('Location: tambah_advokat.php');
    exit;
}

/* ---------- upload file (opsional) ---------- */
$foto = $ktpa = $bas = null;
try {
    if (($_FILES['foto_profil']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $foto = simpan_upload($_FILES['foto_profil'], 'advokat', ['image'], 3145728, false);
    }
    if (($_FILES['file_ktpa']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $ktpa = simpan_upload($_FILES['file_ktpa'], 'advokat', ['image', 'pdf'], 3145728, false);
    }
    if (($_FILES['file_bas']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $bas = simpan_upload($_FILES['file_bas'], 'advokat', ['image', 'pdf'], 3145728, false);
    }
} catch (RuntimeException $e) {
    hapus_upload($foto);
    hapus_upload($ktpa);
    hapus_upload($bas);
    $_SESSION['adv_errors'] = [$e->getMessage()];
    $_SESSION['adv_old']    = $f;
    header('Location: tambah_advokat.php');
    exit;
}

/* ---------- simpan ---------- */
$sql = 'INSERT INTO data_advokat
        (nia, nama_lengkap, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, agama,
         alamat_domisili, nama_kantor_hukum, alamat_kantor, no_hp, email,
         universitas_asal, tanggal_sumpah_pt, nomor_bas, masa_berlaku_ktpa,
         status_keanggotaan, foto_profil, file_ktpa, file_bas)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
$st = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $st,
    str_repeat('s', 20),
    $f['nia'], $f['nama_lengkap'], $f['nik'], $f['tempat_lahir'], $f['tanggal_lahir'],
    $f['jenis_kelamin'], $f['agama'], $f['alamat_domisili'], $f['nama_kantor_hukum'],
    $f['alamat_kantor'], $f['no_hp'], $f['email'], $f['universitas_asal'],
    $f['tanggal_sumpah_pt'], $f['nomor_bas'], $f['masa_berlaku_ktpa'],
    $f['status_keanggotaan'], $foto, $ktpa, $bas
);

if (mysqli_stmt_execute($st)) {
    mysqli_stmt_close($st);
    header('Location: data_advokat.php?pesan=sukses');
    exit;
}

$err = mysqli_errno($conn) === 1062
    ? 'NIA "' . $f['nia'] . '" sudah terdaftar.'
    : 'Gagal menyimpan: ' . mysqli_error($conn);
mysqli_stmt_close($st);
hapus_upload($foto);
hapus_upload($ktpa);
hapus_upload($bas);
$_SESSION['adv_errors'] = [$err];
$_SESSION['adv_old']    = $f;
header('Location: tambah_advokat.php');
exit;
