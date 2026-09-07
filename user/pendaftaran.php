<?php
session_start();
require_once __DIR__ . '/functions.php';

require_user();

$pesan = $_GET['pesan'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pendaftaran Layanan - PERADI Pontianak</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .wrap {
            max-width: 1100px;
            margin: 45px auto;
            padding: 20px;
        }

        .header {
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 2rem;
            font-weight: 800;
        }

        .header p {
            margin-top: 8px;
            color: #64748b;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            background: #fee2e2;
            color: #b91c1c;
            margin-bottom: 25px;
        }

        .services {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .06);
            border-top: 5px solid #dea057;
            transition: .3s;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, .1);
        }

        .service-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #eff6ff;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            margin-bottom: 20px;
        }

        .service-card h2 {
            color: #1e3a8a;
            margin: 0 0 10px;
        }

        .service-card p {
            color: #64748b;
            line-height: 1.7;
        }

        .price {
            background: #eff6ff;
            color: #1e3a8a;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            font-weight: 700;
        }

        .btn {
            display: inline-block;
            padding: 13px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            transition: .3s;
        }

        .btn-primary {
            background: #1e3a8a;
            color: white;
        }

        .btn-primary:hover {
            background: #152c69;
        }

        .btn-back {
            margin-top: 30px;
            background: #e5e7eb;
            color: #333;
        }

        @media(max-width: 768px) {
            .services {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="wrap">

    <div class="header">
        <h1>
            <i class="fa-solid fa-file-signature"></i>
            Pendaftaran Layanan
        </h1>

        <p>
            Pilih layanan sertifikasi yang ingin Anda ikuti.
        </p>
    </div>


    <?php if ($pesan): ?>

        <div class="alert">
            <i class="fa-solid fa-circle-exclamation"></i>

            Gagal membuat pendaftaran.
            Pastikan data valid dan Anda belum memiliki pendaftaran aktif
            untuk layanan yang sama.
        </div>

    <?php endif; ?>


    <div class="services">

        <!-- PKPA -->
        <div class="service-card">

            <div class="service-icon">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>

            <h2>PKPA</h2>

            <p>
                Pendidikan Khusus Profesi Advokat sebagai salah satu
                tahapan untuk mengikuti proses sertifikasi advokat.
            </p>

            <div class="price">
                Biaya: Rp 8.000.000
            </div>

            <a href="pendaftaran_pkpa.php" class="btn btn-primary">
                <i class="fa-solid fa-arrow-right"></i>
                Daftar PKPA
            </a>

        </div>


        <!-- UPA -->
        <div class="service-card">

            <div class="service-icon">
                <i class="fa-solid fa-file-circle-check"></i>
            </div>

            <h2>UPA</h2>

            <p>
                Ujian Profesi Advokat sebagai bagian dari proses
                sertifikasi dan pengangkatan advokat.
            </p>

            <div class="price">
                Biaya: Rp 2.500.000
            </div>

            <a href="pendaftaran_upa.php" class="btn btn-primary">
                <i class="fa-solid fa-arrow-right"></i>
                Daftar UPA
            </a>

        </div>

    </div>


    <a href="dashboard.php" class="btn btn-back">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Dashboard
    </a>

</div>

</body>
</html>