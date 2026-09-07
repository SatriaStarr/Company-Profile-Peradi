<?php
session_start();
require_once __DIR__ . '/functions.php';
require_user();

$uid = user_id();
$id  = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

/* ---------- pendaftaran + pembayaran + hasil + sertifikat (kolom nyata) ---------- */
$s = $conn->prepare(
    "SELECT
        p.id_pendaftaran, p.jenis_pendaftaran, p.status, p.tanggal_daftar, p.catatan,
        pb.jumlah, pb.status AS status_bayar, pb.bukti_pembayaran, pb.tanggal_bayar,
        h.nilai, h.status_lulus, h.catatan AS catatan_hasil,
        s.nomor_sertifikat, s.file_sertifikat, s.tanggal_terbit
     FROM pendaftaran p
     LEFT JOIN pembayaran pb ON pb.id_pendaftaran = p.id_pendaftaran
     LEFT JOIN hasil h       ON h.id_pendaftaran = p.id_pendaftaran
     LEFT JOIN sertifikat s  ON s.id_pendaftaran = p.id_pendaftaran
     WHERE p.id_pendaftaran = ? AND p.id_user = ?
     LIMIT 1"
);
$s->bind_param('ii', $id, $uid);
$s->execute();
$app = $s->get_result()->fetch_assoc();
$s->close();

if (!$app) {
    header('Location: dashboard.php');
    exit;
}

/* ---------- data formulir + berkas (pendaftaran_pkpa / pendaftaran_upa) ---------- */
$jenis   = strtoupper($app['jenis_pendaftaran']);
$tbl     = $jenis === 'UPA' ? 'pendaftaran_upa' : 'pendaftaran_pkpa';
$peserta = null;
if ($chk = $conn->query("SHOW TABLES LIKE '$tbl'")) {
    if ($chk->num_rows > 0) {
        $ps = $conn->prepare("SELECT * FROM `$tbl` WHERE id_pendaftaran = ? ORDER BY id_pendaftaran DESC LIMIT 1");
        $ps->bind_param('i', $id);
        $ps->execute();
        $peserta = $ps->get_result()->fetch_assoc();
        $ps->close();
    }
}

$berkas_map = $jenis === 'UPA'
    ? [
        'foto'            => 'Pas Foto',
        'foto_ktp'        => 'Foto KTP',
        'foto_ijazah'     => 'Foto Ijazah',
        'sertifikat_pkpa' => 'Sertifikat PKPA',
        'tanda_tangan'    => 'Tanda Tangan',
      ]
    : [
        'foto_calon_advokat' => 'Pas Foto',
        'foto_ijazah'        => 'Foto Ijazah',
        'foto_ktp'           => 'Foto KTP',
        'tanda_tangan'       => 'Tanda Tangan',
      ];

$berkas = [];
if ($peserta) {
    foreach ($berkas_map as $col => $label) {
        if (!array_key_exists($col, $peserta)) {
            continue;
        }
        $val = trim((string) ($peserta[$col] ?? ''));
        if ($val !== '') {
            $berkas[] = ['label' => $label, 'file' => $val];
        }
    }
}

function file_href(?string $stored): string
{
    $x = ltrim(str_replace('\\', '/', (string) $stored), '/');
    if ($x === '') {
        return '';
    }
    if (str_starts_with($x, 'uploads/')) {
        return '../' . $x;
    }
    if (str_contains($x, '/')) {
        return '../uploads/' . $x;
    }
    return '../uploads/' . $x;
}

$status_bayar = $app['status_bayar'] ?? 'belum_bayar';
$bukti_href   = file_href($app['bukti_pembayaran'] ?? '');
$sert_href    = $app['file_sertifikat'] ? '../uploads/sertifikat/' . rawurlencode($app['file_sertifikat']) : '';
$pesan        = $_GET['pesan'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pendaftaran <?= e($jenis) ?> - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { box-sizing: border-box; }
body { font-family: 'Montserrat', sans-serif; background: #f4f6f9; margin: 0; color: #333; }
.wrap { max-width: 950px; margin: 35px auto; padding: 20px; }
.back { display: inline-flex; align-items: center; gap: 7px; color: #1e3a8a; text-decoration: none; font-weight: 700; font-size: 13px; margin-bottom: 18px; }
.card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,.06); margin-bottom: 20px; }
h1 { color: #1e3a8a; font-size: 24px; margin: 0 0 16px; }
h2 { color: #1e3a8a; font-size: 17px; margin: 0 0 14px; }
.grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
.box { background: #f8fafc; padding: 14px; border-radius: 10px; }
.box b { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
.box span { font-weight: 600; font-size: 13px; }
.badge { display: inline-block; padding: 6px 11px; border-radius: 30px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
.badge.wait { background: #fff7ed; color: #c2410c; }
.badge.ok { background: #dcfce7; color: #166534; }
.badge.bad { background: #fee2e2; color: #b91c1c; }
.btn { display: inline-flex; align-items: center; gap: 7px; background: #1e3a8a; color: #fff; padding: 11px 16px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px; }
.btn.gold { background: #dea057; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 11px; border-bottom: 1px solid #eee; text-align: left; font-size: 13px; }
.alert { padding: 13px 16px; border-radius: 10px; font-weight: 600; font-size: 13px; margin-bottom: 16px; background: #dcfce7; color: #166534; }
.note { color: #64748b; font-size: 13px; }
</style>
</head>
<body>
<div class="wrap">

    <a class="back" href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>

    <?php if ($pesan === 'berhasil'): ?>
        <div class="alert"><i class="fa-solid fa-circle-check"></i> Bukti pembayaran berhasil dikirim.</div>
    <?php endif; ?>

    <div class="card">
        <h1><?= e($jenis) ?> &middot; #<?= e($app['id_pendaftaran']) ?></h1>
        <div class="grid">
            <div class="box"><b>Tanggal Daftar</b><span><?= e(date('d-m-Y', strtotime($app['tanggal_daftar']))) ?></span></div>
            <div class="box"><b>Status Pendaftaran</b><span class="badge <?= e(badge_class($app['status'])) ?>"><?= e(app_status_label($app['status'])) ?></span></div>
            <div class="box"><b>Status Pembayaran</b><span class="badge <?= e(badge_class($status_bayar)) ?>"><?= e(ucwords(str_replace('_', ' ', $status_bayar))) ?></span></div>
            <div class="box"><b>Hasil</b><span><?= e($app['status_lulus'] ?? 'Belum Diumumkan') ?><?= $app['nilai'] !== null ? ' (' . e($app['nilai']) . ')' : '' ?></span></div>
        </div>
        <?php if (trim((string) $app['catatan']) !== ''): ?>
            <p class="note" style="margin-top:14px"><b>Catatan admin:</b> <?= nl2br(e($app['catatan'])) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($peserta): ?>
    <div class="card">
        <h2>1. Data Peserta</h2>
        <div class="grid">
            <?php
            $show = [
                'nama_lengkap' => 'Nama Lengkap', 'nik' => 'NIK',
                'tempat_lahir' => 'Tempat Lahir', 'tanggal_lahir' => 'Tanggal Lahir',
                'pekerjaan' => 'Pekerjaan', 'no_hp' => 'No. HP', 'alamat' => 'Alamat',
            ];
            foreach ($show as $k => $lbl):
                if (!array_key_exists($k, $peserta) || trim((string) $peserta[$k]) === '') {
                    continue;
                } ?>
                <div class="box"><b><?= e($lbl) ?></b><span><?= e($peserta[$k]) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2><?= $peserta ? '2.' : '1.' ?> Berkas Persyaratan</h2>
        <?php if ($berkas): ?>
        <table>
            <tr><th>Jenis</th><th>File</th></tr>
            <?php foreach ($berkas as $b): ?>
                <tr>
                    <td><?= e($b['label']) ?></td>
                    <td><a href="<?= e(file_href($b['file'])) ?>" target="_blank" rel="noopener">Lihat</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
        <p class="note">Berkas dikirim saat mengisi formulir pendaftaran. Belum ada berkas untuk pendaftaran ini.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2><?= $peserta ? '3.' : '2.' ?> Pembayaran</h2>
        <p class="note">Total tagihan: <b>Rp <?= number_format((float) ($app['jumlah'] ?? 0), 0, ',', '.') ?></b></p>
        <?php if ($status_bayar === 'lunas'): ?>
            <p><span class="badge ok">Lunas</span> Pembayaran sudah diverifikasi admin.</p>
        <?php elseif ($status_bayar === 'menunggu_verifikasi'): ?>
            <p><span class="badge wait">Menunggu Verifikasi</span> Bukti pembayaran sedang diperiksa admin.</p>
            <?php if ($bukti_href): ?><p><a class="btn" href="<?= e($bukti_href) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-image"></i> Lihat bukti terkirim</a></p><?php endif; ?>
        <?php elseif ($status_bayar === 'ditolak'): ?>
            <p><span class="badge bad">Ditolak</span> Bukti pembayaran ditolak. Silakan unggah ulang.</p>
            <a class="btn gold" href="pembayaran.php?id=<?= $id ?>"><i class="fa-solid fa-upload"></i> Unggah Ulang Bukti</a>
        <?php else: ?>
            <a class="btn gold" href="pembayaran.php?id=<?= $id ?>"><i class="fa-solid fa-money-bill-transfer"></i> Lakukan Pembayaran</a>
        <?php endif; ?>
    </div>

    <?php if (($app['status_lulus'] ?? 'Belum Diumumkan') !== 'Belum Diumumkan'): ?>
    <div class="card">
        <h2><?= $peserta ? '4.' : '3.' ?> Hasil Ujian &amp; Sertifikat</h2>
        <p>Status: <b><?= e($app['status_lulus']) ?></b><?= $app['nilai'] !== null ? ' &mdash; Nilai: <b>' . e($app['nilai']) . '</b>' : '' ?></p>
        <?php if (trim((string) ($app['catatan_hasil'] ?? '')) !== ''): ?><p class="note"><?= nl2br(e($app['catatan_hasil'])) ?></p><?php endif; ?>
        <?php if ($sert_href): ?>
            <p>
                <a class="btn" href="<?= e($sert_href) ?>" target="_blank" rel="noopener" download>
                    <i class="fa-solid fa-file-arrow-down"></i> Unduh Sertifikat
                </a>
            </p>
            <?php if ($app['nomor_sertifikat']): ?><p class="note">Nomor: <?= e($app['nomor_sertifikat']) ?><?= $app['tanggal_terbit'] ? ' &middot; Terbit: ' . e(date('d-m-Y', strtotime($app['tanggal_terbit']))) : '' ?></p><?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
