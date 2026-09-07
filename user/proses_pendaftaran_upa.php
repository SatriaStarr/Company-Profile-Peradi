<?php

session_start();

require_once __DIR__ . '/functions.php';

require_user();


// =====================================================
// 1. HARUS POST
// =====================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: pendaftaran_upa.php');

    exit;
}


// =====================================================
// 2. USER
// =====================================================

$uid = user_id();


// =====================================================
// 3. DATA FORM
// =====================================================

$nama_lengkap =
    trim($_POST['nama_lengkap'] ?? '');

$nama_lengkap_gelar =
    trim($_POST['nama_lengkap_gelar'] ?? '');

$jenis_identitas =
    trim($_POST['jenis_identitas'] ?? '');

$nomor_identitas =
    trim($_POST['nomor_identitas'] ?? '');

$masa_berakhir_identitas =
    trim($_POST['masa_berakhir_identitas'] ?? '');

$jenis_kelamin =
    trim($_POST['jenis_kelamin'] ?? '');

$tempat_lahir =
    trim($_POST['tempat_lahir'] ?? '');

$tanggal_lahir =
    trim($_POST['tanggal_lahir'] ?? '');

$no_hp =
    trim($_POST['no_hp'] ?? '');

$email =
    trim($_POST['email'] ?? '');

$alamat =
    trim($_POST['alamat'] ?? '');

$rt =
    trim($_POST['rt'] ?? '');

$rw =
    trim($_POST['rw'] ?? '');

$no_rumah =
    trim($_POST['no_rumah'] ?? '');

$kelurahan =
    trim($_POST['kelurahan'] ?? '');

$kecamatan =
    trim($_POST['kecamatan'] ?? '');

$kota =
    trim($_POST['kota'] ?? '');

$provinsi =
    trim($_POST['provinsi'] ?? '');

$kode_pos =
    trim($_POST['kode_pos'] ?? '');

$kota_ujian =
    trim($_POST['kota_ujian'] ?? '');

$dpc_dipilih =
    trim($_POST['dpc_dipilih'] ?? '');

$kebutuhan_khusus =
    trim($_POST['kebutuhan_khusus'] ?? '');

$keterangan_kebutuhan =
    trim($_POST['keterangan_kebutuhan'] ?? '');

$tahun_lulus =
    trim($_POST['tahun_lulus'] ?? '');

$no_ijazah =
    trim($_POST['no_ijazah'] ?? '');

$fakultas =
    trim($_POST['fakultas'] ?? '');

$nama_universitas =
    trim($_POST['nama_universitas'] ?? '');

$nama_sertifikat_pkpa =
    trim($_POST['nama_sertifikat_pkpa'] ?? '');

$nomor_sertifikat_pkpa =
    trim($_POST['nomor_sertifikat_pkpa'] ?? '');

$tanggal_sertifikat_pkpa =
    trim($_POST['tanggal_sertifikat_pkpa'] ?? '');

$pelaksana_pkpa =
    trim($_POST['pelaksana_pkpa'] ?? '');

$tanda_tangan =
    $_POST['tanda_tangan'] ?? '';


// =====================================================
// 4. VALIDASI DATA WAJIB
// =====================================================

$wajib = [
    $nama_lengkap,
    $nama_lengkap_gelar,
    $jenis_identitas,
    $nomor_identitas,
    $jenis_kelamin,
    $tempat_lahir,
    $tanggal_lahir,
    $no_hp,
    $email,
    $alamat,
    $kota,
    $provinsi,
    $kota_ujian,
    $dpc_dipilih,
    $kebutuhan_khusus,
    $tahun_lulus,
    $no_ijazah,
    $fakultas,
    $nama_universitas,
    $nama_sertifikat_pkpa,
    $nomor_sertifikat_pkpa,
    $tanggal_sertifikat_pkpa,
    $pelaksana_pkpa,
    $tanda_tangan
];


foreach ($wajib as $value) {

    if ($value === '') {

        header(
            'Location: pendaftaran_upa.php?pesan=data_tidak_lengkap'
        );

        exit;
    }
}


// =====================================================
// 5. VALIDASI IDENTITAS
// =====================================================

if (
    !in_array(
        $jenis_identitas,
        ['KTP', 'SIM', 'Paspor'],
        true
    )
) {

    header(
        'Location: pendaftaran_upa.php?pesan=data_tidak_lengkap'
    );

    exit;
}


// =====================================================
// 6. VALIDASI NIK / IDENTITAS
// =====================================================

if (
    $jenis_identitas === 'KTP' &&
    !preg_match(
        '/^[0-9]{16}$/',
        $nomor_identitas
    )
) {

    header(
        'Location: pendaftaran_upa.php?pesan=nik'
    );

    exit;
}


// =====================================================
// 7. VALIDASI EMAIL
// =====================================================

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    header(
        'Location: pendaftaran_upa.php?pesan=data_tidak_lengkap'
    );

    exit;
}


// =====================================================
// 8. CEK PENDAFTARAN UPA AKTIF
// =====================================================

$cek = $conn->prepare("
    SELECT id_pendaftaran
    FROM pendaftaran
    WHERE id_user = ?
    AND jenis_pendaftaran = 'UPA'
    AND status <> 'ditolak'
    LIMIT 1
");

$cek->bind_param(
    'i',
    $uid
);

$cek->execute();

$hasil =
    $cek->get_result();

if ($hasil->num_rows > 0) {

    $cek->close();

    header(
        'Location: pendaftaran_upa.php?pesan=sudah_daftar'
    );

    exit;
}

$cek->close();


// =====================================================
// 9. FUNGSI UPLOAD
// =====================================================

function upload_upa_file(
    $file,
    $folder,
    $prefix,
    $allow_pdf = false
) {

    if (
        !isset($file) ||
        $file['error'] !== UPLOAD_ERR_OK
    ) {

        throw new Exception(
            'File gagal diupload.'
        );
    }


    if (
        $file['size'] >
        2 * 1024 * 1024
    ) {

        throw new Exception(
            'Ukuran file maksimal 2 MB.'
        );
    }


    $finfo =
        finfo_open(
            FILEINFO_MIME_TYPE
        );

    $mime =
        finfo_file(
            $finfo,
            $file['tmp_name']
        );

    finfo_close($finfo);


    $allowed = [
        'image/jpeg',
        'image/png'
    ];


    if ($allow_pdf) {

        $allowed[] =
            'application/pdf';
    }


    if (
        !in_array(
            $mime,
            $allowed,
            true
        )
    ) {

        throw new Exception(
            'Format file tidak diperbolehkan.'
        );
    }


    switch ($mime) {

        case 'image/png':
            $extension = 'png';
            break;

        case 'application/pdf':
            $extension = 'pdf';
            break;

        default:
            $extension = 'jpg';
            break;
    }


    $filename =
        $prefix .
        '_' .
        time() .
        '_' .
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


    return
        $folder .
        '/' .
        $filename;
}


// =====================================================
// 10. MULAI TRANSAKSI
// =====================================================

$conn->begin_transaction();


try {


    // =================================================
    // 11. UPLOAD DOKUMEN
    // =================================================

    $foto =
        upload_upa_file(
            $_FILES['foto'],
            'uploads/upa/foto',
            'upa_foto'
        );


    $foto_ktp =
        upload_upa_file(
            $_FILES['foto_ktp'],
            'uploads/upa/ktp',
            'upa_ktp'
        );


    $foto_ijazah =
        upload_upa_file(
            $_FILES['foto_ijazah'],
            'uploads/upa/ijazah',
            'upa_ijazah',
            true
        );


    $sertifikat_pkpa =
        upload_upa_file(
            $_FILES['sertifikat_pkpa'],
            'uploads/upa/sertifikat_pkpa',
            'upa_sertifikat',
            true
        );


    // =================================================
    // 12. SIMPAN TANDA TANGAN
    // =================================================

    if (
        !preg_match(
            '/^data:image\/png;base64,/',
            $tanda_tangan
        )
    ) {

        throw new Exception(
            'Tanda tangan tidak valid.'
        );
    }


    $signature_data =
        preg_replace(
            '/^data:image\/png;base64,/',
            '',
            $tanda_tangan
        );


    $signature_data =
        str_replace(
            ' ',
            '+',
            $signature_data
        );


    $signature_binary =
        base64_decode(
            $signature_data,
            true
        );


    if ($signature_binary === false) {

        throw new Exception(
            'Tanda tangan tidak valid.'
        );
    }


    $signature_dir =
        __DIR__ .
        '/uploads/upa/tanda_tangan';


    if (!is_dir($signature_dir)) {

        mkdir(
            $signature_dir,
            0755,
            true
        );
    }


    $signature_name =
        'upa_ttd_' .
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

        throw new Exception(
            'Gagal menyimpan tanda tangan.'
        );
    }


    $tanda_tangan_path =
        'uploads/upa/tanda_tangan/' .
        $signature_name;


    // =================================================
    // 13. PENDAFTARAN UTAMA
    // =================================================

    $jenis =
        'UPA';

    $status =
        'menunggu_verifikasi';


    $stmt =
        $conn->prepare("
            INSERT INTO pendaftaran
            (
                id_user,
                jenis_pendaftaran,
                status
            )
            VALUES
            (?, ?, ?)
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
    // 14. DETAIL UPA
    // =================================================

    // nik diisi sama dengan nomor identitas bila KTP
    $nik_val = ($jenis_identitas === 'KTP') ? $nomor_identitas : null;

    $stmt =
        $conn->prepare("
            INSERT INTO pendaftaran_upa
            (
                id_pendaftaran,
                nik,
                nama_lengkap,
                nama_lengkap_gelar,
                jenis_identitas,
                nomor_identitas,
                masa_berakhir_identitas,
                jenis_kelamin,
                tempat_lahir,
                tanggal_lahir,
                no_hp,
                email,
                alamat,
                rt,
                rw,
                no_rumah,
                kelurahan,
                kecamatan,
                kota,
                provinsi,
                kode_pos,
                kota_ujian,
                dpc_dipilih,
                kebutuhan_khusus,
                keterangan_kebutuhan,
                tahun_lulus,
                no_ijazah,
                fakultas,
                nama_universitas,
                nama_sertifikat_pkpa,
                nomor_sertifikat_pkpa,
                tanggal_sertifikat_pkpa,
                pelaksana_pkpa,
                foto,
                foto_ktp,
                foto_ijazah,
                sertifikat_pkpa,
                tanda_tangan
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?
            )
        ");


    $stmt->bind_param(
        'is' . str_repeat('s', 36),
        $id_pendaftaran,
        $nik_val,
        $nama_lengkap,
        $nama_lengkap_gelar,
        $jenis_identitas,
        $nomor_identitas,
        $masa_berakhir_identitas,
        $jenis_kelamin,
        $tempat_lahir,
        $tanggal_lahir,
        $no_hp,
        $email,
        $alamat,
        $rt,
        $rw,
        $no_rumah,
        $kelurahan,
        $kecamatan,
        $kota,
        $provinsi,
        $kode_pos,
        $kota_ujian,
        $dpc_dipilih,
        $kebutuhan_khusus,
        $keterangan_kebutuhan,
        $tahun_lulus,
        $no_ijazah,
        $fakultas,
        $nama_universitas,
        $nama_sertifikat_pkpa,
        $nomor_sertifikat_pkpa,
        $tanggal_sertifikat_pkpa,
        $pelaksana_pkpa,
        $foto,
        $foto_ktp,
        $foto_ijazah,
        $sertifikat_pkpa,
        $tanda_tangan_path
    );


    $stmt->execute();

    $stmt->close();


    // =================================================
    // 15. PEMBAYARAN UPA
    // =================================================

    $jumlah =
        750000;

    $status_pembayaran =
        'belum_bayar';


    $stmt =
        $conn->prepare("
            INSERT INTO pembayaran
            (
                id_pendaftaran,
                jumlah,
                status
            )
            VALUES
            (?, ?, ?)
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
    // 16. NOTIFIKASI
    // =================================================

    notify_user(
        $uid,
        'Pendaftaran UPA berhasil',
        'Pendaftaran UPA berhasil dibuat. Silakan melakukan pembayaran dan menunggu verifikasi.'
    );

    require_once __DIR__ . '/../notifikasi_helper.php';
    kirim_notifikasi_admin(
        $conn,
        'Pendaftaran UPA baru',
        ($_SESSION['nama_lengkap'] ?? ('User #' . $uid))
            . ' mengajukan pendaftaran UPA (#' . $id_pendaftaran . '). Perlu verifikasi berkas.'
    );


    // =================================================
    // 17. COMMIT
    // =================================================

    $conn->commit();


    // =================================================
    // 18. ARAHKAN KE PEMBAYARAN
    // =================================================

    header(
        'Location: pembayaran.php?id=' .
        $id_pendaftaran
    );

    exit;


} catch (Throwable $e) {

    $conn->rollback();

    die(
        'Gagal membuat pendaftaran UPA: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}

?>