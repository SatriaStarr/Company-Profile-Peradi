<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/google_config.php';

if (!google_aktif()) {
    header('Location: login.php?pesan=google_off');
    exit;
}

/* user membatalkan / error dari Google */
if (isset($_GET['error'])) {
    header('Location: login.php?pesan=google_batal');
    exit;
}

/* ---------- 1. Verifikasi state (CSRF) ---------- */
$state = $_GET['state'] ?? '';
if (!$state || !hash_equals($_SESSION['google_oauth_state'] ?? '', $state)) {
    unset($_SESSION['google_oauth_state']);
    header('Location: login.php?pesan=google_gagal');
    exit;
}
unset($_SESSION['google_oauth_state']);

$code = $_GET['code'] ?? '';
if ($code === '') {
    header('Location: login.php?pesan=google_gagal');
    exit;
}

/* ---------- 2. Tukar code -> access token ---------- */
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $code,
        'client_id'     => google_client_id(),
        'client_secret' => google_client_secret(),
        'redirect_uri'  => google_redirect_uri(),
        'grant_type'    => 'authorization_code',
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$token_response = curl_exec($ch);
$token_http     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($token_response === false || $token_http >= 400) {
    header('Location: login.php?pesan=google_gagal');
    exit;
}
$token        = json_decode($token_response, true);
$access_token = $token['access_token'] ?? '';
if ($access_token === '') {
    header('Location: login.php?pesan=google_gagal');
    exit;
}

/* ---------- 3. Ambil profil user ---------- */
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $access_token],
]);
$user_response = curl_exec($ch);
$user_http     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($user_response === false || $user_http >= 400) {
    header('Location: login.php?pesan=google_gagal');
    exit;
}

$g        = json_decode($user_response, true);
$google_id = trim((string) ($g['sub'] ?? ''));
$email     = strtolower(trim((string) ($g['email'] ?? '')));
$name      = trim((string) ($g['name'] ?? '')) ?: 'Pengguna Google';
$verified  = (bool) ($g['email_verified'] ?? false);

if ($google_id === '' || $email === '' || !$verified) {
    header('Location: login.php?pesan=google_gagal');
    exit;
}

/*
|--------------------------------------------------------------------------
| 4. Cari / buat user  (cegah akun ganda)
|--------------------------------------------------------------------------
| Prioritas pencocokan: google_id -> email.
*/
$user = null;

$st = $conn->prepare('SELECT * FROM users WHERE google_id = ? LIMIT 1');
$st->bind_param('s', $google_id);
$st->execute();
$user = $st->get_result()->fetch_assoc();
$st->close();

if (!$user) {
    $st = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $st->bind_param('s', $email);
    $st->execute();
    $user = $st->get_result()->fetch_assoc();
    $st->close();

    if ($user) {
        /* email sudah terdaftar -> tautkan google_id ke akun itu */
        $st = $conn->prepare('UPDATE users SET google_id = ? WHERE id_user = ? LIMIT 1');
        $st->bind_param('si', $google_id, $user['id_user']);
        $st->execute();
        $st->close();
        $user['google_id'] = $google_id;
    }
}

if ($user) {
    /* hanya role user yang boleh login lewat halaman ini */
    if (($user['role'] ?? '') !== 'user') {
        header('Location: login.php?pesan=gagal');
        exit;
    }
    $status = $user['status'] ?? 'active';
    if ($status === 'pending') {
        header('Location: login.php?pesan=pending');
        exit;
    }
    if ($status === 'banned') {
        header('Location: login.php?pesan=banned');
        exit;
    }
} else {
    /* ---------- buat akun user baru ---------- */
    $base = preg_replace('/[^a-z0-9._-]/', '', strtolower(strtok($email, '@')));
    if ($base === '' || strlen($base) < 3) {
        $base = 'google' . $base;
    }
    $base = substr($base, 0, 40);

    $username = $base;
    for ($i = 0; $i < 25; $i++) {
        $st = $conn->prepare('SELECT id_user FROM users WHERE username = ? LIMIT 1');
        $st->bind_param('s', $username);
        $st->execute();
        $taken = (bool) $st->get_result()->fetch_assoc();
        $st->close();
        if (!$taken) {
            break;
        }
        $username = $base . random_int(10, 9999);
    }

    /* password acak — TIDAK menyimpan password Google */
    $rand_hash = password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);

    $st = $conn->prepare(
        "INSERT INTO users (username, email, google_id, password, nama_lengkap, role, status)
         VALUES (?, ?, ?, ?, ?, 'user', 'active')"
    );
    $st->bind_param('sssss', $username, $email, $google_id, $rand_hash, $name);
    if (!$st->execute()) {
        $st->close();
        header('Location: login.php?pesan=google_gagal');
        exit;
    }
    $new_id = $conn->insert_id;
    $st->close();

    $st = $conn->prepare('SELECT * FROM users WHERE id_user = ? LIMIT 1');
    $st->bind_param('i', $new_id);
    $st->execute();
    $user = $st->get_result()->fetch_assoc();
    $st->close();
}

/*
|--------------------------------------------------------------------------
| 5. Buat session — SAMA dengan login username/password
|--------------------------------------------------------------------------
*/
session_regenerate_id(true);
$_SESSION['user_id']      = (int) $user['id_user'];
$_SESSION['username']     = $user['username'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['email']        = $user['email'] ?? $email;
$_SESSION['role']         = 'user';
$_SESSION['user_login']   = true;

header('Location: dashboard.php');
exit;
