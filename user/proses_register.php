<?php
require_once __DIR__ . '/../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$nama     = trim($_POST['nama_lengkap'] ?? '');
$email    = trim($_POST['email'] ?? '');
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
if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
    header('Location: register.php?pesan=username');
    exit;
}
if (strlen($password) < 6) {
    header('Location: register.php?pesan=password');
    exit;
}

/* username unik */
$st = $conn->prepare('SELECT id_user FROM users WHERE username = ? LIMIT 1');
$st->bind_param('s', $username);
$st->execute();
if ($st->get_result()->fetch_assoc()) {
    $st->close();
    header('Location: register.php?pesan=username');
    exit;
}
$st->close();

/* email unik */
$st = $conn->prepare('SELECT id_user FROM users WHERE email = ? LIMIT 1');
$st->bind_param('s', $email);
$st->execute();
if ($st->get_result()->fetch_assoc()) {
    $st->close();
    header('Location: register.php?pesan=email_terdaftar');
    exit;
}
$st->close();

$hash = password_hash($password, PASSWORD_DEFAULT);

$st = $conn->prepare(
    "INSERT INTO users (username, email, password, nama_lengkap, role, status)
     VALUES (?, ?, ?, ?, 'user', 'active')"
);
$st->bind_param('ssss', $username, $email, $hash, $nama);

if (!$st->execute()) {
    $st->close();
    die('Gagal membuat akun.');
}
$st->close();

header('Location: login.php?pesan=sukses_regis');
exit;
