<?php
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../notifikasi_helper.php';

$view = ($_GET['view'] ?? 'saya') === 'semua' ? 'semua' : 'saya';

/*
|--------------------------------------------------------------------------
| AKSI (POST): hapus notifikasi milik sendiri
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    csrf_check();
    $idn = (int) ($_POST['id_notifikasi'] ?? 0);
    if ($idn > 0) {
        $st = $conn->prepare('DELETE FROM notifikasi WHERE id_notifikasi = ? AND id_user = ? LIMIT 1');
        $st->bind_param('ii', $idn, $admin_id);
        $st->execute();
        $st->close();
    }
    header('Location: notifikasi.php');
    exit;
}

$keyword = trim($_GET['keyword'] ?? '');

/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/
$rows = [];
$baru = 0;

if ($view === 'saya') {

    $st = $conn->prepare(
        'SELECT id_notifikasi, judul, pesan, is_read, created_at
         FROM notifikasi WHERE id_user = ?
         ORDER BY id_notifikasi DESC'
    );
    $st->bind_param('i', $admin_id);
    $st->execute();
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) {
        $r['_baru'] = ((int) $r['is_read'] === 0);
        if ($r['_baru']) {
            $baru++;
        }
        $rows[] = $r;
    }
    $st->close();

    if ($baru > 0) {
        $conn->query('UPDATE notifikasi SET is_read = 1 WHERE id_user = ' . (int) $admin_id . ' AND is_read = 0');
    }

} else {

    $sql = "SELECT n.id_notifikasi, n.id_user, n.judul, n.pesan, n.is_read, n.created_at,
                   u.nama_lengkap, u.username
            FROM notifikasi n
            LEFT JOIN users u ON u.id_user = n.id_user";
    $params = [];
    $types  = '';
    if ($keyword !== '') {
        $sql .= ' WHERE n.judul LIKE ? OR n.pesan LIKE ? OR u.nama_lengkap LIKE ? OR u.username LIKE ?';
        $like = '%' . $keyword . '%';
        $params = [$like, $like, $like, $like];
        $types  = 'ssss';
    }
    $sql .= ' ORDER BY n.id_notifikasi DESC LIMIT 300';
    $st = $conn->prepare($sql);
    if ($params) {
        $st->bind_param($types, ...$params);
    }
    $st->execute();
    $res = $st->get_result();
    while ($r = $res->fetch_assoc()) {
        $r['_baru'] = ((int) $r['is_read'] === 0);
        $rows[] = $r;
    }
    $st->close();
}

$total_belum_saya = hitung_notifikasi_belum_dibaca($conn, $admin_id);

function waktu_id(?string $v): string
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
<title>Notifikasi - PERADI Pontianak</title>
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
.admin-role { font-size: 10px; color: #777; }
.content { padding: 35px; }
.page-header { margin-bottom: 20px; }
.page-header h2 { color: #1e3a8a; font-size: 21px; font-weight: 800; }
.page-header p { color: #777; font-size: 12px; margin-top: 5px; }
.tabs { display: flex; gap: 8px; margin-bottom: 18px; }
.tab { padding: 10px 16px; border-radius: 8px; background: #fff; color: #1e3a8a; text-decoration: none; font-size: 12px; font-weight: 700; border: 1px solid #e5e7eb; display: inline-flex; align-items: center; gap: 7px; }
.tab.active { background: #1e3a8a; color: #fff; border-color: #1e3a8a; }
.tab .count { background: #ef4444; color: #fff; border-radius: 20px; font-size: 10px; padding: 1px 7px; }
.card { background: #fff; border-radius: 12px; padding: 22px; box-shadow: 0 5px 20px rgba(0,0,0,.05); margin-bottom: 18px; }
.filter { display: flex; gap: 10px; }
.form-control { flex: 1; padding: 11px 13px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 12px; }
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 11px 15px; border-radius: 8px; border: none; background: #1e3a8a; color: #fff; text-decoration: none; font-size: 11px; font-weight: 700; cursor: pointer; }
.notif-list { display: flex; flex-direction: column; gap: 10px; }
.notif-item { padding: 15px 16px; border-radius: 9px; background: #f8fafc; border-left: 4px solid #e5e7eb; position: relative; }
.notif-item.baru { border-left-color: #dea057; background: #fffbeb; }
.notif-head { display: flex; justify-content: space-between; gap: 15px; }
.notif-title { color: #1e3a8a; font-size: 13px; font-weight: 800; }
.notif-date { color: #999; font-size: 10px; white-space: nowrap; }
.notif-user { color: #777; font-size: 10px; margin-top: 4px; }
.notif-msg { color: #555; font-size: 12px; line-height: 1.6; margin-top: 8px; }
.tag { display: inline-block; margin-top: 8px; padding: 3px 8px; border-radius: 20px; font-size: 8px; font-weight: 800; text-transform: uppercase; }
.tag.baru { background: #fef3c7; color: #92400e; }
.tag.lama { background: #dcfce7; color: #166534; }
.del { position: absolute; top: 12px; right: 12px; background: none; border: none; color: #cbd5e1; cursor: pointer; font-size: 13px; }
.del:hover { color: #ef4444; }
.empty { text-align: center; padding: 40px 15px; color: #888; font-size: 12px; }
.empty i { display: block; font-size: 35px; color: #dea057; margin-bottom: 10px; }
@media(max-width:900px) { .main { margin-left: 0; } .content { padding: 20px 16px; } .admin-name, .admin-role { display: none; } }
</style>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="main">

<header class="topbar">
    <div class="topbar-title">
        <h1>Notifikasi</h1>
        <p>Pemberitahuan untuk admin & riwayat notifikasi pengguna</p>
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
        <h2><i class="fa-solid fa-bell" style="color:#dea057;"></i> Notifikasi</h2>
        <p>Notifikasi admin dibuat otomatis saat ada pendaftaran baru dan bukti pembayaran baru.</p>
    </div>

    <div class="tabs">
        <a class="tab <?= $view === 'saya' ? 'active' : '' ?>" href="notifikasi.php?view=saya">
            <i class="fa-solid fa-inbox"></i> Notifikasi Saya
            <?php if ($view !== 'saya' && $total_belum_saya > 0): ?><span class="count"><?= $total_belum_saya ?></span><?php endif; ?>
        </a>
        <a class="tab <?= $view === 'semua' ? 'active' : '' ?>" href="notifikasi.php?view=semua">
            <i class="fa-solid fa-list"></i> Semua Pengguna
        </a>
    </div>

    <?php if ($view === 'semua'): ?>
    <div class="card">
        <form method="GET" class="filter">
            <input type="hidden" name="view" value="semua">
            <input type="text" name="keyword" class="form-control" value="<?= e($keyword) ?>"
                   placeholder="Cari judul, pesan, nama user, atau username...">
            <button type="submit" class="btn"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            <a href="notifikasi.php?view=semua" class="btn" style="background:#dea057"><i class="fa-solid fa-rotate-left"></i></a>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <?php if ($rows): ?>
        <div class="notif-list">
            <?php foreach ($rows as $r): ?>
            <div class="notif-item <?= $r['_baru'] ? 'baru' : '' ?>">
                <?php if ($view === 'saya'): ?>
                    <form method="POST" onsubmit="return confirm('Hapus notifikasi ini?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id_notifikasi" value="<?= e($r['id_notifikasi']) ?>">
                        <button class="del" title="Hapus" type="submit"><i class="fa-solid fa-xmark"></i></button>
                    </form>
                <?php endif; ?>
                <div class="notif-head">
                    <div class="notif-title"><?= e($r['judul']) ?></div>
                    <div class="notif-date"><?= waktu_id($r['created_at']) ?></div>
                </div>
                <?php if ($view === 'semua'): ?>
                    <div class="notif-user"><i class="fa-solid fa-user"></i>
                        <?= e($r['nama_lengkap'] ?? $r['username'] ?? 'User') ?> · ID User: <?= e($r['id_user']) ?>
                    </div>
                <?php endif; ?>
                <div class="notif-msg"><?= nl2br(e($r['pesan'])) ?></div>
                <span class="tag <?= $r['_baru'] ? 'baru' : 'lama' ?>"><?= $r['_baru'] ? 'Belum Dibaca' : 'Sudah Dibaca' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty">
            <i class="fa-solid fa-bell-slash"></i>
            <?= $view === 'saya' ? 'Belum ada notifikasi untuk Anda.' : 'Belum ada notifikasi.' ?>
        </div>
        <?php endif; ?>
    </div>

</section>
</main>
</body>
</html>
