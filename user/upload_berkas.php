<?php
/*
 * Berkas persyaratan kini diunggah sekaligus saat mengisi formulir
 * pendaftaran (pendaftaran_pkpa.php / pendaftaran_upa.php) dan disimpan
 * di tabel pendaftaran_pkpa / pendaftaran_upa. Tidak ada tabel `berkas`.
 * Halaman upload terpisah ini dinonaktifkan; arahkan ke detail pendaftaran.
 */
session_start();
require_once __DIR__ . '/functions.php';
require_user();

$id = (int) ($_GET['id'] ?? $_POST['id_pendaftaran'] ?? 0);
if ($id > 0) {
    header('Location: detail_pendaftaran.php?id=' . $id);
} else {
    header('Location: dashboard.php');
}
exit;
