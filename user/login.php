<?php
session_start();

require_once __DIR__ . '/../koneksi.php';

/*
|--------------------------------------------------------------------------
| JIKA SUDAH LOGIN
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['user_login']) && $_SESSION['user_login'] === true) {
    header("Location: dashboard.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| PROSES LOGIN
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login    = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | VALIDASI INPUT
    |--------------------------------------------------------------------------
    */
    if ($login === '' || $password === '') {
        header("Location: login.php?pesan=gagal");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | CARI USER
    |--------------------------------------------------------------------------
    | Pastikan tabel database kamu bernama "users", bukan "user".
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM users
         WHERE username = ? OR email = ?
         LIMIT 1"
    );

    if (!$stmt) {
        die("Error database: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ss", $login, $login);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);


    /*
    |--------------------------------------------------------------------------
    | USER TIDAK DITEMUKAN
    |--------------------------------------------------------------------------
    */

    if (!$result || mysqli_num_rows($result) === 0) {

        mysqli_stmt_close($stmt);

        header("Location: login.php?pesan=notfound");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | AMBIL DATA USER
    |--------------------------------------------------------------------------
    */

    $user = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    /*
    |--------------------------------------------------------------------------
    | KHUSUS ROLE USER
    |--------------------------------------------------------------------------
    */

    if (($user['role'] ?? '') !== 'user') {

        header("Location: login.php?pesan=gagal");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | CEK PASSWORD
    |--------------------------------------------------------------------------
    */

    $stored_password = $user['password'] ?? '';

    $password_valid = false;


    // Password normal berupa password_hash()
    if (password_verify($password, $stored_password)) {

        $password_valid = true;

    }

    /*
    |--------------------------------------------------------------------------
    | SUPPORT PASSWORD LAMA PLAINTEXT
    |--------------------------------------------------------------------------
    | Kalau sebelumnya database masih menyimpan password biasa,
    | password tersebut akan otomatis diubah menjadi hash.
    |--------------------------------------------------------------------------
    */

    elseif (
        $stored_password !== '' &&
        hash_equals((string)$stored_password, (string)$password)
    ) {

        $password_valid = true;

        $new_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $id_user = (int)$user['id_user'];

        $update = mysqli_prepare(
            $conn,
            "UPDATE users SET password = ? WHERE id_user = ?"
        );

        if ($update) {

            mysqli_stmt_bind_param(
                $update,
                "si",
                $new_hash,
                $id_user
            );

            mysqli_stmt_execute($update);
            mysqli_stmt_close($update);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD SALAH
    |--------------------------------------------------------------------------
    */

    if (!$password_valid) {

        header("Location: login.php?pesan=gagal");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | CEK STATUS AKUN
    |--------------------------------------------------------------------------
    */

    $status = $user['status'] ?? 'active';


    // Menunggu verifikasi admin
    if ($status === 'pending') {

        header("Location: login.php?pesan=pending");
        exit;
    }


    // Akun diblokir
    if ($status === 'banned') {

        header("Location: login.php?pesan=banned");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN BERHASIL
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);


    $_SESSION['user_id'] = $user['id_user'];

    $_SESSION['username'] = $user['username'];

    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];

    $_SESSION['email'] = $user['email'] ?? '';

    $_SESSION['role'] = 'user';

    $_SESSION['user_login'] = true;


    /*
    |--------------------------------------------------------------------------
    | LANGSUNG KE DASHBOARD
    |--------------------------------------------------------------------------
    */

    header("Location: dashboard.php");
    exit;
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

    <title>Login User - PERADI Pontianak</title>


    <!-- GOOGLE FONT -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
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
            display: flex;
            background: #fff;
        }


        /* =====================================================
           LEFT SIDE
        ===================================================== */

        .left-side {

            flex: 1.2;

            background:
                linear-gradient(
                    rgba(30, 58, 138, 0.65),
                    rgba(30, 58, 138, 0.85)
                ),
                url('../image/peradi-tower.jpg');

            background-size: cover;
            background-position: center;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;

            color: white;

            text-align: center;

            padding: 40px;
        }


        .left-side h1 {

            font-size: 3rem;

            font-weight: 800;

            letter-spacing: 2px;

            margin-bottom: 10px;
        }


        .left-side p {

            font-size: 1.1rem;

            letter-spacing: 1px;
        }


        /* =====================================================
           RIGHT SIDE
        ===================================================== */

        .right-side {

            flex: 1;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 40px;

            position: relative;
        }


        .login-card {

            width: 100%;

            max-width: 400px;
        }


        /* =====================================================
           LOGO
        ===================================================== */

        .logo-area {

            text-align: center;

            margin-bottom: 30px;

            color: #1e3a8a;
        }


        .logo-area i {

            font-size: 3rem;

            margin-bottom: 12px;
        }


        .logo-area h2 {

            font-weight: 800;

            text-transform: uppercase;
        }


        .logo-area p {

            color: #777;

            font-size: 0.85rem;

            margin-top: 8px;
        }


        /* =====================================================
           ALERT
        ===================================================== */

        .alert {

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 0.85rem;
        }


        .alert-danger {

            background: #fee2e2;

            color: #b91c1c;
        }


        .alert-success {

            background: #dcfce7;

            color: #166534;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .form-group {

            margin-bottom: 18px;
        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-size: 0.85rem;

            font-weight: 600;

            color: #555;
        }


        .form-group input {

            width: 100%;

            padding: 14px;

            border: 1px solid #ddd;

            border-radius: 8px;

            background: #f9f9f9;

            outline: none;

            font-size: 0.9rem;
        }


        .form-group input:focus {

            border-color: #1e3a8a;

            background: white;

            box-shadow:
                0 0 0 3px rgba(30,58,138,0.1);
        }


        /* =====================================================
           LOGIN BUTTON
        ===================================================== */

        .btn-login {

            width: 100%;

            padding: 14px;

            border: none;

            border-radius: 8px;

            background: #1e3a8a;

            color: white;

            font-weight: 700;

            font-size: 1rem;

            cursor: pointer;

            transition: .3s;
        }


        .btn-login:hover {

            background: #152c69;

            transform: translateY(-1px);
        }


        /* =====================================================
           DIVIDER
        ===================================================== */

        .divider {

            display: flex;

            align-items: center;

            gap: 12px;

            margin: 22px 0;

            color: #999;

            font-size: .8rem;
        }


        .divider::before,
        .divider::after {

            content: '';

            flex: 1;

            height: 1px;

            background: #ddd;
        }


        /* =====================================================
           GOOGLE
        ===================================================== */

        .google-btn {

            width: 100%;

            padding: 13px;

            background: white;

            color: #333;

            border: 1px solid #ddd;

            border-radius: 8px;

            text-decoration: none;

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 10px;

            font-weight: 600;

            font-size: .9rem;

            transition: .3s;
        }


        .google-btn:hover {

            background: #f8f8f8;

            box-shadow:
                0 4px 12px rgba(0,0,0,.08);
        }


        .google-btn i {

            color: #4285F4;

            font-size: 1.1rem;
        }


        /* =====================================================
           REGISTER
        ===================================================== */

        .register-link {

            text-align: center;

            margin-top: 25px;

            font-size: .85rem;

            color: #777;
        }


        .register-link a {

            color: #dea057;

            font-weight: 700;

            text-decoration: none;
        }


        .register-link a:hover {

            text-decoration: underline;
        }


        /* =====================================================
           BACK
        ===================================================== */

        .back-link {

            position: absolute;

            top: 20px;

            right: 25px;

            color: #999;

            text-decoration: none;

            font-size: .8rem;
        }


        .back-link:hover {

            color: #1e3a8a;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media(max-width: 768px) {

            body {

                flex-direction: column;
            }


            .left-side {

                min-height: 260px;
            }


            .right-side {

                padding: 40px 20px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     LEFT
========================================================= -->

<div class="left-side">

    <h1>PERADI</h1>

    <p>
        Sistem Informasi Sertifikasi Advokat
    </p>

</div>


<!-- =========================================================
     RIGHT
========================================================= -->

<div class="right-side">


    <a
        href="../index.php"
        class="back-link"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Kembali

    </a>


    <div class="login-card">


        <!-- LOGO -->

        <div class="logo-area">

            <i class="fa-solid fa-scale-balanced"></i>

            <h2>Login User</h2>

            <p>
                Silakan masuk untuk mengikuti proses sertifikasi
            </p>

        </div>


        <!-- =================================================
             PESAN ERROR
        ================================================== -->

        <?php if (isset($_GET['pesan'])): ?>


            <?php if ($_GET['pesan'] === 'gagal'): ?>

                <div class="alert alert-danger">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    Username/email atau password salah.

                </div>


            <?php elseif ($_GET['pesan'] === 'notfound'): ?>

                <div class="alert alert-danger">

                    <i class="fa-solid fa-user-slash"></i>

                    Akun tidak ditemukan.

                </div>


            <?php elseif ($_GET['pesan'] === 'pending'): ?>

                <div class="alert alert-danger">

                    <i class="fa-solid fa-clock"></i>

                    Akun kamu masih menunggu verifikasi admin.

                </div>


            <?php elseif ($_GET['pesan'] === 'sukses_regis'): ?>

                <div class="alert alert-success">

                    <i class="fa-solid fa-check"></i>

                    Registrasi berhasil. Silakan login.

                </div>


            <?php elseif ($_GET['pesan'] === 'banned'): ?>

                <div class="alert alert-danger">

                    <i class="fa-solid fa-ban"></i>

                    Akun kamu diblokir. Hubungi admin.

                </div>


            <?php elseif ($_GET['pesan'] === 'belum_login'): ?>

                <div class="alert alert-danger">

                    <i class="fa-solid fa-lock"></i>

                    Silakan login terlebih dahulu.

                </div>


            <?php elseif ($_GET['pesan'] === 'google_batal'): ?>

                <div class="alert alert-danger">

                    <i class="fa-brands fa-google"></i>

                    Login dengan Google dibatalkan.

                </div>

            <?php endif; ?>


        <?php endif; ?>


        <!-- =================================================
             FORM LOGIN
             SEKARANG ACTION KE FILE INI SENDIRI
        ================================================== -->

        <form
            action="login.php"
            method="POST"
        >


            <div class="form-group">

                <label>
                    Email / Username
                </label>


                <input
                    type="text"
                    name="username"
                    placeholder="Masukkan email atau username"
                    autocomplete="username"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>


                <input
                    type="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn-login"
            >

                <i class="fa-solid fa-right-to-bracket"></i>

                Masuk

            </button>


        </form>


        <!-- =================================================
             GOOGLE LOGIN
        ================================================== -->

        <div class="divider">
            ATAU
        </div>


        <a
            href="google_login.php"
            class="google-btn"
        >

            <i class="fa-brands fa-google"></i>

            Login dengan Google

        </a>


        <!-- =================================================
             REGISTER
        ================================================== -->

        <div class="register-link">

            Belum punya akun?

            <a href="register.php">
                Buat Akun
            </a>

        </div>


    </div>

</div>


</body>

</html>