<?php require_once __DIR__ . '/partials/csrf.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrasi Admin - PERADI</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }

        .card {
            background: white;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-top: 5px solid #1e3a8a;
        }

        .logo {
            text-align: center;
            color: #1e3a8a;
            font-size: 45px;
            margin-bottom: 10px;
        }

        h2 {
            color: #1e3a8a;
            margin-bottom: 5px;
            text-align: center;
            margin-top: 0;
        }

        p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
            transition: 0.3s;
        }

        input:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.1);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #152c69;
        }

        .link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .link a {
            color: #dea057;
            text-decoration: none;
            font-weight: bold;
        }

        .link a:hover {
            text-decoration: underline;
        }

        .back {
            text-align: center;
            margin-top: 15px;
        }

        .back a {
            color: #777;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .back a:hover {
            color: #1e3a8a;
        }

        .info {
            background: #eff6ff;
            color: #1e3a8a;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            margin-bottom: 20px;
            line-height: 1.5;
        }
    </style>
</head>

<body>

<div class="card">

    <div class="logo">
        <i class="fa-solid fa-scale-balanced"></i>
    </div>

    <h2>Buat Akun Admin</h2>

    <p>
        Daftar akun administrator sistem.
    </p>

    <div class="info">
        Akun ini digunakan untuk mengelola sistem. Setelah mendaftar, akun admin menunggu verifikasi Super Admin.
    </div>

    <form action="proses_register.php" method="POST">
        <?= csrf_field() ?>

        <label>Nama Lengkap</label>
        <input
            type="text"
            name="nama_lengkap"
            placeholder="Masukkan nama lengkap"
            required
        >

        <label>Username</label>
        <input
            type="text"
            name="username"
            placeholder="Masukkan username"
            required
        >

        <label>Password</label>
        <input
            type="password"
            name="password"
            placeholder="Buat password"
            required
        >

        <button type="submit">
            Daftar Sekarang
        </button>

    </form>

    <div class="link">
        Sudah punya akun?
        <a href="login.php">Login Disini</a>
    </div>

    <div class="back">
        <a href="../index.php">
            ← Kembali ke halaman utama
        </a>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

</body>
</html>