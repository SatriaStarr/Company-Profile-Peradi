<?php
require_once __DIR__ . '/partials/admin_auth.php';

/*
|--------------------------------------------------------------------------
| AKSI: HAPUS USER (POST, super_admin saja)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    csrf_check();

    if (!$is_super_admin) {
        header('Location: data_user.php?pesan=forbidden');
        exit;
    }

    $id_hapus = (int) ($_POST['id_user'] ?? 0);

    if ($id_hapus <= 0 || $id_hapus === $admin_id) {
        header('Location: data_user.php?pesan=hapus_gagal');
        exit;
    }

    // jangan hapus super_admin aktif terakhir
    $target = null;
    if ($st = mysqli_prepare($conn, 'SELECT role, status FROM users WHERE id_user = ? LIMIT 1')) {
        mysqli_stmt_bind_param($st, 'i', $id_hapus);
        mysqli_stmt_execute($st);
        $target = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
        mysqli_stmt_close($st);
    }
    if (!$target) {
        header('Location: data_user.php?pesan=hapus_gagal');
        exit;
    }
    if ($target['role'] === 'super_admin') {
        $c = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) n FROM users WHERE role = 'super_admin'"));
        if ((int) $c['n'] <= 1) {
            header('Location: data_user.php?pesan=hapus_super');
            exit;
        }
    }

    if ($st = mysqli_prepare($conn, 'DELETE FROM users WHERE id_user = ? LIMIT 1')) {
        mysqli_stmt_bind_param($st, 'i', $id_hapus);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        header('Location: data_user.php?pesan=' . ($ok ? 'hapus_ok' : 'hapus_gagal'));
        exit;
    }
    header('Location: data_user.php?pesan=hapus_gagal');
    exit;
}

/*
|--------------------------------------------------------------------------
| AKSI: AKTIFKAN USER PENDING (POST)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'aktifkan') {
    csrf_check();
    $id_akt = (int) ($_POST['id_user'] ?? 0);
    if ($id_akt > 0) {
        if ($st = mysqli_prepare($conn,
            "UPDATE users SET status = 'active' WHERE id_user = ? AND status = 'pending' LIMIT 1")) {
            mysqli_stmt_bind_param($st, 'i', $id_akt);
            mysqli_stmt_execute($st);
            mysqli_stmt_close($st);
        }
    }
    header('Location: data_user.php?pesan=aktif_ok');
    exit;
}

/*
|--------------------------------------------------------------------------
| FILTER + PAGINATION
|--------------------------------------------------------------------------
*/
$keyword     = trim($_GET['keyword'] ?? '');
$filter_role = trim($_GET['role'] ?? '');
$valid_roles = ['user', 'admin', 'super_admin'];
if (!in_array($filter_role, $valid_roles, true)) {
    $filter_role = '';
}

$per_page = 10;
$page     = max(1, (int) ($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$where  = ['1 = 1'];
$params = [];
$types  = '';

if ($keyword !== '') {
    $where[]  = '(nama_lengkap LIKE ? OR username LIKE ? OR email LIKE ?)';
    $like     = '%' . $keyword . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}
if ($filter_role !== '') {
    $where[]  = 'role = ?';
    $params[] = $filter_role;
    $types   .= 's';
}
$where_sql = implode(' AND ', $where);

/* total */
$total_user = 0;
if ($st = mysqli_prepare($conn, "SELECT COUNT(*) n FROM users WHERE $where_sql")) {
    if ($params) {
        mysqli_stmt_bind_param($st, $types, ...$params);
    }
    mysqli_stmt_execute($st);
    $total_user = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($st))['n'] ?? 0);
    mysqli_stmt_close($st);
}
$total_page = max(1, (int) ceil($total_user / $per_page));
if ($page > $total_page) {
    $page   = $total_page;
    $offset = ($page - 1) * $per_page;
}

/* data */
$users = [];
$sql = "SELECT id_user, username, nama_lengkap, email, role, status, created_at
        FROM users WHERE $where_sql
        ORDER BY id_user DESC
        LIMIT ? OFFSET ?";
if ($st = mysqli_prepare($conn, $sql)) {
    $p2 = $params;
    $t2 = $types . 'ii';
    $p2[] = $per_page;
    $p2[] = $offset;
    mysqli_stmt_bind_param($st, $t2, ...$p2);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    while ($row = mysqli_fetch_assoc($res)) {
        $users[] = $row;
    }
    mysqli_stmt_close($st);
}

/* pesan */
$pesan_map = [
    'hapus_ok'     => ['success', 'User berhasil dihapus.'],
    'hapus_gagal'  => ['danger',  'User gagal dihapus.'],
    'hapus_super'  => ['danger',  'Tidak bisa menghapus super admin terakhir.'],
    'forbidden'    => ['danger',  'Hanya super admin yang boleh menghapus user.'],
    'aktif_ok'     => ['success', 'User berhasil diaktifkan.'],
    'edit_ok'      => ['success', 'Data user berhasil diperbarui.'],
];
$pesan = $pesan_map[$_GET['pesan'] ?? ''] ?? null;

function u_page_url(int $p): string
{
    $q = ['page' => $p];
    if (($_GET['keyword'] ?? '') !== '') {
        $q['keyword'] = $_GET['keyword'];
    }
    if (($_GET['role'] ?? '') !== '') {
        $q['role'] = $_GET['role'];
    }
    return 'data_user.php?' . http_build_query($q);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data User - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
body { background: #f4f6f9; color: #333; }
.main { margin-left: 250px; min-height: 100vh; }
.topbar { height: 75px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 35px; border-bottom: 1px solid #e5e7eb; }
.topbar-title h1 { color: #1e3a8a; font-size: 22px; font-weight: 800; }
.topbar-title p { color: #777; font-size: 12px; margin-top: 4px; }
.admin-info { display: flex; align-items: center; gap: 12px; }
.admin-avatar { width: 42px; height: 42px; border-radius: 50%; background: #dea057; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.admin-name { font-size: 13px; font-weight: 700; }
.admin-role { font-size: 10px; color: #777; margin-top: 3px; }
.content { padding: 35px; }
.page-header { margin-bottom: 25px; }
.page-header h2 { color: #1e3a8a; font-size: 21px; font-weight: 800; }
.page-header p { color: #777; font-size: 12px; margin-top: 5px; }
.card { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,.05); margin-bottom: 20px; }
.alert { padding: 13px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 12px; font-weight: 600; }
.alert.success { background: #dcfce7; color: #166534; }
.alert.danger { background: #fee2e2; color: #b91c1c; }
.filter { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; align-items: center; }
.form-control { padding: 11px 13px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 12px; background: #fff; }
.form-control:focus { border-color: #1e3a8a; }
.input-search { flex: 1; min-width: 220px; position: relative; }
.input-search input { width: 100%; padding-left: 38px; }
.input-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #999; font-size: 13px; }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 10px 14px; border-radius: 8px; border: none; background: #1e3a8a; color: #fff; text-decoration: none; font-size: 11px; font-weight: 700; cursor: pointer; transition: .25s; }
.btn:hover { background: #152c69; }
.btn-gold { background: #dea057; } .btn-gold:hover { background: #c98d46; }
.btn-danger { background: #dc2626; } .btn-danger:hover { background: #b91c1c; }
.btn-green { background: #10b981; } .btn-green:hover { background: #059669; }
.btn-icon { width: 40px; padding: 10px 0; }
.btn-sm { padding: 8px 10px; font-size: 10px; }
.total { margin-bottom: 14px; color: #777; font-size: 12px; }
.table-wrapper { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 760px; }
th { text-align: left; padding: 12px; background: #f8fafc; color: #666; font-size: 10px; text-transform: uppercase; white-space: nowrap; }
td { padding: 13px 12px; border-top: 1px solid #eee; font-size: 11px; vertical-align: middle; }
.user-name { color: #1e3a8a; font-weight: 700; }
.username { color: #999; font-size: 9px; margin-top: 3px; }
.pill { display: inline-block; padding: 5px 9px; border-radius: 20px; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: .3px; }
.pill.user { background: #eef2ff; color: #1e3a8a; }
.pill.admin, .pill.super_admin { background: #fff7ed; color: #c2410c; }
.pill.active { background: #dcfce7; color: #166534; }
.pill.pending { background: #fef3c7; color: #92400e; }
.pill.banned { background: #fee2e2; color: #b91c1c; }
.actions { display: flex; gap: 5px; flex-wrap: wrap; }
.empty { text-align: center; padding: 40px 15px; color: #888; font-size: 12px; }
.empty i { display: block; font-size: 35px; color: #dea057; margin-bottom: 10px; }
.pagination { display: flex; align-items: center; justify-content: space-between; margin-top: 20px; padding-top: 16px; border-top: 1px solid #eee; flex-wrap: wrap; gap: 12px; }
.pagination-info { color: #777; font-size: 11px; }
.pagination-links { display: flex; gap: 5px; }
.page-link { min-width: 34px; height: 34px; padding: 0 8px; border-radius: 7px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #e5e7eb; color: #1e3a8a; text-decoration: none; font-size: 11px; font-weight: 700; }
.page-link:hover, .page-link.active { background: #1e3a8a; color: #fff; border-color: #1e3a8a; }
@media(max-width:900px) { .main { margin-left: 0; } .topbar { padding: 0 20px; } .content { padding: 20px 16px; } .admin-name, .admin-role { display: none; } }
</style>
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="main">

<header class="topbar">
    <div class="topbar-title">
        <h1>Data User</h1>
        <p>Daftar akun pengguna sistem PERADI</p>
    </div>
    <div class="admin-info">
        <div class="admin-avatar"><?= e(strtoupper(substr($admin_nama, 0, 1))) ?></div>
        <div>
            <div class="admin-name"><?= e($admin_nama) ?></div>
            <div class="admin-role"><?= e($admin_role) ?></div>
        </div>
    </div>
</header>

<section class="content">

    <div class="page-header">
        <h2><i class="fa-solid fa-users" style="color:#dea057;"></i> Data User</h2>
        <p>Kelola akun pengguna: cari, filter, lihat detail, edit, dan hapus.</p>
    </div>

    <?php if ($pesan): ?>
        <div class="alert <?= e($pesan[0]) ?>"><?= e($pesan[1]) ?></div>
    <?php endif; ?>

    <div class="card">

        <form method="GET" class="filter" id="filterForm">
            <div class="input-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="keyword" id="keyword" class="form-control"
                       value="<?= e($keyword) ?>"
                       placeholder="Cari nama, username, atau email..."
                       autocomplete="off">
            </div>

            <select name="role" class="form-control" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Role</option>
                <option value="user"        <?= $filter_role === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin"       <?= $filter_role === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="super_admin" <?= $filter_role === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
            </select>

            <a href="data_user.php" class="btn btn-gold btn-icon" title="Reset filter" aria-label="Reset filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </form>

        <div class="total">Menampilkan <strong><?= count($users) ?></strong> dari <strong><?= $total_user ?></strong> user.</div>

        <?php if (!empty($users)): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>#<?= e($user['id_user']) ?></td>
                        <td>
                            <div class="user-name"><?= e($user['nama_lengkap']) ?></div>
                            <div class="username">@<?= e($user['username']) ?></div>
                        </td>
                        <td><?= e($user['email'] ?? '-') ?: '-' ?></td>
                        <td><span class="pill <?= e($user['role']) ?>"><?= e(str_replace('_', ' ', $user['role'])) ?></span></td>
                        <td><span class="pill <?= e($user['status']) ?>"><?= e($user['status']) ?></span></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-sm" href="detail_user.php?id=<?= e($user['id_user']) ?>" title="Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a class="btn btn-sm btn-gold" href="edit_user.php?id=<?= e($user['id_user']) ?>" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <?php if ($user['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="aktifkan">
                                        <input type="hidden" name="id_user" value="<?= e($user['id_user']) ?>">
                                        <button class="btn btn-sm btn-green" title="Aktifkan" type="submit">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($is_super_admin && $user['id_user'] != $admin_id): ?>
                                    <form method="POST" style="display:inline"
                                          onsubmit="return confirm('Hapus user <?= e(addslashes($user['username'])) ?>? Tindakan ini permanen.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id_user" value="<?= e($user['id_user']) ?>">
                                        <button class="btn btn-sm btn-danger" title="Hapus" type="submit">
                                            <i class="fa-solid fa-trash"></i>
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

        <div class="pagination">
            <div class="pagination-info">
                Halaman <strong><?= $page ?></strong> dari <strong><?= $total_page ?></strong>
            </div>
            <?php if ($total_page > 1): ?>
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="<?= e(u_page_url($page - 1)) ?>"><i class="fa-solid fa-chevron-left"></i></a>
                <?php endif; ?>
                <?php
                $s = max(1, $page - 2);
                $en = min($total_page, $page + 2);
                for ($i = $s; $i <= $en; $i++): ?>
                    <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="<?= e(u_page_url($i)) ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_page): ?>
                    <a class="page-link" href="<?= e(u_page_url($page + 1)) ?>"><i class="fa-solid fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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

<script>
/* auto-search saat mengetik (debounce) + tetap bisa Enter */
(function () {
    var input = document.getElementById('keyword');
    var form  = document.getElementById('filterForm');
    var t;
    input.addEventListener('input', function () {
        clearTimeout(t);
        t = setTimeout(function () { form.submit(); }, 500);
    });
    form.addEventListener('submit', function () { clearTimeout(t); });
})();
</script>

</body>
</html>
