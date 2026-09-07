<?php
/*
|--------------------------------------------------------------------------
| LAPORAN & REKAPITULASI (ADMIN)
|--------------------------------------------------------------------------
| Rekap pendaftaran, pembayaran, dan keanggotaan advokat berdasarkan
| rentang tanggal. Mendukung cetak (print) dan unduh CSV.
*/

require_once __DIR__ . '/partials/admin_auth.php';

/* ---------- rentang tanggal ---------- */
$hari_ini = date('Y-m-d');
$default_dari = date('Y-m-01', strtotime('-11 months')); // ~1 tahun ke belakang

$dari = $_GET['dari'] ?? $default_dari;
$sampai = $_GET['sampai'] ?? $hari_ini;

/* validasi format tanggal, fallback bila tidak valid */
$rx = '/^\d{4}-\d{2}-\d{2}$/';
if (!preg_match($rx, $dari)) {
    $dari = $default_dari;
}
if (!preg_match($rx, $sampai)) {
    $sampai = $hari_ini;
}
if ($dari > $sampai) {
    [$dari, $sampai] = [$sampai, $dari];
}

$dari_dt = $dari . ' 00:00:00';
$sampai_dt = $sampai . ' 23:59:59';

/*
|--------------------------------------------------------------------------
| AMBIL DATA REKAP
|--------------------------------------------------------------------------
*/

/* ---- pendaftaran per status ---- */
$rekap_status = [
    'menunggu' => 0, 'menunggu_verifikasi' => 0, 'berkas_diperiksa' => 0,
    'menunggu_pembayaran' => 0, 'pembayaran_diperiksa' => 0, 'terdaftar' => 0,
    'selesai' => 0, 'ditolak' => 0,
];
$stmt = mysqli_prepare(
    $conn,
    "SELECT status, COUNT(*) c FROM pendaftaran
     WHERE tanggal_daftar BETWEEN ? AND ? GROUP BY status"
);
mysqli_stmt_bind_param($stmt, 'ss', $dari_dt, $sampai_dt);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($r)) {
    if (isset($rekap_status[$row['status']])) {
        $rekap_status[$row['status']] += (int) $row['c'];
    }
}
mysqli_stmt_close($stmt);
/* gabungkan alias legacy 'menunggu' ke 'menunggu_verifikasi' */
$rekap_status['menunggu_verifikasi'] += $rekap_status['menunggu'];
unset($rekap_status['menunggu']);
$total_pendaftaran = array_sum($rekap_status);

/* ---- pendaftaran per jenis ---- */
$rekap_jenis = ['PKPA' => 0, 'UPA' => 0];
$stmt = mysqli_prepare(
    $conn,
    "SELECT jenis_pendaftaran, COUNT(*) c FROM pendaftaran
     WHERE tanggal_daftar BETWEEN ? AND ? GROUP BY jenis_pendaftaran"
);
mysqli_stmt_bind_param($stmt, 'ss', $dari_dt, $sampai_dt);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($r)) {
    $rekap_jenis[$row['jenis_pendaftaran']] = (int) $row['c'];
}
mysqli_stmt_close($stmt);

/* ---- pendaftaran per bulan ---- */
$rekap_bulan = [];
$stmt = mysqli_prepare(
    $conn,
    "SELECT DATE_FORMAT(tanggal_daftar, '%Y-%m') bln,
            SUM(jenis_pendaftaran = 'PKPA') pkpa,
            SUM(jenis_pendaftaran = 'UPA') upa,
            COUNT(*) total
     FROM pendaftaran
     WHERE tanggal_daftar BETWEEN ? AND ?
     GROUP BY bln ORDER BY bln"
);
mysqli_stmt_bind_param($stmt, 'ss', $dari_dt, $sampai_dt);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($r)) {
    $rekap_bulan[] = $row;
}
mysqli_stmt_close($stmt);

/* ---- pembayaran ---- */
$rekap_bayar = [];
$stmt = mysqli_prepare(
    $conn,
    "SELECT pb.status, COUNT(*) c, COALESCE(SUM(pb.jumlah),0) total
     FROM pembayaran pb
     JOIN pendaftaran p ON p.id_pendaftaran = pb.id_pendaftaran
     WHERE p.tanggal_daftar BETWEEN ? AND ?
     GROUP BY pb.status"
);
mysqli_stmt_bind_param($stmt, 'ss', $dari_dt, $sampai_dt);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
$total_lunas_rp = 0;
$total_transaksi = 0;
while ($row = mysqli_fetch_assoc($r)) {
    $rekap_bayar[$row['status']] = ['c' => (int) $row['c'], 'total' => (float) $row['total']];
    $total_transaksi += (int) $row['c'];
    if ($row['status'] === 'lunas') {
        $total_lunas_rp = (float) $row['total'];
    }
}
mysqli_stmt_close($stmt);

/* ---- hasil ujian ---- */
$rekap_hasil = ['Lulus' => 0, 'Tidak Lulus' => 0, 'Belum Diumumkan' => 0];
$stmt = mysqli_prepare(
    $conn,
    "SELECT h.status_lulus, COUNT(*) c
     FROM hasil h JOIN pendaftaran p ON p.id_pendaftaran = h.id_pendaftaran
     WHERE p.tanggal_daftar BETWEEN ? AND ?
     GROUP BY h.status_lulus"
);
mysqli_stmt_bind_param($stmt, 'ss', $dari_dt, $sampai_dt);
mysqli_stmt_execute($stmt);
$r = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($r)) {
    $rekap_hasil[$row['status_lulus']] = (int) $row['c'];
}
mysqli_stmt_close($stmt);
$total_sertifikat = 0;
if ($q = mysqli_query(
    $conn,
    "SELECT COUNT(*) c FROM sertifikat s
     JOIN pendaftaran p ON p.id_pendaftaran = s.id_pendaftaran
     WHERE p.tanggal_daftar BETWEEN '" . mysqli_real_escape_string($conn, $dari_dt) . "'
       AND '" . mysqli_real_escape_string($conn, $sampai_dt) . "'"
)) {
    $total_sertifikat = (int) (mysqli_fetch_assoc($q)['c'] ?? 0);
}

/* ---- data advokat (tidak difilter tanggal — data master keanggotaan saat ini) ---- */
$rekap_advokat = ['aktif' => 0, 'cuti' => 0, 'non_aktif' => 0, 'meninggal' => 0];
if ($q = mysqli_query($conn, "SELECT status_keanggotaan, COUNT(*) c FROM data_advokat GROUP BY status_keanggotaan")) {
    while ($row = mysqli_fetch_assoc($q)) {
        if (isset($rekap_advokat[$row['status_keanggotaan']])) {
            $rekap_advokat[$row['status_keanggotaan']] = (int) $row['c'];
        }
    }
}
$total_advokat = array_sum($rekap_advokat);

/* label status */
$status_label = [
    'menunggu' => 'Menunggu Verifikasi',
    'menunggu_verifikasi' => 'Menunggu Verifikasi',
    'berkas_diperiksa' => 'Berkas Diperiksa',
    'menunggu_pembayaran' => 'Menunggu Pembayaran',
    'pembayaran_diperiksa' => 'Pembayaran Diperiksa',
    'terdaftar' => 'Terdaftar',
    'selesai' => 'Selesai',
    'ditolak' => 'Ditolak',
];
$bayar_label = [
    'belum_bayar' => 'Belum Bayar',
    'menunggu_verifikasi' => 'Menunggu Verifikasi',
    'pending' => 'Pending (Gateway)',
    'lunas' => 'Lunas',
    'ditolak' => 'Ditolak',
    'kadaluarsa' => 'Kadaluarsa',
    'dibatalkan' => 'Dibatalkan',
];

function rp($n): string
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}
function bulan_id(string $ym): string
{
    $b = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
    [$y, $m] = explode('-', $ym);
    return ($b[$m] ?? $m) . ' ' . $y;
}

/*
|--------------------------------------------------------------------------
| EKSPOR CSV
|--------------------------------------------------------------------------
*/
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="laporan_' . $dari . '_sd_' . $sampai . '.csv"');
    $out = fopen('php://output', 'w');
    fprintf($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8

    fputcsv($out, ['LAPORAN REKAPITULASI - DPC PERADI PONTIANAK']);
    fputcsv($out, ['Periode', $dari . ' s/d ' . $sampai]);
    fputcsv($out, ['Dicetak', date('d-m-Y H:i')]);
    fputcsv($out, []);

    fputcsv($out, ['A. PENDAFTARAN PER STATUS']);
    fputcsv($out, ['Status', 'Jumlah']);
    foreach ($rekap_status as $k => $v) {
        fputcsv($out, [$status_label[$k] ?? $k, $v]);
    }
    fputcsv($out, ['TOTAL', $total_pendaftaran]);
    fputcsv($out, []);

    fputcsv($out, ['B. PENDAFTARAN PER JENIS']);
    fputcsv($out, ['Jenis', 'Jumlah']);
    foreach ($rekap_jenis as $k => $v) {
        fputcsv($out, [$k, $v]);
    }
    fputcsv($out, []);

    fputcsv($out, ['C. PENDAFTARAN PER BULAN']);
    fputcsv($out, ['Bulan', 'PKPA', 'UPA', 'Total']);
    foreach ($rekap_bulan as $b) {
        fputcsv($out, [bulan_id($b['bln']), (int) $b['pkpa'], (int) $b['upa'], (int) $b['total']]);
    }
    fputcsv($out, []);

    fputcsv($out, ['D. PEMBAYARAN']);
    fputcsv($out, ['Status', 'Jumlah Transaksi', 'Nilai (Rp)']);
    foreach ($bayar_label as $k => $lbl) {
        $row = $rekap_bayar[$k] ?? ['c' => 0, 'total' => 0];
        fputcsv($out, [$lbl, $row['c'], $row['total']]);
    }
    fputcsv($out, ['TOTAL PEMASUKAN TERVERIFIKASI (LUNAS)', '', $total_lunas_rp]);
    fputcsv($out, []);

    fputcsv($out, ['E. HASIL UJIAN & SERTIFIKAT']);
    foreach ($rekap_hasil as $k => $v) {
        fputcsv($out, [$k, $v]);
    }
    fputcsv($out, ['Sertifikat terbit', $total_sertifikat]);
    fputcsv($out, []);

    fputcsv($out, ['F. KEANGGOTAAN ADVOKAT (data master saat ini)']);
    fputcsv($out, ['Status', 'Jumlah']);
    foreach ($rekap_advokat as $k => $v) {
        fputcsv($out, [ucwords(str_replace('_', ' ', $k)), $v]);
    }
    fputcsv($out, ['TOTAL', $total_advokat]);

    fclose($out);
    exit;
}

$export_qs = http_build_query(['dari' => $dari, 'sampai' => $sampai, 'export' => 'csv']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan &amp; Rekapitulasi - Admin PERADI</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
body { background: #f4f6f9; color: #333; }
.main { margin-left: 250px; min-height: 100vh; }
.topbar { height: 75px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 35px; border-bottom: 1px solid #e5e7eb; }
.topbar h1 { color: #1e3a8a; font-size: 21px; font-weight: 800; }
.topbar p { color: #777; font-size: 12px; margin-top: 3px; }
.content { padding: 32px 35px; }

.toolbar { background: #fff; border-radius: 12px; padding: 20px 22px; box-shadow: 0 5px 20px rgba(0,0,0,.05); margin-bottom: 24px; display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; }
.toolbar .fld { display: flex; flex-direction: column; gap: 5px; }
.toolbar label { font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; }
.toolbar input[type=date] { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; }
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 16px; border-radius: 8px; background: #1e3a8a; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; border: none; cursor: pointer; }
.btn:hover { background: #152c69; }
.btn.gold { background: #dea057; }
.btn.gold:hover { background: #c98d46; }
.btn.ghost { background: #eef2ff; color: #1e3a8a; }

.cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 26px; }
.card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 5px 20px rgba(0,0,0,.05); border-left: 4px solid #dea057; }
.card h3 { font-size: 12px; color: #777; margin-bottom: 6px; font-weight: 600; }
.card strong { font-size: 22px; color: #1e3a8a; }
.card small { display: block; margin-top: 4px; color: #94a3b8; font-size: 11px; }

.panel { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 5px 20px rgba(0,0,0,.05); margin-bottom: 22px; }
.panel h2 { color: #1e3a8a; font-size: 15px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 11px 12px; border-bottom: 1px solid #eee; font-size: 12.5px; text-align: left; }
th { background: #f8fafc; color: #666; text-transform: uppercase; font-size: 10.5px; letter-spacing: .5px; }
td.num, th.num { text-align: right; }
tr.total td { font-weight: 800; color: #1e3a8a; border-top: 2px solid #1e3a8a; background: #f8fafc; }

.report-head { display: none; }

@media (max-width: 1100px) { .cards { grid-template-columns: repeat(2, 1fr); } }

@media print {
    .sidebar, .sidebar-toggle, .sidebar-backdrop, .topbar, .toolbar, .no-print { display: none !important; }
    .main { margin-left: 0 !important; }
    .content { padding: 0; }
    .report-head { display: block; text-align: center; margin-bottom: 18px; border-bottom: 2px solid #1e3a8a; padding-bottom: 12px; }
    .report-head h1 { font-size: 16px; color: #1e3a8a; }
    .report-head p { font-size: 12px; color: #333; }
    .panel, .card { box-shadow: none; border: 1px solid #ddd; break-inside: avoid; }
    body { background: #fff; }
}
</style>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="main">

    <header class="topbar">
        <div>
            <h1>Laporan &amp; Rekapitulasi</h1>
            <p>Ringkasan data pendaftaran, pembayaran, dan keanggotaan advokat</p>
        </div>
        <div class="admin-info" style="display:flex;align-items:center;gap:10px;">
            <div style="width:40px;height:40px;border-radius:50%;background:#dea057;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;">
                <?= e(strtoupper(substr($admin_nama, 0, 1))) ?>
            </div>
        </div>
    </header>

    <section class="content">

        <form class="toolbar no-print" method="get" action="laporan.php">
            <div class="fld">
                <label for="dari">Dari Tanggal</label>
                <input type="date" id="dari" name="dari" value="<?= e($dari) ?>" max="<?= e($hari_ini) ?>">
            </div>
            <div class="fld">
                <label for="sampai">Sampai Tanggal</label>
                <input type="date" id="sampai" name="sampai" value="<?= e($sampai) ?>" max="<?= e($hari_ini) ?>">
            </div>
            <button type="submit" class="btn"><i class="fa-solid fa-filter"></i> Terapkan</button>
            <a class="btn gold" href="laporan.php?<?= e($export_qs) ?>"><i class="fa-solid fa-file-csv"></i> Unduh CSV</a>
            <button type="button" class="btn ghost" onclick="window.print()"><i class="fa-solid fa-print"></i> Cetak</button>
        </form>

        <div class="report-head">
            <h1>LAPORAN REKAPITULASI &mdash; DPC PERADI PONTIANAK</h1>
            <p>Sistem Informasi Manajemen Sertifikasi Advokat</p>
            <p>Periode: <?= e(date('d-m-Y', strtotime($dari))) ?> s/d <?= e(date('d-m-Y', strtotime($sampai))) ?>
                &nbsp;|&nbsp; Dicetak: <?= e(date('d-m-Y H:i')) ?></p>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Total Pendaftaran</h3>
                <strong><?= $total_pendaftaran ?></strong>
                <small>PKPA <?= $rekap_jenis['PKPA'] ?> &middot; UPA <?= $rekap_jenis['UPA'] ?></small>
            </div>
            <div class="card">
                <h3>Selesai / Terdaftar</h3>
                <strong><?= $rekap_status['terdaftar'] + $rekap_status['selesai'] ?></strong>
                <small>Ditolak <?= $rekap_status['ditolak'] ?></small>
            </div>
            <div class="card">
                <h3>Pemasukan Terverifikasi</h3>
                <strong style="font-size:18px;"><?= rp($total_lunas_rp) ?></strong>
                <small><?= ($rekap_bayar['lunas']['c'] ?? 0) ?> transaksi lunas</small>
            </div>
            <div class="card">
                <h3>Sertifikat Terbit</h3>
                <strong><?= $total_sertifikat ?></strong>
                <small>Lulus <?= $rekap_hasil['Lulus'] ?> &middot; Tidak Lulus <?= $rekap_hasil['Tidak Lulus'] ?></small>
            </div>
        </div>

        <div class="panel">
            <h2><i class="fa-solid fa-list-check" style="color:#dea057;"></i> A. Pendaftaran per Status</h2>
            <table>
                <thead><tr><th>Status</th><th class="num">Jumlah</th><th class="num">Persentase</th></tr></thead>
                <tbody>
                <?php foreach ($rekap_status as $k => $v): ?>
                    <tr>
                        <td><?= e($status_label[$k] ?? $k) ?></td>
                        <td class="num"><?= $v ?></td>
                        <td class="num"><?= $total_pendaftaran ? number_format($v / $total_pendaftaran * 100, 1) : '0,0' ?>%</td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="total"><td>TOTAL</td><td class="num"><?= $total_pendaftaran ?></td><td class="num">100%</td></tr>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2><i class="fa-solid fa-calendar-days" style="color:#dea057;"></i> B. Pendaftaran per Bulan</h2>
            <?php if ($rekap_bulan): ?>
            <table>
                <thead><tr><th>Bulan</th><th class="num">PKPA</th><th class="num">UPA</th><th class="num">Total</th></tr></thead>
                <tbody>
                <?php foreach ($rekap_bulan as $b): ?>
                    <tr>
                        <td><?= e(bulan_id($b['bln'])) ?></td>
                        <td class="num"><?= (int) $b['pkpa'] ?></td>
                        <td class="num"><?= (int) $b['upa'] ?></td>
                        <td class="num"><?= (int) $b['total'] ?></td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="total">
                        <td>TOTAL</td>
                        <td class="num"><?= $rekap_jenis['PKPA'] ?></td>
                        <td class="num"><?= $rekap_jenis['UPA'] ?></td>
                        <td class="num"><?= $total_pendaftaran ?></td>
                    </tr>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color:#94a3b8;font-size:13px;">Tidak ada pendaftaran pada rentang tanggal ini.</p>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h2><i class="fa-solid fa-money-bill-wave" style="color:#dea057;"></i> C. Rekap Pembayaran</h2>
            <table>
                <thead><tr><th>Status</th><th class="num">Transaksi</th><th class="num">Nilai</th></tr></thead>
                <tbody>
                <?php foreach ($bayar_label as $k => $lbl):
                    $row = $rekap_bayar[$k] ?? ['c' => 0, 'total' => 0]; ?>
                    <tr>
                        <td><?= e($lbl) ?></td>
                        <td class="num"><?= $row['c'] ?></td>
                        <td class="num"><?= rp($row['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="total">
                        <td>PEMASUKAN TERVERIFIKASI (LUNAS)</td>
                        <td class="num"><?= $rekap_bayar['lunas']['c'] ?? 0 ?></td>
                        <td class="num"><?= rp($total_lunas_rp) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2><i class="fa-solid fa-award" style="color:#dea057;"></i> D. Hasil Ujian &amp; Sertifikat</h2>
            <table>
                <thead><tr><th>Keterangan</th><th class="num">Jumlah</th></tr></thead>
                <tbody>
                    <tr><td>Lulus</td><td class="num"><?= $rekap_hasil['Lulus'] ?></td></tr>
                    <tr><td>Tidak Lulus</td><td class="num"><?= $rekap_hasil['Tidak Lulus'] ?></td></tr>
                    <tr><td>Belum Diumumkan</td><td class="num"><?= $rekap_hasil['Belum Diumumkan'] ?></td></tr>
                    <tr class="total"><td>Sertifikat Terbit</td><td class="num"><?= $total_sertifikat ?></td></tr>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2><i class="fa-solid fa-address-card" style="color:#dea057;"></i> E. Keanggotaan Advokat <span style="font-weight:500;color:#94a3b8;font-size:11px;">(data master saat ini)</span></h2>
            <table>
                <thead><tr><th>Status Keanggotaan</th><th class="num">Jumlah</th><th class="num">Persentase</th></tr></thead>
                <tbody>
                <?php foreach ($rekap_advokat as $k => $v): ?>
                    <tr>
                        <td><?= e(ucwords(str_replace('_', ' ', $k))) ?></td>
                        <td class="num"><?= $v ?></td>
                        <td class="num"><?= $total_advokat ? number_format($v / $total_advokat * 100, 1) : '0,0' ?>%</td>
                    </tr>
                <?php endforeach; ?>
                    <tr class="total"><td>TOTAL</td><td class="num"><?= $total_advokat ?></td><td class="num">100%</td></tr>
                </tbody>
            </table>
        </div>

    </section>

</main>

</body>
</html>
