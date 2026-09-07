<?php
session_start();

// Cek: Apakah user sudah login?
if (isset($_SESSION['status_login']) && $_SESSION['status_login'] == true) {
    // Kalau SUDAH login, langsung lempar ke Dashboard
    header("Location: dashboard.php");
} else {
    // Kalau BELUM login, lempar ke login.php
    header("Location: login.php");
}
exit;
?>