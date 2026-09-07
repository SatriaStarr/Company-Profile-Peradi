<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* --- CSS KHUSUS HALAMAN PKPA --- */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background-color: #f4f6f9;
        color: #333;
    }

    /* HEADER PAGE */
    .page-header {
        position: relative;
        width: 100%;
        height: 50vh;
        background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2070&auto=format&fit=crop');
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin-top: 0;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            to bottom,
            rgba(30, 58, 138, 0.9),
            rgba(30, 58, 138, 0.6)
        );
        z-index: 1;
    }

    .header-content {
        position: relative;
        z-index: 2;
        color: white;
        margin-top: 20px;
    }

    .header-title {
        font-size: 3.5rem;
        font-weight: 800;
        color: #dea057;
        letter-spacing: 3px;
        margin-bottom: 5px;
    }

    .header-subtitle {
        font-size: 1.2rem;
        font-weight: 400;
        letter-spacing: 2px;
        text-transform: uppercase;
        border-top: 1px solid rgba(255,255,255,0.3);
        padding-top: 10px;
        display: inline-block;
    }

    /* KONTEN UTAMA */
    .content-section {
        padding: 60px 5%;
        max-width: 1100px;
        margin: 0 auto;
    }

    .section-title {
        text-align: center;
        font-size: 2rem;
        color: #1e3a8a;
        margin-bottom: 40px;
        font-weight: 800;
        text-transform: uppercase;
        position: relative;
    }

    .section-title::after {
        content: '';
        display: block;
        width: 60px;
        height: 4px;
        background: #dea057;
        margin: 15px auto 0;
        border-radius: 2px;
    }

    .text-content {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #555;
        text-align: center;
        max-width: 800px;
        margin: 0 auto 40px;
    }

    /* =====================================================
       LOGIN / BUAT AKUN
       ===================================================== */

    .account-box {
        max-width: 850px;
        margin: 0 auto 70px;
        padding: 30px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border-left: 5px solid #dea057;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
    }

    .account-info {
        flex: 1;
    }

    .account-info h3 {
        color: #1e3a8a;
        font-size: 1.25rem;
        margin-bottom: 8px;
    }

    .account-info p {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .account-buttons {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }

    .btn-account {
        padding: 12px 22px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.3s;
    }

    .btn-login {
        background: #1e3a8a;
        color: white;
    }

    .btn-login:hover {
        background: #152c69;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30,58,138,0.25);
    }

    .btn-register {
        background: #dea057;
        color: white;
    }

    .btn-register:hover {
        background: #c8893d;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(222,160,87,0.25);
    }

    /* GRID MITRA */
    .mitra-grid {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 80px;
        flex-wrap: wrap;
    }

    .mitra-card {
        background: white;
        padding: 40px 30px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: 0.3s;
        border-top: 5px solid #1e3a8a;
        width: 350px;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .mitra-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(30, 58, 138, 0.2);
        border-top-color: #dea057;
    }

    .mitra-icon {
        font-size: 3rem;
        color: #1e3a8a;
        margin-bottom: 15px;
    }

    .mitra-name {
        font-weight: 800;
        font-size: 1.1rem;
        color: #333;
        margin-bottom: 15px;
        line-height: 1.4;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* BADGE */
    .badge-status {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 15px;
        letter-spacing: 1px;
    }

    .badge-negeri {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .badge-swasta {
        background-color: #fff3e0;
        color: #ef6c00;
        border: 1px solid #ffe0b2;
    }

    /* DETAIL */
    .mitra-detail {
        margin-top: 15px;
        font-size: 0.9rem;
        color: #555;
        width: 100%;
        text-align: left;
        border-top: 1px dashed #eee;
        padding-top: 20px;
    }

    .detail-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
        gap: 12px;
    }

    .detail-item i {
        color: #1e3a8a;
        font-size: 1rem;
        margin-top: 3px;
        min-width: 20px;
    }

    /* SYARAT */
    .syarat-container {
        display: flex;
        flex-wrap: wrap;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        border: 1px solid #eee;
    }

    .syarat-left {
        flex: 1;
        background: #1e3a8a;
        color: white;
        padding: 50px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 300px;
        position: relative;
        overflow: hidden;
    }

    .syarat-left::after {
        content: '\f0c0';
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        bottom: -30px;
        right: -30px;
        font-size: 10rem;
        color: rgba(255,255,255,0.05);
        transform: rotate(-15deg);
    }

    .syarat-right {
        flex: 2;
        padding: 50px 40px;
        min-width: 300px;
    }

    .syarat-item {
        display: flex;
        margin-bottom: 20px;
        align-items: flex-start;
        border-bottom: 1px dashed #eee;
        padding-bottom: 15px;
    }

    .syarat-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .syarat-number {
        background: #dea057;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        font-weight: bold;
        font-size: 0.9rem;
        margin-right: 20px;
        flex-shrink: 0;
        box-shadow: 0 3px 6px rgba(222, 160, 87, 0.4);
    }

    .syarat-text {
        font-size: 1rem;
        color: #444;
        line-height: 1.6;
    }

    .syarat-text strong {
        color: #1e3a8a;
        font-weight: 700;
    }

    /* DOWNLOAD */
    .btn-download:hover {
        background-color: rgba(255,255,255,0.1);
        transform: translateY(-2px);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {

        .header-title {
            font-size: 2.5rem;
        }

        .mitra-grid {
            flex-direction: column;
            align-items: center;
        }

        .syarat-container {
            flex-direction: column;
        }

        .mitra-card {
            width: 100%;
            max-width: 400px;
        }

        /* Account Box Mobile */
        .account-box {
            flex-direction: column;
            text-align: center;
            padding: 25px 20px;
        }

        .account-buttons {
            width: 100%;
            flex-direction: column;
        }

        .btn-account {
            justify-content: center;
            width: 100%;
        }
    }
</style>


<!-- =====================================================
     HEADER PKPA
     ===================================================== -->

<header class="page-header">

    <div class="header-content">

        <h1 class="header-title">
            P.K.P.A
        </h1>

        <p class="header-subtitle">
            Pendidikan Khusus Profesi Advokat
        </p>

    </div>

</header>


<!-- =====================================================
     KONTEN UTAMA
     ===================================================== -->

<div class="content-section">


    <!-- ================= DESKRIPSI ================= -->

    <h2 class="section-title">
        Deskripsi Program
    </h2>

    <p class="text-content">

        Pendidikan Khusus Profesi Advokat (PKPA) adalah pendidikan
        yang wajib diikuti oleh Sarjana Hukum yang ingin menjadi Advokat.

        Sesuai dengan mandat Undang-Undang Advokat No. 18 Tahun 2003,
        DPC PERADI Pontianak bekerjasama dengan Perguruan Tinggi yang
        fakultas hukumnya minimal terakreditasi B untuk menyelenggarakan
        pendidikan ini guna mencetak advokat yang profesional,
        berintegritas, dan berkualitas.

    </p>

    <!-- ================= MITRA ================= -->

    <h2 class="section-title">
        Mitra Penyelenggara
    </h2>

    <p class="text-content" style="margin-bottom: 30px;">

        Berikut adalah daftar Universitas dan Institusi di wilayah
        Kalimantan Barat yang telah bekerjasama resmi dengan
        DPC PERADI Pontianak untuk menyelenggarakan PKPA:

    </p>


    <div class="mitra-grid">


        <!-- MITRA 1 -->

        <div class="mitra-card">

            <span class="badge-status badge-negeri">
                Negeri
            </span>

            <i class="fa-solid fa-building-columns mitra-icon"></i>

            <div class="mitra-name">

                Program Magister Ilmu Hukum<br>
                Universitas Tanjungpura

            </div>

            <div class="mitra-detail">

                <div class="detail-item">

                    <i class="fa-solid fa-location-dot"></i>

                    <span>
                        Jl. Imam Bonjol (Jl. Daya Nasional)
                        No. 1 Pontianak
                    </span>

                </div>

                <div class="detail-item">

                    <i class="fa-solid fa-phone"></i>

                    <span>
                        (0561) 582741
                    </span>

                </div>

                <div class="detail-item">

                    <i class="fa-solid fa-fax"></i>

                    <span>
                        Fax: (0561) 582742
                    </span>

                </div>

            </div>

        </div>


        <!-- MITRA 2 -->

        <div class="mitra-card">

            <span class="badge-status badge-swasta">
                Swasta
            </span>

            <i class="fa-solid fa-scale-balanced mitra-icon"></i>

            <div class="mitra-name">

                STIH Soelthan M Tsjafioeddin<br>
                Singkawang

            </div>

            <div class="mitra-detail">

                <div class="detail-item">

                    <i class="fa-solid fa-location-dot"></i>

                    <span>
                        Jalan Soelthan M. Tsjafioeddin
                        No. 29 Singkawang
                    </span>

                </div>

                <div class="detail-item">

                    <i class="fa-solid fa-phone"></i>

                    <span>
                        (0562) 631926
                    </span>

                </div>

            </div>

        </div>

    </div>

    <!-- =================================================
         LOGIN / BUAT AKUN
         ================================================= -->

    <div class="account-box">

        <div class="account-info">

            <h3>
                <i class="fa-solid fa-user-check"></i>
                Ingin Mengikuti PKPA?
            </h3>

            <p>
                Silakan login jika sudah memiliki akun.
                Jika belum memiliki akun, silakan melakukan pendaftaran
                akun terlebih dahulu.
            </p>

        </div>


        <div class="account-buttons">

            <!-- TOMBOL LOGIN -->

            <a href="user/login.php"
               class="btn-account btn-login">

                <i class="fa-solid fa-right-to-bracket"></i>

                Login

            </a>


            <!-- TOMBOL BUAT AKUN -->

            <a href="user/register.php"
               class="btn-account btn-register">

                <i class="fa-solid fa-user-plus"></i>

                Buat Akun

            </a>

        </div>

    </div>

    <!-- ================= SYARAT ================= -->

    <h2 class="section-title">
        Syarat Pendaftaran
    </h2>


    <div class="syarat-container">


        <!-- BAGIAN KIRI -->

        <div class="syarat-left">

            <h3 style="
                font-size: 2rem;
                margin-bottom: 15px;
                line-height: 1.2;
            ">

                BERKAS<br>
                FISIK

            </h3>


            <p style="
                font-size: 0.95rem;
                opacity: 0.9;
                margin-bottom: 25px;
            ">

                Seluruh berkas wajib dimasukkan ke dalam
                <strong>Map Merah</strong> dan diserahkan
                ke Sekretariat.

            </p>


            <!-- WHATSAPP -->

            <a href="https://wa.me/+6281285701976"
               target="_blank"
               style="
                    background: white;
                    color: #1e3a8a;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 30px;
                    font-weight: bold;
                    display: inline-block;
                    font-size: 0.9rem;
                    transition: 0.3s;
                    margin-bottom: 15px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
               ">

                <i class="fa-brands fa-whatsapp"></i>

                Hubungi Admin

            </a>


            <!-- DOWNLOAD FORMULIR -->

            <a href="uploads/layanan/FORMULIR PENDAFTARAN PKPA 2026.pdf"
               download
               class="btn-download"
               style="
                    border: 2px solid rgba(255,255,255,0.8);
                    color: white;
                    padding: 10px 25px;
                    text-decoration: none;
                    border-radius: 30px;
                    font-weight: bold;
                    display: inline-block;
                    font-size: 0.9rem;
                    transition: 0.3s;
               ">

                <i class="fa-solid fa-file-arrow-down"></i>

                Unduh Formulir

            </a>

        </div>


        <!-- BAGIAN KANAN -->

        <div class="syarat-right">


            <!-- SYARAT 1 -->

            <div class="syarat-item">

                <div class="syarat-number">
                    1
                </div>

                <div class="syarat-text">

                    <strong>
                        Fotocopy Ijazah S1 Hukum
                    </strong>

                    <br>

                    Legalisir (2 lembar)

                </div>

            </div>


            <!-- SYARAT 2 -->

            <div class="syarat-item">

                <div class="syarat-number">
                    2
                </div>

                <div class="syarat-text">

                    <strong>
                        Fotocopy KTP
                    </strong>

                    <br>

                    Yang masih berlaku (2 lembar)

                </div>

            </div>


            <!-- SYARAT 3 -->

            <div class="syarat-item">

                <div class="syarat-number">
                    3
                </div>

                <div class="syarat-text">

                    <strong>
                        Pas Foto Terbaru
                    </strong>

                    <br>

                    Latar belakang merah/biru.
                    Ukuran 3x4 (4 lembar) dan 4x6 (1 lembar)

                </div>

            </div>


            <!-- SYARAT 4 -->

            <div class="syarat-item">

                <div class="syarat-number">
                    4
                </div>

                <div class="syarat-text">

                    <strong>
                        Bukti Transfer Biaya
                    </strong>

                    <br>

                    Biaya pendaftaran dan biaya pendidikan
                    (Asli & Fotocopy)

                </div>

            </div>


            <!-- SYARAT 5 -->

            <div class="syarat-item">

                <div class="syarat-number">
                    5
                </div>

                <div class="syarat-text">

                    <strong>
                        Mengisi Formulir Pendaftaran
                    </strong>

                    <br>

                    Formulir disediakan oleh Panitia Penyelenggara
                    atau unduh di samping

                </div>

            </div>


        </div>

    </div>

</div>


<?php include 'footer.php'; ?>