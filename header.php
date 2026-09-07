<?php
// =====================================================
// RUNNING TEXT
// =====================================================

$running_texts = [];

if (isset($conn)) {

    // Cek apakah tabel running_text ada
    $check_table = mysqli_query(
        $conn,
        "SHOW TABLES LIKE 'running_text'"
    );

    if ($check_table && mysqli_num_rows($check_table) > 0) {

        // Ambil running text aktif
        $query_running = mysqli_query(
            $conn,
            "SELECT * FROM running_text
             WHERE is_active = 1
             ORDER BY urutan ASC"
        );

        if ($query_running && mysqli_num_rows($query_running) > 0) {

            while ($row = mysqli_fetch_assoc($query_running)) {

                $running_texts[] = [
                    'icon' => $row['icon'],
                    'pesan' => $row['pesan']
                ];
            }
        }
    }
}


// =====================================================
// DEFAULT RUNNING TEXT
// =====================================================

if (empty($running_texts)) {

    $running_texts = [

        [
            'icon' => '📢',
            'pesan' =>
            'Selamat Datang di Website Resmi DPC PERADI Pontianak. Pendaftaran PKPA Gelombang I Tahun 2026 Segera Dibuka!'
        ],

        [
            'icon' => '📢',
            'pesan' =>
            'Pendaftaran PKPA Gelombang I Tahun 2026 Segera Dibuka! Cek syarat dan jadwal.'
        ],

        [
            'icon' => '📢',
            'pesan' =>
            'Jangan lupa kunjungi Galeri kami untuk melihat kegiatan terbaru.'
        ],

        [
            'icon' => '💬',
            'pesan' =>
            'Hubungi kami via kontak resmi untuk informasi lebih lanjut.'
        ]
    ];
}


$running_texts_json = json_encode(
    $running_texts,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);

?>



<!-- =====================================================
     FONT & ICON
===================================================== -->

<link
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>



<style>

/* =====================================================
   RESET
===================================================== */

* {
    box-sizing: border-box;
}


/* =====================================================
   RUNNING TEXT
===================================================== */

.running-text-container {

    position: relative;

    width: 100%;
    height: 35px;

    background-color: #0096c7;

    color: #ffffff;

    z-index: 1002;

    overflow: hidden;

    line-height: 35px;

    font-size: 0.85rem;

    font-weight: 700;

    letter-spacing: 0.5px;

    border-bottom: 2px solid #0077b6;

    font-family: 'Montserrat', sans-serif;
}


.running-text {

    display: inline-block;

    white-space: nowrap;

    padding-left: 100%;

    animation: scrolling 25s linear infinite;

    opacity: 1;

    transition: opacity 0.5s ease;
}


@keyframes scrolling {

    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-100%);
    }
}


.hidden {
    opacity: 0;
}



/* =====================================================
   NAVBAR
===================================================== */

nav {

    position: relative;

    top: 0;
    left: 0;

    width: 100%;

    padding: 15px 50px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    z-index: 1000;

    background-color: #1e3a8a;

    box-shadow:
        0 4px 6px rgba(0, 0, 0, 0.1);

    border-bottom: 3px solid #dea057;

    font-family: 'Montserrat', sans-serif;
}



/* =====================================================
   LOGO
===================================================== */

.logo-container {

    display: flex;

    align-items: center;

    gap: 12px;

    text-decoration: none;
}


.logo-text {

    color: white;

    display: flex;

    flex-direction: column;
}


.logo-text h3 {

    font-size: 1.3rem;

    font-weight: 800;

    line-height: 1;

    letter-spacing: 0.5px;

    margin: 0;
}


.logo-text span {

    font-size: 0.75rem;

    font-weight: 400;

    letter-spacing: 2px;

    color: #dea057;
}



/* =====================================================
   NAV LINKS
===================================================== */

.nav-links {

    list-style: none;

    display: flex;

    gap: 30px;

    align-items: center;

    margin: 0;

    padding: 0;
}


.nav-links li {

    position: relative;
}


.nav-links a {

    text-decoration: none;

    color: white;

    font-size: 0.85rem;

    font-weight: 600;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    transition: 0.3s ease;

    opacity: 0.9;
}


.nav-links a:hover,
.nav-links a.active {

    color: #dea057;

    opacity: 1;
}



/* =====================================================
   LOGIN BUTTON
===================================================== */

.login-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    background: #dea057;

    color: #1e3a8a !important;

    padding: 10px 18px;

    border-radius: 8px;

    font-weight: 800 !important;

    opacity: 1 !important;

    transition: all 0.3s ease;

    box-shadow:
        0 4px 10px rgba(0, 0, 0, 0.15);
}


.login-btn:hover {

    background: white;

    color: #1e3a8a !important;

    transform: translateY(-2px);

    box-shadow:
        0 6px 15px rgba(0, 0, 0, 0.2);
}



/* =====================================================
   DROPDOWN
===================================================== */

.dropdown {

    position: relative;
}


.dropdown-content {

    display: none;

    position: absolute;

    top: 100%;

    left: 0;

    background-color: #1e3a8a;

    min-width: 240px;

    padding-top: 10px;

    box-shadow:
        0px 8px 16px rgba(0,0,0,0.2);

    border-top: 2px solid #dea057;

    z-index: 1001;
}


.dropdown:hover .dropdown-content {

    display: block;

    animation: slideDown 0.3s ease;
}


.dropdown-content a {

    display: block;

    padding: 12px 20px;

    color: white;

    text-transform: none;

    border-bottom:
        1px solid rgba(255, 255, 255, 0.1);
}


.dropdown-content a:hover {

    background-color: #152c69;

    color: #dea057;

    padding-left: 25px;
}


@keyframes slideDown {

    from {

        opacity: 0;

        transform: translateY(-10px);
    }

    to {

        opacity: 1;

        transform: translateY(0);
    }
}



/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 768px) {

    nav {

        padding: 15px 20px;

        flex-direction: column;

        gap: 15px;
    }


    .nav-links {

        flex-direction: column;

        width: 100%;

        text-align: center;

        gap: 15px;
    }


    .login-btn {

        justify-content: center;

        width: 140px;
    }
}

</style>



<?php

$page = basename($_SERVER['PHP_SELF']);

?>



<!-- =====================================================
     RUNNING TEXT HANYA DI HOME
===================================================== -->

<?php if ($page == 'index.php'): ?>

<div class="running-text-container">

    <span
        id="ticker-text"
        class="running-text"
    >

        <?php

        echo htmlspecialchars($running_texts[0]['icon'] ?? '', ENT_QUOTES, 'UTF-8');

        ?>

        <strong>INFO PENTING:</strong>

        <?php

        echo htmlspecialchars(
            $running_texts[0]['pesan'],
            ENT_QUOTES,
            'UTF-8'
        );

        ?>

    </span>

</div>

<?php endif; ?>



<!-- =====================================================
     NAVBAR
===================================================== -->

<nav>

    <!-- LOGO -->

    <a
        href="index.php"
        class="logo-container"
    >

        <i
            class="fa-solid fa-scale-balanced fa-2x"
            style="color: white;"
        ></i>

        <div class="logo-text">

            <h3>PERADI</h3>

            <span>Data Center</span>

        </div>

    </a>



    <!-- MENU -->

    <ul class="nav-links">


        <!-- HOME -->

        <li>

            <a
                href="index.php"
                class="<?php
                    if ($page == 'index.php') {
                        echo 'active';
                    }
                ?>"
            >

                Home

            </a>

        </li>



        <!-- PERATURAN -->

        <li class="dropdown">

            <a
                href="peraturan.php"
                class="<?php
                    if ($page == 'peraturan.php') {
                        echo 'active';
                    }
                ?>"
            >

                Peraturan

                <i
                    class="fa-solid fa-chevron-down"
                    style="font-size: 0.7rem;"
                ></i>

            </a>


            <div class="dropdown-content">

                <a href="peraturan.php?doc=uu18">
                    Undang-Undang No. 18 Tahun 2003
                </a>

                <a href="peraturan.php?doc=kodeetik">
                    Kode Etik Advokat
                </a>

                <a href="peraturan.php?doc=ad">
                    Anggaran Dasar
                </a>

                <a href="peraturan.php?doc=prt">
                    Peraturan Rumah Tangga
                </a>

                <a href="peraturan.php?doc=magang">
                    Peraturan Magang
                </a>

                <a href="peraturan.php?doc=keanggotaan">
                    Peraturan Keanggotaan
                </a>

                <a href="peraturan.php?doc=perpindahan">
                    Peraturan Perpindahan Domisili Anggota
                </a>

            </div>

        </li>



        <!-- STRUKTUR -->

        <li>

            <a
                href="struktur.php"
                class="<?php
                    if ($page == 'struktur.php') {
                        echo 'active';
                    }
                ?>"
            >

                Struktur

            </a>

        </li>



        <!-- LAYANAN -->

        <li class="dropdown">

            <a
                href="#"
                class="<?php

                    if (
                        $page == 'pkpa.php' ||
                        $page == 'upa.php' ||
                        $page == 'sumpah.php'
                    ) {

                        echo 'active';
                    }

                ?>"
            >

                Layanan

                <i
                    class="fa-solid fa-chevron-down"
                    style="font-size: 0.7rem;"
                ></i>

            </a>


            <div class="dropdown-content">

                <a href="pkpa.php">
                    PKPA
                </a>

                <a href="upa.php">
                    UPA
                </a>

                <a href="sumpah.php">
                    Pengangkatan &amp; Sumpah
                </a>

            </div>

        </li>



        <!-- GALERI -->

        <li>

            <a
                href="galeri.php"
                class="<?php
                    if ($page == 'galeri.php') {
                        echo 'active';
                    }
                ?>"
            >

                Galeri

            </a>

        </li>



        <!-- CEK SERTIFIKAT -->

        <li>

            <a
                href="cek_sertifikat.php"
                class="<?php
                    if ($page == 'cek_sertifikat.php') {
                        echo 'active';
                    }
                ?>"
            >

                Cek Sertifikat

            </a>

        </li>



        <!-- =================================================
             LOGIN
        ================================================== -->

        <li class="dropdown">

            <a
                href="login.php"
                class="<?php if($page == 'login.php') echo 'active'; ?>"
            >
                Login
                <i
                    class="fa-solid fa-chevron-down"
                    style="font-size: 0.7rem;"
                ></i>
            </a>

            <div class="dropdown-content">

            <a href="user/login.php">
                <i
                    class="fa-solid fa-user"
                    style="width: 20px;"
                ></i>
                Login User
            </a>

            <a href="admin/login.php">
                <i
                    class="fa-solid fa-user-shield"
                    style="width: 20px;"
                ></i>
                Login Admin
            </a>

        </div>
    </li>




    </ul>

</nav>



<!-- =====================================================
     RUNNING TEXT JAVASCRIPT
===================================================== -->

<?php if ($page == 'index.php'): ?>

<script>

const messages =
    <?php echo $running_texts_json; ?>;


const tickerText =
    document.getElementById('ticker-text');


let idx = 0;


const intervalMs = 25000;

const fadeMs = 500;



function changeMessage() {

    tickerText.classList.add('hidden');


    setTimeout(function () {

        idx =
            (idx + 1) %
            messages.length;


        const msg =
            messages[idx];


        tickerText.textContent = '';
        const iconSpan = document.createElement('span');
        iconSpan.textContent = (msg.icon || '') + ' ';
        const label = document.createElement('strong');
        label.textContent = 'INFO PENTING: ';
        const pesanSpan = document.createElement('span');
        pesanSpan.textContent = msg.pesan || '';
        tickerText.append(iconSpan, label, pesanSpan);


        tickerText.classList.remove('hidden');

    }, fadeMs);

}


setInterval(
    changeMessage,
    intervalMs
);

</script>

<?php endif; ?>