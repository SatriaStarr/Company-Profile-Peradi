<?php
// Isi dua nilai ini dari Google Cloud Console.
// Jangan masukkan client secret ke GitHub/public repository.
const GOOGLE_CLIENT_ID = '';
const GOOGLE_CLIENT_SECRET = '';

function google_redirect_uri(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/WEB-DPC-PERADI-PONTIANAK/user/google_callback.php';
}
?>
