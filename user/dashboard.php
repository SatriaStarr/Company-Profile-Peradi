<?php
session_start();

require_once __DIR__ . '/../koneksi.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['user_login'] ?? false) !== true ||
    ($_SESSION['role'] ?? '') !== 'user'
) {
    header('Location: login.php?pesan=belum_login');
    exit;
}


/*
|--------------------------------------------------------------------------
| DATA USER DARI SESSION
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION['user_id'];

$username = $_SESSION['username'] ?? '';
$nama_lengkap = $_SESSION['nama_lengkap'] ?? 'User';
$email = $_SESSION['email'] ?? '';


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
| AMBIL DATA USER TERBARU DARI DATABASE
|--------------------------------------------------------------------------
*/

$user = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM users WHERE id_user = ? LIMIT 1"
);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (
        $result &&
        mysqli_num_rows($result) > 0
    ) {

        $user = mysqli_fetch_assoc($result);

        $nama_lengkap =
            $user['nama_lengkap']
            ?? $nama_lengkap;

        $username =
            $user['username']
            ?? $username;

        $email =
            $user['email']
            ?? $email;
    }

    mysqli_stmt_close($stmt);
}


/*
|--------------------------------------------------------------------------
| CEK STATUS USER
|--------------------------------------------------------------------------
*/

$status_user =
    $user['status']
    ?? 'active';

if ($status_user === 'banned') {

    session_unset();
    session_destroy();

    header('Location: login.php?pesan=banned');
    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL PENDAFTARAN TERBARU
|--------------------------------------------------------------------------
*/

$pendaftaran = null;

$check_table = mysqli_query(
    $conn,
    "SHOW TABLES LIKE 'pendaftaran'"
);

if (
    $check_table &&
    mysqli_num_rows($check_table) > 0
) {

    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM pendaftaran
         WHERE id_user = ?
         ORDER BY id_pendaftaran DESC
         LIMIT 1"
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $user_id
        );

        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

        if (
            $result &&
            mysqli_num_rows($result) > 0
        ) {

            $pendaftaran =
                mysqli_fetch_assoc($result);
        }

        mysqli_stmt_close($stmt);
    }
}


/*
|--------------------------------------------------------------------------
| STATUS PENDAFTARAN
|--------------------------------------------------------------------------
*/

$status_pendaftaran =
    $pendaftaran['status']
    ?? 'belum_mendaftar';


$status_label = [

    'belum_mendaftar'
        => 'Belum Mendaftar',

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


$status_tampil =
    $status_label[$status_pendaftaran]
    ?? ucwords(
        str_replace(
            '_',
            ' ',
            $status_pendaftaran
        )
    );


/*
|--------------------------------------------------------------------------
| CLASS STATUS
|--------------------------------------------------------------------------
*/

$status_class = 'waiting';

if (
    in_array(
        $status_pendaftaran,
        [
            'terdaftar',
            'selesai'
        ],
        true
    )
) {

    $status_class = 'success';

} elseif (
    $status_pendaftaran === 'ditolak'
) {

    $status_class = 'danger';
}


/*
|--------------------------------------------------------------------------
| JENIS PENDAFTARAN
|--------------------------------------------------------------------------
*/

$jenis_pendaftaran =
    $pendaftaran['jenis']
    ?? '-';

$jenis_tampil =
    strtoupper($jenis_pendaftaran);

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
        Dashboard User - PERADI Pontianak
    </title>


    <!-- GOOGLE FONT -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
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
   MENU TITLE
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


/* =========================================================
   MENU
========================================================= */

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


.logout a {

    color: #ffdddd;

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


/* =========================================================
   USER INFO
========================================================= */

.user-info {

    display: flex;

    align-items: center;

    gap: 12px;

}


.user-avatar {

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


.user-name {

    font-size: 13px;

    font-weight: 700;

    color: #333;

}


.user-role {

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

}


.welcome p {

    font-size: 13px;

    opacity: .85;

}


/* =========================================================
   CARDS
========================================================= */

.cards {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

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

    font-size: 18px;

    color: #1e3a8a;

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

    align-items: center;

    justify-content: space-between;

    margin-bottom: 25px;

}


.application-header h2 {

    color: #1e3a8a;

    font-size: 18px;

}


/* =========================================================
   STATUS
========================================================= */

.status {

    padding:
        8px
        13px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

}


.status.waiting {

    background: #fff7ed;

    color: #c2410c;

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
   EMPTY
========================================================= */

.empty {

    text-align: center;

    padding:
        35px
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

    margin-bottom: 20px;

}


/* =========================================================
   BUTTON
========================================================= */

.btn {

    display: inline-block;

    padding:
        12px
        20px;

    border-radius: 7px;

    background: #1e3a8a;

    color: white;

    text-decoration: none;

    font-size: 12px;

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
   QUICK MENU
========================================================= */

.quick-menu {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;

    margin-top: 20px;

}


.quick-item {

    display: flex;

    align-items: center;

    gap: 15px;

    padding: 18px;

    border-radius: 10px;

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    text-decoration: none;

    transition: .3s;

}


.quick-item:hover {

    border-color: #dea057;

    transform: translateY(-2px);

}


.quick-icon {

    width: 42px;
    height: 42px;

    border-radius: 9px;

    background: #eef2ff;

    color: #1e3a8a;

    display: flex;

    align-items: center;

    justify-content: center;

}


.quick-text strong {

    display: block;

    color: #1e3a8a;

    font-size: 13px;

    margin-bottom: 4px;

}


.quick-text span {

    color: #777;

    font-size: 11px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 1000px) {

    .cards {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width: 900px) {

    .sidebar {

        width: 210px;

    }

    .main {

        margin-left: 210px;

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

    .cards {

        grid-template-columns: 1fr;

    }

    .quick-menu {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">


    <!-- BRAND -->

    <div class="brand">

        <i class="fa-solid fa-scale-balanced"></i>

        <div class="brand-text">

            <h2>PERADI</h2>

            <span>DATA CENTER</span>

        </div>

    </div>


    <!-- MENU UTAMA -->

    <div class="menu-title">

        Menu Utama

    </div>


    <ul class="menu">


        <!-- DASHBOARD -->

        <li>

            <a
                href="dashboard.php"
                class="active"
            >

                <i class="fa-solid fa-gauge-high"></i>

                Dashboard

            </a>

        </li>


        <!-- PROFIL -->

        <li>

            <a
                href="profil.php"
            >

                <i class="fa-solid fa-user"></i>

                Profil Saya

            </a>

        </li>


        <!-- DAFTAR LAYANAN -->

        <li>

            <a
                href="pendaftaran.php"
            >

                <i class="fa-solid fa-file-circle-check"></i>

                Daftar Layanan

            </a>

        </li>


        <!-- PENGUMUMAN -->

        <li>

            <a
                href="pengumuman.php"
            >

                <i class="fa-solid fa-bullhorn"></i>

                Pengumuman

            </a>

        </li>


        <!-- NOTIFIKASI -->

        <li>

            <a
                href="notifikasi.php"
            >

                <i class="fa-solid fa-bell"></i>

                Notifikasi

            </a>

        </li>


    </ul>


    <!-- SISTEM -->

    <div class="menu-title">

        Sistem

    </div>


    <ul class="menu">


        <!-- WEBSITE UTAMA -->

        <li>

            <a
                href="../index.php"
            >

                <i class="fa-solid fa-house"></i>

                Website Utama

            </a>

        </li>


    </ul>


    <!-- LOGOUT -->

    <div class="logout">

        <ul class="menu">

            <li>

                <a
                    href="logout.php"
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
                Dashboard
            </h1>

            <p>
                Sistem Informasi Sertifikasi Advokat
            </p>

        </div>


        <div class="user-info">


            <div class="user-avatar">

                <?= e(
                    strtoupper(
                        substr(
                            $nama_lengkap,
                            0,
                            1
                        )
                    )
                ); ?>

            </div>


            <div>

                <div class="user-name">

                    <?= e($nama_lengkap); ?>

                </div>

                <div class="user-role">

                    PESERTA / USER

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
                <?= e($nama_lengkap); ?>
                👋

            </h2>

            <p>

                Selamat datang di Dashboard Peserta
                Sertifikasi Advokat DPC PERADI Pontianak.

            </p>

        </div>



        <!-- =================================================
             USER CARDS
        ================================================== -->

        <div class="cards">


            <!-- USERNAME -->

            <div class="card">

                <div class="card-icon">

                    <i class="fa-solid fa-user"></i>

                </div>

                <h3>
                    Username
                </h3>

                <strong>

                    <?= e($username); ?>

                </strong>

            </div>



            <!-- EMAIL -->

            <div class="card">

                <div class="card-icon">

                    <i class="fa-solid fa-envelope"></i>

                </div>

                <h3>
                    Email
                </h3>

                <strong
                    style="font-size:14px;"
                >

                    <?= e($email); ?>

                </strong>

            </div>



            <!-- STATUS -->

            <div class="card">

                <div class="card-icon">

                    <i class="fa-solid fa-clipboard-check"></i>

                </div>

                <h3>
                    Status Pendaftaran
                </h3>

                <strong>

                    <?= e($status_tampil); ?>

                </strong>

            </div>


        </div>



        <!-- =================================================
             PENDAFTARAN
        ================================================== -->

        <div class="application">


            <div class="application-header">


                <h2>

                    <i
                        class="fa-solid fa-file-signature"
                        style="color:#dea057;"
                    ></i>

                    Pendaftaran

                </h2>


                <span
                    class="status <?= e($status_class); ?>"
                >

                    <?= e($status_tampil); ?>

                </span>


            </div>



            <?php if ($pendaftaran === null): ?>


                <!-- BELUM ADA PENDAFTARAN -->

                <div class="empty">


                    <i
                        class="fa-solid fa-file-circle-plus"
                    ></i>


                    <h3>

                        Belum Ada Pendaftaran

                    </h3>


                    <p>

                        Kamu belum memiliki
                        pendaftaran PKPA atau UPA.
                        Silakan pilih layanan yang ingin
                        kamu ikuti.

                    </p>


                    <!--
                    =================================================
                    PENTING:
                    Dashboard sekarang hanya menuju
                    halaman Daftar Layanan.
                    Tidak langsung ke PKPA / UPA.
                    =================================================
                    -->

                    <a
                        href="pendaftaran.php"
                        class="btn btn-gold"
                    >

                        <i
                            class="fa-solid fa-file-circle-check"
                        ></i>

                        Daftar Layanan

                    </a>


                </div>



            <?php else: ?>


                <!-- ADA PENDAFTARAN -->

                <div
                    style="
                        display:grid;
                        grid-template-columns:
                            repeat(3,1fr);
                        gap:20px;
                    "
                >


                    <!-- ID -->

                    <div>

                        <small
                            style="color:#777;"
                        >

                            ID Pendaftaran

                        </small>


                        <p
                            style="
                                font-weight:700;
                                color:#1e3a8a;
                                margin-top:5px;
                            "
                        >

                            #<?= e(
                                $pendaftaran[
                                    'id_pendaftaran'
                                ] ?? '-'
                            ); ?>

                        </p>

                    </div>



                    <!-- JENIS -->

                    <div>

                        <small
                            style="color:#777;"
                        >

                            Jenis Layanan

                        </small>


                        <p
                            style="
                                font-weight:700;
                                color:#1e3a8a;
                                margin-top:5px;
                            "
                        >

                            <?= e(
                                $jenis_tampil
                            ); ?>

                        </p>

                    </div>



                    <!-- STATUS -->

                    <div>

                        <small
                            style="color:#777;"
                        >

                            Status

                        </small>


                        <p
                            style="margin-top:5px;"
                        >

                            <span
                                class="status
                                <?= e(
                                    $status_class
                                ); ?>"
                            >

                                <?= e(
                                    $status_tampil
                                ); ?>

                            </span>

                        </p>

                    </div>


                </div>



                <!-- DETAIL -->

                <div
                    style="
                        margin-top:25px;
                        padding-top:20px;
                        border-top:
                            1px solid #eee;
                    "
                >

                    <a
                        href="detail_pendaftaran.php?id=<?= e(
                            $pendaftaran[
                                'id_pendaftaran'
                            ] ?? ''
                        ); ?>"
                        class="btn"
                    >

                        <i
                            class="fa-solid fa-eye"
                        ></i>

                        Lihat Detail Pendaftaran

                    </a>


                </div>


            <?php endif; ?>


        </div>



        <!-- =================================================
             MENU CEPAT
        ================================================== -->

        <div
            style="
                margin-top:25px;
                margin-bottom:15px;
            "
        >

            <h2
                style="
                    color:#1e3a8a;
                    font-size:18px;
                "
            >

                Menu Cepat

            </h2>

        </div>



        <div class="quick-menu">


            <!-- DAFTAR LAYANAN -->

            <a
                href="pendaftaran.php"
                class="quick-item"
            >

                <div class="quick-icon">

                    <i
                        class="fa-solid fa-file-circle-check"
                    ></i>

                </div>


                <div class="quick-text">

                    <strong>
                        Daftar Layanan
                    </strong>

                    <span>
                        Pilih layanan PKPA atau UPA
                    </span>

                </div>

            </a>



            <!-- PENGUMUMAN -->

            <a
                href="pengumuman.php"
                class="quick-item"
            >

                <div class="quick-icon">

                    <i
                        class="fa-solid fa-bullhorn"
                    ></i>

                </div>


                <div class="quick-text">

                    <strong>
                        Pengumuman
                    </strong>

                    <span>
                        Informasi resmi dari DPC PERADI
                    </span>

                </div>

            </a>



            <!-- NOTIFIKASI -->

            <a
                href="notifikasi.php"
                class="quick-item"
            >

                <div class="quick-icon">

                    <i
                        class="fa-solid fa-bell"
                    ></i>

                </div>


                <div class="quick-text">

                    <strong>
                        Notifikasi
                    </strong>

                    <span>
                        Lihat informasi terbaru
                    </span>

                </div>

            </a>



            <!-- PROFIL -->

            <a
                href="profil.php"
                class="quick-item"
            >

                <div class="quick-icon">

                    <i
                        class="fa-solid fa-user"
                    ></i>

                </div>


                <div class="quick-text">

                    <strong>
                        Profil Saya
                    </strong>

                    <span>
                        Kelola data akun dan profil
                    </span>

                </div>

            </a>


        </div>


    </section>


</main>


</body>

</html>