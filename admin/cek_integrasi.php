<?php
/*
|--------------------------------------------------------------------------
| Diagnostik integrasi (Midtrans & Google) — admin only
|--------------------------------------------------------------------------
| Buka: /WEB-DPC-PERADI-PONTIANAK/admin/cek_integrasi.php
| Cek apakah kredensial sudah terisi & bisa terhubung.
*/
require_once __DIR__ . '/partials/admin_auth.php';
require_once __DIR__ . '/../config/midtrans.php';
require_once __DIR__ . '/../user/google_config.php';

function badge(bool $ok, string $okText = 'OK', string $noText = 'BELUM'): string
{
    return $ok
        ? '<span style="background:#dcfce7;color:#166534;padding:3px 10px;border-radius:20px;font-weight:700;font-size:12px;">' . $okText . '</span>'
        : '<span style="background:#fee2e2;color:#b91c1c;padding:3px 10px;border-radius:20px;font-weight:700;font-size:12px;">' . $noText . '</span>';
}

/* ---------- Midtrans ---------- */
$m_aktif = midtrans_aktif();
$m_mode  = midtrans_is_production() ? 'PRODUCTION' : 'Sandbox';
$m_conn  = null;
$m_msg   = '';
if ($m_aktif) {
    try {
        // panggilan status ke order_id palsu -> 404 tapi membuktikan auth & konektivitas
        [$code, $json] = midtrans_request('GET', midtrans_base_api() . '/CEK-KONEKSI-' . time() . '/status');
        if ($code === 401) {
            $m_conn = false;
            $m_msg  = 'HTTP 401 — Server Key salah / tidak valid.';
        } else {
            $m_conn = true;
            $m_msg  = 'Terhubung ke Midtrans (HTTP ' . $code . ' untuk order palsu = wajar).';
        }
    } catch (Throwable $e) {
        $m_conn = false;
        $m_msg  = $e->getMessage();
    }
}
$webhook_url = (function () {
    $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $s . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/WEB-DPC-PERADI-PONTIANAK/midtrans_notify.php';
})();

/* ---------- Google ---------- */
$g_aktif = function_exists('google_aktif') && google_aktif();
$g_redirect = function_exists('google_redirect_uri') ? google_redirect_uri() : '(google_config.php tidak termuat)';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cek Integrasi - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
body { background: #f4f6f9; color: #333; }
.main { margin-left: 250px; }
.wrap { padding: 35px; max-width: 900px; }
h1 { color: #1e3a8a; font-size: 22px; margin-bottom: 6px; }
.sub { color: #777; font-size: 13px; margin-bottom: 24px; }
.card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 5px 20px rgba(0,0,0,.05); margin-bottom: 18px; }
.card h2 { color: #1e3a8a; font-size: 16px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.row { display: flex; justify-content: space-between; gap: 15px; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.row:last-child { border-bottom: none; }
.row .k { color: #64748b; }
.row .v { font-weight: 600; text-align: right; word-break: break-all; }
code { background: #f1f5f9; padding: 2px 6px; border-radius: 5px; font-size: 12px; }
.hint { font-size: 12px; color: #94a3b8; margin-top: 12px; line-height: 1.6; }
@media(max-width:900px){ .main { margin-left: 0; } .wrap { padding: 20px 16px; } }
</style>
</head>
<body>
<?php include __DIR__ . '/partials/sidebar.php'; ?>
<main class="main">
<div class="wrap">
    <h1>Cek Integrasi</h1>
    <p class="sub">Diagnostik konfigurasi Payment Gateway (Midtrans) &amp; Login Google.</p>

    <div class="card">
        <h2><i class="fa-solid fa-credit-card" style="color:#dea057"></i> Midtrans</h2>
        <div class="row"><span class="k">Kredensial terisi</span><span class="v"><?= badge($m_aktif) ?></span></div>
        <div class="row"><span class="k">Mode</span><span class="v"><?= e($m_mode) ?> <?= badge(!midtrans_is_production(), 'aman', 'HATI-HATI') ?></span></div>
        <div class="row"><span class="k">Client Key</span><span class="v"><?= $m_aktif ? e(substr(midtrans_client_key(), 0, 18)) . '…' : '-' ?></span></div>
        <?php if ($m_aktif): ?>
        <div class="row"><span class="k">Koneksi ke API Midtrans</span><span class="v"><?= badge((bool) $m_conn) ?></span></div>
        <div class="row"><span class="k">Keterangan</span><span class="v"><?= e($m_msg) ?></span></div>
        <?php endif; ?>
        <div class="row"><span class="k">URL Webhook (isi di dashboard Midtrans)</span><span class="v"><code><?= e($webhook_url) ?></code></span></div>
        <p class="hint">
            Isi <code>config/midtrans_keys.local.php</code>. Panduan lengkap: <code>SETUP_MIDTRANS.md</code>.<br>
            Untuk webhook di localhost, pakai ngrok atau tombol "Cek status pembayaran" di halaman hasil.
        </p>
    </div>

    <div class="card">
        <h2><i class="fa-brands fa-google" style="color:#ea4335"></i> Login Google</h2>
        <div class="row"><span class="k">Kredensial terisi</span><span class="v"><?= badge($g_aktif) ?></span></div>
        <div class="row"><span class="k">Redirect URI (daftarkan PERSIS ini di Google Console)</span><span class="v"><code><?= e($g_redirect) ?></code></span></div>
        <p class="hint">
            Isi <code>config/google_keys.local.php</code>. Panduan lengkap: <code>SETUP_GOOGLE.md</code>.<br>
            Redirect URI di Google Cloud Console → Credentials → OAuth client harus sama persis (http/https + host + path).
        </p>
    </div>

    <div class="card">
        <h2><i class="fa-solid fa-database" style="color:#1e3a8a"></i> Migrasi Database</h2>
        <?php
        $checks = [
            "pendaftaran.status alur 7 tahap" => (bool) preg_match('/berkas_diperiksa/',
                (string) (mysqli_fetch_assoc(mysqli_query($conn, "SHOW COLUMNS FROM pendaftaran LIKE 'status'"))['Type'] ?? '')),
            "sertifikat.tanggal_terbit" => (mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM sertifikat LIKE 'tanggal_terbit'")) > 0),
            "pendaftaran_upa lengkap (>30 kolom)" => ((int) mysqli_fetch_row(mysqli_query($conn,
                "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='pendaftaran_upa'"))[0] > 30),
            "pembayaran kolom gateway" => (mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM pembayaran LIKE 'order_id'")) > 0),
        ];
        foreach ($checks as $label => $ok): ?>
            <div class="row"><span class="k"><?= e($label) ?></span><span class="v"><?= badge($ok, 'sudah', 'BELUM') ?></span></div>
        <?php endforeach; ?>
        <p class="hint">Jika ada "BELUM", jalankan file di <code>database/migrasi_2026_09_07_*.sql</code>.</p>
    </div>
</div>
</main>
</body>
</html>
