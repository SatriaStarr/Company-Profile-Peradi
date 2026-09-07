<?php
require_once __DIR__ . '/partials/admin_auth.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: data_user.php');
    exit;
}

/* ---------- data user ---------- */
$user = null;
if ($st = mysqli_prepare($conn,
    'SELECT id_user, username, nama_lengkap, email, google_id, role, status, created_at
     FROM users WHERE id_user = ? LIMIT 1')) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
}
if (!$user) {
    header('Location: data_user.php?pesan=hapus_gagal');
    exit;
}

/* ---------- pendaftaran user ---------- */
$pendaftaran = [];
if ($st = mysqli_prepare($conn,
    "SELECT p.id_pendaftaran, p.jenis_pendaftaran, p.status, p.tanggal_daftar,
            pb.status AS status_bayar, pb.jumlah
     FROM pendaftaran p
     LEFT JOIN pembayaran pb ON pb.id_pendaftaran = p.id_pendaftaran
     WHERE p.id_user = ?
     ORDER BY p.id_pendaftaran DESC")) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    while ($r = mysqli_fetch_assoc($res)) {
        $pendaftaran[] = $r;
    }
    mysqli_stmt_close($st);
}

/* ---------- notifikasi terakhir ---------- */
$notif = [];
if ($st = mysqli_prepare($conn,
    'SELECT judul, pesan, is_read, created_at FROM notifikasi
     WHERE id_user = ? ORDER BY id_notifikasi DESC LIMIT 10')) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    while ($r = mysqli_fetch_assoc($res)) {
        $notif[] = $r;
    }
    mysqli_stmt_close($st);
}

function fmt_dt(?string $v): string
{
    if (!$v) {
        return '-';
    }
    $t = strtotime($v);
    return $t ? date('d M Y H:i', $t) : e($v);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail User - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
body { background: #f4f6f9; color: #333; }
.main { margin-left: 250px; min-height: 100vh; }
.topbar { height: 75px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 35px; border-bottom: 1px solid #e5e7eb; }
.topbar-title h1 { color: #1e3a8a; font-size: 22px; font-weight: 800; }
.content { padding: 35px; max-width: 1000px; }
.back { display: inline-flex; align-items: center; gap: 7px; color: #1e3a8a; text-decoration: none; font-weight: 700; font-size: 12px; margin-bottom: 18px; }
.card { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,.05); margin-bottom: 20px; }
.card h3 { color: #1e3a8a; font-size: 15px; margin-bottom: 16px; }
.profile { display: flex; gap: 18px; align-items: center; margin-bottom: 6px; }
.avatar { width: 64px; height: 64px; border-radius: 50%; background: #dea057; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 26px; }
.grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px 24px; margin-top: 16px; }
.field label { display: block; font-size: 10px; color: #999; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
.field div { font-size: 13px; font-weight: 600; color: #1e293b; }
.pill { display: inline-block; padding: 5px 9px; border-radius: 20px; font-size: 9px; font-weight: 800; text-transform: uppercase; }
.pill.user { background: #eef2ff; color: #1e3a8a; }
.pill.admin, .pill.super_admin { background: #fff7ed; color: #c2410c; }
.pill.active, .pill.lunas { background: #dcfce7; color: #166534; }
.pill.pending, .pill.menunggu_verifikasi, .pill.menunggu { background: #fef3c7; color: #92400e; }
.pill.banned, .pill.ditolak { background: #fee2e2; color: #b91c1c; }
.pill.belum_bayar { background: #f1f5f9; color: #475569; }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; padding: 10px; background: #f8fafc; color: #666; font-size: 10px; text-transform: uppercase; }
td { padding: 11px 10px; border-top: 1px solid #eee; font-size: 11px; }
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 14px; border-radius: 8px; background: #dea057; color: #fff; text-decoration: none; font-size: 11px; font-weight: 700; }
.muted { color: #94a3b8; font-size: 12px; }
@media(max-width:900px) { .main { margin-left: 0; } .grid { grid-template-columns: 1fr; } .content { padding: 20px 16px; } }
</style>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="main">
<header class="topbar">
    <div class="topbar-title"><h1>Detail User</h1></div>
</header>

<section class="content">
    <a class="back" href="data_user.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Data User</a>

    <div class="card">
        <div class="profile">
            <div class="avatar"><?= e(strtoupper(substr($user['nama_lengkap'] ?: $user['username'], 0, 1))) ?></div>
            <div>
                <h3 style="margin:0"><?= e($user['nama_lengkap']) ?></h3>
                <div class="muted">@<?= e($user['username']) ?></div>
            </div>
            <div style="margin-left:auto">
                <a class="btn" href="edit_user.php?id=<?= e($user['id_user']) ?>"><i class="fa-solid fa-pen"></i> Edit</a>
            </div>
        </div>
        <div class="grid">
            <div class="field"><label>ID User</label><div>#<?= e($user['id_user']) ?></div></div>
            <div class="field"><label>Email</label><div><?= e($user['email'] ?: '-') ?></div></div>
            <div class="field"><label>Role</label><div><span class="pill <?= e($user['role']) ?>"><?= e(str_replace('_', ' ', $user['role'])) ?></span></div></div>
            <div class="field"><label>Status</label><div><span class="pill <?= e($user['status']) ?>"><?= e($user['status']) ?></span></div></div>
            <div class="field"><label>Login Google</label><div><?= $user['google_id'] ? 'Terhubung' : 'Tidak' ?></div></div>
            <div class="field"><label>Terdaftar</label><div><?= fmt_dt($user['created_at']) ?></div></div>
        </div>
    </div>

    <div class="card">
        <h3><i class="fa-solid fa-file-signature" style="color:#dea057"></i> Pendaftaran Sertifikasi</h3>
        <?php if ($pendaftaran): ?>
        <table>
            <thead><tr><th>ID</th><th>Jenis</th><th>Status</th><th>Pembayaran</th><th>Tanggal</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pendaftaran as $p): ?>
                <tr>
                    <td>#<?= e($p['id_pendaftaran']) ?></td>
                    <td><strong><?= e($p['jenis_pendaftaran']) ?></strong></td>
                    <td><span class="pill <?= e($p['status'] ?: 'menunggu') ?>"><?= e(str_replace('_', ' ', $p['status'] ?: 'menunggu')) ?></span></td>
                    <td>
                        <span class="pill <?= e($p['status_bayar'] ?? 'belum_bayar') ?>"><?= e(str_replace('_', ' ', $p['status_bayar'] ?? 'belum_bayar')) ?></span>
                        <?php if ($p['jumlah'] !== null): ?><br><span class="muted">Rp <?= number_format((float) $p['jumlah'], 0, ',', '.') ?></span><?php endif; ?>
                    </td>
                    <td><?= fmt_dt($p['tanggal_daftar']) ?></td>
                    <td><a class="muted" href="detail_pendaftaran.php?id=<?= e($p['id_pendaftaran']) ?>">Lihat &rarr;</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="muted">User ini belum melakukan pendaftaran.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3><i class="fa-solid fa-bell" style="color:#dea057"></i> Notifikasi Terakhir</h3>
        <?php if ($notif): ?>
        <table>
            <thead><tr><th>Judul</th><th>Pesan</th><th>Dibaca</th><th>Waktu</th></tr></thead>
            <tbody>
            <?php foreach ($notif as $n): ?>
                <tr>
                    <td><strong><?= e($n['judul']) ?></strong></td>
                    <td><?= e($n['pesan']) ?></td>
                    <td><?= $n['is_read'] ? 'Ya' : 'Belum' ?></td>
                    <td><?= fmt_dt($n['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="muted">Belum ada notifikasi.</p>
        <?php endif; ?>
    </div>

</section>
</main>
</body>
</html>
