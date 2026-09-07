<?php

session_start();

require_once __DIR__ . '/functions.php';

require_user();


// =====================================================
// 1. PASTIKAN REQUEST DARI FORM
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pendaftaran.php');
    exit;
}


// =====================================================
// 2. AMBIL DATA USER
// =====================================================

$uid = user_id();

$jenis = $_POST['jenis'] ?? '';
$tanggal = $_POST['tanggal'] ?? '';


// =====================================================
// 3. VALIDASI JENIS PENDAFTARAN
// =====================================================

if (!in_array($jenis, ['PKPA', 'UPA'], true)) {
    header('Location: pendaftaran.php?pesan=gagal');
    exit;
}


// =====================================================
// 4. VALIDASI TANGGAL
// =====================================================

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    header('Location: pendaftaran.php?pesan=gagal');
    exit;
}


// =====================================================
// 5. CEK APAKAH SUDAH ADA PENDAFTARAN AKTIF
// =====================================================

$cek = $conn->prepare("
    SELECT id_pendaftaran
    FROM pendaftaran
    WHERE id_user = ?
    AND jenis_pendaftaran = ?
    AND status <> 'ditolak'
    LIMIT 1
");

$cek->bind_param('is', $uid, $jenis);
$cek->execute();

$hasil_cek = $cek->get_result();

if ($hasil_cek->num_rows > 0) {
    $cek->close();

    header('Location: pendaftaran.php?pesan=gagal');
    exit;
}

$cek->close();


// =====================================================
// 6. TENTUKAN BIAYA
// =====================================================

if ($jenis === 'PKPA') {
    $jumlah = 2500000;
} else {
    $jumlah = 750000;
}


// =====================================================
// 7. MULAI TRANSAKSI DATABASE
// =====================================================

$conn->begin_transaction();


try {

    // =================================================
    // 8. SIMPAN PENDAFTARAN
    //
    // Database:
    // id_user
    // jenis_pendaftaran
    // status
    // tanggal_daftar
    // catatan
    //
    // tanggal_daftar otomatis current_timestamp()
    // =================================================

    $status = 'menunggu';

    $stmt = $conn->prepare("
        INSERT INTO pendaftaran
        (
            id_user,
            jenis_pendaftaran,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ");

    $stmt->bind_param(
        'iss',
        $uid,
        $jenis,
        $status
    );

    $stmt->execute();

    $id_pendaftaran = $conn->insert_id;

    $stmt->close();


    // =================================================
    // 9. BUAT DATA PEMBAYARAN
    //
    // Tabel pembayaran TIDAK punya kolom metode.
    // Jadi jangan masukkan metode QRIS ke database.
    // =================================================

    $status_pembayaran = 'belum_bayar';

    $stmt = $conn->prepare("
        INSERT INTO pembayaran
        (
            id_pendaftaran,
            jumlah,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ");

    $stmt->bind_param(
        'ids',
        $id_pendaftaran,
        $jumlah,
        $status_pembayaran
    );

    $stmt->execute();

    $stmt->close();


    // =================================================
    // 10. BUAT NOTIFIKASI
    // =================================================

    notify_user(
        $uid,
        'Pendaftaran berhasil',
        'Pendaftaran ' . $jenis . ' berhasil dibuat. Silakan lengkapi berkas dan pembayaran.'
    );

    require_once __DIR__ . '/../notifikasi_helper.php';
    kirim_notifikasi_admin(
        $conn,
        'Pendaftaran ' . $jenis . ' baru',
        ($_SESSION['nama_lengkap'] ?? ('User #' . $uid))
            . ' mengajukan pendaftaran ' . $jenis . ' (#' . $id_pendaftaran . '). Perlu verifikasi berkas.'
    );


    // =================================================
    // 11. SIMPAN SEMUA PERUBAHAN
    // =================================================

    $conn->commit();


    // =================================================
    // 12. ARAHKAN KE DETAIL PENDAFTARAN
    // =================================================

    header(
        'Location: detail_pendaftaran.php?id=' . $id_pendaftaran
    );

    exit;


} catch (Throwable $e) {

    // Kalau ada error, batalkan semua perubahan
    $conn->rollback();

    // Untuk sementara tampilkan error supaya gampang
    // mengetahui masalahnya saat development
    die(
        'Gagal membuat pendaftaran: ' .
        htmlspecialchars($e->getMessage())
    );
}
?>