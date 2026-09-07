```php
<?php

session_start();

require_once __DIR__ . '/../koneksi.php';


/*
|--------------------------------------------------------------------------
| JIKA SUDAH LOGIN
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['status_login']) &&
    $_SESSION['status_login'] === true
) {

    $role = $_SESSION['role'] ?? '';

    if ($role === 'admin' || $role === 'super_admin') {

        header("Location: dashboard.php");
        exit;

    }

    if ($role === 'user') {

        header("Location: ../user/dashboard.php");
        exit;

    }
}


/*
|--------------------------------------------------------------------------
| COOKIE INGAT SAYA
|--------------------------------------------------------------------------
*/

$cookie_email = $_COOKIE['email'] ?? '';


/*
|--------------------------------------------------------------------------
| PROSES LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ingat_aku = isset($_POST['ingat_aku']);


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
    */

    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM users
         WHERE username = ?
         OR email = ?
         LIMIT 1"
    );


    if (!$stmt) {

        die(
            "Error database: " .
            mysqli_error($conn)
        );

    }


    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $login,
        $login
    );


    mysqli_stmt_execute($stmt);


    $result = mysqli_stmt_get_result($stmt);


    /*
    |--------------------------------------------------------------------------
    | AKUN TIDAK DITEMUKAN
    |--------------------------------------------------------------------------
    */

    if (
        !$result ||
        mysqli_num_rows($result) === 0
    ) {

        mysqli_stmt_close($stmt);

        header(
            "Location: login.php?pesan=notfound"
        );

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
    | CEK ROLE ADMIN
    |--------------------------------------------------------------------------
    */

    $role = $user['role'] ?? '';


    if (
        $role !== 'admin' &&
        $role !== 'super_admin'
    ) {

        header(
            "Location: login.php?pesan=bukan_admin"
        );

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | CEK PASSWORD
    |--------------------------------------------------------------------------
    */

    $stored_password =
        $user['password'] ?? '';

    $password_valid = false;


    /*
    |--------------------------------------------------------------------------
    | PASSWORD HASH
    |--------------------------------------------------------------------------
    */

    if (
        password_verify(
            $password,
            $stored_password
        )
    ) {

        $password_valid = true;

    }


    /*
    |--------------------------------------------------------------------------
    | SUPPORT PASSWORD LAMA PLAINTEXT
    |--------------------------------------------------------------------------
    */

    elseif (
        $stored_password !== '' &&
        hash_equals(
            (string)$stored_password,
            (string)$password
        )
    ) {

        $password_valid = true;


        /*
        | Upgrade password lama
        | menjadi password_hash()
        */

        $new_hash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        $id_user =
            (int)$user['id_user'];


        $update = mysqli_prepare(
            $conn,
            "UPDATE users
             SET password = ?
             WHERE id_user = ?"
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

        header(
            "Location: login.php?pesan=gagal"
        );

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | CEK STATUS AKUN
    |--------------------------------------------------------------------------
    */

    $status =
        $user['status'] ?? 'active';


    /*
    | Pending
    */

    if ($status === 'pending') {

        header(
            "Location: login.php?pesan=pending"
        );

        exit;

    }


    /*
    | Banned
    */

    if ($status === 'banned') {

        header(
            "Location: login.php?pesan=banned"
        );

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | LOGIN BERHASIL
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);


    $_SESSION['user_id'] =
        $user['id_user'];

    $_SESSION['username'] =
        $user['username'];

    $_SESSION['nama_lengkap'] =
        $user['nama_lengkap'];

    $_SESSION['email'] =
        $user['email'] ?? '';

    $_SESSION['role'] =
        $role;

    $_SESSION['status_login'] =
        true;


    /*
    |--------------------------------------------------------------------------
    | INGAT SAYA
    |--------------------------------------------------------------------------
    */

    if ($ingat_aku) {

        /*
        | Simpan hanya email/username.
        | Password JANGAN disimpan di cookie.
        */

        setcookie(
            'email',
            $login,
            time() + (86400 * 30),
            '/',
            '',
            false,
            true
        );

    } else {

        setcookie(
            'email',
            '',
            time() - 3600,
            '/'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECT ADMIN
    |--------------------------------------------------------------------------
    */

    header("Location: ../admin/dashboard.php");
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

    <title>Login Admin - PERADI Pontianak</title>


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

            font-family:
                'Montserrat',
                sans-serif;

        }


        body {

            min-height: 100vh;

            display: flex;

            background: #fff;

        }


        /*
        =====================================================
        LEFT SIDE
        =====================================================
        */

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


        /*
        =====================================================
        RIGHT SIDE
        =====================================================
        */

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


        /*
        =====================================================
        LOGO
        =====================================================
        */

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


        /*
        =====================================================
        ALERT
        =====================================================
        */

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


        .alert-warning {

            background: #fff7ed;

            color: #c2410c;

        }


        .alert-success {

            background: #dcfce7;

            color: #166534;

        }


        /*
        =====================================================
        FORM
        =====================================================
        */

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
                0 0 0 3px
                rgba(30,58,138,0.1);

        }


        /*
        =====================================================
        REMEMBER ME
        =====================================================
        */

        .remember {

            display: flex;

            align-items: center;

            gap: 8px;

            margin-bottom: 20px;

            font-size: 0.85rem;

            color: #666;

        }


        .remember input {

            width: 16px;

            height: 16px;

            accent-color: #1e3a8a;

        }


        .remember label {

            cursor: pointer;

        }


        /*
        =====================================================
        LOGIN BUTTON
        =====================================================
        */

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


        /*
        =====================================================
        REGISTER
        =====================================================
        */

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


        /*
        =====================================================
        BACK
        =====================================================
        */

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


        /*
        =====================================================
        RESPONSIVE
        =====================================================
        */

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


<!-- =====================================================
     LEFT
===================================================== -->

<div class="left-side">

    <h1>PERADI</h1>

    <p>
        Sistem Informasi Sertifikasi Advokat
    </p>

</div>


<!-- =====================================================
     RIGHT
===================================================== -->

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

            <i class="fa-solid fa-user-shield"></i>

            <h2>Login Admin</h2>

            <p>
                Silakan masuk ke sistem administrasi
            </p>

        </div>


        <!-- =================================================
             PESAN
        ================================================== -->

        <?php if (isset($_GET['pesan'])): ?>


            <?php if ($_GET['pesan'] === 'gagal'): ?>

                <div class="alert alert-danger">

                    <i
                        class="fa-solid fa-circle-exclamation"
                    ></i>

                    Username/email atau password salah.

                </div>


            <?php elseif ($_GET['pesan'] === 'notfound'): ?>

                <div class="alert alert-danger">

                    <i
                        class="fa-solid fa-user-slash"
                    ></i>

                    Akun tidak ditemukan.

                </div>


            <?php elseif ($_GET['pesan'] === 'bukan_admin'): ?>

                <div class="alert alert-danger">

                    <i
                        class="fa-solid fa-user-lock"
                    ></i>

                    Akun ini bukan akun administrator.

                </div>


            <?php elseif ($_GET['pesan'] === 'pending'): ?>

                <div class="alert alert-warning">

                    <i
                        class="fa-solid fa-clock"
                    ></i>

                    Akun masih menunggu verifikasi.

                </div>


            <?php elseif ($_GET['pesan'] === 'banned'): ?>

                <div class="alert alert-danger">

                    <i
                        class="fa-solid fa-ban"
                    ></i>

                    Akun ini telah diblokir.

                </div>


            <?php elseif ($_GET['pesan'] === 'sukses_regis'): ?>

                <div class="alert alert-success">

                    <i
                        class="fa-solid fa-check"
                    ></i>

                    Registrasi berhasil. Silakan login.

                </div>


            <?php endif; ?>


        <?php endif; ?>


        <!-- =================================================
             FORM LOGIN
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
                    value="<?= htmlspecialchars($cookie_email); ?>"
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


            <div class="remember">

                <input
                    type="checkbox"
                    name="ingat_aku"
                    id="ingat_aku"
                >

                <label for="ingat_aku">
                    Ingat Saya
                </label>

            </div>


            <button
                type="submit"
                class="btn-login"
            >

                <i
                    class="fa-solid fa-right-to-bracket"
                ></i>

                Masuk Sistem

            </button>


        </form>


        <!-- =================================================
             INFO
        ================================================== -->

        <div class="register-link">

            <i
                class="fa-solid fa-shield-halved"
            ></i>

            Akses khusus administrator PERADI

        </div>


    </div>

</div>


</body>

</html>
```
