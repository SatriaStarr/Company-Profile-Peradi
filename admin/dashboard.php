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
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}


/*
|--------------------------------------------------------------------------
| DATA ADMIN
|--------------------------------------------------------------------------
*/

$admin_nama = $_SESSION['nama_lengkap']
    ?? $_SESSION['username']
    ?? 'Administrator';

$admin_email = $_SESSION['email'] ?? '';

$admin_role = $role === 'super_admin'
    ? 'SUPER ADMIN'
    : 'ADMIN';


/*
|--------------------------------------------------------------------------
| TOTAL USER
|--------------------------------------------------------------------------
*/

$total_user = 0;

$q_user = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'user'"
);

if ($q_user) {
    $row = mysqli_fetch_assoc($q_user);
    $total_user = (int) ($row['total'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| CEK TABEL PENDAFTARAN
|--------------------------------------------------------------------------
*/

$check_table = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'pendaftaran'"
);

$has_pendaftaran_table = (
    $check_table &&
    mysqli_num_rows($check_table) > 0
);


/*
|--------------------------------------------------------------------------
| TOTAL PENDAFTARAN
|--------------------------------------------------------------------------
*/

$total_pendaftaran = 0;

if ($has_pendaftaran_table) {

    $q = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM pendaftaran"
    );

    if ($q) {

        $row = mysqli_fetch_assoc($q);

        $total_pendaftaran =
            (int) ($row['total'] ?? 0);
    }
}


/*
|--------------------------------------------------------------------------
| JUMLAH BERDASARKAN STATUS PENDAFTARAN
|--------------------------------------------------------------------------
|
| Status "menunggu" digunakan oleh proses pendaftaran
| sebagai status awal.
|
*/

$status_count = [

    'menunggu' => 0,

    'menunggu_verifikasi' => 0,

    'berkas_diperiksa' => 0,

    'menunggu_pembayaran' => 0,

    'pembayaran_diperiksa' => 0,

    'terdaftar' => 0,

    'selesai' => 0,

    'ditolak' => 0

];


if ($has_pendaftaran_table) {

    $q_status = mysqli_query(
        $conn,
        "SELECT status, COUNT(*) AS total
         FROM pendaftaran
         GROUP BY status"
    );

    if ($q_status) {

        while ($row = mysqli_fetch_assoc($q_status)) {

            $status = $row['status'] ?? '';

            if (isset($status_count[$status])) {

                $status_count[$status] =
                    (int) $row['total'];
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| TOTAL PENDING USER
|--------------------------------------------------------------------------
*/

$total_pending_user = 0;

$q_pending_user = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE status = 'pending'"
);

if ($q_pending_user) {

    $row =
        mysqli_fetch_assoc(
            $q_pending_user
        );

    $total_pending_user =
        (int) ($row['total'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| PENDAFTARAN TERBARU
|--------------------------------------------------------------------------
*/

$pendaftaran_terbaru = [];


if ($has_pendaftaran_table) {

    /*
    |--------------------------------------------------------------------------
    | PENTING
    |--------------------------------------------------------------------------
    | Kolom yang benar adalah:
    | jenis_pendaftaran
    |
    | BUKAN:
    | jenis
    |--------------------------------------------------------------------------
    */

    $q_terbaru = mysqli_query(
        $conn,
        "SELECT
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
         ORDER BY p.id_pendaftaran DESC
         LIMIT 8"
    );


    if ($q_terbaru) {

        while (
            $row =
                mysqli_fetch_assoc(
                    $q_terbaru
                )
        ) {

            $pendaftaran_terbaru[] =
                $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| LABEL STATUS
|--------------------------------------------------------------------------
*/

$status_label = [

    'belum_mendaftar'
        => 'Belum Mendaftar',

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


        case 'menunggu':

        case 'menunggu_verifikasi':

        default:

            return 'waiting';
    }
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
    Dashboard Admin - PERADI Pontianak
</title>


<!-- GOOGLE FONT -->

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


<!-- FONT AWESOME -->

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


/* =========================================================
   BRAND
========================================================= */

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


/* =========================================================
   MENU
========================================================= */

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


/* =========================================================
   LOGOUT
========================================================= */

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


/* =========================================================
   TOPBAR TITLE
========================================================= */

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


/* =========================================================
   ADMIN INFO
========================================================= */

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
   WELCOME
========================================================= */

.welcome {

    background:
        linear-gradient(
            135deg,
            #1e3a8a,
            #294da5
        );

    border-radius: 14px;

    padding: 30px;

    color: white;

    margin-bottom: 25px;

    position: relative;

    overflow: hidden;
}


.welcome::after {

    content: '';

    position: absolute;

    width: 180px;

    height: 180px;

    border-radius: 50%;

    background:
        rgba(255,255,255,.06);

    right: -40px;

    top: -50px;
}


.welcome h2 {

    font-size: 24px;

    font-weight: 800;

    margin-bottom: 8px;

    position: relative;

    z-index: 2;
}


.welcome p {

    font-size: 13px;

    opacity: .85;

    position: relative;

    z-index: 2;
}


/* =========================================================
   STAT CARDS
========================================================= */

.cards {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 25px;
}


.card {

    background: white;

    border-radius: 12px;

    padding: 22px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.05);

    border-left:
        4px solid
        #dea057;
}


.card-icon {

    width: 45px;

    height: 45px;

    border-radius: 10px;

    background: #eef2ff;

    color: #1e3a8a;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 19px;

    margin-bottom: 15px;
}


.card h3 {

    font-size: 13px;

    color: #777;

    margin-bottom: 7px;
}


.card strong {

    font-size: 24px;

    color: #1e3a8a;
}


/* =========================================================
   SECOND STATS
========================================================= */

.status-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 25px;
}


.status-card {

    background: white;

    padding: 20px;

    border-radius: 12px;

    box-shadow:
        0 5px 20px
        rgba(0,0,0,.05);
}


.status-card h4 {

    font-size: 12px;

    color: #777;

    margin-bottom: 8px;
}


.status-card strong {

    font-size: 22px;

    color: #1e3a8a;
}


/* =========================================================
   STATUS COLORS
========================================================= */

.status {

    display: inline-block;

    padding:
        7px
        12px;

    border-radius: 20px;

    font-size: 10px;

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
   APPLICATION
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

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;
}


.application-header h2 {

    color: #1e3a8a;

    font-size: 18px;
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


thead {

    background: #f8fafc;
}


th {

    text-align: left;

    padding: 14px;

    font-size: 11px;

    color: #666;

    text-transform: uppercase;
}


td {

    padding:
        15px
        14px;

    border-top:
        1px solid
        #eee;

    font-size: 12px;
}


td strong {

    color: #1e3a8a;
}


/* =========================================================
   BUTTON
========================================================= */

.btn {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding:
        9px
        13px;

    border-radius: 7px;

    background: #1e3a8a;

    color: white;

    text-decoration: none;

    font-size: 11px;

    font-weight: 700;

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


/* =========================================================
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding:
        50px
        20px;

    color: #777;
}


.empty i {

    font-size: 45px;

    color: #dea057;

    margin-bottom: 15px;
}


.empty h3 {

    color: #1e3a8a;

    margin-bottom: 8px;
}


.empty p {

    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 1100px) {

    .cards,
    .status-grid {

        grid-template-columns:
            repeat(2, 1fr);
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


    .cards,
    .status-grid {

        grid-template-columns: 1fr;
    }


    .admin-name,
    .admin-role {

        display: none;
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

            <a
                href="dashboard.php"
                class="active"
            >

                <i class="fa-solid fa-gauge-high"></i>

                Dashboard

            </a>

        </li>


        <li>

            <a
                href="data_pendaftaran.php"
            >

                <i class="fa-solid fa-file-circle-check"></i>

                Data Pendaftaran

            </a>

        </li>


        <li>

            <a
                href="data_user.php"
            >

                <i class="fa-solid fa-user-group"></i>

                Data User

            </a>

        </li>


        <li>

            <a
                href="pengumuman.php"
            >

                <i class="fa-solid fa-bullhorn"></i>

                Pengumuman

            </a>

        </li>


        <li>

            <a
                href="notifikasi.php"
            >

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

            <a
                href="data_advokat.php"
            >

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
                    onclick="return confirm('Yakin ingin keluar dari akun?');">
                     <i class="fa-solid fa-right-from-bracket"></i>

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
                Dashboard Admin
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


        <!-- WELCOME -->

        <div class="welcome">

            <h2>

                Selamat Datang,
                <?= e($admin_nama); ?>
                👋

            </h2>

            <p>

                Kelola data peserta, pendaftaran,
                verifikasi dan informasi Sertifikasi
                Advokat DPC PERADI Pontianak.

            </p>

        </div>



        <!-- =================================================
             STATISTIK UTAMA
        ================================================== -->

        <div class="cards">


            <!-- TOTAL USER -->

            <div class="card">

                <div class="card-icon">

                    <i class="fa-solid fa-user-group"></i>

                </div>

                <h3>
                    Total Peserta
                </h3>

                <strong>
                    <?= $total_user; ?>
                </strong>

            </div>



            <!-- TOTAL PENDAFTARAN -->

            <div class="card">

                <div class="card-icon">

                    <i class="fa-solid fa-file-signature"></i>

                </div>

                <h3>
                    Total Pendaftaran
                </h3>

                <strong>
                    <?= $total_pendaftaran; ?>
                </strong>

            </div>



            <!-- USER PENDING -->

            <div class="card">

                <div class="card-icon">

                    <i class="fa-solid fa-user-clock"></i>

                </div>

                <h3>
                    User Menunggu Verifikasi
                </h3>

                <strong>
                    <?= $total_pending_user; ?>
                </strong>

            </div>



            <!-- SELESAI -->

            <div class="card">

                <div class="card-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>

                <h3>
                    Pendaftaran Selesai
                </h3>

                <strong>
                    <?= $status_count['selesai']; ?>
                </strong>

            </div>


        </div>



        <!-- =================================================
             STATUS PENDAFTARAN
        ================================================== -->

        <div class="status-grid">


            <!-- MENUNGGU VERIFIKASI -->

            <div class="status-card">

                <h4>
                    Menunggu Verifikasi
                </h4>

                <strong>
                    <?= (
                        $status_count['menunggu']
                        +
                        $status_count['menunggu_verifikasi']
                    ); ?>
                </strong>

            </div>



            <!-- BERKAS DIPERIKSA -->

            <div class="status-card">

                <h4>
                    Berkas Diperiksa
                </h4>

                <strong>
                    <?= $status_count['berkas_diperiksa']; ?>
                </strong>

            </div>



            <!-- MENUNGGU PEMBAYARAN -->

            <div class="status-card">

                <h4>
                    Menunggu Pembayaran
                </h4>

                <strong>
                    <?= $status_count['menunggu_pembayaran']; ?>
                </strong>

            </div>



            <!-- PEMBAYARAN DIPERIKSA -->

            <div class="status-card">

                <h4>
                    Pembayaran Diperiksa
                </h4>

                <strong>
                    <?= $status_count['pembayaran_diperiksa']; ?>
                </strong>

            </div>



            <!-- TERDAFTAR -->

            <div class="status-card">

                <h4>
                    Sudah Terdaftar
                </h4>

                <strong>
                    <?= $status_count['terdaftar']; ?>
                </strong>

            </div>



            <!-- SELESAI -->

            <div class="status-card">

                <h4>
                    Selesai
                </h4>

                <strong>
                    <?= $status_count['selesai']; ?>
                </strong>

            </div>



            <!-- DITOLAK -->

            <div class="status-card">

                <h4>
                    Ditolak
                </h4>

                <strong>
                    <?= $status_count['ditolak']; ?>
                </strong>

            </div>


        </div>



        <!-- =================================================
             PENDAFTARAN TERBARU
        ================================================== -->

        <div class="application">


            <div class="application-header">


                <h2>

                    <i
                        class="fa-solid fa-clock-rotate-left"
                        style="color:#dea057;"
                    ></i>

                    Pendaftaran Terbaru

                </h2>


                <a
                    href="data_pendaftaran.php"
                    class="btn btn-gold"
                >

                    <i
                        class="fa-solid fa-list"
                    ></i>

                    Kelola Semua

                </a>


            </div>



            <?php if (empty($pendaftaran_terbaru)): ?>


                <!-- BELUM ADA PENDAFTARAN -->

                <div class="empty">

                    <i
                        class="fa-solid fa-file-circle-xmark"
                    ></i>

                    <h3>
                        Belum Ada Pendaftaran
                    </h3>

                    <p>
                        Belum ada peserta yang melakukan
                        pendaftaran layanan.
                    </p>

                </div>


            <?php else: ?>


                <!-- ADA PENDAFTARAN -->

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
                            $pendaftaran_terbaru
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

                            ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <strong>

                                        #<?= e(
                                            $p[
                                                'id_pendaftaran'
                                            ]
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- PESERTA -->

                                <td>

                                    <strong>

                                        <?= e(
                                            $p[
                                                'nama_lengkap'
                                            ]
                                            ??
                                            $p[
                                                'username'
                                            ]
                                            ??
                                            '-'
                                        ); ?>

                                    </strong>

                                </td>



                                <!-- EMAIL -->

                                <td>

                                    <?= e(
                                        $p['email']
                                        ?? '-'
                                    ); ?>

                                </td>



                                <!-- LAYANAN -->

                                <td>

                                    <?= e(
                                        strtoupper(
                                            $p[
                                                'jenis_pendaftaran'
                                            ]
                                            ?? '-'
                                        )
                                    ); ?>

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

                                    <a
                                        href="detail_pendaftaran.php?id=<?= e(
                                            $p[
                                                'id_pendaftaran'
                                            ]
                                        ); ?>"
                                        class="btn"
                                    >

                                        <i
                                            class="fa-solid fa-eye"
                                        ></i>

                                        Detail

                                    </a>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php endif; ?>


        </div>


    </section>


</main>


</body>

</html>