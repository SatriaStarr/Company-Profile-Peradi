<?php
session_start();
require_once __DIR__ . '/functions.php';
require_user();

$uid = user_id();

/*
|--------------------------------------------------------------------------
| AKSI: hapus satu notifikasi (POST)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $idn = (int) ($_POST['id_notifikasi'] ?? 0);
    if ($idn > 0) {
        $st = $conn->prepare('DELETE FROM notifikasi WHERE id_notifikasi = ? AND id_user = ? LIMIT 1');
        $st->bind_param('ii', $idn, $uid);
        $st->execute();
        $st->close();
    }
    header('Location: notifikasi.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil notifikasi + snapshot mana yang belum dibaca SEBELUM ditandai
|--------------------------------------------------------------------------
*/
$st = $conn->prepare(
    'SELECT id_notifikasi, judul, pesan, is_read, created_at
     FROM notifikasi WHERE id_user = ?
     ORDER BY id_notifikasi DESC'
);
$st->bind_param('i', $uid);
$st->execute();
$res = $st->get_result();

$rows = [];
$baru = 0;
while ($r = $res->fetch_assoc()) {
    if ((int) $r['is_read'] === 0) {
        $baru++;
        $r['_baru'] = true;
    } else {
        $r['_baru'] = false;
    }
    $rows[] = $r;
}
$st->close();

/* setelah ditampilkan, tandai semua sudah dibaca */
if ($baru > 0) {
    $conn->query('UPDATE notifikasi SET is_read = 1 WHERE id_user = ' . (int) $uid . ' AND is_read = 0');
}

function waktu_id(?string $v): string
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
<title>Notifikasi - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { box-sizing: border-box; }
body { margin: 0; background: #f4f6f9; color: #333; font-family: 'Montserrat', sans-serif; }
.container { max-width: 820px; margin: 40px auto; padding: 20px; }
.back { display: inline-flex; align-items: center; gap: 7px; margin-bottom: 20px; color: #1e3a8a; text-decoration: none; font-weight: 700; }
.head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 8px; }
.title { color: #1e3a8a; font-size: 26px; font-weight: 800; margin: 0; }
.subtitle { color: #64748b; margin-bottom: 22px; font-size: 13px; }
.item { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 6px 18px rgba(0,0,0,.05); margin-bottom: 12px; border-left: 4px solid #e5e7eb; position: relative; }
.item.baru { border-left-color: #dea057; background: #fffbeb; }
.item h3 { color: #1e3a8a; margin: 0 0 4px; font-size: 15px; }
.item .date { color: #94a3b8; font-size: 11px; margin-bottom: 8px; }
.item p { color: #475569; font-size: 13px; line-height: 1.6; margin: 0; }
.tag { display: inline-block; margin-top: 8px; padding: 3px 8px; border-radius: 20px; font-size: 9px; font-weight: 800; text-transform: uppercase; }
.tag.baru { background: #fef3c7; color: #92400e; }
.tag.lama { background: #dcfce7; color: #166534; }
.del { position: absolute; top: 14px; right: 14px; background: none; border: none; color: #cbd5e1; cursor: pointer; font-size: 14px; }
.del:hover { color: #ef4444; }
.empty { text-align: center; padding: 60px 20px; color: #94a3b8; }
.empty i { font-size: 44px; color: #dea057; display: block; margin-bottom: 14px; }
.pill-count { background: #ef4444; color: #fff; border-radius: 20px; font-size: 11px; font-weight: 700; padding: 3px 9px; }
</style>
</head>
<body>
<div class="container">

    <a class="back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>

    <div class="head">
        <h1 class="title"><i class="fa-solid fa-bell" style="color:#dea057"></i> Notifikasi</h1>
        <?php if ($baru > 0): ?>
            <span class="pill-count"><?= $baru ?> baru</span>
        <?php endif; ?>
    </div>
    <p class="subtitle">Pemberitahuan otomatis: pendaftaran, pembayaran, hasil ujian, dan sertifikat.</p>

    <?php if ($rows): ?>
        <?php foreach ($rows as $r): ?>
            <div class="item <?= $r['_baru'] ? 'baru' : '' ?>">
                <form method="POST" onsubmit="return confirm('Hapus notifikasi ini?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id_notifikasi" value="<?= e($r['id_notifikasi']) ?>">
                    <button class="del" title="Hapus" type="submit"><i class="fa-solid fa-xmark"></i></button>
                </form>
                <h3><?= e($r['judul']) ?></h3>
                <div class="date"><i class="fa-regular fa-clock"></i> <?= waktu_id($r['created_at']) ?></div>
                <p><?= nl2br(e($r['pesan'])) ?></p>
                <span class="tag <?= $r['_baru'] ? 'baru' : 'lama' ?>"><?= $r['_baru'] ? 'Baru' : 'Sudah dibaca' ?></span>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">
            <i class="fa-solid fa-bell-slash"></i>
            Belum ada notifikasi.
        </div>
    <?php endif; ?>

</div>
</body>
</html>
