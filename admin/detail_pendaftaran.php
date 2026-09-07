<?php

session_start();

require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/partials/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
}


/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['status_login']) ||
    $_SESSION['status_login'] !== true
) {
    header("Location: login.php?pesan=belum_login");
    exit;
}

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'super_admin'], true)) {
    header("Location: login.php?pesan=no_access");
    exit;
}


/*
|--------------------------------------------------------------------------
| FUNGSI DASAR
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


function redirectDetail($id, $pesan = '')
{
    $url = 'detail_pendaftaran.php?id=' . (int) $id;

    if ($pesan !== '') {
        $url .= '&pesan=' . urlencode($pesan);
    }

    header("Location: " . $url);
    exit;
}


/*
|--------------------------------------------------------------------------
| CEK TABEL
|--------------------------------------------------------------------------
*/

function tableExists($conn, $table)
{
    $table = mysqli_real_escape_string($conn, $table);

    $result = mysqli_query(
        $conn,
        "SHOW TABLES LIKE '$table'"
    );

    return (
        $result &&
        mysqli_num_rows($result) > 0
    );
}


/*
|--------------------------------------------------------------------------
| CEK KOLOM
|--------------------------------------------------------------------------
*/

function columnExists($conn, $table, $column)
{
    if (!tableExists($conn, $table)) {
        return false;
    }

    $table = str_replace('`', '', $table);
    $column = mysqli_real_escape_string($conn, $column);

    $result = mysqli_query(
        $conn,
        "SHOW COLUMNS FROM `$table` LIKE '$column'"
    );

    return (
        $result &&
        mysqli_num_rows($result) > 0
    );
}


/*
|--------------------------------------------------------------------------
| AMBIL NAMA KOLOM JENIS PENDAFTARAN
|--------------------------------------------------------------------------
*/

$jenisColumn = 'jenis';

if (!columnExists(
    $conn,
    'pendaftaran',
    'jenis'
)) {

    if (
        columnExists(
            $conn,
            'pendaftaran',
            'jenis_pendaftaran'
        )
    ) {
        $jenisColumn = 'jenis_pendaftaran';
    }
}


/*
|--------------------------------------------------------------------------
| ID PENDAFTARAN
|--------------------------------------------------------------------------
*/

$id_pendaftaran =
    (int) ($_GET['id'] ?? 0);


if ($id_pendaftaran <= 0) {

    header(
        "Location: data_pendaftaran.php?pesan=gagal"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| PESAN
|--------------------------------------------------------------------------
*/

$pesan =
    $_GET['pesan'] ?? '';

$alert_message = '';
$alert_type = '';


$pesan_map = [

    'status_berhasil'
        => [
            'Status pendaftaran berhasil diperbarui.',
            'success'
        ],

    'hasil_berhasil'
        => [
            'Hasil ujian berhasil disimpan.',
            'success'
        ],

    'sertifikat_berhasil'
        => [
            'Data sertifikat berhasil disimpan.',
            'success'
        ],

    'gagal'
        => [
            'Terjadi kesalahan. Silakan coba lagi.',
            'danger'
        ],

    'tabel_tidak_ada'
        => [
            'Tabel yang dibutuhkan belum tersedia.',
            'danger'
        ]

];


if (isset($pesan_map[$pesan])) {

    $alert_message =
        $pesan_map[$pesan][0];

    $alert_type =
        $pesan_map[$pesan][1];
}


/*
|--------------------------------------------------------------------------
| DATA ADMIN
|--------------------------------------------------------------------------
*/

$admin_nama =
    $_SESSION['nama_lengkap']
    ?? $_SESSION['username']
    ?? 'Administrator';

$admin_role =
    $role === 'super_admin'
    ? 'SUPER ADMIN'
    : 'ADMIN';


/*
|--------------------------------------------------------------------------
| PROSES UBAH STATUS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'ubah_status'
) {

    $status_baru =
        trim($_POST['status'] ?? '');

    $status_valid = [

        'menunggu_verifikasi',
        'berkas_diperiksa',
        'menunggu_pembayaran',
        'pembayaran_diperiksa',
        'terdaftar',
        'selesai',
        'ditolak'

    ];


    /*
    |--------------------------------------------------------------------------
    | Kompatibilitas status lama
    |--------------------------------------------------------------------------
    */

    if ($status_baru === 'menunggu') {
        $status_baru = 'menunggu_verifikasi';
    }


    if (
        !in_array(
            $status_baru,
            $status_valid,
            true
        )
    ) {

        redirectDetail(
            $id_pendaftaran,
            'gagal'
        );
    }


    if (
        !columnExists(
            $conn,
            'pendaftaran',
            'status'
        )
    ) {

        redirectDetail(
            $id_pendaftaran,
            'gagal'
        );
    }


    $stmt = mysqli_prepare(
        $conn,
        "UPDATE pendaftaran
         SET status = ?
         WHERE id_pendaftaran = ?
         LIMIT 1"
    );


    if (!$stmt) {

        redirectDetail(
            $id_pendaftaran,
            'gagal'
        );
    }


    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $status_baru,
        $id_pendaftaran
    );


    $berhasil =
        mysqli_stmt_execute($stmt);


    mysqli_stmt_close($stmt);


    if (!$berhasil) {

        redirectDetail(
            $id_pendaftaran,
            'gagal'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NOTIFIKASI USER
    |--------------------------------------------------------------------------
    */

    if (
        tableExists(
            $conn,
            'notifikasi'
        )
    ) {

        /*
        |--------------------------------------------------------------
        | Ambil id_user dari pendaftaran
        |--------------------------------------------------------------
        */

        $user_id = 0;


        $stmt_user =
            mysqli_prepare(
                $conn,
                "SELECT id_user
                 FROM pendaftaran
                 WHERE id_pendaftaran = ?
                 LIMIT 1"
            );


        if ($stmt_user) {

            mysqli_stmt_bind_param(
                $stmt_user,
                "i",
                $id_pendaftaran
            );

            mysqli_stmt_execute(
                $stmt_user
            );

            $result_user =
                mysqli_stmt_get_result(
                    $stmt_user
                );

            if ($result_user) {

                $row_user =
                    mysqli_fetch_assoc(
                        $result_user
                    );

                $user_id =
                    (int) (
                        $row_user['id_user']
                        ?? 0
                    );
            }

            mysqli_stmt_close(
                $stmt_user
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Buat notifikasi hanya jika kolom tersedia
        |--------------------------------------------------------------------------
        */

        if (
            $user_id > 0 &&
            columnExists(
                $conn,
                'notifikasi',
                'id_user'
            ) &&
            columnExists(
                $conn,
                'notifikasi',
                'judul'
            ) &&
            columnExists(
                $conn,
                'notifikasi',
                'pesan'
            )
        ) {

            $label_status = [

                'menunggu_verifikasi'
                    => 'Menunggu Verifikasi',

                'berkas_diperiksa'
                    => 'Berkas Diperiksa',

                'menunggu_pembayaran'
                    => 'Menunggu Pembayaran',

                'pembayaran_diperiksa'
                    => 'Pembayaran Diperiksa',

                'terdaftar'
                    => 'Sudah Terdaftar',

                'selesai'
                    => 'Selesai',

                'ditolak'
                    => 'Ditolak'

            ];


            $status_text =
                $label_status[
                    $status_baru
                ]
                ?? $status_baru;


            $judul =
                'Status Pendaftaran Diperbarui';


            $isi =
                'Status pendaftaran Anda sekarang: '
                . $status_text;


            $stmt_notif =
                mysqli_prepare(
                    $conn,
                    "INSERT INTO notifikasi
                     (id_user, judul, pesan)
                     VALUES (?, ?, ?)"
                );


            if ($stmt_notif) {

                mysqli_stmt_bind_param(
                    $stmt_notif,
                    "iss",
                    $user_id,
                    $judul,
                    $isi
                );

                mysqli_stmt_execute(
                    $stmt_notif
                );

                mysqli_stmt_close(
                    $stmt_notif
                );
            }
        }
    }


    redirectDetail(
        $id_pendaftaran,
        'status_berhasil'
    );
}


/*
|--------------------------------------------------------------------------
| PROSES VERIFIKASI PEMBAYARAN (manual/transfer)
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'verifikasi_pembayaran'
) {
    $keputusan = $_POST['keputusan'] ?? '';
    if (!in_array($keputusan, ['lunas', 'ditolak'], true)) {
        redirectDetail($id_pendaftaran, 'gagal');
    }

    $tgl = $keputusan === 'lunas' ? date('Y-m-d H:i:s') : null;
    $stmt = mysqli_prepare(
        $conn,
        'UPDATE pembayaran SET status = ?, tanggal_bayar = COALESCE(tanggal_bayar, ?)
         WHERE id_pendaftaran = ? ORDER BY id_pembayaran DESC LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'ssi', $keputusan, $tgl, $id_pendaftaran);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$ok) {
        redirectDetail($id_pendaftaran, 'gagal');
    }

    /* jika lunas, dorong status pendaftaran ke "terdaftar" bila masih di tahap pembayaran */
    if ($keputusan === 'lunas') {
        mysqli_query(
            $conn,
            "UPDATE pendaftaran SET status = 'terdaftar'
             WHERE id_pendaftaran = " . (int) $id_pendaftaran . "
             AND status IN ('menunggu_verifikasi','berkas_diperiksa','menunggu_pembayaran','pembayaran_diperiksa')"
        );
    }

    /* notifikasi user */
    $uid_n = 0;
    if ($su = mysqli_prepare($conn, 'SELECT id_user FROM pendaftaran WHERE id_pendaftaran = ? LIMIT 1')) {
        mysqli_stmt_bind_param($su, 'i', $id_pendaftaran);
        mysqli_stmt_execute($su);
        $uid_n = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($su))['id_user'] ?? 0);
        mysqli_stmt_close($su);
    }
    if ($uid_n > 0) {
        require_once __DIR__ . '/../notifikasi_helper.php';
        if ($keputusan === 'lunas') {
            kirim_notifikasi($conn, $uid_n, 'Pembayaran diverifikasi',
                'Pembayaran Anda telah diverifikasi dan dinyatakan LUNAS. Anda resmi terdaftar.');
        } else {
            kirim_notifikasi($conn, $uid_n, 'Pembayaran ditolak',
                'Bukti pembayaran Anda ditolak admin. Silakan unggah ulang bukti pembayaran yang benar.');
        }
    }

    redirectDetail($id_pendaftaran, 'status_berhasil');
}


/*
|--------------------------------------------------------------------------
| PROSES HASIL UJIAN
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'simpan_hasil'
) {

    if (
        !tableExists(
            $conn,
            'hasil'
        )
    ) {

        redirectDetail(
            $id_pendaftaran,
            'tabel_tidak_ada'
        );
    }


    $nilai_raw =
        trim($_POST['nilai'] ?? '');

    $status_lulus =
        trim(
            $_POST['status_lulus']
            ?? ''
        );

    /* normalisasi ke nilai enum kolom hasil.status_lulus
       ('Lulus','Tidak Lulus','Belum Diumumkan') — terima juga
       nilai lama 'lulus'/'tidak_lulus' dari form */
    $map_lulus = [
        'lulus'          => 'Lulus',
        'Lulus'          => 'Lulus',
        'tidak_lulus'    => 'Tidak Lulus',
        'Tidak Lulus'    => 'Tidak Lulus',
    ];
    $status_lulus = $map_lulus[$status_lulus] ?? 'Belum Diumumkan';

    $catatan =
        trim(
            $_POST['catatan']
            ?? ''
        );


    $nilai = null;

    if ($nilai_raw !== '') {
        $nilai = (float) $nilai_raw;
    }


    /*
    |--------------------------------------------------------------------------
    | CEK apakah data hasil sudah ada
    |--------------------------------------------------------------------------
    */

    $hasil_id_column = null;

    if (
        columnExists(
            $conn,
            'hasil',
            'id_hasil'
        )
    ) {
        $hasil_id_column = 'id_hasil';
    }


    $hasil_exists = false;


    if (
        columnExists(
            $conn,
            'hasil',
            'id_pendaftaran'
        )
    ) {

        $stmt_cek =
            mysqli_prepare(
                $conn,
                "SELECT id_pendaftaran
                 FROM hasil
                 WHERE id_pendaftaran = ?
                 LIMIT 1"
            );


        if ($stmt_cek) {

            mysqli_stmt_bind_param(
                $stmt_cek,
                "i",
                $id_pendaftaran
            );

            mysqli_stmt_execute(
                $stmt_cek
            );

            $result_cek =
                mysqli_stmt_get_result(
                    $stmt_cek
                );

            $hasil_exists =
                $result_cek &&
                mysqli_num_rows(
                    $result_cek
                ) > 0;

            mysqli_stmt_close(
                $stmt_cek
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Susun kolom yang tersedia
    |--------------------------------------------------------------------------
    */

    $columns = [];
    $values = [];
    $types = '';
    $params = [];


    if (
        columnExists(
            $conn,
            'hasil',
            'nilai'
        )
    ) {

        $columns[] = 'nilai';
        $values[] = '?';
        $types .= 'd';
        $params[] = $nilai;
    }


    if (
        columnExists(
            $conn,
            'hasil',
            'status_lulus'
        )
    ) {

        $columns[] = 'status_lulus';
        $values[] = '?';
        $types .= 's';
        $params[] = $status_lulus;
    }


    if (
        columnExists(
            $conn,
            'hasil',
            'catatan'
        )
    ) {

        $columns[] = 'catatan';
        $values[] = '?';
        $types .= 's';
        $params[] = $catatan;
    }


    if (empty($columns)) {

        redirectDetail(
            $id_pendaftaran,
            'gagal'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($hasil_exists) {

        $set = [];

        foreach ($columns as $column) {
            $set[] = "`$column` = ?";
        }


        $sql =
            "UPDATE hasil
             SET "
            . implode(
                ', ',
                $set
            )
            . "
             WHERE id_pendaftaran = ?
             LIMIT 1";


        $params_update =
            $params;

        $types_update =
            $types . 'i';

        $params_update[] =
            $id_pendaftaran;


        $stmt =
            mysqli_prepare(
                $conn,
                $sql
            );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                $types_update,
                ...$params_update
            );

            mysqli_stmt_execute(
                $stmt
            );

            mysqli_stmt_close(
                $stmt
            );
        }

    } else {

        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        array_unshift(
            $columns,
            'id_pendaftaran'
        );

        array_unshift(
            $values,
            '?'
        );

        $types =
            'i' . $types;

        array_unshift(
            $params,
            $id_pendaftaran
        );


        $sql =
            "INSERT INTO hasil
             (`"
            . implode(
                '`, `',
                $columns
            )
            . "`)
             VALUES ("
            . implode(
                ', ',
                $values
            )
            . ")" ;


        $stmt =
            mysqli_prepare(
                $conn,
                $sql
            );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                $types,
                ...$params
            );

            mysqli_stmt_execute(
                $stmt
            );

            mysqli_stmt_close(
                $stmt
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | NOTIFIKASI HASIL
    |--------------------------------------------------------------------------
    */

    if (
        tableExists(
            $conn,
            'notifikasi'
        ) &&
        columnExists(
            $conn,
            'notifikasi',
            'id_user'
        ) &&
        columnExists(
            $conn,
            'notifikasi',
            'judul'
        ) &&
        columnExists(
            $conn,
            'notifikasi',
            'pesan'
        )
    ) {

        $user_id = 0;


        $stmt_user =
            mysqli_prepare(
                $conn,
                "SELECT id_user
                 FROM pendaftaran
                 WHERE id_pendaftaran = ?
                 LIMIT 1"
            );


        if ($stmt_user) {

            mysqli_stmt_bind_param(
                $stmt_user,
                "i",
                $id_pendaftaran
            );

            mysqli_stmt_execute(
                $stmt_user
            );

            $result_user =
                mysqli_stmt_get_result(
                    $stmt_user
                );


            if ($result_user) {

                $row_user =
                    mysqli_fetch_assoc(
                        $result_user
                    );

                $user_id =
                    (int) (
                        $row_user['id_user']
                        ?? 0
                    );
            }


            mysqli_stmt_close(
                $stmt_user
            );
        }


        if ($user_id > 0) {

            $judul =
                'Hasil Ujian Tersedia';

            $pesan_notif =
                'Hasil ujian pendaftaran Anda '
                . 'telah diperbarui. Silakan '
                . 'cek dashboard Anda.';


            $stmt_notif =
                mysqli_prepare(
                    $conn,
                    "INSERT INTO notifikasi
                     (id_user, judul, pesan)
                     VALUES (?, ?, ?)"
                );


            if ($stmt_notif) {

                mysqli_stmt_bind_param(
                    $stmt_notif,
                    "iss",
                    $user_id,
                    $judul,
                    $pesan_notif
                );

                mysqli_stmt_execute(
                    $stmt_notif
                );

                mysqli_stmt_close(
                    $stmt_notif
                );
            }
        }
    }


    redirectDetail(
        $id_pendaftaran,
        'hasil_berhasil'
    );
}


/*
|--------------------------------------------------------------------------
| PROSES SERTIFIKAT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'simpan_sertifikat'
) {

    if (
        !tableExists(
            $conn,
            'sertifikat'
        )
    ) {

        redirectDetail(
            $id_pendaftaran,
            'tabel_tidak_ada'
        );
    }


    $nomor_sertifikat =
        trim(
            $_POST['nomor_sertifikat']
            ?? ''
        );

    $tanggal_terbit =
        trim(
            $_POST['tanggal_terbit']
            ?? ''
        );


    /*
    |--------------------------------------------------------------------------
    | Upload file
    |--------------------------------------------------------------------------
    */

    $file_sertifikat =
        '';


    if (
        isset(
            $_FILES['file_sertifikat']
        ) &&
        $_FILES[
            'file_sertifikat'
        ]['error'] === UPLOAD_ERR_OK
    ) {

        $file =
            $_FILES[
                'file_sertifikat'
            ];


        /*
        |--------------------------------------------------------------------------
        | Maksimal 10 MB
        |--------------------------------------------------------------------------
        */

        if (
            $file['size']
            > 10 * 1024 * 1024
        ) {

            redirectDetail(
                $id_pendaftaran,
                'gagal'
            );
        }


        $extension =
            strtolower(
                pathinfo(
                    $file['name'],
                    PATHINFO_EXTENSION
                )
            );


        if ($extension !== 'pdf') {

            redirectDetail(
                $id_pendaftaran,
                'gagal'
            );
        }


        // Validasi MIME asli (bukan sekadar ekstensi)
        $finfo_s = finfo_open(FILEINFO_MIME_TYPE);
        $mime_s  = finfo_file($finfo_s, $file['tmp_name']);
        finfo_close($finfo_s);

        if ($mime_s !== 'application/pdf') {

            redirectDetail(
                $id_pendaftaran,
                'gagal'
            );
        }


        $upload_dir =
            __DIR__
            . '/../uploads/sertifikat/';


        if (
            !is_dir(
                $upload_dir
            )
        ) {

            mkdir(
                $upload_dir,
                0775,
                true
            );
        }


        $file_sertifikat =
            'sertifikat_'
            . $id_pendaftaran
            . '_'
            . time()
            . '.pdf';


        $destination =
            $upload_dir
            . $file_sertifikat;


        if (
            !move_uploaded_file(
                $file['tmp_name'],
                $destination
            )
        ) {

            redirectDetail(
                $id_pendaftaran,
                'gagal'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Cek data sertifikat
    |--------------------------------------------------------------------------
    */

    $sertifikat_exists = false;


    if (
        columnExists(
            $conn,
            'sertifikat',
            'id_pendaftaran'
        )
    ) {

        $stmt_cek =
            mysqli_prepare(
                $conn,
                "SELECT id_pendaftaran
                 FROM sertifikat
                 WHERE id_pendaftaran = ?
                 LIMIT 1"
            );


        if ($stmt_cek) {

            mysqli_stmt_bind_param(
                $stmt_cek,
                "i",
                $id_pendaftaran
            );

            mysqli_stmt_execute(
                $stmt_cek
            );

            $result_cek =
                mysqli_stmt_get_result(
                    $stmt_cek
                );

            $sertifikat_exists =
                $result_cek &&
                mysqli_num_rows(
                    $result_cek
                ) > 0;

            mysqli_stmt_close(
                $stmt_cek
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Ambil file lama jika tidak upload baru
    |--------------------------------------------------------------------------
    */

    if (
        $file_sertifikat === '' &&
        columnExists(
            $conn,
            'sertifikat',
            'file_sertifikat'
        )
    ) {

        $stmt_old =
            mysqli_prepare(
                $conn,
                "SELECT file_sertifikat
                 FROM sertifikat
                 WHERE id_pendaftaran = ?
                 LIMIT 1"
            );


        if ($stmt_old) {

            mysqli_stmt_bind_param(
                $stmt_old,
                "i",
                $id_pendaftaran
            );

            mysqli_stmt_execute(
                $stmt_old
            );

            $result_old =
                mysqli_stmt_get_result(
                    $stmt_old
                );


            if ($result_old) {

                $row_old =
                    mysqli_fetch_assoc(
                        $result_old
                    );

                $file_sertifikat =
                    $row_old[
                        'file_sertifikat'
                    ]
                    ?? '';
            }


            mysqli_stmt_close(
                $stmt_old
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Kolom dinamis sertifikat
    |--------------------------------------------------------------------------
    */

    $columns = [];
    $values = [];
    $types = '';
    $params = [];


    if (
        columnExists(
            $conn,
            'sertifikat',
            'nomor_sertifikat'
        )
    ) {

        $columns[] =
            'nomor_sertifikat';

        $values[] =
            '?';

        $types .= 's';

        $params[] =
            $nomor_sertifikat;
    }


    if (
        columnExists(
            $conn,
            'sertifikat',
            'tanggal_terbit'
        )
    ) {

        $columns[] =
            'tanggal_terbit';

        $values[] =
            '?';

        $types .= 's';

        $params[] =
            $tanggal_terbit;
    }


    if (
        columnExists(
            $conn,
            'sertifikat',
            'file_sertifikat'
        )
    ) {

        $columns[] =
            'file_sertifikat';

        $values[] =
            '?';

        $types .= 's';

        $params[] =
            $file_sertifikat;
    }


    if (empty($columns)) {

        redirectDetail(
            $id_pendaftaran,
            'gagal'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE / INSERT
    |--------------------------------------------------------------------------
    */

    if ($sertifikat_exists) {

        $set = [];

        foreach ($columns as $column) {
            $set[] = "`$column` = ?";
        }


        $sql =
            "UPDATE sertifikat
             SET "
            . implode(
                ', ',
                $set
            )
            . "
             WHERE id_pendaftaran = ?
             LIMIT 1";


        $params[] =
            $id_pendaftaran;

        $types .= 'i';


        $stmt =
            mysqli_prepare(
                $conn,
                $sql
            );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                $types,
                ...$params
            );

            mysqli_stmt_execute(
                $stmt
            );

            mysqli_stmt_close(
                $stmt
            );
        }

    } else {

        array_unshift(
            $columns,
            'id_pendaftaran'
        );

        array_unshift(
            $values,
            '?'
        );

        array_unshift(
            $params,
            $id_pendaftaran
        );

        $types =
            'i' . $types;


        $sql =
            "INSERT INTO sertifikat
             (`"
            . implode(
                '`, `',
                $columns
            )
            . "`)
             VALUES ("
            . implode(
                ', ',
                $values
            )
            . ")" ;


        $stmt =
            mysqli_prepare(
                $conn,
                $sql
            );


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                $types,
                ...$params
            );

            mysqli_stmt_execute(
                $stmt
            );

            mysqli_stmt_close(
                $stmt
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Notifikasi sertifikat
    |--------------------------------------------------------------------------
    */

    if (
        tableExists(
            $conn,
            'notifikasi'
        ) &&
        columnExists(
            $conn,
            'notifikasi',
            'id_user'
        ) &&
        columnExists(
            $conn,
            'notifikasi',
            'judul'
        ) &&
        columnExists(
            $conn,
            'notifikasi',
            'pesan'
        )
    ) {

        $user_id = 0;


        $stmt_user =
            mysqli_prepare(
                $conn,
                "SELECT id_user
                 FROM pendaftaran
                 WHERE id_pendaftaran = ?
                 LIMIT 1"
            );


        if ($stmt_user) {

            mysqli_stmt_bind_param(
                $stmt_user,
                "i",
                $id_pendaftaran
            );

            mysqli_stmt_execute(
                $stmt_user
            );

            $result_user =
                mysqli_stmt_get_result(
                    $stmt_user
                );


            if ($result_user) {

                $row_user =
                    mysqli_fetch_assoc(
                        $result_user
                    );

                $user_id =
                    (int) (
                        $row_user['id_user']
                        ?? 0
                    );
            }


            mysqli_stmt_close(
                $stmt_user
            );
        }


        if ($user_id > 0) {

            $judul =
                'Sertifikat Tersedia';

            $pesan_notif =
                'Sertifikat Anda telah tersedia. '
                . 'Silakan cek dashboard Anda.';


            $stmt_notif =
                mysqli_prepare(
                    $conn,
                    "INSERT INTO notifikasi
                     (id_user, judul, pesan)
                     VALUES (?, ?, ?)"
                );


            if ($stmt_notif) {

                mysqli_stmt_bind_param(
                    $stmt_notif,
                    "iss",
                    $user_id,
                    $judul,
                    $pesan_notif
                );

                mysqli_stmt_execute(
                    $stmt_notif
                );

                mysqli_stmt_close(
                    $stmt_notif
                );
            }
        }
    }


    redirectDetail(
        $id_pendaftaran,
        'sertifikat_berhasil'
    );
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PENDAFTARAN
|--------------------------------------------------------------------------
*/

$pendaftaran = null;


$sql =
    "SELECT
        p.*,
        u.nama_lengkap,
        u.username,
        u.email
     FROM pendaftaran p
     LEFT JOIN users u
        ON p.id_user = u.id_user
     WHERE p.id_pendaftaran = ?
     LIMIT 1";


$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id_pendaftaran
    );

    mysqli_stmt_execute(
        $stmt
    );

    $result =
        mysqli_stmt_get_result(
            $stmt
        );


    if ($result) {

        $pendaftaran =
            mysqli_fetch_assoc(
                $result
            );
    }


    mysqli_stmt_close(
        $stmt
    );
}


if (!$pendaftaran) {

    header(
        "Location: data_pendaftaran.php?pesan=gagal"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| DATA DASAR
|--------------------------------------------------------------------------
*/

$nama =
    $pendaftaran['nama_lengkap']
    ?? $pendaftaran['username']
    ?? 'Peserta';


$username =
    $pendaftaran['username']
    ?? '-';


$email =
    $pendaftaran['email']
    ?? '-';


$jenis =
    $pendaftaran[
        $jenisColumn
    ]
    ?? '-';


$status =
    $pendaftaran['status']
    ?? '';


/*
|--------------------------------------------------------------------------
| LABEL STATUS
|--------------------------------------------------------------------------
*/

$status_label = [

    'menunggu'
        => 'Menunggu Verifikasi',

    'menunggu_verifikasi'
        => 'Menunggu Verifikasi',

    'berkas_diperiksa'
        => 'Berkas Diperiksa',

    'menunggu_pembayaran'
        => 'Menunggu Pembayaran',

    'pembayaran_diperiksa'
        => 'Pembayaran Diperiksa',

    'terdaftar'
        => 'Sudah Terdaftar',

    'selesai'
        => 'Selesai',

    'ditolak'
        => 'Ditolak'

];


$status_tampil =
    $status_label[
        $status
    ]
    ?? ucwords(
        str_replace(
            '_',
            ' ',
            $status
        )
    );


function statusClass($status)
{
    switch ($status) {

        case 'terdaftar':
        case 'selesai':

            return 'success';


        case 'ditolak':

            return 'danger';


        case 'berkas_diperiksa':

            return 'info';


        case 'menunggu_pembayaran':
        case 'pembayaran_diperiksa':

            return 'warning';


        default:

            return 'waiting';
    }
}


/*
|--------------------------------------------------------------------------
| DATA PEMBAYARAN
|--------------------------------------------------------------------------
*/

$pembayaran = null;


if (
    tableExists(
        $conn,
        'pembayaran'
    )
) {

    $stmt =
        mysqli_prepare(
            $conn,
            "SELECT *
             FROM pembayaran
             WHERE id_pendaftaran = ?
             ORDER BY id_pembayaran DESC
             LIMIT 1"
        );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_pendaftaran
        );

        mysqli_stmt_execute(
            $stmt
        );

        $result =
            mysqli_stmt_get_result(
                $stmt
            );


        if ($result) {

            $pembayaran =
                mysqli_fetch_assoc(
                    $result
                );
        }


        mysqli_stmt_close(
            $stmt
        );
    }
}


/*
|--------------------------------------------------------------------------
| DATA PESERTA (FORMULIR PKPA / UPA) + BERKAS
|--------------------------------------------------------------------------
| Tidak ada tabel `berkas`. Data peserta & berkas ada di
| pendaftaran_pkpa / pendaftaran_upa (sesuai jenis pendaftaran).
*/

$peserta = null;      // baris pendaftaran_pkpa / pendaftaran_upa
$berkas  = [];        // [ ['nama'=>..., 'file'=>path relatif root], ... ]

$jenis_upper = strtoupper((string) $jenis);
$peserta_table = $jenis_upper === 'UPA' ? 'pendaftaran_upa'
    : ($jenis_upper === 'PKPA' ? 'pendaftaran_pkpa' : '');

if ($peserta_table !== '' && tableExists($conn, $peserta_table)) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM `$peserta_table` WHERE id_pendaftaran = ? ORDER BY id_pendaftaran DESC LIMIT 1"
    );
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id_pendaftaran);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $peserta = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
    }

    if ($peserta) {
        // peta kolom file -> label yang ramah
        $berkas_map = $jenis_upper === 'UPA'
            ? [
                'foto'            => 'Pas Foto',
                'foto_ktp'        => 'Foto KTP',
                'foto_ijazah'     => 'Foto Ijazah',
                'sertifikat_pkpa' => 'Sertifikat PKPA',
                'tanda_tangan'    => 'Tanda Tangan',
              ]
            : [
                'foto_calon_advokat' => 'Pas Foto',
                'foto_ijazah'        => 'Foto Ijazah',
                'foto_ktp'           => 'Foto KTP',
                'tanda_tangan'       => 'Tanda Tangan',
              ];

        foreach ($berkas_map as $col => $label) {
            if (!array_key_exists($col, $peserta)) {
                continue;
            }
            $val = trim((string) ($peserta[$col] ?? ''));
            if ($val !== '') {
                $berkas[] = ['nama' => $label, 'file' => $val, 'status' => 'Terkirim'];
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| DATA HASIL
|--------------------------------------------------------------------------
*/

$hasil = null;


if (
    tableExists(
        $conn,
        'hasil'
    )
) {

    $stmt =
        mysqli_prepare(
            $conn,
            "SELECT *
             FROM hasil
             WHERE id_pendaftaran = ?
             LIMIT 1"
        );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_pendaftaran
        );

        mysqli_stmt_execute(
            $stmt
        );

        $result =
            mysqli_stmt_get_result(
                $stmt
            );


        if ($result) {

            $hasil =
                mysqli_fetch_assoc(
                    $result
                );
        }


        mysqli_stmt_close(
            $stmt
        );
    }
}


/*
|--------------------------------------------------------------------------
| DATA SERTIFIKAT
|--------------------------------------------------------------------------
*/

$sertifikat = null;


if (
    tableExists(
        $conn,
        'sertifikat'
    )
) {

    $stmt =
        mysqli_prepare(
            $conn,
            "SELECT *
             FROM sertifikat
             WHERE id_pendaftaran = ?
             LIMIT 1"
        );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_pendaftaran
        );

        mysqli_stmt_execute(
            $stmt
        );

        $result =
            mysqli_stmt_get_result(
                $stmt
            );


        if ($result) {

            $sertifikat =
                mysqli_fetch_assoc(
                    $result
                );
        }


        mysqli_stmt_close(
            $stmt
        );
    }
}


/*
|--------------------------------------------------------------------------
| FUNGSI FORMAT DATA BERKAS
|--------------------------------------------------------------------------
*/

function getBerkasName($row)
{
    foreach (
        [
            'jenis_berkas',
            'nama_berkas',
            'nama',
            'jenis'
        ]
        as $key
    ) {

        if (
            isset($row[$key]) &&
            $row[$key] !== ''
        ) {
            return $row[$key];
        }
    }

    return 'Dokumen';
}


function getBerkasFile($row)
{
    foreach (
        [
            'file',
            'file_berkas',
            'nama_file',
            'path_file'
        ]
        as $key
    ) {

        if (
            isset($row[$key]) &&
            $row[$key] !== ''
        ) {
            return $row[$key];
        }
    }

    return '';
}


function getBerkasStatus($row)
{
    return
        $row['status']
        ?? 'Belum Diperiksa';
}


/*
|--------------------------------------------------------------------------
| Ubah path file tersimpan -> URL yang benar dari halaman /admin
|--------------------------------------------------------------------------
| Menangani nilai lama:
|   "uploads/foto/x.jpg"          -> ../uploads/foto/x.jpg
|   "/uploads/foto/x.jpg"         -> ../uploads/foto/x.jpg
|   "user/uploads/pembayaran/x"   -> ../user/uploads/pembayaran/x
|   "x.jpg" (hanya nama file)     -> ../uploads/<fallback>/x.jpg
*/
function fileUrl(?string $stored, string $fallbackDir = ''): string
{
    $s = trim(str_replace('\\', '/', (string) $stored));
    if ($s === '') {
        return '';
    }
    $s = ltrim($s, '/');
    if (str_starts_with($s, 'uploads/') || str_starts_with($s, 'user/uploads/')) {
        return '../' . $s;
    }
    if (str_contains($s, '/')) {
        return '../uploads/' . $s;
    }
    return $fallbackDir !== ''
        ? '../uploads/' . trim($fallbackDir, '/') . '/' . $s
        : '../uploads/' . $s;
}


/*
|--------------------------------------------------------------------------
| DATA SERTIFIKAT
|--------------------------------------------------------------------------
*/

$nomor_sertifikat =
    $sertifikat[
        'nomor_sertifikat'
    ]
    ?? '';


$tanggal_terbit =
    $sertifikat[
        'tanggal_terbit'
    ]
    ?? '';


$file_sertifikat =
    $sertifikat[
        'file_sertifikat'
    ]
    ?? '';


/*
|--------------------------------------------------------------------------
| DATA HASIL
|--------------------------------------------------------------------------
*/

$nilai =
    $hasil['nilai']
    ?? '';


$status_lulus =
    $hasil['status_lulus']
    ?? '';


$catatan_hasil =
    $hasil['catatan']
    ?? '';


?>

<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Detail Pendaftaran - PERADI Pontianak
</title>


<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>


<style>

/* =========================================================
   RESET
========================================================= */

* {

    margin: 0;
    padding: 0;

    box-sizing: border-box;

    font-family:
        'Montserrat',
        sans-serif;
}


/* =========================================================
   BODY
========================================================= */

body {

    background: #f4f6f9;

    color: #333;

}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: #1e3a8a;

    color: white;

    padding: 25px 18px;

    z-index: 100;

}


.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        10px
        8px
        30px;

    border-bottom:
        1px solid
        rgba(255,255,255,.15);

    margin-bottom: 25px;

}


.brand i {

    font-size: 32px;

}


.brand-text h2 {

    font-size: 20px;

    font-weight: 800;

}


.brand-text span {

    color: #dea057;

    font-size: 11px;

    letter-spacing: 2px;

}


.menu-title {

    font-size: 11px;

    color:
        rgba(255,255,255,.6);

    text-transform: uppercase;

    margin:
        20px
        10px
        10px;

}


.menu {

    list-style: none;

}


.menu li {

    margin-bottom: 6px;

}


.menu a {

    display: flex;

    align-items: center;

    gap: 12px;

    padding:
        13px
        14px;

    border-radius: 8px;

    color: white;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition: .3s;

}


.menu a:hover,
.menu a.active {

    background: #152c69;

    color: #dea057;

}


.menu a i {

    width: 20px;

    text-align: center;

}


.logout {

    position: absolute;

    left: 18px;
    right: 18px;

    bottom: 25px;

}


/* =========================================================
   MAIN
========================================================= */

.main {

    margin-left: 250px;

    min-height: 100vh;

}


/* =========================================================
   TOPBAR
========================================================= */

.topbar {

    height: 75px;

    background: white;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding:
        0
        35px;

    border-bottom:
        1px solid
        #e5e7eb;

}


.topbar-title h1 {

    color: #1e3a8a;

    font-size: 22px;

    font-weight: 800;

}


.topbar-title p {

    color: #777;

    font-size: 12px;

    margin-top: 4px;

}


.admin-info {

    display: flex;

    align-items: center;

    gap: 12px;

}


.admin-avatar {

    width: 42px;
    height: 42px;

    border-radius: 50%;

    background: #dea057;

    color: white;

    display: flex;

    align-items: center;

    justify-content: center;

    font-weight: 800;

}


.admin-name {

    font-size: 13px;

    font-weight: 700;

}


.admin-role {

    font-size: 10px;

    color: #777;

    margin-top: 3px;

}


/* =========================================================
   CONTENT
========================================================= */

.content {

    padding: 35px;

}


/* =========================================================
   PAGE HEADER
========================================================= */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.page-header h2 {

    color: #1e3a8a;

    font-size: 21px;

    font-weight: 800;

}


.page-header p {

    color: #777;

    font-size: 12px;

    margin-top: 5px;

}


.header-actions {

    display: flex;

    gap: 8px;

}


/* =========================================================
   ALERT
========================================================= */

.alert {

    padding:
        14px
        18px;

    border-radius: 9px;

    margin-bottom: 20px;

    display: flex;

    align-items: center;

    gap: 10px;

    font-size: 12px;

    font-weight: 600;

}


.alert.success {

    background: #dcfce7;

    color: #166534;

}


.alert.danger {

    background: #fee2e2;

    color: #b91c1c;

}


/* =========================================================
   GRID
========================================================= */

.grid {

    display: grid;

    grid-template-columns:
        1fr
        1fr;

    gap: 20px;

}


.card {

    background: white;

    border-radius: 12px;

    padding: 25px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.05);

    margin-bottom: 20px;

}


.card.full {

    grid-column:
        1 / -1;

}


.card-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 20px;

    padding-bottom: 15px;

    border-bottom:
        1px solid
        #eee;

}


.card-header h3 {

    color: #1e3a8a;

    font-size: 16px;

    font-weight: 800;

}


.card-header h3 i {

    color: #dea057;

    margin-right: 7px;

}


/* =========================================================
   DATA
========================================================= */

.data-grid {

    display: grid;

    grid-template-columns:
        1fr
        1fr;

    gap: 15px;

}


.data-item {

    padding:
        14px;

    background: #f8fafc;

    border-radius: 8px;

}


.data-label {

    font-size: 9px;

    color: #888;

    text-transform: uppercase;

    font-weight: 700;

    margin-bottom: 5px;

}


.data-value {

    font-size: 12px;

    font-weight: 700;

    color: #1e3a8a;

    word-break: break-word;

}


/* =========================================================
   STATUS
========================================================= */

.status {

    display: inline-block;

    padding:
        7px
        11px;

    border-radius: 20px;

    font-size: 9px;

    font-weight: 700;

}


.status.waiting {

    background: #fff7ed;

    color: #c2410c;

}


.status.warning {

    background: #fef3c7;

    color: #92400e;

}


.status.info {

    background: #dbeafe;

    color: #1d4ed8;

}


.status.success {

    background: #dcfce7;

    color: #166534;

}


.status.danger {

    background: #fee2e2;

    color: #b91c1c;

}


/* =========================================================
   FORM
========================================================= */

.form-group {

    margin-bottom: 15px;

}


.form-group label {

    display: block;

    font-size: 10px;

    font-weight: 700;

    color: #555;

    margin-bottom: 7px;

}


.form-control {

    width: 100%;

    padding: 11px 13px;

    border:
        1px solid
        #e5e7eb;

    border-radius: 8px;

    outline: none;

    font-size: 11px;

    background: white;

}


.form-control:focus {

    border-color:
        #1e3a8a;

}


textarea.form-control {

    min-height: 100px;

    resize: vertical;

}


/* =========================================================
   BUTTON
========================================================= */

.btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    padding:
        11px
        16px;

    border-radius: 8px;

    border: none;

    background: #1e3a8a;

    color: white;

    text-decoration: none;

    font-size: 10px;

    font-weight: 700;

    cursor: pointer;

}


.btn:hover {

    background: #152c69;

}


.btn-gold {

    background: #dea057;

}


.btn-gold:hover {

    background: #c98d46;

}


.btn-success {

    background: #16a34a;

}


.btn-danger {

    background: #dc2626;

}


.btn-small {

    padding:
        8px
        11px;

    font-size: 9px;

}


/* =========================================================
   PAYMENT
========================================================= */

.payment-box {

    background: #f8fafc;

    border-radius: 10px;

    padding: 18px;

}


.payment-row {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding:
        11px
        0;

    border-bottom:
        1px solid
        #e5e7eb;

}


.payment-row:last-child {

    border-bottom: none;

}


.payment-label {

    font-size: 10px;

    color: #777;

}


.payment-value {

    font-size: 11px;

    font-weight: 700;

    color: #1e3a8a;

    text-align: right;

}


/* =========================================================
   DOCUMENT
========================================================= */

.document-list {

    display: flex;

    flex-direction: column;

    gap: 10px;

}


.document-item {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 13px;

    background: #f8fafc;

    border-radius: 8px;

}


.document-info {

    display: flex;

    align-items: center;

    gap: 10px;

}


.document-icon {

    width: 38px;
    height: 38px;

    border-radius: 8px;

    background: #eef2ff;

    color: #1e3a8a;

    display: flex;

    align-items: center;

    justify-content: center;

}


.document-name {

    font-size: 11px;

    font-weight: 700;

    color: #1e3a8a;

}


.document-status {

    font-size: 9px;

    color: #777;

    margin-top: 3px;

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding: 35px 15px;

    color: #888;

    font-size: 11px;

}


.empty i {

    display: block;

    font-size: 35px;

    color: #dea057;

    margin-bottom: 10px;

}


/* =========================================================
   TABLE
========================================================= */

.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

}


th {

    text-align: left;

    padding: 11px;

    background: #f8fafc;

    color: #666;

    font-size: 9px;

    text-transform: uppercase;

}


td {

    padding: 12px 11px;

    border-top:
        1px solid
        #eee;

    font-size: 10px;

}


/* =========================================================
   PAYMENT IMAGE
========================================================= */

.payment-proof {

    display: inline-flex;

    align-items: center;

    gap: 7px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 1000px) {

    .grid {

        grid-template-columns: 1fr;

    }

    .card.full {

        grid-column: auto;

    }

}


@media(max-width: 700px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

    }


    .logout {

        position: static;

        margin-top: 20px;

    }


    .main {

        margin-left: 0;

    }


    .topbar {

        padding:
            0
            20px;

    }


    .content {

        padding: 20px;

    }


    .admin-name,
    .admin-role {

        display: none;

    }


    .page-header {

        display: block;

    }


    .header-actions {

        margin-top: 15px;

    }


    .data-grid {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<?php include __DIR__ . '/partials/sidebar.php'; ?>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


<header class="topbar">

    <div class="topbar-title">

        <h1>
            Detail Pendaftaran
        </h1>

        <p>
            Detail dan pengelolaan data peserta
        </p>

    </div>


    <div class="admin-info">

        <div class="admin-avatar">

            <?= e(
                strtoupper(
                    substr(
                        $admin_nama,
                        0,
                        1
                    )
                )
            ); ?>

        </div>


        <div>

            <div class="admin-name">
                <?= e($admin_nama); ?>
            </div>

            <div class="admin-role">
                <?= e($admin_role); ?>
            </div>

        </div>

    </div>

</header>


<section class="content">


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="page-header">

    <div>

        <h2>

            <i
                class="fa-solid fa-file-signature"
                style="color:#dea057;"
            ></i>

            Pendaftaran #<?= e(
                $id_pendaftaran
            ); ?>

        </h2>

        <p>

            <?= e($nama); ?>

            ·

            <?= e(
                strtoupper($jenis)
            ); ?>

        </p>

    </div>


    <div class="header-actions">

        <a
            href="data_pendaftaran.php"
            class="btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Kembali

        </a>

    </div>

</div>


<!-- =========================================================
     ALERT
========================================================= -->

<?php if ($alert_message !== ''): ?>

<div class="alert <?= e($alert_type); ?>">

    <?php if (
        $alert_type === 'success'
    ): ?>

        <i class="fa-solid fa-circle-check"></i>

    <?php else: ?>

        <i class="fa-solid fa-circle-exclamation"></i>

    <?php endif; ?>

    <?= e($alert_message); ?>

</div>

<?php endif; ?>


<!-- =========================================================
     DATA PESERTA + STATUS
========================================================= -->

<div class="grid">


<div class="card">

    <div class="card-header">

        <h3>

            <i class="fa-solid fa-user"></i>

            Data Peserta

        </h3>

    </div>


    <div class="data-grid">


        <div class="data-item">

            <div class="data-label">
                ID Pendaftaran
            </div>

            <div class="data-value">

                #<?= e(
                    $id_pendaftaran
                ); ?>

            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                ID User
            </div>

            <div class="data-value">

                <?= e(
                    $pendaftaran['id_user']
                    ?? '-'
                ); ?>

            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                Nama Lengkap
            </div>

            <div class="data-value">

                <?= e($nama); ?>

            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                Username
            </div>

            <div class="data-value">

                @<?= e(
                    $username
                ); ?>

            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                Email
            </div>

            <div class="data-value">

                <?= e($email); ?>

            </div>

        </div>


        <div class="data-item">

            <div class="data-label">
                Jenis Pendaftaran
            </div>

            <div class="data-value">

                <?= e(
                    strtoupper(
                        $jenis
                    )
                ); ?>

            </div>

        </div>

        <?php
        // Data dari formulir PKPA / UPA (bila peserta sudah mengisi detail)
        $peserta_fields = [
            'nama_lengkap'  => 'Nama pada Formulir',
            'nik'           => 'NIK',
            'tempat_lahir'  => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'pekerjaan'     => 'Pekerjaan',
            'no_hp'         => 'No. HP',
            'alamat'        => 'Alamat',
        ];
        if ($peserta):
            foreach ($peserta_fields as $pf => $plabel):
                if (!array_key_exists($pf, $peserta) || trim((string) $peserta[$pf]) === '') {
                    continue;
                }
        ?>
        <div class="data-item">
            <div class="data-label"><?= e($plabel) ?></div>
            <div class="data-value"><?= e($peserta[$pf]) ?></div>
        </div>
        <?php
            endforeach;
        endif;
        ?>

    </div>

</div>


<div class="card">

    <div class="card-header">

        <h3>

            <i class="fa-solid fa-clock"></i>

            Status Pendaftaran

        </h3>

    </div>


    <div
        style="
            margin-bottom:20px;
        "
    >

        <span
            class="status <?= e(
                statusClass(
                    $status
                )
            ); ?>"
        >

            <?= e(
                $status_tampil
            ); ?>

        </span>

    </div>


    <form
        method="POST"
        action="detail_pendaftaran.php?id=<?= e(
            $id_pendaftaran
        ); ?>"
    >
        <?= csrf_field() ?>

        <input
            type="hidden"
            name="aksi"
            value="ubah_status"
        >


        <div class="form-group">

            <label>
                Ubah Status
            </label>


            <select
                name="status"
                class="form-control"
                required
            >

                <option
                    value="menunggu_verifikasi"
                    <?= (
                        $status ===
                        'menunggu' ||
                        $status ===
                        'menunggu_verifikasi'
                    )
                        ? 'selected'
                        : ''
                    ?>
                >
                    Menunggu Verifikasi
                </option>


                <option
                    value="berkas_diperiksa"
                    <?= $status ===
                        'berkas_diperiksa'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Berkas Diperiksa
                </option>


                <option
                    value="menunggu_pembayaran"
                    <?= $status ===
                        'menunggu_pembayaran'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Menunggu Pembayaran
                </option>


                <option
                    value="pembayaran_diperiksa"
                    <?= $status ===
                        'pembayaran_diperiksa'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Pembayaran Diperiksa
                </option>


                <option
                    value="terdaftar"
                    <?= $status ===
                        'terdaftar'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Sudah Terdaftar
                </option>


                <option
                    value="selesai"
                    <?= $status ===
                        'selesai'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Selesai
                </option>


                <option
                    value="ditolak"
                    <?= $status ===
                        'ditolak'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Ditolak
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="btn btn-gold"
        >

            <i class="fa-solid fa-save"></i>

            Simpan Status

        </button>

    </form>

</div>


</div>


<!-- =========================================================
     PEMBAYARAN
========================================================= -->

<div class="card">

    <div class="card-header">

        <h3>

            <i class="fa-solid fa-money-bill-wave"></i>

            Pembayaran

        </h3>

    </div>


    <?php if ($pembayaran): ?>


        <div class="payment-box">


            <?php if (
                isset(
                    $pembayaran[
                        'id_pembayaran'
                    ]
                )
            ): ?>

                <div class="payment-row">

                    <div class="payment-label">
                        ID Pembayaran
                    </div>

                    <div class="payment-value">

                        #<?= e(
                            $pembayaran[
                                'id_pembayaran'
                            ]
                        ); ?>

                    </div>

                </div>

            <?php endif; ?>


            <?php if (
                isset(
                    $pembayaran['jumlah']
                )
            ): ?>

                <div class="payment-row">

                    <div class="payment-label">
                        Jumlah
                    </div>

                    <div class="payment-value">

                        Rp <?= e(
                            number_format(
                                (float)
                                $pembayaran[
                                    'jumlah'
                                ],
                                0,
                                ',',
                                '.'
                            )
                        ); ?>

                    </div>

                </div>

            <?php endif; ?>


            <div class="payment-row">
                <div class="payment-label">Metode</div>
                <div class="payment-value">
                    <?php $__m = $pembayaran['metode'] ?? 'manual'; ?>
                    <?= $__m === 'midtrans' ? 'Payment Gateway (Midtrans)' : 'Transfer Bank (Manual)' ?>
                </div>
            </div>

            <?php if (($pembayaran['metode'] ?? '') === 'midtrans'): ?>
                <?php foreach ([
                    'order_id'           => 'Order ID',
                    'transaction_id'     => 'Transaction ID',
                    'payment_type'       => 'Jenis Pembayaran',
                    'transaction_status' => 'Status Transaksi (Midtrans)',
                ] as $__k => $__lbl):
                    if (empty($pembayaran[$__k])) { continue; } ?>
                    <div class="payment-row">
                        <div class="payment-label"><?= e($__lbl) ?></div>
                        <div class="payment-value"><?= e($pembayaran[$__k]) ?></div>
                    </div>
                <?php endforeach; ?>
                <?php if (!empty($pembayaran['gross_amount'])): ?>
                    <div class="payment-row">
                        <div class="payment-label">Nominal Gateway</div>
                        <div class="payment-value">Rp <?= e(number_format((float) $pembayaran['gross_amount'], 0, ',', '.')) ?></div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>


            <?php if (
                isset(
                    $pembayaran['status']
                )
            ): ?>

                <div class="payment-row">

                    <div class="payment-label">
                        Status Pembayaran
                    </div>

                    <div class="payment-value">

                        <?= e(
                            $pembayaran[
                                'status'
                            ]
                        ); ?>

                    </div>

                </div>

            <?php endif; ?>


            <?php if (
                isset(
                    $pembayaran[
                        'tanggal_bayar'
                    ]
                )
            ): ?>

                <div class="payment-row">

                    <div class="payment-label">
                        Tanggal Bayar
                    </div>

                    <div class="payment-value">

                        <?= e(
                            $pembayaran[
                                'tanggal_bayar'
                            ]
                        ); ?>

                    </div>

                </div>

            <?php endif; ?>


            <?php if (
                isset(
                    $pembayaran[
                        'bukti_pembayaran'
                    ]
                ) &&
                $pembayaran[
                    'bukti_pembayaran'
                ] !== ''
            ): ?>


                <div class="payment-row">

                    <div class="payment-label">
                        Bukti Pembayaran
                    </div>

                    <div class="payment-value">

                        <a
                            href="<?= e(fileUrl($pembayaran['bukti_pembayaran'], 'pembayaran')); ?>"
                            target="_blank"
                            class="btn btn-small"
                        >

                            <i
                                class="fa-solid fa-eye"
                            ></i>

                            Lihat Bukti

                        </a>

                    </div>

                </div>

            <?php endif; ?>


            <?php if (($pembayaran['metode'] ?? 'manual') === 'manual' && ($pembayaran['status'] ?? '') === 'menunggu_verifikasi'): ?>
                <div class="payment-row" style="border-top:1px dashed #e5e7eb;padding-top:14px;margin-top:6px;">
                    <div class="payment-label">Verifikasi Pembayaran</div>
                    <div class="payment-value" style="display:flex;gap:8px;">
                        <form method="POST" action="detail_pendaftaran.php?id=<?= e($id_pendaftaran) ?>"
                              onsubmit="return confirm('Setujui pembayaran ini sebagai LUNAS?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="verifikasi_pembayaran">
                            <input type="hidden" name="keputusan" value="lunas">
                            <button type="submit" class="btn btn-small" style="background:#10b981;">
                                <i class="fa-solid fa-check"></i> Setujui (Lunas)
                            </button>
                        </form>
                        <form method="POST" action="detail_pendaftaran.php?id=<?= e($id_pendaftaran) ?>"
                              onsubmit="return confirm('Tolak bukti pembayaran ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="verifikasi_pembayaran">
                            <input type="hidden" name="keputusan" value="ditolak">
                            <button type="submit" class="btn btn-small" style="background:#dc2626;">
                                <i class="fa-solid fa-xmark"></i> Tolak
                            </button>
                        </form>
                    </div>
                </div>
            <?php elseif (($pembayaran['status'] ?? '') === 'lunas'): ?>
                <div class="payment-row">
                    <div class="payment-label">Verifikasi Pembayaran</div>
                    <div class="payment-value" style="color:#166534;font-weight:700;">
                        <i class="fa-solid fa-circle-check"></i> Sudah diverifikasi LUNAS
                    </div>
                </div>
            <?php endif; ?>


        </div>


    <?php else: ?>


        <div class="empty">

            <i class="fa-solid fa-receipt"></i>

            Belum ada data pembayaran.

        </div>


    <?php endif; ?>

</div>


<!-- =========================================================
     BERKAS
========================================================= -->

<div class="card">

    <div class="card-header">

        <h3>

            <i class="fa-solid fa-folder-open"></i>

            Berkas Peserta

        </h3>

    </div>


    <?php if (!empty($berkas)): ?>


        <div class="document-list">


            <?php foreach (
                $berkas
                as $dokumen
            ): ?>


                <?php

                $nama_berkas =
                    getBerkasName(
                        $dokumen
                    );

                $file_berkas =
                    getBerkasFile(
                        $dokumen
                    );

                $status_berkas =
                    getBerkasStatus(
                        $dokumen
                    );

                ?>


                <div
                    class="document-item"
                >

                    <div
                        class="document-info"
                    >

                        <div
                            class="document-icon"
                        >

                            <i
                                class="fa-solid fa-file"
                            ></i>

                        </div>


                        <div>

                            <div
                                class="document-name"
                            >

                                <?= e(
                                    $nama_berkas
                                ); ?>

                            </div>


                            <div
                                class="document-status"
                            >

                                Status:

                                <?= e(
                                    $status_berkas
                                ); ?>

                            </div>

                        </div>

                    </div>


                    <?php if (
                        $file_berkas !== ''
                    ): ?>

                        <a
                            href="<?= e(fileUrl($file_berkas)); ?>"
                            target="_blank"
                            class="btn btn-small"
                        >

                            <i
                                class="fa-solid fa-eye"
                            ></i>

                            Lihat

                        </a>

                    <?php endif; ?>


                </div>


            <?php endforeach; ?>


        </div>


    <?php else: ?>


        <div class="empty">

            <i
                class="fa-solid fa-folder-open"
            ></i>

            Belum ada berkas peserta.

        </div>


    <?php endif; ?>

</div>


<!-- =========================================================
     HASIL UJIAN
========================================================= -->

<div class="card">

    <div class="card-header">

        <h3>

            <i class="fa-solid fa-graduation-cap"></i>

            Hasil Ujian

        </h3>

    </div>


    <form
        method="POST"
        action="detail_pendaftaran.php?id=<?= e(
            $id_pendaftaran
        ); ?>"
    >
        <?= csrf_field() ?>

        <input
            type="hidden"
            name="aksi"
            value="simpan_hasil"
        >


        <div class="form-group">

            <label>
                Nilai
            </label>

            <input
                type="number"
                name="nilai"
                class="form-control"
                min="0"
                max="100"
                step="0.01"
                value="<?= e(
                    $nilai
                ); ?>"
                placeholder="Contoh: 85"
            >

        </div>


        <div class="form-group">

            <label>
                Status Kelulusan
            </label>

            <select
                name="status_lulus"
                class="form-control"
            >

                <option value="Belum Diumumkan"
                    <?= in_array($status_lulus, ['', 'Belum Diumumkan'], true) ? 'selected' : '' ?>
                >
                    Belum Diumumkan
                </option>

                <option
                    value="Lulus"
                    <?= $status_lulus === 'Lulus' ? 'selected' : '' ?>
                >
                    Lulus
                </option>

                <option
                    value="Tidak Lulus"
                    <?= $status_lulus === 'Tidak Lulus' ? 'selected' : '' ?>
                >
                    Tidak Lulus
                </option>

            </select>

        </div>


        <div class="form-group">

            <label>
                Catatan
            </label>

            <textarea
                name="catatan"
                class="form-control"
                placeholder="Catatan hasil ujian..."
            ><?= e(
                $catatan_hasil
            ); ?></textarea>

        </div>


        <button
            type="submit"
            class="btn btn-gold"
        >

            <i class="fa-solid fa-save"></i>

            Simpan Hasil

        </button>

    </form>

</div>


<!-- =========================================================
     SERTIFIKAT
========================================================= -->

<div class="card">

    <div class="card-header">

        <h3>

            <i class="fa-solid fa-certificate"></i>

            Sertifikat

        </h3>

    </div>


    <form
        method="POST"
        action="detail_pendaftaran.php?id=<?= e(
            $id_pendaftaran
        ); ?>"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>

        <input
            type="hidden"
            name="aksi"
            value="simpan_sertifikat"
        >


        <div class="form-group">

            <label>
                Nomor Sertifikat
            </label>

            <input
                type="text"
                name="nomor_sertifikat"
                class="form-control"
                value="<?= e(
                    $nomor_sertifikat
                ); ?>"
                placeholder="Nomor sertifikat"
            >

        </div>


        <div class="form-group">

            <label>
                Tanggal Terbit
            </label>

            <input
                type="date"
                name="tanggal_terbit"
                class="form-control"
                value="<?= e(
                    $tanggal_terbit
                ); ?>"
            >

        </div>


        <div class="form-group">

            <label>
                Upload Sertifikat PDF
            </label>

            <input
                type="file"
                name="file_sertifikat"
                class="form-control"
                accept=".pdf,application/pdf"
            >

            <small
                style="
                    display:block;
                    margin-top:6px;
                    color:#888;
                    font-size:9px;
                "
            >
                Format PDF, maksimal 10 MB.
            </small>

        </div>


        <?php if (
            $file_sertifikat !== ''
        ): ?>

            <div
                style="
                    margin-bottom:15px;
                    padding:12px;
                    background:#f0fdf4;
                    border-radius:8px;
                    font-size:10px;
                "
            >

                <i
                    class="fa-solid fa-file-pdf"
                    style="color:#dc2626;"
                ></i>

                Sertifikat sudah tersedia.

                <a
                    href="../uploads/sertifikat/<?= e(
                        basename(
                            $file_sertifikat
                        )
                    ); ?>"
                    target="_blank"
                    style="
                        color:#1e3a8a;
                        font-weight:700;
                    "
                >
                    Lihat Sertifikat
                </a>

            </div>

        <?php endif; ?>


        <button
            type="submit"
            class="btn btn-gold"
        >

            <i class="fa-solid fa-save"></i>

            Simpan Sertifikat

        </button>

    </form>

</div>


</section>

</main>


</body>

</html>