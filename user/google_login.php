<?php
session_start();
require_once __DIR__ . '/google_config.php';

if (!google_aktif()) {
    header('Location: login.php?pesan=google_off');
    exit;
}

/* state = proteksi CSRF untuk alur OAuth */
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = [
    'client_id'     => google_client_id(),
    'redirect_uri'  => google_redirect_uri(),
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'online',
    'state'         => $state,
    'prompt'        => 'select_account',
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
