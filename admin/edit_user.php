<?php
require_once __DIR__ . '/partials/admin_auth.php';

$id = (int) ($_GET['id'] ?? $_POST['id_user'] ?? 0);
if ($id <= 0) {
    header('Location: data_user.php');
    exit;
}

/* ---------- ambil user ---------- */
$user = null;
if ($st = mysqli_prepare($conn,
    'SELECT id_user, username, nama_lengkap, email, role, status FROM users WHERE id_user = ? LIMIT 1')) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
}
if (!$user) {
    header('Location: data_user.php?pesan=hapus_gagal');
    exit;
}

/* hanya super_admin yang boleh mengedit akun admin / super_admin lain */
$target_is_privileged = in_array($user['role'], ['admin', 'super_admin'], true);
if ($target_is_privileged && !$is_super_admin && $user['id_user'] != $admin_id) {
    header('Location: data_user.php?pesan=forbidden');
    exit;
}

$errors = [];
$old    = $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? $user['role'];
    $status   = $_POST['status'] ?? $user['status'];
    $pass_new = $_POST['password'] ?? '';

    $old = compact('nama', 'username', 'email', 'role', 'status') + ['id_user' => $id];

    /* role/status hanya boleh diubah super_admin, dan tidak untuk diri sendiri */
    if (!$is_super_admin || $user['id_user'] == $admin_id) {
        $role   = $user['role'];
        $status = $user['status'];
    }

    if ($nama === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if ($username === '') {
        $errors[] = 'Username wajib diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
        $errors[] = 'Username 3-50 karakter, hanya huruf/angka/titik/garis.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (!in_array($role, ['user', 'admin', 'super_admin'], true)) {
        $errors[] = 'Role tidak valid.';
    }
    if (!in_array($status, ['active', 'pending', 'banned'], true)) {
        $errors[] = 'Status tidak valid.';
    }
    if ($pass_new !== '' && strlen($pass_new) < 6) {
        $errors[] = 'Password baru minimal 6 karakter.';
    }

    /* username unik */
    if (!$errors && $username !== $user['username']) {
        if ($st = mysqli_prepare($conn, 'SELECT id_user FROM users WHERE username = ? AND id_user <> ? LIMIT 1')) {
            mysqli_stmt_bind_param($st, 'si', $username, $id);
            mysqli_stmt_execute($st);
            if (mysqli_fetch_assoc(mysqli_stmt_get_result($st))) {
                $errors[] = 'Username sudah dipakai user lain.';
            }
            mysqli_stmt_close($st);
        }
    }

    /* jangan turunkan super_admin aktif terakhir */
    if (!$errors && $user['role'] === 'super_admin' && $role !== 'super_admin') {
        $c = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) n FROM users WHERE role = 'super_admin'"));
        if ((int) $c['n'] <= 1) {
            $errors[] = 'Tidak bisa menurunkan super admin terakhir.';
        }
    }

    if (!$errors) {
        $email_val = $email !== '' ? $email : null;
        if ($pass_new !== '') {
            $hash = password_hash($pass_new, PASSWORD_DEFAULT);
            $st = mysqli_prepare($conn,
                'UPDATE users SET nama_lengkap=?, username=?, email=?, role=?, status=?, password=? WHERE id_user=? LIMIT 1');
            mysqli_stmt_bind_param($st, 'ssssssi', $nama, $username, $email_val, $role, $status, $hash, $id);
        } else {
            $st = mysqli_prepare($conn,
                'UPDATE users SET nama_lengkap=?, username=?, email=?, role=?, status=? WHERE id_user=? LIMIT 1');
            mysqli_stmt_bind_param($st, 'sssssi', $nama, $username, $email_val, $role, $status, $id);
        }
        if ($st && mysqli_stmt_execute($st)) {
            mysqli_stmt_close($st);
            header('Location: data_user.php?pesan=edit_ok');
            exit;
        }
        $errors[] = 'Gagal menyimpan perubahan: ' . mysqli_error($conn);
    }
}

$can_edit_role = $is_super_admin && $user['id_user'] != $admin_id;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
body { background: #f4f6f9; color: #333; }
.main { margin-left: 250px; min-height: 100vh; }
.topbar { height: 75px; background: #fff; display: flex; align-items: center; padding: 0 35px; border-bottom: 1px solid #e5e7eb; }
.topbar h1 { color: #1e3a8a; font-size: 22px; font-weight: 800; }
.content { padding: 35px; max-width: 640px; }
.back { display: inline-flex; align-items: center; gap: 7px; color: #1e3a8a; text-decoration: none; font-weight: 700; font-size: 12px; margin-bottom: 18px; }
.card { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 5px 20px rgba(0,0,0,.05); }
.form-group { margin-bottom: 18px; }
label { display: block; font-size: 11px; font-weight: 700; color: #555; margin-bottom: 7px; }
.form-control { width: 100%; padding: 12px 13px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 13px; background: #fff; }
.form-control:focus { border-color: #1e3a8a; }
.form-control:disabled { background: #f1f5f9; color: #94a3b8; }
.hint { margin-top: 5px; font-size: 11px; color: #94a3b8; }
.alert { padding: 13px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 12px; font-weight: 600; background: #fee2e2; color: #b91c1c; }
.alert ul { margin: 6px 0 0 18px; }
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 12px 18px; border-radius: 8px; border: none; background: #1e3a8a; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; cursor: pointer; }
.btn-gold { background: #dea057; }
.btn-plain { background: #e5e7eb; color: #333; }
@media(max-width:900px) { .main { margin-left: 0; } .content { padding: 20px 16px; } }
</style>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="main">
<header class="topbar"><h1>Edit User</h1></header>

<section class="content">
    <a class="back" href="data_user.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Data User</a>

    <div class="card">
        <?php if ($errors): ?>
            <div class="alert">
                Perbaiki hal berikut:
                <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id_user" value="<?= e($id) ?>">

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" required
                       value="<?= e($old['nama_lengkap'] ?? $old['nama'] ?? $user['nama_lengkap']) ?>">
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required
                       value="<?= e($old['username'] ?? $user['username']) ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?= e($old['email'] ?? $user['email']) ?>">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" <?= $can_edit_role ? '' : 'disabled' ?>>
                    <?php foreach (['user' => 'User', 'admin' => 'Admin', 'super_admin' => 'Super Admin'] as $rv => $rl): ?>
                        <option value="<?= $rv ?>" <?= ($old['role'] ?? $user['role']) === $rv ? 'selected' : '' ?>><?= $rl ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$can_edit_role): ?>
                    <div class="hint">Hanya super admin yang dapat mengubah role user lain.</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" <?= $can_edit_role ? '' : 'disabled' ?>>
                    <?php foreach (['active' => 'Active', 'pending' => 'Pending', 'banned' => 'Banned'] as $sv => $sl): ?>
                        <option value="<?= $sv ?>" <?= ($old['status'] ?? $user['status']) === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Reset Password (opsional)</label>
                <input type="text" name="password" class="form-control" autocomplete="new-password"
                       placeholder="Kosongkan jika tidak diubah">
                <div class="hint">Minimal 6 karakter. Password lama tidak dapat dilihat.</div>
            </div>

            <button type="submit" class="btn btn-gold"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
            <a href="detail_user.php?id=<?= e($id) ?>" class="btn btn-plain">Batal</a>
        </form>
    </div>
</section>
</main>
</body>
</html>
