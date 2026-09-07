<?php

session_start();

require_once __DIR__ . '/functions.php';

require_user();


// =====================================================
// 1. HARUS POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: dashboard.php');

    exit;
}


// =====================================================
// 2. USER
// =====================================================

$uid = user_id();


// =====================================================
// 3. ID PENDAFTARAN
// =====================================================

$id_pendaftaran = isset($_POST['id_pendaftaran'])
    ? (int) $_POST['id_pendaftaran']
    : 0;


if ($id_pendaftaran <= 0) {

    header('Location: dashboard.php');

    exit;
}


// =====================================================
// 4. CEK PENDAFTARAN MILIK USER
// =====================================================

$stmt = $conn->prepare("
    SELECT
        id_pendaftaran,
        jenis_pendaftaran

    FROM pendaftaran

    WHERE id_pendaftaran = ?
    AND id_user = ?

    LIMIT 1
");

$stmt->bind_param(
    'ii',
    $id_pendaftaran,
    $uid
);

$stmt->execute();

$result = $stmt->get_result();

$pendaftaran = $result->fetch_assoc();

$stmt->close();


if (!$pendaftaran) {

    header('Location: dashboard.php');

    exit;
}


// =====================================================
// 5. CEK FILE
// =====================================================

if (
    !isset($_FILES['bukti_pembayaran']) ||
    $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK
) {

    header(
        'Location: pembayaran.php?id=' .
        $id_pendaftaran .
        '&pesan=gagal'
    );

    exit;
}


$file = $_FILES['bukti_pembayaran'];


// =====================================================
// 6. BATAS UKURAN 2 MB
// =====================================================

$max_size = 2 * 1024 * 1024;

if ($file['size'] > $max_size) {

    header(
        'Location: pembayaran.php?id=' .
        $id_pendaftaran .
        '&pesan=ukuran'
    );

    exit;
}


// =====================================================
// 7. CEK MIME FILE
// =====================================================

$finfo = finfo_open(FILEINFO_MIME_TYPE);

$mime = finfo_file(
    $finfo,
    $file['tmp_name']
);

finfo_close($finfo);


$allowed_types = [
    'image/jpeg',
    'image/png'
];


if (!in_array($mime, $allowed_types, true)) {

    header(
        'Location: pembayaran.php?id=' .
        $id_pendaftaran .
        '&pesan=format'
    );

    exit;
}


// =====================================================
// 8. TENTUKAN EXTENSION
// =====================================================

if ($mime === 'image/png') {

    $extension = 'png';

} else {

    $extension = 'jpg';

}


// =====================================================
// 9. BUAT FOLDER
// =====================================================

$upload_dir = __DIR__ . '/../uploads/pembayaran';


if (!is_dir($upload_dir)) {

    if (!mkdir(
        $upload_dir,
        0755,
        true
    )) {

        header(
            'Location: pembayaran.php?id=' .
            $id_pendaftaran .
            '&pesan=gagal'
        );

        exit;
    }
}


// =====================================================
// 10. NAMA FILE
// =====================================================

$filename =
    'bukti_' .
    $id_pendaftaran .
    '_' .
    $uid .
    '_' .
    time() .
    '_' .
    bin2hex(random_bytes(5)) .
    '.' .
    $extension;


$destination =
    $upload_dir .
    '/' .
    $filename;


$relative_path =
    'uploads/pembayaran/' .
    $filename;


// =====================================================
// 11. PINDAHKAN FILE
// =====================================================

if (
    !move_uploaded_file(
        $file['tmp_name'],
        $destination
    )
) {

    header(
        'Location: pembayaran.php?id=' .
        $id_pendaftaran .
        '&pesan=gagal'
    );

    exit;
}


// =====================================================
// 12. UPDATE DATABASE
// =====================================================

try {

    $conn->begin_transaction();


    $status = 'menunggu_verifikasi';


    $stmt = $conn->prepare("
        UPDATE pembayaran

        SET
            bukti_pembayaran = ?,
            status = ?,
            tanggal_bayar = NOW()

        WHERE id_pendaftaran = ?

        LIMIT 1
    ");


    $stmt->bind_param(
        'ssi',
        $relative_path,
        $status,
        $id_pendaftaran
    );


    $stmt->execute();


    if ($stmt->affected_rows === 0) {

        throw new Exception(
            'Data pembayaran tidak ditemukan.'
        );

    }


    $stmt->close();


    // =================================================
    // 13. NOTIFIKASI USER
    // =================================================

    notify_user(
        $uid,
        'Bukti pembayaran dikirim',
        'Bukti pembayaran ' .
        $pendaftaran['jenis_pendaftaran'] .
        ' berhasil dikirim dan sedang menunggu verifikasi admin.'
    );

    require_once __DIR__ . '/../notifikasi_helper.php';
    kirim_notifikasi_admin(
        $conn,
        'Bukti pembayaran baru',
        ($_SESSION['nama_lengkap'] ?? ('User #' . $uid))
            . ' mengunggah bukti pembayaran ' . $pendaftaran['jenis_pendaftaran']
            . ' (pendaftaran #' . $id_pendaftaran . '). Perlu verifikasi.'
    );


    // =================================================
    // 14. COMMIT
    // =================================================

    $conn->commit();


    // =================================================
    // 15. REDIRECT
    // =================================================

    header(
        'Location: pembayaran.php?id=' .
        $id_pendaftaran .
        '&pesan=berhasil'
    );

    exit;


} catch (Throwable $e) {

    $conn->rollback();


    // Hapus file kalau database gagal
    if (file_exists($destination)) {

        unlink($destination);

    }


    die(
        'Gagal menyimpan pembayaran: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

?>