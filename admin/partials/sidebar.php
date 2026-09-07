<?php
/*
|--------------------------------------------------------------------------
| SIDEBAR ADMIN — KOMPONEN TUNGGAL
|--------------------------------------------------------------------------
| Dipakai bersama oleh SEMUA halaman admin. Desain mengacu ke dashboard.
| Sertakan tepat setelah <body>:
|
|     <?php include __DIR__ . '/partials/sidebar.php'; ?>
|
| Lalu bungkus konten halaman dengan:  <main class="main"> ... </main>
|
| Komponen ini membawa CSS + markup + JS sendiri sehingga tampil konsisten
| walau halaman tidak punya CSS sidebar.
*/

if (!function_exists('e')) {
    function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
}

$__current = basename($_SERVER['PHP_SELF'] ?? '');

/* halaman yang menandai menu tertentu sebagai aktif */
$__active_map = [
    'dashboard.php'          => 'dashboard',
    'data_pendaftaran.php'   => 'pendaftaran',
    'detail_pendaftaran.php' => 'pendaftaran',
    'peserta.php'            => 'pendaftaran',
    'data_user.php'          => 'user',
    'pengumuman.php'         => 'pengumuman',
    'notifikasi.php'         => 'notifikasi',
    'data_advokat.php'       => 'advokat',
    'detail_advokat.php'     => 'advokat',
    'tambah_advokat.php'     => 'advokat',
    'edit_advokat.php'       => 'advokat',
];
$__active = $__active_map[$__current] ?? '';

/* badge notifikasi admin yang belum dibaca (opsional, aman bila tabel kosong) */
$__notif_belum = 0;
if (isset($conn) && $conn instanceof mysqli && !empty($_SESSION['id_user'])) {
    if ($__q = @mysqli_query($conn, 'SELECT COUNT(*) c FROM notifikasi WHERE id_user = ' . (int) $_SESSION['id_user'] . ' AND is_read = 0')) {
        $__notif_belum = (int) (mysqli_fetch_assoc($__q)['c'] ?? 0);
    }
}
?>
<style>
/* ===== SIDEBAR ADMIN (komponen tunggal) ===== */
.sidebar{position:fixed;left:0;top:0;width:250px;height:100vh;background:#1e3a8a;color:#fff;
    padding:22px 16px;z-index:200;display:flex;flex-direction:column;overflow-y:auto;
    font-family:'Montserrat',sans-serif;transition:transform .3s ease}
.sidebar *{box-sizing:border-box}
.sidebar .brand{display:flex;align-items:center;gap:12px;padding:10px 8px 24px;
    border-bottom:1px solid rgba(255,255,255,.15);margin-bottom:18px}
.sidebar .brand i{font-size:30px;color:#dea057}
.sidebar .brand-text h2{font-size:19px;font-weight:800;margin:0;line-height:1}
.sidebar .brand-text span{color:#dea057;font-size:10px;letter-spacing:2px;display:block;margin-top:3px}
.sidebar .menu-title{font-size:10px;color:rgba(255,255,255,.55);text-transform:uppercase;
    letter-spacing:1px;margin:16px 10px 8px}
.sidebar .menu{list-style:none;margin:0;padding:0}
.sidebar .menu li{margin-bottom:5px}
.sidebar .menu a{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:8px;
    color:#fff;text-decoration:none;font-size:13px;font-weight:600;transition:.25s}
.sidebar .menu a:hover,.sidebar .menu a.active{background:#152c69;color:#dea057}
.sidebar .menu a i{width:20px;text-align:center;font-size:14px}
.sidebar .menu a .badge{margin-left:auto;background:#ef4444;color:#fff;font-size:10px;
    font-weight:700;padding:2px 7px;border-radius:10px;min-width:18px;text-align:center}
.sidebar .menu a.active .badge{background:#dea057;color:#152c69}
.sidebar .sidebar-spacer{flex:1}
.sidebar .logout-link{color:#fecaca;border-top:1px solid rgba(255,255,255,.12);
    margin-top:14px;padding-top:14px}
.sidebar .logout-link a{color:#fecaca}
.sidebar .logout-link a:hover{background:rgba(239,68,68,.15);color:#fff}

/* tombol hamburger (mobile) */
.sidebar-toggle{display:none;position:fixed;top:16px;left:16px;z-index:201;width:44px;height:44px;
    border:none;border-radius:9px;background:#1e3a8a;color:#fff;font-size:18px;cursor:pointer}
.sidebar-backdrop{display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:199}

@media(max-width:900px){
    .sidebar{transform:translateX(-100%)}
    .sidebar.open{transform:translateX(0)}
    .sidebar-toggle{display:block}
    .sidebar-backdrop.show{display:block}
    .main{margin-left:0 !important}
}
</style>

<button class="sidebar-toggle" id="sidebarToggle" aria-label="Menu">
    <i class="fa-solid fa-bars"></i>
</button>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<aside class="sidebar" id="adminSidebar">

    <div class="brand">
        <i class="fa-solid fa-scale-balanced"></i>
        <div class="brand-text">
            <h2>PERADI</h2>
            <span>DATA CENTER</span>
        </div>
    </div>

    <div class="menu-title">Menu Utama</div>
    <ul class="menu">
        <li><a href="dashboard.php" class="<?= $__active === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>

        <li><a href="data_pendaftaran.php" class="<?= $__active === 'pendaftaran' ? 'active' : '' ?>">
            <i class="fa-solid fa-file-circle-check"></i> Data Pendaftaran</a></li>

        <li><a href="data_user.php" class="<?= $__active === 'user' ? 'active' : '' ?>">
            <i class="fa-solid fa-user-group"></i> Data User</a></li>

        <li><a href="pengumuman.php" class="<?= $__active === 'pengumuman' ? 'active' : '' ?>">
            <i class="fa-solid fa-bullhorn"></i> Pengumuman</a></li>

        <li><a href="notifikasi.php" class="<?= $__active === 'notifikasi' ? 'active' : '' ?>">
            <i class="fa-solid fa-bell"></i> Notifikasi
            <?php if ($__notif_belum > 0): ?><span class="badge"><?= $__notif_belum > 99 ? '99+' : $__notif_belum ?></span><?php endif; ?>
        </a></li>
    </ul>

    <div class="menu-title">Sistem</div>
    <ul class="menu">
        <li><a href="data_advokat.php" class="<?= $__active === 'advokat' ? 'active' : '' ?>">
            <i class="fa-solid fa-address-card"></i> Data Advokat</a></li>
        <?php if (($_SESSION['role'] ?? '') === 'super_admin'): ?>
        <li><a href="cek_integrasi.php" class="<?= $__current === 'cek_integrasi.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-plug-circle-check"></i> Cek Integrasi</a></li>
        <?php endif; ?>
        <li><a href="../index.php">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Website</a></li>
    </ul>

    <div class="sidebar-spacer"></div>

    <ul class="menu logout-link">
        <li><a href="logout.php" id="adminLogoutLink" data-logout>
            <i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
    </ul>

</aside>

<?php include __DIR__ . '/../../partials/logout_modal.php'; ?>

<script>
(function () {
    var sb = document.getElementById('adminSidebar');
    var tg = document.getElementById('sidebarToggle');
    var bd = document.getElementById('sidebarBackdrop');
    function open(){ sb.classList.add('open'); bd.classList.add('show'); }
    function close(){ sb.classList.remove('open'); bd.classList.remove('show'); }
    if (tg) tg.addEventListener('click', function () {
        sb.classList.contains('open') ? close() : open();
    });
    if (bd) bd.addEventListener('click', close);
})();
</script>
