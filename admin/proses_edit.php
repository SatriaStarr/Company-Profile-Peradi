<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../upload_helper.php';
require_once __DIR__ . '/partials/advokat_lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: data_advokat.php');
    exit;
}
csrf_check();

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

/* data lama (untuk file yang tidak diganti) */
$old = null;
if ($st = mysqli_prepare($conn, 'SELECT foto_profil, file_ktpa, file_bas FROM data_advokat WHERE id_advokat = ? LIMIT 1')) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $old = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
}
if (!$old) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

[$f, $errors] = advokat_input($conn);

if ($errors) {
    $_SESSION['adv_errors'] = $errors;
    $_SESSION['adv_old']    = $f;
    header('Location: edit_advokat.php?id=' . $id);
    exit;
}

/* file: pakai baru bila diunggah, jika tidak pertahankan lama */
$foto = $old['foto_profil'];
$ktpa = $old['file_ktpa'];
$bas  = $old['file_bas'];
$baru = [];

try {
    if (($_FILES['foto_profil']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $foto = simpan_upload($_FILES['foto_profil'], 'advokat', ['image'], 3145728, false);
        $baru[] = [$foto, $old['foto_profil']];
    }
    if (($_FILES['file_ktpa']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $ktpa = simpan_upload($_FILES['file_ktpa'], 'advokat', ['image', 'pdf'], 3145728, false);
        $baru[] = [$ktpa, $old['file_ktpa']];
    }
    if (($_FILES['file_bas']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $bas = simpan_upload($_FILES['file_bas'], 'advokat', ['image', 'pdf'], 3145728, false);
        $baru[] = [$bas, $old['file_bas']];
    }
} catch (RuntimeException $e) {
    foreach ($baru as $b) {
        hapus_upload($b[0]);
    }
    $_SESSION['adv_errors'] = [$e->getMessage()];
    $_SESSION['adv_old']    = $f;
    header('Location: edit_advokat.php?id=' . $id);
    exit;
}

$sql = 'UPDATE data_advokat SET
        nia=?, nama_lengkap=?, nik=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, agama=?,
        alamat_domisili=?, nama_kantor_hukum=?, alamat_kantor=?, no_hp=?, email=?,
        universitas_asal=?, tanggal_sumpah_pt=?, nomor_bas=?, masa_berlaku_ktpa=?,
        status_keanggotaan=?, foto_profil=?, file_ktpa=?, file_bas=?
        WHERE id_advokat=? LIMIT 1';
$st = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param(
    $st,
    str_repeat('s', 20) . 'i',
    $f['nia'], $f['nama_lengkap'], $f['nik'], $f['tempat_lahir'], $f['tanggal_lahir'],
    $f['jenis_kelamin'], $f['agama'], $f['alamat_domisili'], $f['nama_kantor_hukum'],
    $f['alamat_kantor'], $f['no_hp'], $f['email'], $f['universitas_asal'],
    $f['tanggal_sumpah_pt'], $f['nomor_bas'], $f['masa_berlaku_ktpa'],
    $f['status_keanggotaan'], $foto, $ktpa, $bas, $id
);

if (mysqli_stmt_execute($st)) {
    mysqli_stmt_close($st);
    // hapus file lama yang benar-benar diganti
    foreach ($baru as $b) {
        if ($b[1] && $b[1] !== $b[0] && $b[1] !== 'default.jpg') {
            hapus_upload($b[1]);
        }
    }
    header('Location: data_advokat.php?pesan=sukses_edit');
    exit;
}

$err = mysqli_errno($conn) === 1062
    ? 'NIA "' . $f['nia'] . '" sudah dipakai advokat lain.'
    : 'Gagal memperbarui: ' . mysqli_error($conn);
mysqli_stmt_close($st);
foreach ($baru as $b) {
    hapus_upload($b[0]);
}
$_SESSION['adv_errors'] = [$err];
$_SESSION['adv_old']    = $f;
header('Location: edit_advokat.php?id=' . $id);
exit;
