<?php

session_start();

require_once __DIR__ . '/../koneksi.php';


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
| FUNGSI
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
| ADMIN
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
| FILTER
|--------------------------------------------------------------------------
*/

$keyword =
    trim($_GET['keyword'] ?? '');


/*
|--------------------------------------------------------------------------
| DATA NOTIFIKASI
|--------------------------------------------------------------------------
*/

$notifikasi = [];


$sql = "
    SELECT
        n.id_notifikasi,
        n.id_user,
        n.judul,
        n.pesan,
        n.is_read,
        n.created_at,
        u.nama_lengkap,
        u.username
    FROM notifikasi n
    LEFT JOIN users u
        ON n.id_user = u.id_user
";


$params = [];
$types = '';


if ($keyword !== '') {

    $sql .= "
        WHERE
            n.judul LIKE ?
            OR n.pesan LIKE ?
            OR u.nama_lengkap LIKE ?
            OR u.username LIKE ?
    ";

    $search =
        '%' . $keyword . '%';

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types = 'ssss';
}


$sql .= "
    ORDER BY n.created_at DESC,
             n.id_notifikasi DESC
";


$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if ($stmt) {

    if (!empty($params)) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );
    }

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    if ($result) {

        while (
            $row =
            mysqli_fetch_assoc($result)
        ) {

            $notifikasi[] =
                $row;
        }
    }

    mysqli_stmt_close($stmt);
}


/*
|--------------------------------------------------------------------------
| JUMLAH BELUM DIBACA
|--------------------------------------------------------------------------
*/

$belum_dibaca = 0;

$result_count =
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM notifikasi
         WHERE is_read = 0"
    );

if ($result_count) {

    $row_count =
        mysqli_fetch_assoc(
            $result_count
        );

    $belum_dibaca =
        (int) (
            $row_count['total']
            ?? 0
        );
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
    Notifikasi - PERADI Pontianak
</title>

<link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Montserrat', sans-serif;
}

body {
    background: #f4f6f9;
    color: #333;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 250px;
    height: 100vh;
    background: #1e3a8a;
    color: white;
    padding: 25px 18px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 8px 30px;
    border-bottom: 1px solid rgba(255,255,255,.15);
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
    color: rgba(255,255,255,.6);
    text-transform: uppercase;
    margin: 20px 10px 10px;
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
    padding: 13px 14px;
    border-radius: 8px;
    color: white;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
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

.main {
    margin-left: 250px;
    min-height: 100vh;
}

.topbar {
    height: 75px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 35px;
    border-bottom: 1px solid #e5e7eb;
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
}

.content {
    padding: 35px;
}

.page-header {
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

.card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
    margin-bottom: 20px;
}

.filter {
    display: flex;
    gap: 10px;
}

.form-control {
    flex: 1;
    padding: 11px 13px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    outline: none;
    font-size: 11px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 11px 16px;
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

.summary {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.summary-box {
    background: #f8fafc;
    border-radius: 9px;
    padding: 15px 20px;
    min-width: 180px;
}

.summary-label {
    font-size: 9px;
    color: #777;
    text-transform: uppercase;
    font-weight: 700;
}

.summary-value {
    color: #1e3a8a;
    font-size: 20px;
    font-weight: 800;
    margin-top: 5px;
}

.notification-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.notification-item {
    padding: 16px;
    border-radius: 9px;
    background: #f8fafc;
    border-left: 4px solid #e5e7eb;
}

.notification-item.unread {
    border-left-color: #dea057;
    background: #fffbeb;
}

.notification-head {
    display: flex;
    justify-content: space-between;
    gap: 15px;
}

.notification-title {
    color: #1e3a8a;
    font-size: 12px;
    font-weight: 800;
}

.notification-date {
    color: #999;
    font-size: 9px;
    white-space: nowrap;
}

.notification-user {
    color: #777;
    font-size: 9px;
    margin-top: 5px;
}

.notification-message {
    color: #555;
    font-size: 10px;
    line-height: 1.6;
    margin-top: 10px;
}

.badge {
    display: inline-block;
    padding: 5px 8px;
    border-radius: 20px;
    background: #dcfce7;
    color: #166534;
    font-size: 8px;
    font-weight: 700;
    margin-top: 10px;
}

.badge.unread {
    background: #fef3c7;
    color: #92400e;
}

.empty {
    text-align: center;
    padding: 40px 15px;
    color: #888;
    font-size: 11px;
}

.empty i {
    display: block;
    font-size: 35px;
    color: #dea057;
    margin-bottom: 10px;
}

@media(max-width:700px) {

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

    .content {
        padding: 20px;
    }

    .topbar {
        padding: 0 20px;
    }

    .admin-name,
    .admin-role {
        display: none;
    }

    .notification-head {
        display: block;
    }

    .notification-date {
        display: block;
        margin-top: 5px;
    }

}

</style>

</head>

<body>


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
            <a href="data_pendaftaran.php">
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
            <a href="notifikasi.php" class="active">
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
                    onclick="return confirm('Yakin ingin keluar dari akun?');"
                >
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Keluar
                </a>
            </li>

        </ul>

    </div>

</aside>


<main class="main">


<header class="topbar">

    <div class="topbar-title">

        <h1>Notifikasi</h1>

        <p>Riwayat pemberitahuan kepada pengguna</p>

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


<div class="page-header">

    <h2>

        <i
            class="fa-solid fa-bell"
            style="color:#dea057;"
        ></i>

        Notifikasi

    </h2>

    <p>
        Notifikasi yang dibuat oleh sistem untuk pengguna.
    </p>

</div>


<div class="summary">

    <div class="summary-box">

        <div class="summary-label">
            Belum Dibaca
        </div>

        <div class="summary-value">
            <?= e($belum_dibaca); ?>
        </div>

    </div>

    <div class="summary-box">

        <div class="summary-label">
            Ditampilkan
        </div>

        <div class="summary-value">
            <?= e(count($notifikasi)); ?>
        </div>

    </div>

</div>


<div class="card">

    <form
        method="GET"
        class="filter"
    >

        <input
            type="text"
            name="keyword"
            class="form-control"
            value="<?= e($keyword); ?>"
            placeholder="Cari notifikasi, nama user, atau username..."
        >

        <button
            type="submit"
            class="btn"
        >

            <i class="fa-solid fa-magnifying-glass"></i>

            Cari

        </button>

        <a
            href="notifikasi.php"
            class="btn"
        >

            <i class="fa-solid fa-rotate-left"></i>

            Reset

        </a>

    </form>

</div>


<div class="card">


<?php if (!empty($notifikasi)): ?>


<div class="notification-list">


<?php foreach (
    $notifikasi
    as $item
): ?>


<div
    class="notification-item <?= (
        (int)$item['is_read'] === 0
    )
        ? 'unread'
        : ''
    ?>"
>


<div class="notification-head">


<div>

    <div class="notification-title">

        <?= e(
            $item['judul']
        ); ?>

    </div>


    <div class="notification-user">

        <i class="fa-solid fa-user"></i>

        <?= e(
            $item['nama_lengkap']
            ?? $item['username']
            ?? 'User'
        ); ?>

        · ID User:

        <?= e(
            $item['id_user']
        ); ?>

    </div>

</div>


<div class="notification-date">

    <?= e(
        date(
            'd-m-Y H:i',
            strtotime(
                $item['created_at']
            )
        )
    ); ?>

</div>


</div>


<div class="notification-message">

    <?= nl2br(
        e(
            $item['pesan']
        )
    ); ?>

</div>


<?php if (
    (int)$item['is_read'] === 0
): ?>

    <span class="badge unread">

        <i class="fa-solid fa-envelope"></i>

        Belum Dibaca

    </span>

<?php else: ?>

    <span class="badge">

        <i class="fa-solid fa-envelope-open"></i>

        Sudah Dibaca

    </span>

<?php endif; ?>


</div>


<?php endforeach; ?>


</div>


<?php else: ?>


<div class="empty">

    <i class="fa-solid fa-bell-slash"></i>

    Belum ada notifikasi.

</div>


<?php endif; ?>


</div>


</section>

</main>

</body>

</html>