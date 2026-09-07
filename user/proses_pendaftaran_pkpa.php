<?php

session_start();

require_once __DIR__ . '/functions.php';

require_user();


// =====================================================
// 1. PASTIKAN REQUEST POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: pendaftaran_pkpa.php');
    exit;
}


// =====================================================
// 2. AMBIL USER
// =====================================================

$uid = user_id();


// =====================================================
// 3. AMBIL DATA FORM
// =====================================================

$nama_lengkap  = trim($_POST['nama_lengkap'] ?? '');
$nik           = trim($_POST['nik'] ?? '');
$tempat_lahir  = trim($_POST['tempat_lahir'] ?? '');
$tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
$pekerjaan     = trim($_POST['pekerjaan'] ?? '');
$alamat        = trim($_POST['alamat'] ?? '');
$no_hp         = trim($_POST['no_hp'] ?? '');
$tanda_tangan  = $_POST['tanda_tangan'] ?? '';


// =====================================================
// 4. VALIDASI DATA
// =====================================================

if (
    $nama_lengkap === '' ||
    $nik === '' ||
    $tempat_lahir === '' ||
    $tanggal_lahir === '' ||
    $pekerjaan === '' ||
    $alamat === '' ||
    $no_hp === '' ||
    $tanda_tangan === ''
) {

    header(
        'Location: pendaftaran_pkpa.php?pesan=data_tidak_lengkap'
    );

    exit;
}


// =====================================================
// VALIDASI NIK
// =====================================================

if (!preg_match('/^[0-9]{16}$/', $nik)) {

    header(
        'Location: pendaftaran_pkpa.php?pesan=nik'
    );

    exit;
}


// =====================================================
// VALIDASI TANGGAL
// =====================================================

$date = DateTime::createFromFormat(
    'Y-m-d',
    $tanggal_lahir
);

if (
    !$date ||
    $date->format('Y-m-d') !== $tanggal_lahir
) {

    header(
        'Location: pendaftaran_pkpa.php?pesan=tanggal'
    );

    exit;
}


// =====================================================
// 5. CEK PENDAFTARAN PKPA SEBELUMNYA
// =====================================================

$cek = $conn->prepare("
    SELECT
        p.id_pendaftaran,
        p.status,
        py.status AS status_pembayaran
    FROM pendaftaran p
    LEFT JOIN pembayaran py
        ON py.id_pendaftaran = p.id_pendaftaran
    WHERE p.id_user = ?
    AND p.jenis_pendaftaran = 'PKPA'
    AND p.status <> 'ditolak'
    ORDER BY p.id_pendaftaran DESC
    LIMIT 1
");

$cek->bind_param(
    'i',
    $uid
);

$cek->execute();

$hasil = $cek->get_result();


// =====================================================
// KALAU SUDAH ADA PENDAFTARAN
// =====================================================

if ($hasil->num_rows > 0) {

    $data_lama = $hasil->fetch_assoc();

    $cek->close();

    $id_lama = (int) $data_lama['id_pendaftaran'];

    $status_bayar =
        $data_lama['status_pembayaran'] ?? 'belum_bayar';


    // -------------------------------------------------
    // KALAU BELUM BAYAR
    // LANGSUNG KE HALAMAN PEMBAYARAN
    // -------------------------------------------------

    if (
        $status_bayar === 'belum_bayar' ||
        $status_bayar === 'menunggu_pembayaran'
    ) {

        header(
            'Location: pembayaran.php?id=' . $id_lama
        );

        exit;
    }


    // -------------------------------------------------
    // KALAU SUDAH BAYAR / SUDAH DIPROSES
    // KE DETAIL
    // -------------------------------------------------

    header(
        'Location: detail_pendaftaran.php?id=' . $id_lama
    );

    exit;
}

$cek->close();


// =====================================================
// 6. VALIDASI FILE
// =====================================================

$allowed_types = [
    'image/jpeg',
    'image/png'
];

$max_size = 2 * 1024 * 1024;


// =====================================================
// FUNGSI UPLOAD
// =====================================================

function upload_file(
    $file,
    $folder,
    $prefix
) {

    global $allowed_types, $max_size;


    if (
        !isset($file) ||
        $file['error'] !== UPLOAD_ERR_OK
    ) {

        throw new Exception(
            'File gagal diupload.'
        );
    }


    if ($file['size'] > $max_size) {

        throw new Exception(
            'Ukuran file maksimal 2 MB.'
        );
    }


    $finfo = finfo_open(
        FILEINFO_MIME_TYPE
    );

    $mime = finfo_file(
        $finfo,
        $file['tmp_name']
    );

    finfo_close($finfo);


    if (
        !in_array(
            $mime,
            $allowed_types,
            true
        )
    ) {

        throw new Exception(
            'Format file tidak diperbolehkan.'
        );
    }


    $extension =
        ($mime === 'image/png')
        ? 'png'
        : 'jpg';


    $filename =
        $prefix . '_' .
        time() . '_' .
        bin2hex(
            random_bytes(5)
        ) .
        '.' .
        $extension;


    $upload_dir =
        __DIR__ .
        '/' .
        $folder;


    if (!is_dir($upload_dir)) {

        if (
            !mkdir(
                $upload_dir,
                0755,
                true
            )
        ) {

            throw new Exception(
                'Folder upload tidak dapat dibuat.'
            );
        }
    }


    $destination =
        $upload_dir .
        '/' .
        $filename;


    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {

        throw new Exception(
            'Gagal menyimpan file.'
        );
    }


    return $folder .
        '/' .
        $filename;
}


// =====================================================
// 7. SIMPAN TANDA TANGAN
// =====================================================

$tanda_tangan_path = null;


if (
    preg_match(
        '/^data:image\/png;base64,/',
        $tanda_tangan
    )
) {

    $data = preg_replace(
        '/^data:image\/png;base64,/',
        '',
        $tanda_tangan
    );


    $data = str_replace(
        ' ',
        '+',
        $data
    );


    $signature_binary =
        base64_decode(
            $data,
            true
        );


    if (
        $signature_binary === false
    ) {

        header(
            'Location: pendaftaran_pkpa.php?pesan=ttd'
        );

        exit;
    }


    $signature_dir =
        __DIR__ .
        '/uploads/tanda_tangan';


    if (!is_dir($signature_dir)) {

        mkdir(
            $signature_dir,
            0755,
            true
        );
    }


    $signature_name =
        'ttd_' .
        $uid .
        '_' .
        time() .
        '.png';


    $signature_file =
        $signature_dir .
        '/' .
        $signature_name;


    if (
        file_put_contents(
            $signature_file,
            $signature_binary
        ) === false
    ) {

        header(
            'Location: pendaftaran_pkpa.php?pesan=ttd'
        );

        exit;
    }


    $tanda_tangan_path =
        'uploads/tanda_tangan/' .
        $signature_name;

} else {

    header(
        'Location: pendaftaran_pkpa.php?pesan=ttd'
    );

    exit;
}


// =====================================================
// 8. MULAI TRANSAKSI
// =====================================================

$conn->begin_transaction();


try {


    // =================================================
    // 9. UPLOAD FOTO
    // =================================================

    $foto = upload_file(
        $_FILES['foto'],
        'uploads/foto',
        'foto'
    );


    // =================================================
    // 10. UPLOAD IJAZAH
    // =================================================

    $foto_ijazah = upload_file(
        $_FILES['foto_ijazah'],
        'uploads/ijazah',
        'ijazah'
    );


    // =================================================
    // 11. UPLOAD KTP
    // =================================================

    $foto_ktp = upload_file(
        $_FILES['foto_ktp'],
        'uploads/ktp',
        'ktp'
    );


    // =================================================
    // 12. BUAT PENDAFTARAN
    // =================================================

    $jenis = 'PKPA';

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


    $id_pendaftaran =
        $conn->insert_id;


    $stmt->close();


    // =================================================
    // 13. DATA KHUSUS PKPA
    // =================================================

    $stmt = $conn->prepare("
        INSERT INTO pendaftaran_pkpa
        (
            id_pendaftaran,
            nama_lengkap,
            nik,
            tempat_lahir,
            tanggal_lahir,
            pekerjaan,
            alamat,
            no_hp,
            foto,
            foto_ijazah,
            foto_ktp,
            tanda_tangan
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");


    $stmt->bind_param(
        'isssssssssss',
        $id_pendaftaran,
        $nama_lengkap,
        $nik,
        $tempat_lahir,
        $tanggal_lahir,
        $pekerjaan,
        $alamat,
        $no_hp,
        $foto,
        $foto_ijazah,
        $foto_ktp,
        $tanda_tangan_path
    );


    $stmt->execute();

    $stmt->close();


    // =================================================
    // 14. BUAT TAGIHAN PEMBAYARAN
    // =================================================

    $jumlah = 8000000;

    $status_pembayaran =
        'belum_bayar';


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
    // 15. NOTIFIKASI
    // =================================================

    notify_user(
        $uid,
        'Pendaftaran PKPA berhasil',
        'Pendaftaran PKPA berhasil dibuat. Silakan melakukan pembayaran.'
    );


    // =================================================
    // 16. COMMIT
    // =================================================

    $conn->commit();


    // =================================================
    // 17. LANGSUNG KE PEMBAYARAN
    // =================================================

    header(
        'Location: pembayaran.php?id=' .
        $id_pendaftaran
    );

    exit;


    } catch (Throwable $e) {


    // =================================================
    // ROLLBACK
    // =================================================

    $conn->rollback();


    die(
        'Gagal membuat pendaftaran PKPA: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

?>