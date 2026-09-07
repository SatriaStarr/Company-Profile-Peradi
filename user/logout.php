<?php
session_start();

unset(
    $_SESSION['user_id'],
    $_SESSION['username'],
    $_SESSION['nama_lengkap'],
    $_SESSION['email'],
    $_SESSION['role'],
    $_SESSION['user_login']
);

header('Location: login.php?pesan=logout');
exit;
?>
