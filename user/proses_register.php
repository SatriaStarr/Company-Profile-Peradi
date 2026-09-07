<?php
require_once __DIR__ . '/../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$nama = trim($_POST['nama_lengkap'] ?? '');
$email = trim($_POST['email'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($nama === '' || $email === '' || $username === '' || $password === '') {
    header('Location: register.php?pesan=kosong');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?pesan=email');
    exit;
}

if (strlen($password) < 6) {
    header('Location: register.php?pesan=password');
    exit;
}

$nama_safe = mysqli_real_escape_string($conn, $nama);
$email_safe = mysqli_real_escape_string($conn, $email);
$username_safe = mysqli_real_escape_string($conn, $username);

$cek_username = mysqli_query(
    $conn,
    "SELECT id_user FROM users WHERE username = '$username_safe' LIMIT 1"
);

if ($cek_username && mysqli_num_rows($cek_username) > 0) {
    header('Location: register.php?pesan=username');
    exit;
}

$cek_email = mysqli_query(
    $conn,
    "SELECT id_user FROM users WHERE email = '$email_safe' LIMIT 1"
);

if ($cek_email && mysqli_num_rows($cek_email) > 0) {
    header('Location: register.php?pesan=email_terdaftar');
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$password_safe = mysqli_real_escape_string($conn, $password_hash);

$query = mysqli_query(
    $conn,
    "INSERT INTO users (username, email, password, nama_lengkap, role, status)
     VALUES ('$username_safe', '$email_safe', '$password_safe', '$nama_safe', 'user', 'active')"
);

if (!$query) {
    die('Gagal membuat akun: ' . mysqli_error($conn));
}

header('Location: login.php?pesan=sukses_regis');
exit;
?>
