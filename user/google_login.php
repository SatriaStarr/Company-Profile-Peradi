<?php
session_start();
require_once __DIR__ . '/google_config.php';

if (GOOGLE_CLIENT_ID === '' || GOOGLE_CLIENT_SECRET === '') {
    die('Login Google belum dikonfigurasi. Isi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET di user/google_config.php terlebih dahulu.');
}

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => google_redirect_uri(),
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'online',
    'state' => $state,
    'prompt' => 'select_account'
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
?>
