<?php
session_start();

require_once __DIR__ . '/../koneksi.php';

/*
|--------------------------------------------------------------------------
| PROSES LOGIN USER
|--------------------------------------------------------------------------
*/

// Pastikan hanya menerima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Ambil input
$login = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input
if ($login === '' || $password === '') {
    header('Location: login.php?pesan=gagal');
    exit;
}

/*
|--------------------------------------------------------------------------
| CARI USER
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM users
     WHERE username = ? OR email = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ss", $login, $login);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    mysqli_stmt_close($stmt);
    die("Gagal mengambil data user: " . mysqli_error($conn));
}

// User tidak ditemukan
if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);

    header('Location: login.php?pesan=notfound');
    exit;
}

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| CEK ROLE
|--------------------------------------------------------------------------
*/

// Login ini khusus peserta/user
if (($user['role'] ?? '') !== 'user') {
    header('Location: login.php?pesan=gagal');
    exit;
}

/*
|--------------------------------------------------------------------------
| CEK PASSWORD
|--------------------------------------------------------------------------
*/

$stored_password = $user['password'] ?? '';

if (!password_verify($password, $stored_password)) {
    header('Location: login.php?pesan=gagal');
    exit;
}

/*
|--------------------------------------------------------------------------
| CEK STATUS AKUN
|--------------------------------------------------------------------------
*/

$status = $user['status'] ?? 'active';

if ($status === 'pending') {
    header('Location: login.php?pesan=pending');
    exit;
}

if ($status === 'banned') {
    header('Location: login.php?pesan=banned');
    exit;
}

/*
|--------------------------------------------------------------------------
| LOGIN BERHASIL
|--------------------------------------------------------------------------
*/

// Buat session ID baru untuk keamanan
session_regenerate_id(true);

// Simpan data user ke session
$_SESSION['user_id'] = $user['id_user'];
$_SESSION['username'] = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['email'] = $user['email'] ?? '';
$_SESSION['role'] = 'user';
$_SESSION['user_login'] = true;

/*
|--------------------------------------------------------------------------
| LANGSUNG KE DASHBOARD
|--------------------------------------------------------------------------
*/

header('Location: dashboard.php');
exit;
?>