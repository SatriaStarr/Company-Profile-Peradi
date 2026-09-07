<?php
$base_admin = '/WEB-DPC-PERADI-PONTIANAK/admin/';

// Deteksi halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);

// Function untuk cek active menu
function isActive($page) {
    global $current_page;
    return ($current_page == $page) ? 'active' : '';
}

// Function untuk cek apakah kategori peraturan aktif
function isPeraturanActive() {
    global $current_page;
    return ($current_page == 'peraturan.php') ? true : false;
}

// Function untuk cek apakah submenu layanan aktif
function isLayananActive() {
    global $current_page;
    $layanan_pages = ['layanan_pkpa.php', 'layanan_upa.php', 'layanan_sumpah.php'];
    return in_array($current_page, $layanan_pages);
}

// Function untuk cek apakah ada menu aktif di Kelola Halaman
function isKelolaHalamanActive() {
    global $current_page;
    $kelola_pages = ['struktur_pengurus.php', 'peraturan.php', 'galeri.php', 'kelola_layanan.php', 
                     'layanan_pkpa.php', 'layanan_upa.php', 'layanan_sumpah.php'];
    return in_array($current_page, $kelola_pages);
}
?>

<style>
    /* ========================================
       SIDEBAR - COMPLETE STYLING
       Semua CSS yang dibutuhkan untuk sidebar
       konsisten di SEMUA halaman
    ======================================== */

    /* ===== SIDEBAR CONTAINER ===== */
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #1e3a8a;
        color: white;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        box-sizing: border-box;
        z-index: 100;
        transition: transform 0.3s ease;
    }

    /* Hilangkan scrollbar default tapi tetap bisa scroll */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(222, 160, 87, 0.5);
        border-radius: 10px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: #dea057;
    }

    /* Reset untuk semua elemen dalam sidebar */
    .sidebar * {
        box-sizing: border-box;
    }

    .sidebar ul {
        margin: 0;
        padding: 0;
    }

    .sidebar li {
        list-style: none;
    }

    .sidebar hr {
        margin-left: 0;
        margin-right: 0;
    }

    /* ===== LOGO SECTION ===== */
    .logo-section {
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.1);
    }

    .logo-section i {
        font-size: 2rem;
        color: #dea057;
    }

    .logo-text h2 {
        font-size: 1.4rem;
        font-weight: 800;
        margin: 0;
        line-height: 1;
        color: white;
        letter-spacing: 0.5px;
    }

    .logo-text span {
        font-size: 0.75rem;
        letter-spacing: 1px;
        color: #dea057;
        font-weight: 500;
        display: block;
        margin-top: 2px;
    }

    /* ===== NAVIGATION LINKS ===== */
    .nav-links {
        list-style: none;
        padding: 15px 0;
        margin: 0;
        flex: 1;
        overflow-y: auto;
    }

    .nav-links::-webkit-scrollbar {
        width: 5px;
    }

    .nav-links::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.03);
    }

    .nav-links::-webkit-scrollbar-thumb {
        background: rgba(222, 160, 87, 0.4);
        border-radius: 10px;
    }

    /* Styling Link Menu */
    .nav-links li a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 25px;
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        cursor: pointer;
    }

    .nav-links li a span {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .nav-links li a span i {
        width: 20px;
        text-align: center;
    }

    .nav-links li a:hover,
    .nav-links li.active > a {
        background-color: #152c69;
        color: white;
        border-left-color: #dea057;
    }

    /* ===== BADGE NOTIFIKASI ===== */
    .badge,
    .badge-notif {
        background: #ef4444;
        color: white;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 600;
        margin-left: auto;
        display: inline-block;
        min-width: 18px;
        text-align: center;
    }

    /* ===== DROPDOWN MENU ===== */
    .dropdown-item > a {
        justify-content: space-between;
    }

    /* Icon panah dropdown */
    .arrow {
        transition: transform 0.3s ease;
        font-size: 0.75rem;
        margin-left: 10px;
    }

    .rotate {
        transform: rotate(180deg);
    }

    /* ===== SUBMENU (Level 1) ===== */
    .submenu {
        list-style: none;
        padding: 0;
        display: none;
        background-color: #15204a;
        max-height: 400px;
        overflow-y: auto;
    }

    /* Scrollbar untuk submenu */
    .submenu::-webkit-scrollbar {
        width: 5px;
    }

    .submenu::-webkit-scrollbar-track {
        background: #0f1633;
    }

    .submenu::-webkit-scrollbar-thumb {
        background: #dea057;
        border-radius: 10px;
    }

    .submenu::-webkit-scrollbar-thumb:hover {
        background: #c88a3f;
    }

    .submenu li a {
        padding-left: 45px;
        font-size: 0.85rem;
        border-left: none;
    }

    .submenu li a:hover,
    .submenu li.active > a {
        background-color: #1e2d63;
        border-left: 4px solid #dea057;
    }

    /* ===== SUB-SUBMENU (Level 2) ===== */
    .sub-submenu {
        list-style: none;
        padding: 0;
        display: none;
        background-color: #0f1633;
    }

    .sub-submenu li a {
        padding-left: 65px;
        font-size: 0.8rem;
        opacity: 0.85;
    }

    .sub-submenu li a:hover,
    .sub-submenu li.active a {
        opacity: 1;
        background-color: #15204a;
        border-left: 4px solid #dea057;
    }

    /* Class untuk menampilkan menu via JS */
    .show-menu {
        display: block !important;
    }

    /* ===== SECTION DIVIDER ===== */
    .nav-links hr {
        border: 0;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin: 10px 20px;
    }

    /* ===== MENU CATEGORY LABEL ===== */
    .nav-links > li[style*="padding"] {
        padding: 5px 25px;
        font-size: 0.65rem;
        color: #dea057;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        margin-top: 5px;
    }

    /* ===== LOGOUT SECTION ===== */
    .logout-section {
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.1);
    }

    .logout-section a {
        color: #ef4444;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        text-decoration: none;
        padding: 10px 15px;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .logout-section a:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #ffadad;
        transform: translateX(5px);
    }

    .logout-section a i {
        font-size: 1rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.active {
            transform: translateX(0);
        }
    }

    /* ===== ACTIVE STATE ENHANCEMENT ===== */
    .nav-links li.active > a {
        font-weight: 600;
        box-shadow: inset 0 0 20px rgba(222, 160, 87, 0.1);
    }

    /* ===== SMOOTH TRANSITIONS ===== */
    .nav-links li a,
    .submenu,
    .sub-submenu,
    .arrow {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ===== HOVER EFFECTS ===== */
    .nav-links li:not(.active) a:hover {
        padding-left: 28px;
    }

    /* ===== FOCUS STATES (Accessibility) ===== */
    .nav-links li a:focus {
        outline: 2px solid #dea057;
        outline-offset: -2px;
    }
</style>

<div class="sidebar">
    <!-- LOGO SECTION -->
    <div class="logo-section">
        <i class="fa-solid fa-scale-balanced"></i>
        <div class="logo-text">
            <h2>PERADI</h2>
            <span>Data Center</span>
        </div>
    </div>

    <!-- NAVIGATION LINKS -->
    <ul class="nav-links">
    <li class="<?php echo isActive('peserta.php'); ?>"><a href="peserta.php"><span><i class="fa-solid fa-users"></i> Peserta Sertifikasi</span></a></li>


        <!-- BACK TO HOME -->
        <li>
            <a href="../index.php">
                <span>
                    <i class="fa-solid fa-arrow-left"></i>
                    Layanan Home
                </span>
            </a>
        </li>

        <!-- DASHBOARD -->
        <li class="<?php echo isActive('dashboard.php'); ?>">
            <a href="dashboard.php">
                <span>
                    <i class="fa-solid fa-chart-line"></i>
                    Dashboard
                </span>
            </a>
        </li>

        <!-- DIVIDER -->
        <hr>
        <li style="padding: 5px 25px; font-size: 0.65rem; color: #dea057; text-transform: uppercase; font-weight: 700; letter-spacing: 1px;">
            Menu Utama
        </li>

        <!-- KELOLA HALAMAN (DROPDOWN) -->
        <li class="dropdown-item">
            <a onclick="toggleMenu('menu-halaman', this)">
                <span>
                    <i class="fa-solid fa-layer-group"></i>
                    Kelola Halaman
                </span>
                <i class="fa-solid fa-chevron-down arrow <?php echo isKelolaHalamanActive() ? 'rotate' : ''; ?>"></i>
            </a>

            <ul id="menu-halaman" class="submenu <?php echo isKelolaHalamanActive() ? 'show-menu' : ''; ?>">

                <!-- HOME -->
                <li class="<?php echo isActive('dashboard.php'); ?>">
                    <a href="dashboard.php">
                        <span>
                            <i class="fa-solid fa-house"></i>
                            Home
                        </span>
                    </a>
                </li>

                <!-- PERATURAN (SUB-DROPDOWN) -->
                <li class="dropdown-item <?php echo isPeraturanActive() ? 'active' : ''; ?>">
                    <a onclick="toggleMenu('menu-peraturan', this)">
                        <span>
                            <i class="fa-solid fa-book-open"></i>
                            Peraturan
                        </span>
                        <i class="fa-solid fa-chevron-down arrow <?php echo isPeraturanActive() ? 'rotate' : ''; ?>"></i>
                    </a>
                    <ul id="menu-peraturan" class="sub-submenu <?php echo isPeraturanActive() ? 'show-menu' : ''; ?>">
                        <li class="<?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'uu18') ? 'active' : ''; ?>">
                            <a href="peraturan.php?kategori=uu18">UU No. 18 Tahun 2003</a>
                        </li>
                        <li class="<?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'kodeetik') ? 'active' : ''; ?>">
                            <a href="peraturan.php?kategori=kodeetik">Kode Etik Advokat</a>
                        </li>
                        <li class="<?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'ad') ? 'active' : ''; ?>">
                            <a href="peraturan.php?kategori=ad">Anggaran Dasar</a>
                        </li>
                        <li class="<?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'prt') ? 'active' : ''; ?>">
                            <a href="peraturan.php?kategori=prt">Peraturan Rumah Tangga</a>
                        </li>
                        <li class="<?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'magang') ? 'active' : ''; ?>">
                            <a href="peraturan.php?kategori=magang">Peraturan Magang</a>
                        </li>
                        <li class="<?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'keanggotaan') ? 'active' : ''; ?>">
                            <a href="peraturan.php?kategori=keanggotaan">Peraturan Keanggotaan</a>
                        </li>
                        <li class="<?php echo (isset($_GET['kategori']) && $_GET['kategori'] == 'perpindahan') ? 'active' : ''; ?>">
                            <a href="peraturan.php?kategori=perpindahan">Peraturan Perpindahan</a>
                        </li>
                    </ul>
                </li>

                <!-- STRUKTUR -->
                <li class="<?php echo isActive('struktur_pengurus.php'); ?>">
                    <a href="struktur_pengurus.php">
                        <span>
                            <i class="fa-solid fa-sitemap"></i>
                            Struktur
                        </span>
                    </a>
                </li>

                <!-- KELOLA LAYANAN -->
                <li class="<?php echo isActive('kelola_layanan.php'); ?>">
                    <a href="kelola_layanan.php">
                        <span>
                            <i class="fa-solid fa-pen-to-square"></i>
                            Kelola Layanan
                        </span>
                    </a>
                </li>

                <!-- LAYANAN (SUB-DROPDOWN) -->
                <li class="dropdown-item">
                    <a onclick="toggleMenu('menu-layanan', this)">
                        <span>
                            <i class="fa-solid fa-hand-holding-heart"></i>
                            Layanan
                        </span>
                        <i class="fa-solid fa-chevron-down arrow <?php echo isLayananActive() ? 'rotate' : ''; ?>"></i>
                    </a>
                    <ul id="menu-layanan" class="sub-submenu <?php echo isLayananActive() ? 'show-menu' : ''; ?>">
                        <li class="<?php echo isActive('layanan_pkpa.php'); ?>">
                            <a href="layanan_pkpa.php">PKPA</a>
                        </li>
                        <li class="<?php echo isActive('layanan_upa.php'); ?>">
                            <a href="layanan_upa.php">UPA</a>
                        </li>
                        <li class="<?php echo isActive('layanan_sumpah.php'); ?>">
                            <a href="layanan_sumpah.php">Pengangkatan & Sumpah</a>
                        </li>
                    </ul>
                </li>

                <!-- GALERI -->
                <li class="<?php echo isActive('galeri.php'); ?>">
                    <a href="galeri.php">
                        <span>
                            <i class="fa-solid fa-image"></i>
                            Galeri
                        </span>
                    </a>
                </li>

            </ul>
        </li>

        <!-- DATA ADVOKAT -->
        <li class="<?php echo isActive('data_advokat.php'); ?>">
            <a href="data_advokat.php">
                <span>
                    <i class="fa-solid fa-users-viewfinder"></i>
                    Data Advokat
                </span>
            </a>
        </li>

        <!-- RUNNING TEXT -->
        <li class="<?php echo isActive('kelola_running_text.php'); ?>">
            <a href="kelola_running_text.php">
                <span>
                    <i class="fa-solid fa-scroll"></i>
                    Running Text
                </span>
            </a>
        </li>

        <!-- VERIFIKASI ADMIN (DENGAN BADGE) -->
        <li class="<?php echo isActive('verifikasi_admin.php'); ?>">
            <a href="verifikasi_admin.php">
                <span>
                    <i class="fa-solid fa-user-shield"></i>
                    Verifikasi Admin
                </span>
                <?php if (isset($total_pending) && $total_pending > 0): ?>
                    <span class="badge"><?php echo $total_pending; ?></span>
                <?php endif; ?>
            </a>
        </li>

    </ul>

    <!-- LOGOUT SECTION -->
    <div class="logout-section">
        <a href="logout.php" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
    /**
     * Toggle Menu Function
     * Untuk membuka/menutup dropdown menu
     */
    function toggleMenu(id, element) {
        // 1. Ambil elemen menu yang mau dibuka
        var menu = document.getElementById(id);

        // 2. Toggle class 'show-menu' (Buka/Tutup)
        menu.classList.toggle("show-menu");

        // 3. Putar icon panah
        var arrow = element.querySelector(".arrow");
        if (arrow) {
            arrow.classList.toggle("rotate");
        }
    }
</script>