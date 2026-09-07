<?php
session_start();
require_once __DIR__ . '/functions.php';
require_user();

$uid = user_id();

/* ---------- ambil data user ---------- */
$st = $conn->prepare('SELECT id_user, username, email, google_id, nama_lengkap, role, status, created_at FROM users WHERE id_user = ? LIMIT 1');
$st->bind_param('i', $uid);
$st->execute();
$user = $st->get_result()->fetch_assoc();
$st->close();
if (!$user) {
    header('Location: dashboard.php');
    exit;
}

$errors  = [];
$sukses  = false;
$old     = $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $p1    = $_POST['password'] ?? '';
    $p2    = $_POST['password2'] ?? '';
    $p_cur = $_POST['password_lama'] ?? '';

    $old['nama_lengkap'] = $nama;
    $old['email']        = $email;

    if ($nama === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    /* email unik */
    if (!$errors && $email !== '' && $email !== ($user['email'] ?? '')) {
        $c = $conn->prepare('SELECT id_user FROM users WHERE email = ? AND id_user <> ? LIMIT 1');
        $c->bind_param('si', $email, $uid);
        $c->execute();
        if ($c->get_result()->fetch_assoc()) {
            $errors[] = 'Email sudah dipakai akun lain.';
        }
        $c->close();
    }

    /* ganti password (opsional) */
    $ganti_pw = ($p1 !== '' || $p2 !== '' || $p_cur !== '');
    if ($ganti_pw) {
        $pw = $conn->prepare('SELECT password FROM users WHERE id_user = ? LIMIT 1');
        $pw->bind_param('i', $uid);
        $pw->execute();
        $hash = $pw->get_result()->fetch_assoc()['password'] ?? '';
        $pw->close();
        if (!password_verify($p_cur, $hash)) {
            $errors[] = 'Password lama salah.';
        }
        if (strlen($p1) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        }
        if ($p1 !== $p2) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }
    }

    if (!$errors) {
        $email_val = $email !== '' ? $email : null;
        if ($ganti_pw) {
            $newhash = password_hash($p1, PASSWORD_DEFAULT);
            $u = $conn->prepare('UPDATE users SET nama_lengkap = ?, email = ?, password = ? WHERE id_user = ? LIMIT 1');
            $u->bind_param('sssi', $nama, $email_val, $newhash, $uid);
        } else {
            $u = $conn->prepare('UPDATE users SET nama_lengkap = ?, email = ? WHERE id_user = ? LIMIT 1');
            $u->bind_param('ssi', $nama, $email_val, $uid);
        }
        $u->execute();
        $u->close();

        $_SESSION['nama_lengkap'] = $nama;
        $_SESSION['email']        = $email_val;

        $sukses = true;
        $user['nama_lengkap'] = $nama;
        $user['email']        = $email_val;
        $old = $user;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { box-sizing: border-box; }
body { font-family: 'Montserrat', sans-serif; background: #f4f6f9; margin: 0; color: #333; }
.wrap { max-width: 620px; margin: 40px auto; padding: 20px; }
.back { display: inline-flex; align-items: center; gap: 7px; color: #1e3a8a; text-decoration: none; font-weight: 700; font-size: 13px; margin-bottom: 18px; }
.card { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,.07); }
h1 { color: #1e3a8a; font-size: 22px; margin: 0 0 6px; }
.sub { color: #64748b; font-size: 13px; margin-bottom: 22px; }
.fg { margin-bottom: 16px; }
label { display: block; font-size: 12px; font-weight: 700; color: #555; margin-bottom: 6px; }
input { width: 100%; padding: 12px 13px; border: 1px solid #e5e7eb; border-radius: 9px; outline: none; font-size: 14px; font-family: inherit; }
input:focus { border-color: #1e3a8a; }
input:disabled { background: #f1f5f9; color: #94a3b8; }
.hint { font-size: 12px; color: #94a3b8; margin-top: 4px; }
.alert { padding: 13px 16px; border-radius: 10px; font-weight: 600; font-size: 13px; margin-bottom: 18px; }
.alert.ok { background: #dcfce7; color: #166534; }
.alert.err { background: #fee2e2; color: #b91c1c; }
.alert.err ul { margin: 6px 0 0 18px; }
.divider { border: 0; border-top: 1px solid #eee; margin: 22px 0; }
.btn { display: inline-flex; align-items: center; gap: 7px; background: #dea057; color: #fff; border: none; padding: 13px 20px; border-radius: 9px; font-weight: 700; font-size: 14px; cursor: pointer; font-family: inherit; }
.btn.plain { background: #e5e7eb; color: #333; text-decoration: none; }
</style>
</head>
<body>
<div class="wrap">
    <a class="back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>

    <div class="card">
        <h1>Profil Saya</h1>
        <p class="sub">@<?= e($user['username']) ?> &middot; terdaftar <?= e(date('d M Y', strtotime($user['created_at']))) ?></p>

        <?php if ($sukses): ?>
            <div class="alert ok"><i class="fa-solid fa-circle-check"></i> Profil berhasil diperbarui.</div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert err">Perbaiki:
                <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="fg">
                <label>Username</label>
                <input type="text" value="<?= e($user['username']) ?>" disabled>
                <div class="hint">Username tidak dapat diubah.</div>
            </div>
            <div class="fg">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required value="<?= e($old['nama_lengkap']) ?>">
            </div>
            <div class="fg">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($old['email']) ?>">
                <?php if ($user['google_id']): ?><div class="hint">Akun ini terhubung dengan Google.</div><?php endif; ?>
            </div>

            <hr class="divider">
            <p class="sub" style="margin-bottom:14px"><b>Ganti Password</b> (kosongkan jika tidak diubah)</p>
            <div class="fg">
                <label>Password Lama</label>
                <input type="password" name="password_lama" autocomplete="current-password">
            </div>
            <div class="fg">
                <label>Password Baru</label>
                <input type="password" name="password" autocomplete="new-password">
            </div>
            <div class="fg">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password2" autocomplete="new-password">
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
        </form>
    </div>
</div>
</body>
</html>
