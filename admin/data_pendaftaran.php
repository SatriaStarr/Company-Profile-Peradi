<?php

session_start();

require_once __DIR__ . '/koneksi.php';


/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['status_login']) ||
    $_SESSION['status_login'] !== true
) {
    header("Location: index.php?pesan=belum_login");
    exit;
}

$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'super_admin'], true)) {
    header("Location: index.php?pesan=no_access");
    exit;
}


/*
|--------------------------------------------------------------------------
| FUNGSI ESCAPE
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
| PESAN
|--------------------------------------------------------------------------
*/

$pesan = $_GET['pesan'] ?? '';

$alert_message = '';
$alert_type = '';


if ($pesan === 'status_berhasil') {

    $alert_message =
        'Status pendaftaran berhasil diperbarui.';

    $alert_type = 'success';

} elseif ($pesan === 'hapus_berhasil') {

    $alert_message =
        'Data pendaftaran berhasil dihapus.';

    $alert_type = 'success';

} elseif ($pesan === 'gagal') {

    $alert_message =
        'Terjadi kesalahan. Silakan coba lagi.';

    $alert_type = 'danger';

}


/*
|--------------------------------------------------------------------------
| CEK TABLE PENDAFTARAN
|--------------------------------------------------------------------------
*/

$check_table = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'pendaftaran'"
);

$table_exists =
    $check_table &&
    mysqli_num_rows($check_table) > 0;


/*
|--------------------------------------------------------------------------
| PROSES UBAH STATUS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'ubah_status'
) {

    $id_pendaftaran =
        (int) ($_POST['id_pendaftaran'] ?? 0);

    $status_baru =
        trim($_POST['status'] ?? '');

    $status_valid = [

        'menunggu',
        'menunggu_verifikasi',
        'berkas_diperiksa',
        'menunggu_pembayaran',
        'pembayaran_diperiksa',
        'terdaftar',
        'selesai',
        'ditolak'

    ];


    if (
        $id_pendaftaran <= 0 ||
        !in_array(
            $status_baru,
            $status_valid,
            true
        )
    ) {

        header(
            "Location: data_pendaftaran.php?pesan=gagal"
        );

        exit;
    }


    $stmt = mysqli_prepare(
        $conn,
        "UPDATE pendaftaran
         SET status = ?
         WHERE id_pendaftaran = ?
         LIMIT 1"
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $status_baru,
            $id_pendaftaran
        );

        $berhasil =
            mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        if ($berhasil) {

            header(
                "Location: data_pendaftaran.php?pesan=status_berhasil"
            );

            exit;

        } else {

            header(
                "Location: data_pendaftaran.php?pesan=gagal"
            );

            exit;
        }

    } else {

        header(
            "Location: data_pendaftaran.php?pesan=gagal"
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| PROSES HAPUS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi']) &&
    $_POST['aksi'] === 'hapus'
) {

    /*
    |--------------------------------------------------------------------------
    | HANYA SUPER ADMIN YANG BOLEH HAPUS
    |--------------------------------------------------------------------------
    */

    if ($role !== 'super_admin') {

        header(
            "Location: data_pendaftaran.php?pesan=gagal"
        );

        exit;
    }


    $id_pendaftaran =
        (int) ($_POST['id_pendaftaran'] ?? 0);


    if ($id_pendaftaran <= 0) {

        header(
            "Location: data_pendaftaran.php?pesan=gagal"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS DATA KHUSUS PKPA
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM pendaftaran_pkpa
         WHERE id_pendaftaran = ?"
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_pendaftaran
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS DATA KHUSUS UPA
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM pendaftaran_upa
         WHERE id_pendaftaran = ?"
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_pendaftaran
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS PEMBAYARAN
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM pembayaran
         WHERE id_pendaftaran = ?"
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_pendaftaran
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS PENDAFTARAN UTAMA
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM pendaftaran
         WHERE id_pendaftaran = ?
         LIMIT 1"
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id_pendaftaran
        );

        $berhasil =
            mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        if ($berhasil) {

            header(
                "Location: data_pendaftaran.php?pesan=hapus_berhasil"
            );

            exit;

        } else {

            header(
                "Location: data_pendaftaran.php?pesan=gagal"
            );

            exit;
        }

    } else {

        header(
            "Location: data_pendaftaran.php?pesan=gagal"
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| SEARCH & FILTER
|--------------------------------------------------------------------------
*/

$search =
    trim($_GET['search'] ?? '');

$filter_status =
    trim($_GET['status'] ?? '');


/*
|--------------------------------------------------------------------------
| PAGINATION
|--------------------------------------------------------------------------
*/

$per_page = 10;

$page =
    max(
        1,
        (int) ($_GET['page'] ?? 1)
    );

$offset =
    ($page - 1) * $per_page;


/*
|--------------------------------------------------------------------------
| TOTAL DATA
|--------------------------------------------------------------------------
*/

$total_data = 0;


if ($table_exists) {

    $where = [];

    $params = [];

    $types = '';


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    if ($search !== '') {

        $where[] =
            "(
                u.nama_lengkap LIKE ?
                OR u.username LIKE ?
                OR u.email LIKE ?
                OR CAST(p.id_pendaftaran AS CHAR) LIKE ?
            )";


        $search_value =
            '%' . $search . '%';


        $params[] = $search_value;
        $params[] = $search_value;
        $params[] = $search_value;
        $params[] = $search_value;

        $types .= 'ssss';
    }


    /*
    |--------------------------------------------------------------------------
    | FILTER STATUS
    |--------------------------------------------------------------------------
    */

    if ($filter_status !== '') {

        $where[] =
            "p.status = ?";

        $params[] =
            $filter_status;

        $types .= 's';
    }


    $where_sql = '';

    if (!empty($where)) {

        $where_sql =
            'WHERE ' .
            implode(
                ' AND ',
                $where
            );
    }


    $sql_count = "
        SELECT COUNT(*) AS total
        FROM pendaftaran p
        LEFT JOIN users u
            ON p.id_user = u.id_user
        $where_sql
    ";


    $stmt_count =
        mysqli_prepare(
            $conn,
            $sql_count
        );


    if ($stmt_count) {

        if (!empty($params)) {

            mysqli_stmt_bind_param(
                $stmt_count,
                $types,
                ...$params
            );
        }


        mysqli_stmt_execute(
            $stmt_count
        );


        $result_count =
            mysqli_stmt_get_result(
                $stmt_count
            );


        if ($result_count) {

            $row_count =
                mysqli_fetch_assoc(
                    $result_count
                );

            $total_data =
                (int) (
                    $row_count['total']
                    ?? 0
                );
        }


        mysqli_stmt_close(
            $stmt_count
        );
    }
}


$total_page =
    max(
        1,
        (int) ceil(
            $total_data / $per_page
        )
    );


if ($page > $total_page) {

    $page = $total_page;

    $offset =
        ($page - 1) * $per_page;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA PENDAFTARAN
|--------------------------------------------------------------------------
*/

$pendaftaran = [];


if ($table_exists) {

    $where = [];

    $params = [];

    $types = '';


    if ($search !== '') {

        $where[] =
            "(
                u.nama_lengkap LIKE ?
                OR u.username LIKE ?
                OR u.email LIKE ?
                OR CAST(p.id_pendaftaran AS CHAR) LIKE ?
            )";


        $search_value =
            '%' . $search . '%';


        $params[] = $search_value;
        $params[] = $search_value;
        $params[] = $search_value;
        $params[] = $search_value;

        $types .= 'ssss';
    }


    if ($filter_status !== '') {

        $where[] =
            "p.status = ?";

        $params[] =
            $filter_status;

        $types .= 's';
    }


    $where_sql = '';

    if (!empty($where)) {

        $where_sql =
            'WHERE ' .
            implode(
                ' AND ',
                $where
            );
    }


    /*
    |--------------------------------------------------------------------------
    | PENTING
    |--------------------------------------------------------------------------
    | Gunakan jenis_pendaftaran,
    | BUKAN p.jenis.
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            p.id_pendaftaran,
            p.id_user,
            p.jenis_pendaftaran,
            p.status,

            u.nama_lengkap,
            u.username,
            u.email

        FROM pendaftaran p

        LEFT JOIN users u
            ON p.id_user = u.id_user

        $where_sql

        ORDER BY
            p.id_pendaftaran DESC

        LIMIT ?
        OFFSET ?
    ";


    $stmt =
        mysqli_prepare(
            $conn,
            $sql
        );


    if ($stmt) {

        $params[] =
            $per_page;

        $params[] =
            $offset;

        $types .= 'ii';


        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );


        mysqli_stmt_execute(
            $stmt
        );


        $result =
            mysqli_stmt_get_result(
                $stmt
            );


        if ($result) {

            while (
                $row =
                mysqli_fetch_assoc($result)
            ) {

                $pendaftaran[] =
                    $row;
            }
        }


        mysqli_stmt_close(
            $stmt
        );
    }
}


/*
|--------------------------------------------------------------------------
| LABEL STATUS
|--------------------------------------------------------------------------
*/

$status_label = [

    'menunggu'
        => 'Menunggu',

    'menunggu_verifikasi'
        => 'Menunggu Verifikasi',

    'berkas_diperiksa'
        => 'Berkas Diperiksa',

    'menunggu_pembayaran'
        => 'Menunggu Pembayaran',

    'pembayaran_diperiksa'
        => 'Pembayaran Diperiksa',

    'terdaftar'
        => 'Terdaftar',

    'selesai'
        => 'Selesai',

    'ditolak'
        => 'Ditolak'

];


/*
|--------------------------------------------------------------------------
| CLASS STATUS
|--------------------------------------------------------------------------
*/

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
| URL PAGINATION
|--------------------------------------------------------------------------
*/

function pageUrl(
    $page,
    $search,
    $status
) {

    $query = [

        'page' =>
            $page

    ];


    if ($search !== '') {

        $query['search'] =
            $search;
    }


    if ($status !== '') {

        $query['status'] =
            $status;
    }


    return
        'data_pendaftaran.php?' .
        http_build_query($query);
}

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
    Data Pendaftaran - PERADI Pontianak
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
   TOOLBAR
========================================================= */

.toolbar {

    background: white;

    border-radius: 12px;

    padding: 20px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.05);

    margin-bottom: 20px;

}


.filter-form {

    display: grid;

    grid-template-columns:
        1fr
        220px
        auto
        auto;

    gap: 12px;

    align-items: center;

}


.input-group {

    position: relative;

}


.input-group i {

    position: absolute;

    left: 14px;

    top: 50%;

    transform:
        translateY(-50%);

    color: #999;

}


.input {

    width: 100%;

    padding:
        12px
        14px
        12px
        40px;

    border:
        1px solid
        #e5e7eb;

    border-radius: 8px;

    outline: none;

    font-size: 12px;

}


.input:focus {

    border-color:
        #1e3a8a;

}


select.input {

    padding-left: 14px;

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

    font-size: 11px;

    font-weight: 700;

    cursor: pointer;

    transition: .3s;

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


.btn-danger {

    background: #dc2626;

}


.btn-danger:hover {

    background: #b91c1c;

}


.btn-small {

    padding:
        8px
        10px;

    font-size: 10px;

}


/* =========================================================
   TABLE CARD
========================================================= */

.application {

    background: white;

    border-radius: 12px;

    padding: 25px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.05);

}


.application-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 20px;

}


.application-header h3 {

    color: #1e3a8a;

    font-size: 17px;

}


.application-count {

    color: #777;

    font-size: 11px;

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

    min-width: 900px;

}


thead {

    background: #f8fafc;

}


th {

    text-align: left;

    padding: 14px;

    font-size: 10px;

    color: #666;

    text-transform: uppercase;

    white-space: nowrap;

}


td {

    padding: 15px 14px;

    border-top:
        1px solid
        #eee;

    font-size: 11px;

    vertical-align: middle;

}


td strong {

    color: #1e3a8a;

}


.user-cell {

    display: flex;

    align-items: center;

    gap: 10px;

}


.user-icon {

    width: 36px;
    height: 36px;

    border-radius: 50%;

    background: #eef2ff;

    color: #1e3a8a;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

}


.user-name {

    font-weight: 700;

    color: #1e3a8a;

    margin-bottom: 3px;

}


.user-username {

    color: #999;

    font-size: 9px;

}


.email {

    color: #555;

}


.id {

    font-weight: 800;

    color: #1e3a8a;

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

    white-space: nowrap;

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
   ACTION
========================================================= */

.actions {

    display: flex;

    gap: 6px;

    align-items: center;

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding:
        60px
        20px;

    color: #777;

}


.empty i {

    font-size: 48px;

    color: #dea057;

    margin-bottom: 15px;

}


.empty h3 {

    color: #1e3a8a;

    margin-bottom: 8px;

}


.empty p {

    font-size: 12px;

}


/* =========================================================
   PAGINATION
========================================================= */

.pagination {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: 25px;

    padding-top: 20px;

    border-top:
        1px solid
        #eee;

}


.pagination-info {

    color: #777;

    font-size: 11px;

}


.pagination-links {

    display: flex;

    gap: 5px;

}


.page-link {

    width: 34px;
    height: 34px;

    border-radius: 7px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f8fafc;

    border:
        1px solid
        #e5e7eb;

    color: #1e3a8a;

    text-decoration: none;

    font-size: 11px;

    font-weight: 700;

}


.page-link:hover,
.page-link.active {

    background: #1e3a8a;

    color: white;

    border-color:
        #1e3a8a;

}


/* =========================================================
   MODAL
========================================================= */

.modal {

    display: none;

    position: fixed;

    z-index: 999;

    inset: 0;

    background:
        rgba(15,23,42,.55);

    align-items: center;

    justify-content: center;

    padding: 20px;

}


.modal.show {

    display: flex;

}


.modal-box {

    width: 100%;

    max-width: 450px;

    background: white;

    border-radius: 14px;

    padding: 25px;

    box-shadow:
        0 20px 50px
        rgba(0,0,0,.2);

}


.modal-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 20px;

}


.modal-header h3 {

    color: #1e3a8a;

    font-size: 17px;

}


.close-modal {

    width: 32px;
    height: 32px;

    border: none;

    border-radius: 7px;

    background: #f1f5f9;

    cursor: pointer;

    color: #555;

}


.modal label {

    display: block;

    font-size: 11px;

    font-weight: 700;

    color: #555;

    margin-bottom: 7px;

}


.modal select {

    width: 100%;

    padding: 12px;

    border:
        1px solid
        #e5e7eb;

    border-radius: 8px;

    outline: none;

    font-size: 12px;

}


.modal-footer {

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    margin-top: 20px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 1000px) {

    .filter-form {

        grid-template-columns:
            1fr
            1fr;

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


    .pagination {

        display: block;

    }


    .pagination-links {

        margin-top: 15px;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">


    <div class="brand">

        <i class="fa-solid fa-scale-balanced"></i>

        <div class="brand-text">

            <h2>PERADI</h2>

            <span>DATA CENTER</span>

        </div>

    </div>


    <div class="menu-title">

        Menu Utama

    </div>


    <ul class="menu">


        <li>

            <a href="dashboard.php">

                <i class="fa-solid fa-gauge-high"></i>

                Dashboard

            </a>

        </li>


        <li>

            <a
                href="data_pendaftaran.php"
                class="active"
            >

                <i class="fa-solid fa-file-circle-check"></i>

                Data Pendaftaran

            </a>

        </li>


        <li>

            <a href="data_user.php">

                <i class="fa-solid fa-user-group"></i>

                Data User

            </a>

        </li>


        <li>

            <a href="pengumuman.php">

                <i class="fa-solid fa-bullhorn"></i>

                Pengumuman

            </a>

        </li>


        <li>

            <a href="notifikasi.php">

                <i class="fa-solid fa-bell"></i>

                Notifikasi

            </a>

        </li>


    </ul>


    <div class="menu-title">

        Sistem

    </div>


    <ul class="menu">

        <li>

            <a href="data_advokat.php">

                <i class="fa-solid fa-house"></i>

                Data Advokat

            </a>

        </li>

    </ul>


    <div class="logout">

        <ul class="menu">

            <li>

                <a
                    href="logout.php"
                    onclick="
                        return confirm(
                            'Yakin ingin keluar dari akun?'
                        );
                    "
                >

                    <i
                        class="fa-solid fa-right-from-bracket"
                    ></i>

                    Keluar

                </a>

            </li>

        </ul>

    </div>


</aside>



<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


    <!-- TOPBAR -->

    <header class="topbar">


        <div class="topbar-title">

            <h1>
                Data Pendaftaran
            </h1>

            <p>
                Sistem Informasi Sertifikasi Advokat
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



    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <section class="content">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <div>

                <h2>

                    <i
                        class="fa-solid fa-file-signature"
                        style="color:#dea057;"
                    ></i>

                    Data Pendaftaran Peserta

                </h2>

                <p>

                    Kelola seluruh data pendaftaran
                    PKPA dan UPA peserta.

                </p>

            </div>

        </div>



        <!-- ALERT -->

        <?php if ($alert_message !== ''): ?>

            <div
                class="alert <?= e($alert_type); ?>"
            >

                <?php if ($alert_type === 'success'): ?>

                    <i
                        class="fa-solid fa-circle-check"
                    ></i>

                <?php else: ?>

                    <i
                        class="fa-solid fa-circle-exclamation"
                    ></i>

                <?php endif; ?>


                <?= e($alert_message); ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             FILTER
        ================================================== -->

        <div class="toolbar">

            <form
                method="GET"
                action="data_pendaftaran.php"
                class="filter-form"
            >


                <div class="input-group">

                    <i
                        class="fa-solid fa-magnifying-glass"
                    ></i>

                    <input
                        type="text"
                        name="search"
                        class="input"
                        placeholder="Cari nama, username, email atau ID..."
                        value="<?= e($search); ?>"
                    >

                </div>



                <select
                    name="status"
                    class="input"
                >

                    <option value="">
                        Semua Status
                    </option>

                    <option
                        value="menunggu"
                        <?= $filter_status === 'menunggu'
                            ? 'selected'
                            : ''; ?>
                    >
                        Menunggu
                    </option>

                    <option
                        value="menunggu_verifikasi"
                        <?= $filter_status === 'menunggu_verifikasi'
                            ? 'selected'
                            : ''; ?>
                    >
                        Menunggu Verifikasi
                    </option>

                    <option
                        value="berkas_diperiksa"
                        <?= $filter_status === 'berkas_diperiksa'
                            ? 'selected'
                            : ''; ?>
                    >
                        Berkas Diperiksa
                    </option>

                    <option
                        value="menunggu_pembayaran"
                        <?= $filter_status === 'menunggu_pembayaran'
                            ? 'selected'
                            : ''; ?>
                    >
                        Menunggu Pembayaran
                    </option>

                    <option
                        value="pembayaran_diperiksa"
                        <?= $filter_status === 'pembayaran_diperiksa'
                            ? 'selected'
                            : ''; ?>
                    >
                        Pembayaran Diperiksa
                    </option>

                    <option
                        value="terdaftar"
                        <?= $filter_status === 'terdaftar'
                            ? 'selected'
                            : ''; ?>
                    >
                        Terdaftar
                    </option>

                    <option
                        value="selesai"
                        <?= $filter_status === 'selesai'
                            ? 'selected'
                            : ''; ?>
                    >
                        Selesai
                    </option>

                    <option
                        value="ditolak"
                        <?= $filter_status === 'ditolak'
                            ? 'selected'
                            : ''; ?>
                    >
                        Ditolak
                    </option>

                </select>



                <button
                    type="submit"
                    class="btn"
                >

                    <i
                        class="fa-solid fa-filter"
                    ></i>

                    Filter

                </button>



                <a
                    href="data_pendaftaran.php"
                    class="btn btn-gold"
                >

                    <i
                        class="fa-solid fa-rotate-left"
                    ></i>

                    Reset

                </a>


            </form>

        </div>



        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="application">


            <div class="application-header">

                <h3>

                    <i
                        class="fa-solid fa-list"
                        style="color:#dea057;"
                    ></i>

                    Daftar Pendaftaran

                </h3>


                <span class="application-count">

                    Total:
                    <strong>
                        <?= number_format($total_data); ?>
                    </strong>
                    pendaftaran

                </span>

            </div>



            <?php if (empty($pendaftaran)): ?>


                <div class="empty">

                    <i
                        class="fa-solid fa-file-circle-xmark"
                    ></i>

                    <h3>
                        Data Tidak Ditemukan
                    </h3>

                    <p>

                        Belum ada data pendaftaran
                        yang sesuai dengan pencarian.

                    </p>

                </div>


            <?php else: ?>


                <div class="table-wrapper">

                    <table>


                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Peserta
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Layanan
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Aksi
                                </th>

                            </tr>

                        </thead>



                        <tbody>


                        <?php foreach (
                            $pendaftaran
                            as $p
                        ): ?>


                            <?php

                            $status =
                                $p['status']
                                ?? '';


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


                            $jenis =
                                $p[
                                    'jenis_pendaftaran'
                                ]
                                ?? '-';


                            $nama =
                                $p[
                                    'nama_lengkap'
                                ]
                                ??
                                $p[
                                    'username'
                                ]
                                ??
                                'Peserta';


                            ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <span class="id">

                                        #<?= e(
                                            $p[
                                                'id_pendaftaran'
                                            ]
                                        ); ?>

                                    </span>

                                </td>



                                <!-- PESERTA -->

                                <td>

                                    <div
                                        class="user-cell"
                                    >

                                        <div
                                            class="user-icon"
                                        >

                                            <i
                                                class="fa-solid fa-user"
                                            ></i>

                                        </div>


                                        <div>

                                            <div
                                                class="user-name"
                                            >

                                                <?= e(
                                                    $nama
                                                ); ?>

                                            </div>


                                            <div
                                                class="user-username"
                                            >

                                                @<?= e(
                                                    $p[
                                                        'username'
                                                    ]
                                                    ?? '-'
                                                ); ?>

                                            </div>

                                        </div>

                                    </div>

                                </td>



                                <!-- EMAIL -->

                                <td>

                                    <span
                                        class="email"
                                    >

                                        <?= e(
                                            $p[
                                                'email'
                                            ]
                                            ?? '-'
                                        ); ?>

                                    </span>

                                </td>



                                <!-- LAYANAN -->

                                <td>

                                    <strong>

                                        <?= e(
                                            strtoupper(
                                                $jenis
                                            )
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="status
                                        <?= e(
                                            statusClass(
                                                $status
                                            )
                                        ); ?>"
                                    >

                                        <?= e(
                                            $status_tampil
                                        ); ?>

                                    </span>

                                </td>



                                <!-- AKSI -->

                                <td>

                                    <div
                                        class="actions"
                                    >


                                        <!-- DETAIL -->

                                        <a
                                            href="detail_pendaftaran.php?id=<?= e(
                                                $p[
                                                    'id_pendaftaran'
                                                ]
                                            ); ?>"
                                            class="btn btn-small"
                                            title="Lihat Detail"
                                        >

                                            <i
                                                class="fa-solid fa-eye"
                                            ></i>

                                        </a>



                                        <!-- UBAH STATUS -->

                                        <button
                                            type="button"
                                            class="btn btn-small btn-gold"
                                            title="Ubah Status"
                                            onclick="
                                                openStatusModal(
                                                    '<?= e(
                                                        $p[
                                                            'id_pendaftaran'
                                                        ]
                                                    ); ?>',
                                                    '<?= e(
                                                        $status
                                                    ); ?>'
                                                )
                                            "
                                        >

                                            <i
                                                class="fa-solid fa-pen"
                                            ></i>

                                        </button>



                                        <?php if (
                                            $role === 'super_admin'
                                        ): ?>


                                            <!-- HAPUS -->

                                            <form
                                                method="POST"
                                                action="data_pendaftaran.php"
                                                onsubmit="
                                                    return confirm(
                                                        'Yakin ingin menghapus data pendaftaran ini? Data pembayaran dan data detail peserta juga akan dihapus.'
                                                    );
                                                "
                                                style="
                                                    display:inline;
                                                "
                                            >

                                                <input
                                                    type="hidden"
                                                    name="aksi"
                                                    value="hapus"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id_pendaftaran"
                                                    value="<?= e(
                                                        $p[
                                                            'id_pendaftaran'
                                                        ]
                                                    ); ?>"
                                                >


                                                <button
                                                    type="submit"
                                                    class="btn btn-small btn-danger"
                                                    title="Hapus"
                                                >

                                                    <i
                                                        class="fa-solid fa-trash"
                                                    ></i>

                                                </button>

                                            </form>


                                        <?php endif; ?>


                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>



                <!-- =================================================
                     PAGINATION
                ================================================== -->

                <div class="pagination">


                    <div class="pagination-info">

                        Menampilkan

                        <strong>
                            <?= $total_data > 0
                                ? $offset + 1
                                : 0; ?>
                        </strong>

                        sampai

                        <strong>
                            <?= min(
                                $offset + $per_page,
                                $total_data
                            ); ?>
                        </strong>

                        dari

                        <strong>
                            <?= $total_data; ?>
                        </strong>

                        data

                    </div>



                    <?php if ($total_page > 1): ?>

                        <div
                            class="pagination-links"
                        >


                            <?php if ($page > 1): ?>

                                <a
                                    href="<?= e(
                                        pageUrl(
                                            $page - 1,
                                            $search,
                                            $filter_status
                                        )
                                    ); ?>"
                                    class="page-link"
                                >

                                    <i
                                        class="fa-solid fa-chevron-left"
                                    ></i>

                                </a>

                            <?php endif; ?>



                            <?php

                            $start_page =
                                max(
                                    1,
                                    $page - 2
                                );


                            $end_page =
                                min(
                                    $total_page,
                                    $page + 2
                                );

                            ?>



                            <?php for (
                                $i = $start_page;
                                $i <= $end_page;
                                $i++
                            ): ?>


                                <a
                                    href="<?= e(
                                        pageUrl(
                                            $i,
                                            $search,
                                            $filter_status
                                        )
                                    ); ?>"
                                    class="
                                        page-link
                                        <?= $i === $page
                                            ? 'active'
                                            : ''; ?>
                                    "
                                >

                                    <?= $i; ?>

                                </a>


                            <?php endfor; ?>



                            <?php if (
                                $page < $total_page
                            ): ?>

                                <a
                                    href="<?= e(
                                        pageUrl(
                                            $page + 1,
                                            $search,
                                            $filter_status
                                        )
                                    ); ?>"
                                    class="page-link"
                                >

                                    <i
                                        class="fa-solid fa-chevron-right"
                                    ></i>

                                </a>

                            <?php endif; ?>


                        </div>

                    <?php endif; ?>


                </div>


            <?php endif; ?>


        </div>


    </section>


</main>



<!-- =========================================================
     MODAL UBAH STATUS
========================================================= -->

<div
    class="modal"
    id="statusModal"
>


    <div class="modal-box">


        <div class="modal-header">

            <h3>

                <i
                    class="fa-solid fa-pen-to-square"
                    style="color:#dea057;"
                ></i>

                Ubah Status Pendaftaran

            </h3>


            <button
                type="button"
                class="close-modal"
                onclick="closeStatusModal()"
            >

                <i
                    class="fa-solid fa-xmark"
                ></i>

            </button>

        </div>



        <form
            method="POST"
            action="data_pendaftaran.php"
        >


            <input
                type="hidden"
                name="aksi"
                value="ubah_status"
            >


            <input
                type="hidden"
                name="id_pendaftaran"
                id="modal_id_pendaftaran"
            >


            <label>
                Status Pendaftaran
            </label>


            <select
                name="status"
                id="modal_status"
                required
            >

                <option value="menunggu">
                    Menunggu
                </option>

                <option value="menunggu_verifikasi">
                    Menunggu Verifikasi
                </option>

                <option value="berkas_diperiksa">
                    Berkas Diperiksa
                </option>

                <option value="menunggu_pembayaran">
                    Menunggu Pembayaran
                </option>

                <option value="pembayaran_diperiksa">
                    Pembayaran Diperiksa
                </option>

                <option value="terdaftar">
                    Terdaftar
                </option>

                <option value="selesai">
                    Selesai
                </option>

                <option value="ditolak">
                    Ditolak
                </option>

            </select>



            <div class="modal-footer">

                <button
                    type="button"
                    class="btn"
                    style="
                        background:#e5e7eb;
                        color:#333;
                    "
                    onclick="closeStatusModal()"
                >

                    Batal

                </button>


                <button
                    type="submit"
                    class="btn btn-gold"
                >

                    <i
                        class="fa-solid fa-save"
                    ></i>

                    Simpan Status

                </button>

            </div>


        </form>


    </div>

</div>



<script>

/*
|--------------------------------------------------------------------------
| MODAL STATUS
|--------------------------------------------------------------------------
*/

function openStatusModal(
    id,
    status
) {

    document.getElementById(
        'modal_id_pendaftaran'
    ).value = id;


    document.getElementById(
        'modal_status'
    ).value = status;


    document.getElementById(
        'statusModal'
    ).classList.add('show');
}


function closeStatusModal()
{

    document.getElementById(
        'statusModal'
    ).classList.remove('show');

}


/*
|--------------------------------------------------------------------------
| CLOSE MODAL KLIK LUAR
|--------------------------------------------------------------------------
*/

document.getElementById(
    'statusModal'
).addEventListener(
    'click',
    function(event) {

        if (
            event.target === this
        ) {

            closeStatusModal();

        }

    }
);


/*
|--------------------------------------------------------------------------
| ESC
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function(event) {

        if (
            event.key === 'Escape'
        ) {

            closeStatusModal();

        }

    }
);

</script>


</body>

</html>