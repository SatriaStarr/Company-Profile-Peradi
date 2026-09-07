<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/partials/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}
csrf_check();

$nama     = trim($_POST['nama_lengkap'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($nama === '' || $username === '' || $password === '') {
    header('Location: register.php?pesan=kosong');
    exit;
}
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
    header('Location: register.php?pesan=username');
    exit;
}
if (strlen($password) < 6) {
    header('Location: register.php?pesan=password');
    exit;
}

/* username unik */
$st = mysqli_prepare($conn, 'SELECT id_user FROM users WHERE username = ? LIMIT 1');
mysqli_stmt_bind_param($st, 's', $username);
mysqli_stmt_execute($st);
if (mysqli_fetch_assoc(mysqli_stmt_get_result($st))) {
    mysqli_stmt_close($st);
    header('Location: register.php?pesan=username');
    exit;
}
mysqli_stmt_close($st);

$hash = password_hash($password, PASSWORD_DEFAULT);

/* Registrasi dari area admin membuat akun ADMIN berstatus pending (perlu disetujui super admin). */
$st = mysqli_prepare(
    $conn,
    "INSERT INTO users (username, password, nama_lengkap, role, status)
     VALUES (?, ?, ?, 'admin', 'pending')"
);
mysqli_stmt_bind_param($st, 'sss', $username, $hash, $nama);

if (!mysqli_stmt_execute($st)) {
    mysqli_stmt_close($st);
    die('Gagal membuat akun admin.');
}
mysqli_stmt_close($st);

header('Location: login.php?pesan=sukses_regis');
exit;
