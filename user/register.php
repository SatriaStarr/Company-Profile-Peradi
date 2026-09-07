<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Daftar User - PERADI Pontianak</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">


    <style>

        * {
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {

            margin: 0;

            min-height: 100vh;

            display: flex;

            justify-content: center;

            align-items: center;

            background: #f4f6f9;

            padding: 20px;

        }


        .card {

            width: 100%;

            max-width: 450px;

            background: white;

            padding: 40px;

            border-radius: 12px;

            box-shadow:
                0 10px 30px rgba(0,0,0,.1);

            border-top:
                5px solid #1e3a8a;

        }


        h2 {

            text-align: center;

            color: #1e3a8a;

            margin: 0 0 8px;

        }


        .description {

            text-align: center;

            color: #777;

            font-size: .85rem;

            margin-bottom: 30px;

        }


        label {

            display: block;

            margin-bottom: 6px;

            font-size: .85rem;

            font-weight: 600;

            color: #555;

        }


        input {

            width: 100%;

            padding: 13px;

            margin-bottom: 18px;

            border: 1px solid #ddd;

            border-radius: 8px;

            outline: none;

        }


        input:focus {

            border-color: #1e3a8a;

            box-shadow:
                0 0 0 3px rgba(30,58,138,.1);

        }


        button {

            width: 100%;

            padding: 14px;

            background: #1e3a8a;

            color: white;

            border: none;

            border-radius: 8px;

            font-weight: 700;

            cursor: pointer;

        }


        button:hover {

            background: #152c69;

        }


        .google {

            display: block;

            width: 100%;

            padding: 13px;

            margin-top: 15px;

            text-align: center;

            text-decoration: none;

            color: #333;

            border: 1px solid #ddd;

            border-radius: 8px;

            font-size: .9rem;

            font-weight: 600;

        }


        .google:hover {

            background: #f7f7f7;

        }


        .link {

            text-align: center;

            margin-top: 25px;

            color: #777;

            font-size: .85rem;

        }


        .link a {

            color: #dea057;

            font-weight: 700;

            text-decoration: none;

        }

    </style>

</head>


<body>


<div class="card">

    <h2>
        Buat Akun
    </h2>


    <p class="description">

        Daftar sebagai calon peserta sertifikasi advokat

    </p>

    <?php if (isset($_GET['pesan'])): ?>
        <div style="background:#fee2e2;color:#b91c1c;padding:12px;border-radius:8px;margin-bottom:18px;font-size:.82rem;">
            <?php
            $pesan = $_GET['pesan'];
            if ($pesan === 'username') echo 'Username sudah digunakan.';
            elseif ($pesan === 'email_terdaftar') echo 'Email sudah terdaftar.';
            elseif ($pesan === 'email') echo 'Format email tidak valid.';
            elseif ($pesan === 'password') echo 'Password minimal 6 karakter.';
            else echo 'Semua data wajib diisi.';
            ?>
        </div>
    <?php endif; ?>


    <form action="proses_register.php" method="POST">


        <label>
            Nama Lengkap
        </label>

        <input
            type="text"
            name="nama_lengkap"
            placeholder="Nama lengkap"
            required
        >


        <label>
            Email
        </label>

        <input
            type="email"
            name="email"
            placeholder="nama@email.com"
            required
        >


        <label>
            Username
        </label>

        <input
            type="text"
            name="username"
            placeholder="Username"
            required
        >


        <label>
            Password
        </label>

        <input
            type="password"
            name="password"
            placeholder="Minimal 6 karakter"
            minlength="6"
            required
        >


        <button type="submit">

            Daftar Sekarang

        </button>


    </form>


    <a href="google_login.php" class="google">

        Login / Daftar dengan Google

    </a>


    <div class="link">

        Sudah punya akun?

        <a href="login.php">
            Login
        </a>

    </div>


</div>


</body>

</html>