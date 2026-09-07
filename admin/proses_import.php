<?php
require_once __DIR__ . '/partials/admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['import'])) {
    header('Location: data_advokat.php');
    exit;
}
csrf_check();

if (($_FILES['file_csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    || !is_uploaded_file($_FILES['file_csv']['tmp_name'])) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}
if ($_FILES['file_csv']['size'] <= 0 || $_FILES['file_csv']['size'] > 5 * 1024 * 1024) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

$ext = strtolower(pathinfo($_FILES['file_csv']['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

$fp = fopen($_FILES['file_csv']['tmp_name'], 'r');
if (!$fp) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

fgetcsv($fp, 10000, ';'); // lewati header

$berhasil = 0;
$gagal    = 0;

$cek = mysqli_prepare($conn, 'SELECT 1 FROM data_advokat WHERE nia = ? LIMIT 1');
$ins = mysqli_prepare(
    $conn,
    'INSERT INTO data_advokat
     (nia, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, no_hp,
      nama_kantor_hukum, alamat_kantor, nik, alamat_domisili, status_keanggotaan)
     VALUES (?,?,?,?,?,?,?,?,?,?,?, "aktif")'
);

while (($c = fgetcsv($fp, 10000, ';')) !== false) {

    $g = fn(int $i) => isset($c[$i]) ? trim($c[$i]) : '';

    $nia = $g(1);
    if ($nia === '') {
        $gagal++;
        continue;
    }

    // cek duplikat
    mysqli_stmt_bind_param($cek, 's', $nia);
    mysqli_stmt_execute($cek);
    if (mysqli_fetch_row(mysqli_stmt_get_result($cek))) {
        $gagal++;
        continue;
    }

    $nama_lengkap  = $g(2);
    $jk_raw        = strtoupper($g(3));
    $jenis_kelamin = in_array($jk_raw, ['L', 'P'], true)
        ? $jk_raw
        : (str_starts_with($jk_raw, 'L') ? 'L' : (str_starts_with($jk_raw, 'P') ? 'P' : null));
    $tempat_lahir  = $g(4);
    $tanggal_lahir = preg_match('/^\d{4}-\d{2}-\d{2}$/', $g(5)) ? $g(5) : null;
    $agama         = $g(6);
    $no_hp         = $g(7);
    $nama_kantor   = $g(8);
    $alamat_kantor = $g(9);
    $nik           = $g(12);
    $alamat_dom    = $g(13);

    mysqli_stmt_bind_param(
        $ins,
        'sssssssssss',
        $nia, $nama_lengkap, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $agama,
        $no_hp, $nama_kantor, $alamat_kantor, $nik, $alamat_dom
    );

    if (mysqli_stmt_execute($ins)) {
        $berhasil++;
    } else {
        $gagal++;
    }
}

fclose($fp);

header('Location: data_advokat.php?pesan=import_selesai&sukses=' . $berhasil . '&gagal=' . $gagal);
exit;
