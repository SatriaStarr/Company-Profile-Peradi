<?php
require_once __DIR__ . '/partials/admin_auth.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: data_advokat.php');
    exit;
}

$data = null;
if ($st = mysqli_prepare($conn, 'SELECT * FROM data_advokat WHERE id_advokat = ? LIMIT 1')) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $data = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
}
if (!$data) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

function v($x): string
{
    $x = trim((string) $x);
    return $x === '' ? '-' : e($x);
}
function vtgl($t): string
{
    if (empty($t) || $t === '0000-00-00') {
        return '-';
    }
    $ts = strtotime($t);
    return $ts ? date('d F Y', $ts) : e($t);
}
function file_url(?string $f): ?string
{
    $f = trim((string) $f);
    if ($f === '' || $f === 'default.jpg') {
        return null;
    }
    $f = str_replace('\\', '/', $f);
    return str_starts_with($f, 'uploads/') ? '../' . $f : '../uploads/advokat/' . $f;
}
$foto = file_url($data['foto_profil']);
$ktpa = file_url($data['file_ktpa']);
$bas  = file_url($data['file_bas']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Advokat - <?= e($data['nama_lengkap']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
body { background: #f4f6f9; color: #333; }
.main { margin-left: 250px; min-height: 100vh; }
.topbar { height: 75px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 35px; border-bottom: 1px solid #e5e7eb; }
.topbar-title h1 { color: #1e3a8a; font-size: 22px; font-weight: 800; }
.content { padding: 35px; max-width: 980px; }
.back { display: inline-flex; align-items: center; gap: 7px; color: #1e3a8a; text-decoration: none; font-weight: 700; font-size: 12px; margin-bottom: 18px; }
.card { background: #fff; border-radius: 14px; box-shadow: 0 5px 20px rgba(0,0,0,.05); overflow: hidden; margin-bottom: 20px; }
.pro-head { background: linear-gradient(135deg, #1e3a8a, #3b82f6); padding: 32px 30px; color: #fff; display: flex; align-items: center; gap: 26px; flex-wrap: wrap; }
.pro-pic { width: 110px; height: 110px; border-radius: 50%; border: 4px solid rgba(255,255,255,.3); object-fit: cover; background: #fff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 800; }
.pro-info h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.pro-info p { opacity: .92; font-size: 13px; margin-bottom: 3px; }
.pro-badge { display: inline-block; margin-top: 8px; padding: 5px 14px; background: rgba(255,255,255,.2); border-radius: 20px; font-size: 12px; font-weight: 600; }
.pro-actions { margin-left: auto; display: flex; gap: 8px; }
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 14px; border-radius: 8px; background: #dea057; color: #fff; text-decoration: none; font-size: 11px; font-weight: 700; border: none; cursor: pointer; }
.btn.plain { background: rgba(255,255,255,.18); }
.grid { padding: 28px 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 22px 30px; }
.sec { grid-column: span 2; font-size: 14px; color: #1e3a8a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px; margin-top: 8px; }
.sec:first-child { margin-top: 0; }
.item label { display: block; font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
.item span { font-weight: 600; color: #1f2937; font-size: 13px; display: block; }
.item.full { grid-column: span 2; }
.filebox { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 14px; border-radius: 8px; text-align: center; }
.filebox a { color: #2563eb; font-weight: 600; text-decoration: none; font-size: 12px; }
.filebox.off { opacity: .55; font-size: 12px; color: #64748b; }
@media(max-width:900px) { .main { margin-left: 0; } .content { padding: 20px 16px; } .grid { grid-template-columns: 1fr; } .sec, .item.full { grid-column: span 1; } .pro-actions { margin-left: 0; } }
</style>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="main">
<header class="topbar"><div class="topbar-title"><h1>Detail Advokat</h1></div></header>

<section class="content">
    <a class="back" href="data_advokat.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Data Advokat</a>

    <div class="card">
        <div class="pro-head">
            <?php if ($foto): ?>
                <img src="<?= e($foto) ?>" class="pro-pic" alt="Foto">
            <?php else: ?>
                <div class="pro-pic"><?= e(strtoupper(substr($data['nama_lengkap'], 0, 1))) ?></div>
            <?php endif; ?>
            <div class="pro-info">
                <h1><?= e($data['nama_lengkap']) ?></h1>
                <p><i class="fa-solid fa-id-badge"></i> NIA: <?= v($data['nia']) ?></p>
                <p><i class="fa-solid fa-briefcase"></i> <?= v($data['nama_kantor_hukum']) ?></p>
                <span class="pro-badge"><?= strtoupper(str_replace('_', ' ', v($data['status_keanggotaan']))) ?></span>
            </div>
            <div class="pro-actions">
                <a class="btn" href="edit_advokat.php?id=<?= e($data['id_advokat']) ?>"><i class="fa-solid fa-pen"></i> Edit</a>
            </div>
        </div>

        <div class="grid">
            <div class="sec">Identitas Pribadi</div>
            <div class="item"><label>NIA</label><span><?= v($data['nia']) ?></span></div>
            <div class="item"><label>NIK</label><span><?= v($data['nik']) ?></span></div>
            <div class="item"><label>Nama Lengkap &amp; Gelar</label><span><?= v($data['nama_lengkap']) ?></span></div>
            <div class="item"><label>Tempat, Tanggal Lahir</label><span><?= v($data['tempat_lahir']) ?>, <?= vtgl($data['tanggal_lahir']) ?></span></div>
            <div class="item"><label>Jenis Kelamin</label><span><?= $data['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($data['jenis_kelamin'] === 'P' ? 'Perempuan' : '-') ?></span></div>
            <div class="item"><label>Agama</label><span><?= v($data['agama']) ?></span></div>
            <div class="item full"><label>Alamat Domisili</label><span><?= v($data['alamat_domisili']) ?></span></div>

            <div class="sec">Kontak &amp; Kantor</div>
            <div class="item"><label>Nama Kantor Hukum</label><span><?= v($data['nama_kantor_hukum']) ?></span></div>
            <div class="item"><label>No HP / WhatsApp</label><span><?= v($data['no_hp']) ?></span></div>
            <div class="item"><label>Email</label><span><?= v($data['email']) ?></span></div>
            <div class="item full"><label>Alamat Kantor</label><span><?= v($data['alamat_kantor']) ?></span></div>

            <div class="sec">Legalitas &amp; Dokumen</div>
            <div class="item"><label>Universitas Asal</label><span><?= v($data['universitas_asal']) ?></span></div>
            <div class="item"><label>Nomor BAS</label><span><?= v($data['nomor_bas']) ?></span></div>
            <div class="item"><label>Tanggal Sumpah PT</label><span><?= vtgl($data['tanggal_sumpah_pt']) ?></span></div>
            <div class="item"><label>Masa Berlaku KTPA</label><span><?= vtgl($data['masa_berlaku_ktpa']) ?></span></div>

            <div class="item">
                <label>Kartu Advokat (KTPA)</label>
                <?php if ($ktpa): ?>
                    <div class="filebox"><a href="<?= e($ktpa) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file-arrow-down"></i> Lihat KTPA</a></div>
                <?php else: ?>
                    <div class="filebox off">Belum ada file KTPA</div>
                <?php endif; ?>
            </div>
            <div class="item">
                <label>Berita Acara Sumpah (BAS)</label>
                <?php if ($bas): ?>
                    <div class="filebox"><a href="<?= e($bas) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-file-arrow-down"></i> Lihat BAS</a></div>
                <?php else: ?>
                    <div class="filebox off">Belum ada file BAS</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
</main>
</body>
</html>
