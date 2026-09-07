<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/google_config.php';

if (isset($_GET['error'])) {
    header('Location: login.php?pesan=google_batal');
    exit;
}

$state = $_GET['state'] ?? '';
if (!$state || !hash_equals($_SESSION['google_oauth_state'] ?? '', $state)) {
    die('Google OAuth state tidak valid. Silakan ulangi login.');
}
unset($_SESSION['google_oauth_state']);

$code = $_GET['code'] ?? '';
if ($code === '') {
    die('Kode login Google tidak ditemukan.');
}

$token_data = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => google_redirect_uri(),
    'grant_type' => 'authorization_code'
];

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($token_data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
]);
$token_response = curl_exec($ch);
$token_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($token_response === false || $token_http >= 400) {
    die('Gagal mendapatkan token Google. Periksa Client ID, Client Secret, dan Redirect URI.');
}

$token = json_decode($token_response, true);
$access_token = $token['access_token'] ?? '';
if ($access_token === '') {
    die('Access token Google tidak ditemukan.');
}

$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token]
]);
$user_response = curl_exec($ch);
$user_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($user_response === false || $user_http >= 400) {
    die('Gagal mengambil data akun Google.');
}

$google_user = json_decode($user_response, true);
$email = trim($google_user['email'] ?? '');
$name = trim($google_user['name'] ?? 'Pengguna Google');
$verified = $google_user['email_verified'] ?? false;

if ($email === '' || !$verified) {
    die('Email Google tidak tersedia atau belum terverifikasi.');
}

$email_safe = mysqli_real_escape_string($conn, $email);
$name_safe = mysqli_real_escape_string($conn, $name);
$query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email_safe' LIMIT 1");

if ($query && mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);

    if (($user['role'] ?? '') !== 'user') {
        header('Location: login.php?pesan=gagal');
        exit;
    }
    if (($user['status'] ?? 'active') === 'pending') {
        header('Location: login.php?pesan=pending');
        exit;
    }
    if (($user['status'] ?? 'active') === 'banned') {
        header('Location: login.php?pesan=banned');
        exit;
    }
} else {
    $base = preg_replace('/[^a-zA-Z0-9._-]/', '', strtolower(strtok($email, '@')));
    if ($base === '') $base = 'googleuser';

    $username = $base;
    for ($i = 0; $i < 20; $i++) {
        $username_safe = mysqli_real_escape_string($conn, $username);
        $cek = mysqli_query($conn, "SELECT id_user FROM users WHERE username = '$username_safe' LIMIT 1");
        if (!$cek || mysqli_num_rows($cek) === 0) break;
        $username = $base . rand(10, 999);
    }

    $username_safe = mysqli_real_escape_string($conn, $username);
    $random_password = password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
    $password_safe = mysqli_real_escape_string($conn, $random_password);

    $insert = mysqli_query(
        $conn,
        "INSERT INTO users (username, email, password, nama_lengkap, role, status)
         VALUES ('$username_safe', '$email_safe', '$password_safe', '$name_safe', 'user', 'active')"
    );

    if (!$insert) die('Gagal membuat akun dari Google: ' . mysqli_error($conn));

    $user_query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email_safe' LIMIT 1");
    $user = mysqli_fetch_assoc($user_query);
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id_user'];
$_SESSION['username'] = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['email'] = $user['email'] ?? $email;
$_SESSION['role'] = 'user';
$_SESSION['user_login'] = true;

header('Location: dashboard.php');
exit;
?>
