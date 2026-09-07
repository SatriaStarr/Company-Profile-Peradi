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

/* Jika dicentang, baris dengan NIA yang sudah ada akan DIPERBARUI, bukan dilewati. */
$mode_update = !empty($_POST['update_existing']);

$fp = fopen($_FILES['file_csv']['tmp_name'], 'r');
if (!$fp) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

fgetcsv($fp, 10000, ';'); // lewati header

$masuk    = 0;   // baris baru
$diupdate = 0;   // baris diperbarui
$dilewati = 0;   // baris dilewati (duplikat, mode skip)
$gagal    = 0;

$cek = mysqli_prepare($conn, 'SELECT id_advokat FROM data_advokat WHERE nia = ? LIMIT 1');
$ins = mysqli_prepare(
    $conn,
    'INSERT INTO data_advokat
     (nia, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, agama, no_hp,
      nama_kantor_hukum, alamat_kantor, nik, alamat_domisili, status_keanggotaan)
     VALUES (?,?,?,?,?,?,?,?,?,?,?, "aktif")'
);
$upd = mysqli_prepare(
    $conn,
    'UPDATE data_advokat SET
        nama_lengkap = COALESCE(NULLIF(?, ""), nama_lengkap),
        jenis_kelamin = COALESCE(?, jenis_kelamin),
        tempat_lahir = COALESCE(NULLIF(?, ""), tempat_lahir),
        tanggal_lahir = COALESCE(?, tanggal_lahir),
        agama = COALESCE(NULLIF(?, ""), agama),
        no_hp = COALESCE(NULLIF(?, ""), no_hp),
        nama_kantor_hukum = COALESCE(NULLIF(?, ""), nama_kantor_hukum),
        alamat_kantor = COALESCE(NULLIF(?, ""), alamat_kantor),
        nik = COALESCE(NULLIF(?, ""), nik),
        alamat_domisili = COALESCE(NULLIF(?, ""), alamat_domisili)
     WHERE nia = ? LIMIT 1'
);

while (($c = fgetcsv($fp, 10000, ';')) !== false) {

    $g = fn(int $i) => isset($c[$i]) ? trim($c[$i]) : '';

    $nia = $g(1);
    if ($nia === '') {
        $gagal++;
        continue;
    }

    mysqli_stmt_bind_param($cek, 's', $nia);
    mysqli_stmt_execute($cek);
    $ada = (bool) mysqli_fetch_row(mysqli_stmt_get_result($cek));

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

    if ($ada) {
        if (!$mode_update) {
            $dilewati++;
            continue;
        }
        mysqli_stmt_bind_param(
            $upd,
            'sssssssssss',
            $nama_lengkap, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $agama,
            $no_hp, $nama_kantor, $alamat_kantor, $nik, $alamat_dom, $nia
        );
        if (mysqli_stmt_execute($upd)) {
            $diupdate++;
        } else {
            $gagal++;
        }
        continue;
    }

    mysqli_stmt_bind_param(
        $ins,
        'sssssssssss',
        $nia, $nama_lengkap, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $agama,
        $no_hp, $nama_kantor, $alamat_kantor, $nik, $alamat_dom
    );
    if (mysqli_stmt_execute($ins)) {
        $masuk++;
    } else {
        $gagal++;
    }
}

fclose($fp);

$sukses = $masuk + $diupdate;
header('Location: data_advokat.php?pesan=import_selesai'
    . '&sukses=' . $sukses
    . '&baru=' . $masuk
    . '&update=' . $diupdate
    . '&lewati=' . $dilewati
    . '&gagal=' . $gagal);
exit;
