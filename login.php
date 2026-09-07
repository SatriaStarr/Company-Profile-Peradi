<?php
session_start();

/*
|--------------------------------------------------------------------------
| JIKA SUDAH LOGIN
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['user_login']) &&
    $_SESSION['user_login'] === true
) {
    header("Location: user/dashboard.php");
    exit;
}

if (
    isset($_SESSION['status_login']) &&
    $_SESSION['status_login'] === true
) {
    if (
        isset($_SESSION['role']) &&
        (
            $_SESSION['role'] === 'admin' ||
            $_SESSION['role'] === 'super_admin'
        )
    ) {
        header("Location: admin/dashboard.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - DPC PERADI Pontianak</title>

    <!-- GOOGLE FONT -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <!-- FONT AWESOME -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            min-height: 100vh;
            background:
                linear-gradient(
                    rgba(30, 58, 138, 0.88),
                    rgba(21, 44, 105, 0.95)
                ),
                url('image/peradi-tower.jpg');

            background-size: cover;
            background-position: center;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 30px;
        }

        /* =========================
           CONTAINER
        ========================= */

        .login-container {
            width: 100%;
            max-width: 900px;

            background: white;

            border-radius: 20px;

            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(0,0,0,0.25);

            display: flex;

            min-height: 520px;
        }

        /* =========================
           LEFT
        ========================= */

        .left-side {
            width: 45%;

            background:
                linear-gradient(
                    rgba(30,58,138,0.85),
                    rgba(30,58,138,0.95)
                ),
                url('image/peradi-tower.jpg');

            background-size: cover;
            background-position: center;

            color: white;

            display: flex;
            flex-direction: column;

            justify-content: center;
            align-items: center;

            text-align: center;

            padding: 40px;
        }

        .left-side i {
            font-size: 4rem;

            color: #dea057;

            margin-bottom: 25px;
        }

        .left-side h1 {
            font-size: 2.8rem;

            font-weight: 800;

            letter-spacing: 2px;

            margin-bottom: 10px;
        }

        .left-side h2 {
            font-size: 1rem;

            font-weight: 400;

            letter-spacing: 2px;

            color: #dea057;

            margin-bottom: 20px;
        }

        .left-side p {
            font-size: .9rem;

            line-height: 1.7;

            opacity: .85;

            max-width: 300px;
        }

        /* =========================
           RIGHT
        ========================= */

        .right-side {
            width: 55%;

            padding: 50px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;
        }

        .logo-area {
            text-align: center;

            margin-bottom: 35px;
        }

        .logo-area i {
            font-size: 2.8rem;

            color: #1e3a8a;

            margin-bottom: 12px;
        }

        .logo-area h2 {
            color: #1e3a8a;

            font-size: 1.7rem;

            font-weight: 800;

            margin-bottom: 8px;
        }

        .logo-area p {
            color: #777;

            font-size: .85rem;
        }

        /* =========================
           ROLE BUTTON
        ========================= */

        .role-container {
            width: 100%;

            max-width: 380px;
        }

        .role-title {
            text-align: center;

            font-size: .9rem;

            color: #666;

            margin-bottom: 18px;

            font-weight: 600;
        }

        .role-button {
            width: 100%;

            padding: 18px 20px;

            border-radius: 12px;

            text-decoration: none;

            display: flex;

            align-items: center;

            gap: 18px;

            margin-bottom: 15px;

            transition: .3s;

            border: 1px solid #eee;

            background: #fff;

            box-shadow:
                0 5px 15px rgba(0,0,0,0.05);
        }

        .role-button:hover {
            transform: translateY(-3px);

            box-shadow:
                0 10px 25px rgba(0,0,0,0.1);
        }

        .role-icon {
            width: 52px;
            height: 52px;

            border-radius: 12px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 1.4rem;

            flex-shrink: 0;
        }

        .user-button .role-icon {
            background: #e8eefc;

            color: #1e3a8a;
        }

        .admin-button .role-icon {
            background: #fff1df;

            color: #c47d20;
        }

        .role-info {
            flex: 1;
        }

        .role-info h3 {
            font-size: 1rem;

            color: #1e3a8a;

            font-weight: 800;

            margin-bottom: 5px;
        }

        .admin-button .role-info h3 {
            color: #9b641c;
        }

        .role-info p {
            font-size: .75rem;

            color: #888;

            line-height: 1.4;
        }

        .arrow {
            color: #aaa;

            font-size: .9rem;
        }

        .back-home {
            margin-top: 20px;

            color: #888;

            text-decoration: none;

            font-size: .8rem;

            transition: .3s;
        }

        .back-home:hover {
            color: #1e3a8a;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 768px) {

            body {
                padding: 15px;
            }

            .login-container {
                flex-direction: column;

                max-width: 500px;
            }

            .left-side {
                width: 100%;

                min-height: 230px;

                padding: 30px;
            }

            .left-side i {
                font-size: 2.8rem;

                margin-bottom: 15px;
            }

            .left-side h1 {
                font-size: 2.2rem;
            }

            .left-side p {
                display: none;
            }

            .right-side {
                width: 100%;

                padding: 40px 25px;
            }

        }

    </style>

</head>

<body>

<div class="login-container">

    <!-- =========================
         LEFT
    ========================= -->

    <div class="left-side">

        <i class="fa-solid fa-scale-balanced"></i>

        <h1>PERADI</h1>

        <h2>DPC PERADI PONTIANAK</h2>

        <p>
            Sistem Informasi Sertifikasi dan
            Pelayanan Advokat.
        </p>

    </div>


    <!-- =========================
         RIGHT
    ========================= -->

    <div class="right-side">

        <div class="logo-area">

            <i class="fa-solid fa-right-to-bracket"></i>

            <h2>Masuk ke Sistem</h2>

            <p>
                Silakan pilih jenis akun Anda
            </p>

        </div>


        <div class="role-container">

            <div class="role-title">
                MASUK SEBAGAI
            </div>


            <!-- =========================
                 USER
            ========================= -->

            <a
                href="user/login.php"
                class="role-button user-button"
            >

                <div class="role-icon">

                    <i class="fa-solid fa-user"></i>

                </div>

                <div class="role-info">

                    <h3>Login User</h3>

                    <p>
                        Untuk peserta PKPA, UPA,
                        dan layanan lainnya.
                    </p>

                </div>

                <div class="arrow">

                    <i class="fa-solid fa-chevron-right"></i>

                </div>

            </a>


            <!-- =========================
                 ADMIN
            ========================= -->

            <a
                href="admin/login.php"
                class="role-button admin-button"
            >

                <div class="role-icon">

                    <i class="fa-solid fa-user-shield"></i>

                </div>

                <div class="role-info">

                    <h3>Login Admin</h3>

                    <p>
                        Untuk pengelolaan data dan
                        administrasi sistem.
                    </p>

                </div>

                <div class="arrow">

                    <i class="fa-solid fa-chevron-right"></i>

                </div>

            </a>

        </div>


        <a
            href="index.php"
            class="back-home"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Kembali ke Website

        </a>

    </div>

</div>

</body>

</html>