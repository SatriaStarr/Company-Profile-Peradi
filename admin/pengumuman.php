<?php

session_start();

require_once __DIR__ . '/../koneksi.php';


/*
|--------------------------------------------------------------------------
| CEK LOGIN
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
| PESAN
|--------------------------------------------------------------------------
*/

$pesan = '';
$tipe = '';


/*
|--------------------------------------------------------------------------
| PROSES TAMBAH / EDIT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['aksi'])
) {

    $aksi =
        $_POST['aksi'];

    $judul =
        trim($_POST['judul'] ?? '');

    $isi =
        trim($_POST['isi'] ?? '');

    $status =
        trim($_POST['status'] ?? 'draft');


    if (
        $judul === '' ||
        $isi === ''
    ) {

        $pesan =
            'Judul dan isi pengumuman wajib diisi.';

        $tipe =
            'danger';

    } elseif (
        !in_array(
            $status,
            ['draft', 'publikasi'],
            true
        )
    ) {

        $pesan =
            'Status pengumuman tidak valid.';

        $tipe =
            'danger';

    } else {

        if ($aksi === 'tambah') {

            $stmt =
                mysqli_prepare(
                    $conn,
                    "INSERT INTO pengumuman
                    (judul, isi, status)
                    VALUES (?, ?, ?)"
                );

            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "sss",
                    $judul,
                    $isi,
                    $status
                );

                if (
                    mysqli_stmt_execute($stmt)
                ) {

                    header(
                        "Location: pengumuman.php?pesan=tambah"
                    );

                    exit;

                } else {

                    $pesan =
                        'Gagal menyimpan pengumuman.';

                    $tipe =
                        'danger';
                }

                mysqli_stmt_close($stmt);

            } else {

                $pesan =
                    'Gagal menyiapkan query.';

                $tipe =
                    'danger';
            }

        } elseif ($aksi === 'edit') {

            $id =
                (int) (
                    $_POST['id_pengumuman']
                    ?? 0
                );

            if ($id <= 0) {

                $pesan =
                    'ID pengumuman tidak valid.';

                $tipe =
                    'danger';

            } else {

                $stmt =
                    mysqli_prepare(
                        $conn,
                        "UPDATE pengumuman
                         SET
                            judul = ?,
                            isi = ?,
                            status = ?
                         WHERE id_pengumuman = ?
                         LIMIT 1"
                    );

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssi",
                        $judul,
                        $isi,
                        $status,
                        $id
                    );

                    if (
                        mysqli_stmt_execute($stmt)
                    ) {

                        header(
                            "Location: pengumuman.php?pesan=edit"
                        );

                        exit;

                    } else {

                        $pesan =
                            'Gagal mengubah pengumuman.';

                        $tipe =
                            'danger';
                    }

                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| PROSES HAPUS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['aksi'] ?? '') === 'hapus'
) {

    $id =
        (int) (
            $_POST['id_pengumuman']
            ?? 0
        );

    if ($id > 0) {

        $stmt =
            mysqli_prepare(
                $conn,
                "DELETE FROM pengumuman
                 WHERE id_pengumuman = ?
                 LIMIT 1"
            );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

            header(
                "Location: pengumuman.php?pesan=hapus"
            );

            exit;
        }
    }
}


/*
|--------------------------------------------------------------------------
| PESAN GET
|--------------------------------------------------------------------------
*/

$get_pesan =
    $_GET['pesan'] ?? '';

if ($get_pesan === 'tambah') {

    $pesan =
        'Pengumuman berhasil dibuat.';

    $tipe =
        'success';

} elseif ($get_pesan === 'edit') {

    $pesan =
        'Pengumuman berhasil diperbarui.';

    $tipe =
        'success';

} elseif ($get_pesan === 'hapus') {

    $pesan =
        'Pengumuman berhasil dihapus.';

    $tipe =
        'success';
}


/*
|--------------------------------------------------------------------------
| MODE EDIT
|--------------------------------------------------------------------------
*/

$edit_data = null;

$edit_id =
    (int) (
        $_GET['edit']
        ?? 0
    );

if ($edit_id > 0) {

    $stmt =
        mysqli_prepare(
            $conn,
            "SELECT *
             FROM pengumuman
             WHERE id_pengumuman = ?
             LIMIT 1"
        );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $edit_id
        );

        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

        if ($result) {

            $edit_data =
                mysqli_fetch_assoc($result);
        }

        mysqli_stmt_close($stmt);
    }
}


/*
|--------------------------------------------------------------------------
| DATA PENGUMUMAN
|--------------------------------------------------------------------------
*/

$pengumuman = [];

$result =
    mysqli_query(
        $conn,
        "SELECT *
         FROM pengumuman
         ORDER BY tanggal_publish DESC,
                  id_pengumuman DESC"
    );

if ($result) {

    while (
        $row =
        mysqli_fetch_assoc($result)
    ) {

        $pengumuman[] =
            $row;
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
    Pengumuman - PERADI Pontianak
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

.card h3 {
    color: #1e3a8a;
    font-size: 15px;
    margin-bottom: 18px;
}

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
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    outline: none;
    font-size: 11px;
}

.form-control:focus {
    border-color: #1e3a8a;
}

textarea.form-control {
    min-height: 130px;
    resize: vertical;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 10px 14px;
    border-radius: 8px;
    border: none;
    background: #1e3a8a;
    color: white;
    text-decoration: none;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
}

.btn-gold {
    background: #dea057;
}

.btn-danger {
    background: #dc2626;
}

.btn-small {
    padding: 8px 10px;
    font-size: 9px;
}

.alert {
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 11px;
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
    padding: 13px 11px;
    border-top: 1px solid #eee;
    font-size: 10px;
    vertical-align: top;
}

.judul {
    font-weight: 700;
    color: #1e3a8a;
}

.isi {
    color: #666;
    line-height: 1.6;
    max-width: 500px;
}

.status {
    display: inline-block;
    padding: 6px 9px;
    border-radius: 20px;
    font-size: 8px;
    font-weight: 700;
}

.status.publikasi {
    background: #dcfce7;
    color: #166534;
}

.status.draft {
    background: #fef3c7;
    color: #92400e;
}

.actions {
    display: flex;
    gap: 5px;
}

.empty {
    text-align: center;
    padding: 35px;
    color: #888;
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
}

</style>

</head>

<body>


<?php include __DIR__ . '/partials/sidebar.php'; ?>


<main class="main">


<header class="topbar">

    <div class="topbar-title">

        <h1>Pengumuman</h1>

        <p>Kelola informasi untuk pengguna</p>

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
            class="fa-solid fa-bullhorn"
            style="color:#dea057;"
        ></i>

        Pengumuman
    </h2>

    <p>
        Buat dan kelola informasi yang akan ditampilkan kepada pengguna.
    </p>

</div>


<?php if ($pesan !== ''): ?>

<div class="alert <?= e($tipe); ?>">

    <?= e($pesan); ?>

</div>

<?php endif; ?>


<div class="card">

    <h3>

        <i
            class="fa-solid fa-pen-to-square"
            style="color:#dea057;"
        ></i>

        <?= $edit_data
            ? 'Edit Pengumuman'
            : 'Buat Pengumuman Baru'; ?>

    </h3>


    <form
        method="POST"
        action="pengumuman.php"
    >

        <input
            type="hidden"
            name="aksi"
            value="<?= $edit_data ? 'edit' : 'tambah'; ?>"
        >

        <?php if ($edit_data): ?>

            <input
                type="hidden"
                name="id_pengumuman"
                value="<?= e(
                    $edit_data['id_pengumuman']
                ); ?>"
            >

        <?php endif; ?>


        <div class="form-group">

            <label>
                Judul Pengumuman
            </label>

            <input
                type="text"
                name="judul"
                class="form-control"
                required
                value="<?= e(
                    $edit_data['judul']
                    ?? ''
                ); ?>"
                placeholder="Contoh: Pendaftaran Ujian Profesi Advokat Dibuka"
            >

        </div>


        <div class="form-group">

            <label>
                Isi Pengumuman
            </label>

            <textarea
                name="isi"
                class="form-control"
                required
                placeholder="Tulis isi pengumuman..."
            ><?= e(
                $edit_data['isi']
                ?? ''
            ); ?></textarea>

        </div>


        <div class="form-group">

            <label>
                Status
            </label>

            <select
                name="status"
                class="form-control"
            >

                <option
                    value="draft"
                    <?= (
                        ($edit_data['status'] ?? '')
                        === 'draft'
                    )
                        ? 'selected'
                        : ''
                    ?>
                >
                    Draft
                </option>

                <option
                    value="publikasi"
                    <?= (
                        ($edit_data['status'] ?? '')
                        === 'publikasi'
                    )
                        ? 'selected'
                        : ''
                    ?>
                >
                    Publikasikan
                </option>

            </select>

        </div>


        <button
            type="submit"
            class="btn btn-gold"
        >

            <i class="fa-solid fa-save"></i>

            <?= $edit_data
                ? 'Simpan Perubahan'
                : 'Publikasikan / Simpan'; ?>

        </button>


        <?php if ($edit_data): ?>

            <a
                href="pengumuman.php"
                class="btn"
            >

                <i class="fa-solid fa-xmark"></i>

                Batal

            </a>

        <?php endif; ?>

    </form>

</div>


<div class="card">

    <h3>

        <i
            class="fa-solid fa-list"
            style="color:#dea057;"
        ></i>

        Daftar Pengumuman

    </h3>


    <?php if (!empty($pengumuman)): ?>

    <div class="table-wrapper">

        <table>

            <thead>

                <tr>

                    <th>
                        Judul
                    </th>

                    <th>
                        Isi
                    </th>

                    <th>
                        Tanggal
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
                $pengumuman
                as $item
            ): ?>

                <tr>

                    <td>

                        <div class="judul">

                            <?= e(
                                $item['judul']
                            ); ?>

                        </div>

                    </td>


                    <td>

                        <div class="isi">

                            <?= nl2br(
                                e(
                                    $item['isi']
                                )
                            ); ?>

                        </div>

                    </td>


                    <td>

                        <?= e(
                            date(
                                'd-m-Y H:i',
                                strtotime(
                                    $item[
                                        'tanggal_publish'
                                    ]
                                )
                            )
                        ); ?>

                    </td>


                    <td>

                        <span
                            class="status <?= e(
                                $item['status']
                            ); ?>"
                        >

                            <?= e(
                                strtoupper(
                                    $item['status']
                                )
                            ); ?>

                        </span>

                    </td>


                    <td>

                        <div class="actions">

                            <a
                                href="pengumuman.php?edit=<?= e(
                                    $item[
                                        'id_pengumuman'
                                    ]
                                ); ?>"
                                class="btn btn-small"
                            >

                                <i class="fa-solid fa-pen"></i>

                            </a>


                            <form
                                method="POST"
                                onsubmit="
                                    return confirm(
                                        'Yakin ingin menghapus pengumuman ini?'
                                    );
                                "
                            >

                                <input
                                    type="hidden"
                                    name="aksi"
                                    value="hapus"
                                >

                                <input
                                    type="hidden"
                                    name="id_pengumuman"
                                    value="<?= e(
                                        $item[
                                            'id_pengumuman'
                                        ]
                                    ); ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-small btn-danger"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <?php else: ?>

    <div class="empty">

        <i
            class="fa-solid fa-bullhorn"
            style="
                font-size:35px;
                color:#dea057;
                display:block;
                margin-bottom:10px;
            "
        ></i>

        Belum ada pengumuman.

    </div>

    <?php endif; ?>

</div>


</section>

</main>

</body>

</html>