<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>UPA - DPC PERADI Pontianak</title>

    <!-- GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- FONT AWESOME -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >


    <style>

        /* =========================================
           CSS GLOBAL
        ========================================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }


        /* =========================================
           HEADER PAGE
        ========================================= */

        .page-header {
            position: relative;
            width: 100%;
            height: 50vh;

            background-image: url('image/peradi-tower.jpg');

            background-size: cover;
            background-position: center;

            display: flex;
            flex-direction: column;

            align-items: center;
            justify-content: center;

            text-align: center;
        }

        .page-header::before {

            content: '';

            position: absolute;

            top: 0;
            left: 0;

            width: 100%;
            height: 100%;

            background:
                linear-gradient(
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


        /* =========================================
           KONTEN UTAMA
        ========================================= */

        .content-section {

            padding: 60px 5%;

            max-width: 1200px;

            margin: 0 auto;
        }


        /* =========================================
           JUDUL SECTION
        ========================================= */

        .section-title {

            text-align: center;

            font-size: 2rem;

            color: #1e3a8a;

            margin-bottom: 50px;

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


        /* =========================================
           BANNER LOGIN UPA
        ========================================= */

        .login-banner {

            background: #ffffff;

            border-left: 7px solid #dea057;

            border-radius: 16px;

            padding: 28px 40px;

            margin-bottom: 80px;

            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 30px;
        }

        .login-banner-content {

            flex: 1;
        }

        .login-banner-content h2 {

            margin: 0 0 10px;

            color: #1e3a8a;

            font-size: 24px;

            font-weight: 800;
        }

        .login-banner-content h2 i {

            margin-right: 8px;
        }

        .login-banner-content p {

            margin: 0;

            color: #64748b;

            font-size: 16px;

            line-height: 1.7;
        }

        .login-banner-actions {

            display: flex;

            gap: 12px;

            flex-shrink: 0;
        }

        .btn-login,
        .btn-register {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 14px 25px;

            border-radius: 9px;

            text-decoration: none;

            font-weight: 700;

            transition: 0.3s;

            white-space: nowrap;
        }

        .btn-login {

            background: #1e3a8a;

            color: white;
        }

        .btn-login:hover {

            background: #152c69;

            transform: translateY(-2px);
        }

        .btn-register {

            background: #dea057;

            color: white;
        }

        .btn-register:hover {

            background: #c88d45;

            transform: translateY(-2px);
        }


        /* =========================================
           TEXT CONTENT
        ========================================= */

        .text-content {

            font-size: 1.05rem;

            line-height: 1.8;

            color: #555;

            text-align: center;

            max-width: 800px;

            margin: 0 auto 50px;
        }


        /* =========================================
           PENGUMUMAN LULUS
        ========================================= */

        .pengumuman-box {

            background:
                linear-gradient(
                    135deg,
                    #1e3a8a 0%,
                    #0d2566 100%
                );

            color: white;

            padding: 30px 40px;

            border-radius: 15px;

            margin-bottom: 80px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            box-shadow:
                0 10px 30px rgba(30, 58, 138, 0.2);

            flex-wrap: wrap;

            gap: 20px;

            border: 1px solid rgba(255,255,255,0.1);
        }

        .pengumuman-content {

            flex: 1;

            min-width: 300px;
        }

        .pengumuman-content h3 {

            font-size: 1.5rem;

            margin-bottom: 10px;

            color: #dea057;

            font-weight: 800;

            text-transform: uppercase;

            display: flex;

            align-items: center;

            gap: 10px;
        }

        .pengumuman-content p {

            font-size: 1rem;

            opacity: 0.9;

            line-height: 1.5;

            margin: 0;
        }

        .btn-cek-lulus {

            background-color: #dea057;

            color: #1e3a8a;

            padding: 12px 30px;

            border-radius: 50px;

            text-decoration: none;

            font-weight: 800;

            transition: 0.3s;

            display: inline-flex;

            align-items: center;

            gap: 10px;

            white-space: nowrap;

            box-shadow:
                0 5px 15px rgba(222, 160, 87, 0.3);
        }

        .btn-cek-lulus:hover {

            background-color: white;

            color: #1e3a8a;

            transform: translateY(-3px);
        }


        /* =========================================
           GRID MATERI UJIAN
        ========================================= */

        .materi-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(300px, 1fr)
                );

            gap: 30px;

            margin-bottom: 80px;
        }

        .materi-card {

            background: white;

            padding: 40px 30px;

            border-radius: 10px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.05);

            transition: 0.3s;

            border-top: 4px solid #1e3a8a;
        }

        .materi-card:hover {

            transform: translateY(-5px);

            box-shadow:
                0 10px 30px rgba(0,0,0,0.1);

            border-top-color: #dea057;
        }

        .card-icon {

            font-size: 2.5rem;

            color: #dea057;

            margin-bottom: 20px;
        }

        .card-title {

            font-size: 1.3rem;

            font-weight: 700;

            color: #1e3a8a;

            margin-bottom: 15px;
        }

        .card-list {

            list-style: none;
        }

        .card-list li {

            margin-bottom: 10px;

            padding-left: 20px;

            position: relative;

            font-size: 0.95rem;

            color: #555;
        }

        .card-list li::before {

            content: '•';

            color: #dea057;

            font-weight: bold;

            font-size: 1.2rem;

            position: absolute;

            left: 0;

            top: -2px;
        }


        /* =========================================
           SYARAT PENDAFTARAN
        ========================================= */

        .syarat-box {

            background: white;

            border-radius: 12px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.05);

            overflow: hidden;

            display: flex;

            flex-wrap: wrap;

            border: 1px solid #eee;

            margin-bottom: 60px;
        }

        .syarat-header {

            background: #1e3a8a;

            color: white;

            padding: 40px;

            flex: 1;

            min-width: 300px;

            display: flex;

            flex-direction: column;

            justify-content: center;
        }

        .syarat-content {

            padding: 40px;

            flex: 2;

            min-width: 300px;
        }

        .syarat-list-item {

            display: flex;

            margin-bottom: 15px;

            align-items: flex-start;

            border-bottom: 1px dashed #eee;

            padding-bottom: 10px;
        }

        .syarat-list-item:last-child {

            border-bottom: none;
        }

        .syarat-num {

            background: #dea057;

            color: white;

            width: 24px;

            height: 24px;

            border-radius: 50%;

            text-align: center;

            line-height: 24px;

            font-size: 0.8rem;

            font-weight: bold;

            margin-right: 15px;

            flex-shrink: 0;

            margin-top: 2px;
        }

        .syarat-text {

            font-size: 0.95rem;

            color: #444;

            line-height: 1.6;
        }

        .syarat-text strong {

            color: #1e3a8a;
        }


        /* =========================================
           BANNER JADWAL & DOWNLOAD
        ========================================= */

        .jadwal-banner {

            background-color: #1e3a8a;

            background-image:
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%232a4ead' fill-opacity='0.2'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");

            border-radius: 12px;

            padding: 40px;

            color: white;

            display: flex;

            flex-wrap: wrap;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 60px;
        }

        .banner-text h3 {

            font-size: 1.5rem;

            font-weight: 700;

            color: #dea057;

            margin-bottom: 10px;
        }

        .banner-text p {

            font-size: 1rem;

            opacity: 0.9;

            max-width: 600px;
        }


        /* =========================================
           TOMBOL DOWNLOAD
        ========================================= */

        .btn-download-form {

            background-color: #dea057;

            color: #1e3a8a;

            padding: 15px 30px;

            border-radius: 50px;

            text-decoration: none;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 1px;

            transition: 0.3s;

            display: inline-flex;

            align-items: center;

            gap: 10px;

            box-shadow:
                0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-download-form:hover {

            background-color: white;

            color: #1e3a8a;

            transform: translateY(-3px);
        }


        /* =========================================
           RESPONSIVE
        ========================================= */

        @media (max-width: 768px) {

            .header-title {

                font-size: 2.5rem;
            }

            .syarat-box {

                flex-direction: column;
            }

            .jadwal-banner {

                flex-direction: column;

                text-align: center;
            }

            .pengumuman-box {

                flex-direction: column;

                text-align: center;
            }

            .login-banner {

                flex-direction: column;

                align-items: flex-start;

                padding: 25px;

                margin-bottom: 60px;
            }

            .login-banner-actions {

                width: 100%;
            }

            .btn-login,
            .btn-register {

                flex: 1;
            }
        }


        @media (max-width: 480px) {

            .login-banner-actions {

                flex-direction: column;

                width: 100%;
            }

            .btn-login,
            .btn-register {

                width: 100%;
            }
        }

    </style>

</head>


<body>


    <!-- HEADER WEBSITE -->
    <?php include 'header.php'; ?>


    <!-- HERO UPA -->
    <header class="page-header">

        <div class="header-content">

            <h1 class="header-title">
                UPA
            </h1>

            <p class="header-subtitle">
                Ujian Profesi Advokat
            </p>

        </div>

    </header>


    <!-- KONTEN -->
    <div class="content-section">


        <!-- =========================================
             TENTANG UPA
        ========================================= -->

        <h2 class="section-title">
            Tentang UPA
        </h2>

        <p class="text-content">

            Ujian Profesi Advokat (UPA) adalah ujian yang
            diselenggarakan oleh Organisasi Advokat PERADI
            bagi para calon advokat yang telah mengikuti
            Pendidikan Khusus Profesi Advokat (PKPA).

            Lulus UPA adalah syarat mutlak untuk dapat dilantik
            dan disumpah menjadi Advokat. Standar kelulusan
            yang tinggi diterapkan demi menjaga kualitas
            Advokat Indonesia.

        </p>

        <!-- =========================================
             BANNER LOGIN
        ========================================= -->

        <div class="login-banner">

            <div class="login-banner-content">

                <h2>
                    <i class="fa-solid fa-user-check"></i>
                    Ingin Mengikuti UPA?
                </h2>

                <p>
                    Silakan login jika sudah memiliki akun.
                    Jika belum memiliki akun, silakan melakukan
                    pendaftaran akun terlebih dahulu.
                </p>

            </div>


            <div class="login-banner-actions">

                <a
                    href="user/login.php"
                    class="btn-login"
                >

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Login

                </a>


                <a
                    href="user/register.php"
                    class="btn-register"
                >

                    <i class="fa-solid fa-user-plus"></i>

                    Buat Akun

                </a>

            </div>

        </div>


        <!-- =========================================
             PENGUMUMAN LULUS
        ========================================= -->

        <div class="pengumuman-box">

            <div class="pengumuman-content">

                <h3>

                    <i class="fa-solid fa-bullhorn"></i>

                    Pengumuman Kelulusan

                </h3>

                <p>

                    Hasil Ujian Profesi Advokat (UPA)
                    Gelombang I Tahun 2025 telah resmi dirilis.

                    Silakan cek nama Anda pada daftar kelulusan
                    melalui tombol di samping.

                </p>

            </div>


            <a
                href="user/login.php"
                target="_blank"
                class="btn-cek-lulus"
            >

                <i class="fa-solid fa-list-check"></i>

                Cek Daftar Kelulusan

            </a>

        </div>


        <!-- =========================================
             MATERI UJIAN
        ========================================= -->

        <h2 class="section-title">
            Materi Ujian
        </h2>


        <div class="materi-grid">


            <!-- MATERI 1 -->

            <div class="materi-card">

                <div class="card-icon">

                    <i class="fa-solid fa-scale-balanced"></i>

                </div>

                <h3 class="card-title">
                    Hukum Acara (Litigasi)
                </h3>

                <ul class="card-list">

                    <li>
                        Hukum Acara Perdata
                    </li>

                    <li>
                        Hukum Acara Pidana
                    </li>

                    <li>
                        Hukum Acara Peradilan Agama
                    </li>

                    <li>
                        Hukum Acara PTUN
                    </li>

                    <li>
                        Hukum Acara Mahkamah Konstitusi
                    </li>

                </ul>

            </div>


            <!-- MATERI 2 -->

            <div class="materi-card">

                <div class="card-icon">

                    <i class="fa-solid fa-book-open"></i>

                </div>

                <h3 class="card-title">
                    Materi Non-Litigasi
                </h3>

                <ul class="card-list">

                    <li>
                        Hukum Perdata & Pidana (Materiil)
                    </li>

                    <li>
                        Undang-Undang Advokat
                    </li>

                    <li>
                        Kode Etik Profesi Advokat
                    </li>

                    <li>
                        Hukum Perburuhan
                    </li>

                    <li>
                        Hukum Persaingan Usaha
                    </li>

                </ul>

            </div>


            <!-- MATERI 3 -->

            <div class="materi-card">

                <div class="card-icon">

                    <i class="fa-solid fa-pen-nib"></i>

                </div>

                <h3 class="card-title">
                    Essay (Hukum Acara)
                </h3>

                <p
                    style="
                        color: #555;
                        font-size: 0.95rem;
                        line-height: 1.6;
                    "
                >

                    Peserta wajib membuat
                    <strong>Surat Kuasa</strong>
                    dan
                    <strong>Surat Gugatan</strong>
                    berdasarkan kasus posisi yang diberikan.

                    Ini adalah poin krusial dalam penilaian
                    kelulusan.

                </p>

            </div>


        </div>


        <!-- =========================================
             SYARAT PENDAFTARAN UPA
        ========================================= -->

        <h2 class="section-title">
            Syarat Pendaftaran UPA
        </h2>


        <div class="syarat-box">


            <!-- HEADER SYARAT -->

            <div class="syarat-header">

                <i
                    class="fa-solid fa-clipboard-check"
                    style="
                        font-size: 4rem;
                        color: #dea057;
                        margin-bottom: 20px;
                    "
                ></i>

                <h3
                    style="
                        font-size: 1.8rem;
                        margin-bottom: 10px;
                    "
                >

                    PERSYARATAN
                    <br>
                    ADMINISTRASI

                </h3>

                <p
                    style="
                        opacity: 0.8;
                        font-size: 0.9rem;
                    "
                >

                    Pastikan seluruh berkas kelengkapan
                    sudah disiapkan sebelum mengisi formulir.

                </p>

            </div>


            <!-- ISI SYARAT -->

            <div class="syarat-content">


                <!-- 1 -->

                <div class="syarat-list-item">

                    <div class="syarat-num">
                        1
                    </div>

                    <div class="syarat-text">

                        <strong>
                            Warga Negara Indonesia (WNI)
                        </strong>.

                    </div>

                </div>


                <!-- 2 -->

                <div class="syarat-list-item">

                    <div class="syarat-num">
                        2
                    </div>

                    <div class="syarat-text">

                        Mengisi
                        <strong>
                            Formulir Pendaftaran
                        </strong>
                        dengan melampirkan:

                    </div>

                </div>


                <!-- SUB SYARAT -->

                <div style="margin-left: 40px;">


                    <!-- A -->

                    <div class="syarat-list-item">

                        <div
                            class="syarat-num"
                            style="
                                background: #eee;
                                color: #555;
                            "
                        >
                            a
                        </div>

                        <div class="syarat-text">

                            Fotokopi
                            <strong>
                                KTP / PASPOR / SIM
                            </strong>
                            yang masih berlaku.

                        </div>

                    </div>


                    <!-- B -->

                    <div class="syarat-list-item">

                        <div
                            class="syarat-num"
                            style="
                                background: #eee;
                                color: #555;
                            "
                        >
                            b
                        </div>

                        <div class="syarat-text">

                            <strong>
                                Bukti Setor Asli Bank
                            </strong>
                            untuk biaya UPA.

                        </div>

                    </div>


                    <!-- C -->

                    <div class="syarat-list-item">

                        <div
                            class="syarat-num"
                            style="
                                background: #eee;
                                color: #555;
                            "
                        >
                            c
                        </div>

                        <div class="syarat-text">

                            <strong>
                                Pas Foto Berwarna 3x4
                            </strong>
                            (4 Lembar).

                            <br>

                            <small
                                style="
                                    color: #1e3a8a;
                                    font-weight: bold;
                                "
                            >
                                *Latar Belakang Biru
                            </small>

                        </div>

                    </div>


                    <!-- D -->

                    <div class="syarat-list-item">

                        <div
                            class="syarat-num"
                            style="
                                background: #eee;
                                color: #555;
                            "
                        >
                            d
                        </div>

                        <div class="syarat-text">

                            Fotokopi
                            <strong>
                                Ijazah S1 Hukum
                            </strong>
                            yang telah dilegalisir asli
                            (Legalisir Basah) oleh perguruan
                            tinggi yang mengeluarkan.

                        </div>

                    </div>


                    <!-- E -->

                    <div class="syarat-list-item">

                        <div
                            class="syarat-num"
                            style="
                                background: #eee;
                                color: #555;
                            "
                        >
                            e
                        </div>

                        <div class="syarat-text">

                            Fotokopi
                            <strong>
                                Sertifikat PKPA
                            </strong>
                            yang dikeluarkan oleh PERADI.

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- =========================================
             JADWAL & DOWNLOAD FORMULIR
        ========================================= -->

        <div class="jadwal-banner">


            <div class="banner-text">

                <h3>

                    <i class="fa-regular fa-calendar-check"></i>

                    Jadwal UPA Gelombang I 2026

                </h3>

                <p>

                    Pendaftaran dibuka mulai
                    1 Januari - 28 Februari 2026.

                    Segera lengkapi berkas Anda dan unduh
                    formulir pendaftaran di sini.

                </p>

            </div>


            <a
                href="uploads/layanan/FORMULIR PENDAFTARAN UPA 2026.pdf"
                download
                class="btn-download-form"
            >

                <i class="fa-solid fa-file-arrow-down"></i>

                Unduh Formulir

            </a>


        </div>


    </div>


    <!-- FOOTER -->

    <?php include 'footer.php'; ?>


</body>

</html>