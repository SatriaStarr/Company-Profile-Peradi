<?php
require_once __DIR__ . '/../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$nama = trim($_POST['nama_lengkap'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($nama === '' || $username === '' || $password === '') {
    header('Location: register.php?pesan=kosong');
    exit;
}

if (strlen($password) < 6) {
    header('Location: register.php?pesan=password');
    exit;
}

$nama_safe = mysqli_real_escape_string($conn, $nama);
$username_safe = mysqli_real_escape_string($conn, $username);

$cek = mysqli_query(
    $conn,
    "SELECT id_user FROM users WHERE username = '$username_safe' LIMIT 1"
);

if ($cek && mysqli_num_rows($cek) > 0) {
    header('Location: register.php?pesan=username');
    exit;
}

$pass_hash = password_hash($password, PASSWORD_DEFAULT);
$pass_safe = mysqli_real_escape_string($conn, $pass_hash);

// Registrasi dari area admin membuat akun ADMIN, bukan akun peserta.
$query = mysqli_query(
    $conn,
    "INSERT INTO users (username, password, nama_lengkap, role, status)
     VALUES ('$username_safe', '$pass_safe', '$nama_safe', 'admin', 'pending')"
);

if (!$query) {
    die('Gagal membuat akun admin: ' . mysqli_error($conn));
}

header('Location: login.php?pesan=sukses_regis');
exit;
?>
