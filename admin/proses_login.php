<?php

session_start();

/*
|--------------------------------------------------------------------------
| KONEKSI DATABASE
|--------------------------------------------------------------------------
| File ini berada di:
| admin/proses_login.php
|
| koneksi.php berada di:
| admin/koneksi.php
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../koneksi.php';


/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
| Sesuaikan dengan nama folder project di htdocs.
|--------------------------------------------------------------------------
*/

$base_url = '/WEB-DPC-PERADI-PONTIANAK';


/*
|--------------------------------------------------------------------------
| CEK METHOD REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: {$base_url}/admin/login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| AMBIL INPUT LOGIN
|--------------------------------------------------------------------------
*/

$login = trim(
    $_POST['email']
    ?? $_POST['username']
    ?? ''
);

$password = $_POST['password'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDASI INPUT
|--------------------------------------------------------------------------
*/

if ($login === '' || $password === '') {

    header(
        "Location: {$base_url}/admin/login.php?pesan=gagal"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| ESCAPE INPUT
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| CARI USER  (prepared statement)
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'ss', $login, $login);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| USER TIDAK DITEMUKAN
|--------------------------------------------------------------------------
*/

if (!$data) {

    header(
        "Location: {$base_url}/admin/login.php?pesan=notfound"
    );

    exit;

}

$role = $data['role'] ?? '';


/*
|--------------------------------------------------------------------------
| CEK ROLE ADMIN
|--------------------------------------------------------------------------
|
| Hanya admin dan super_admin yang boleh masuk
| ke halaman administrator.
|--------------------------------------------------------------------------
*/

if (
    $role !== 'admin' &&
    $role !== 'super_admin'
) {

    header(
        "Location: {$base_url}/admin/login.php?pesan=gagal"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| CEK PASSWORD
|--------------------------------------------------------------------------
*/

$stored_password = $data['password'] ?? '';

$password_valid = password_verify(
    $password,
    $stored_password
);


/*
|--------------------------------------------------------------------------
| SUPPORT PASSWORD LAMA PLAINTEXT
|--------------------------------------------------------------------------
|
| Jika akun lama masih menyimpan password plaintext,
| otomatis diubah menjadi password hash.
|--------------------------------------------------------------------------
*/

if (
    !$password_valid &&
    hash_equals(
        (string) $stored_password,
        (string) $password
    )
) {

    $password_valid = true;

    $new_hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $id = (int) ($data['id_user'] ?? 0);

    if ($id > 0) {

        $up = mysqli_prepare(
            $conn,
            "UPDATE users SET password = ? WHERE id_user = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($up, 'si', $new_hash, $id);
        mysqli_stmt_execute($up);
        mysqli_stmt_close($up);

    }

}


/*
|--------------------------------------------------------------------------
| PASSWORD SALAH
|--------------------------------------------------------------------------
*/

if (!$password_valid) {

    header(
        "Location: {$base_url}/admin/login.php?pesan=gagal"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| CEK STATUS AKUN
|--------------------------------------------------------------------------
*/

$status = $data['status'] ?? 'active';


/*
|--------------------------------------------------------------------------
| AKUN PENDING
|--------------------------------------------------------------------------
*/

if ($status === 'pending') {

    header(
        "Location: {$base_url}/admin/login.php?pesan=pending"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| AKUN BANNED
|--------------------------------------------------------------------------
*/

if ($status === 'banned') {

    header(
        "Location: {$base_url}/admin/login.php?pesan=banned"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| REGENERATE SESSION
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);


/*
|--------------------------------------------------------------------------
| SIMPAN DATA SESSION
|--------------------------------------------------------------------------
*/

$_SESSION['id_user'] = $data['id_user'] ?? null;

$_SESSION['username'] = $data['username'] ?? '';

$_SESSION['nama_lengkap'] =
    $data['nama_lengkap']
    ?? '';

$_SESSION['email'] =
    $data['email']
    ?? '';

$_SESSION['role'] = $role;

$_SESSION['status_login'] = true;

$_SESSION['login'] = true;


/*
|--------------------------------------------------------------------------
| REDIRECT KE DASHBOARD ADMIN
|--------------------------------------------------------------------------
|
| PENTING:
|
| Jangan gunakan:
|
| admin/dashboard.php
|
| karena file ini sudah berada di:
|
| admin
|
| Kita gunakan URL absolut project supaya tidak pernah
| menghasilkan /admin/admin/dashboard.php
|--------------------------------------------------------------------------
*/

header(
    "Location: {$base_url}/admin/dashboard.php"
);

exit;

?>