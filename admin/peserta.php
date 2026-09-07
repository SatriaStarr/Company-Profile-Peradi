<?php
/*
 * Halaman "Peserta Sertifikasi" lama sudah digantikan oleh
 * data_pendaftaran.php (daftar) + detail_pendaftaran.php (detail).
 * Redirect permanen agar tautan lama tetap berfungsi.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    header('Location: detail_pendaftaran.php?id=' . $id, true, 301);
} else {
    header('Location: data_pendaftaran.php', true, 301);
}
exit;
