<?php
session_start();
require_once __DIR__ . '/functions.php';
require_user();

/*
 * Halaman pengumuman untuk user.
 * Hanya menampilkan pengumuman dengan status = 'publikasi'.
 */

$detail = null;
$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $st = $conn->prepare(
        "SELECT id_pengumuman, judul, isi, tanggal_publish
         FROM pengumuman
         WHERE id_pengumuman = ? AND status = 'publikasi'
         LIMIT 1"
    );
    $st->bind_param('i', $id);
    $st->execute();
    $detail = $st->get_result()->fetch_assoc();
    $st->close();
    if (!$detail) {
        header('Location: pengumuman.php');
        exit;
    }
}

$list = [];
if (!$detail) {
    $res = $conn->query(
        "SELECT id_pengumuman, judul, isi, tanggal_publish
         FROM pengumuman
         WHERE status = 'publikasi'
         ORDER BY tanggal_publish DESC, id_pengumuman DESC"
    );
    while ($row = $res->fetch_assoc()) {
        $list[] = $row;
    }
}

function tgl_id(?string $v): string
{
    if (!$v) {
        return '-';
    }
    $t = strtotime($v);
    return $t ? date('d M Y, H:i', $t) : e($v);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $detail ? e($detail['judul']) : 'Pengumuman' ?> - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { box-sizing: border-box; }
body { margin: 0; background: #f4f6f9; color: #333; font-family: 'Montserrat', sans-serif; }
.container { max-width: 860px; margin: 40px auto; padding: 20px; }
.back { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 20px; color: #1e3a8a; text-decoration: none; font-weight: 700; }
.title { color: #1e3a8a; font-size: 26px; font-weight: 800; margin: 0 0 6px; }
.subtitle { color: #64748b; margin-bottom: 24px; font-size: 14px; }
.item { background: #fff; border-radius: 14px; padding: 22px 24px; box-shadow: 0 8px 24px rgba(0,0,0,.06); margin-bottom: 16px; text-decoration: none; display: block; color: inherit; border-left: 5px solid #dea057; transition: transform .15s; }
a.item:hover { transform: translateY(-2px); }
.item h3 { color: #1e3a8a; margin: 0 0 6px; font-size: 17px; }
.item .date { color: #94a3b8; font-size: 12px; margin-bottom: 10px; }
.item .excerpt { color: #475569; font-size: 13px; line-height: 1.6; }
.detail-card { background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
.detail-card .date { color: #94a3b8; font-size: 13px; margin-bottom: 18px; }
.detail-card .body { color: #334155; font-size: 15px; line-height: 1.8; white-space: pre-line; }
.empty { text-align: center; padding: 60px 20px; color: #94a3b8; }
.empty i { font-size: 44px; color: #dea057; display: block; margin-bottom: 14px; }
</style>
</head>
<body>
<div class="container">

<?php if ($detail): ?>

    <a class="back" href="pengumuman.php"><i class="fa-solid fa-arrow-left"></i> Semua Pengumuman</a>

    <div class="detail-card">
        <h1 class="title"><?= e($detail['judul']) ?></h1>
        <div class="date"><i class="fa-regular fa-clock"></i> <?= tgl_id($detail['tanggal_publish']) ?></div>
        <div class="body"><?= nl2br(e($detail['isi'])) ?></div>
    </div>

<?php else: ?>

    <a class="back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>

    <h1 class="title"><i class="fa-solid fa-bullhorn" style="color:#dea057"></i> Pengumuman</h1>
    <p class="subtitle">Informasi resmi dari DPC PERADI Pontianak.</p>

    <?php if ($list): ?>
        <?php foreach ($list as $p):
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($p['isi'])));
            $excerpt = mb_strlen($plain) > 180 ? mb_substr($plain, 0, 180) . '…' : $plain;
        ?>
            <a class="item" href="pengumuman.php?id=<?= e($p['id_pengumuman']) ?>">
                <h3><?= e($p['judul']) ?></h3>
                <div class="date"><i class="fa-regular fa-clock"></i> <?= tgl_id($p['tanggal_publish']) ?></div>
                <div class="excerpt"><?= e($excerpt) ?></div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">
            <i class="fa-solid fa-bullhorn"></i>
            Belum ada pengumuman.
        </div>
    <?php endif; ?>

<?php endif; ?>

</div>
</body>
</html>
