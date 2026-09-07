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
| FILTER
|--------------------------------------------------------------------------
*/

$keyword =
    trim($_GET['keyword'] ?? '');

$filter_role =
    trim($_GET['role'] ?? '');


/*
|--------------------------------------------------------------------------
| QUERY USER
|--------------------------------------------------------------------------
*/

$users = [];

$sql = "
    SELECT
        id_user,
        username,
        nama_lengkap,
        email,
        role
    FROM users
    WHERE 1 = 1
";

$params = [];
$types = '';

if ($keyword !== '') {

    $sql .= "
        AND (
            nama_lengkap LIKE ?
            OR username LIKE ?
            OR email LIKE ?
        )
    ";

    $search = '%' . $keyword . '%';

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= 'sss';
}

if ($filter_role !== '') {

    $sql .= "
        AND role = ?
    ";

    $params[] = $filter_role;
    $types .= 's';
}

$sql .= "
    ORDER BY id_user DESC
";


$stmt = mysqli_prepare(
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

            $users[] = $row;
        }
    }

    mysqli_stmt_close($stmt);
}


/*
|--------------------------------------------------------------------------
| TOTAL USER
|--------------------------------------------------------------------------
*/

$total_user = count($users);

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
    Data User - PERADI Pontianak
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
    z-index: 100;
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
    margin-top: 3px;
}

.content {
    padding: 35px;
}

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
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.form-control {
    padding: 11px 13px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    outline: none;
    font-size: 11px;
    background: white;
}

.form-control:focus {
    border-color: #1e3a8a;
}

.search {
    flex: 1;
    min-width: 200px;
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

.btn-reset {
    width: 48px;
    padding: 11px 0;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    padding: 12px;
    background: #f8fafc;
    color: #666;
    font-size: 9px;
    text-transform: uppercase;
}

td {
    padding: 13px 12px;
    border-top: 1px solid #eee;
    font-size: 10px;
}

.user-name {
    color: #1e3a8a;
    font-weight: 700;
}

.username {
    color: #777;
    font-size: 9px;
    margin-top: 3px;
}

.role {
    display: inline-block;
    padding: 6px 9px;
    border-radius: 20px;
    background: #eef2ff;
    color: #1e3a8a;
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
}

.role.admin,
.role.super_admin {
    background: #fff7ed;
    color: #c2410c;
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

.total {
    margin-bottom: 15px;
    color: #777;
    font-size: 11px;
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

    .topbar {
        padding: 0 20px;
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

    .filter {
        flex-direction: column;
    }

    .search {
        width: 100%;
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
            <a
                href="data_user.php"
                class="active"
            >

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

        <h1>
            Data User
        </h1>

        <p>
            Daftar akun pengguna sistem PERADI
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


    <div class="page-header">

        <div>

            <h2>

                <i
                    class="fa-solid fa-users"
                    style="color:#dea057;"
                ></i>

                Data User

            </h2>


            <p>
                Kelola dan lihat akun pengguna yang terdaftar.
            </p>

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
                class="form-control search"
                value="<?= e($keyword); ?>"
                placeholder="Cari nama, username, atau email..."
            >


            <select
                name="role"
                class="form-control"
                onchange="this.form.submit()"
            >

                <option value="">
                    Semua Role
                </option>


                <option
                    value="user"
                    <?= $filter_role === 'user'
                        ? 'selected'
                        : ''; ?>
                >
                    User
                </option>


                <option
                    value="admin"
                    <?= $filter_role === 'admin'
                        ? 'selected'
                        : ''; ?>
                >
                    Admin
                </option>


                <option
                    value="super_admin"
                    <?= $filter_role === 'super_admin'
                        ? 'selected'
                        : ''; ?>
                >
                    Super Admin
                </option>

            </select>


            <a
                href="data_user.php"
                class="btn btn-reset"
                title="Reset Filter"
                aria-label="Reset Filter"
            >

                <i class="fa-solid fa-rotate-left"></i>

            </a>


        </form>


        <div class="total">

            Menampilkan

            <strong>
                <?= $total_user; ?>
            </strong>

            user.

        </div>


        <?php if (!empty($users)): ?>


        <div class="table-wrapper">


            <table>


                <thead>

                    <tr>

                        <th>
                            ID
                        </th>

                        <th>
                            User
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Role
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php foreach (
                    $users
                    as $user
                ): ?>


                    <tr>


                        <td>

                            #<?= e(
                                $user['id_user']
                            ); ?>

                        </td>


                        <td>

                            <div class="user-name">

                                <?= e(
                                    $user['nama_lengkap']
                                ); ?>

                            </div>


                            <div class="username">

                                @<?= e(
                                    $user['username']
                                ); ?>

                            </div>

                        </td>


                        <td>

                            <?= e(
                                $user['email']
                                ?? '-'
                            ); ?>

                        </td>


                        <td>

                            <span
                                class="role <?= e(
                                    $user['role']
                                ); ?>"
                            >

                                <?= e(
                                    $user['role']
                                ); ?>

                            </span>

                        </td>


                    </tr>


                <?php endforeach; ?>


                </tbody>


            </table>


        </div>


        <?php else: ?>


        <div class="empty">

            <i class="fa-solid fa-user-slash"></i>

            Data user tidak ditemukan.

        </div>


        <?php endif; ?>


    </div>


</section>


</main>


</body>

</html>