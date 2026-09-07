<?php
session_start();
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Struktur Pengurus</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">

    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 700px;
            margin: 0 auto;
        }

        .form-section-title {
            color: #1e3a8a;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: Poppins;
        }

        .file-hint {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 5px;
            display: block;
        }

        .btn-submit {
            background: #1e3a8a;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: #1e40af;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #64748b;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="main-content" style="margin-left:0;width:100%;padding:40px;">

    <a href="struktur_pengurus.php" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Struktur Pengurus
    </a>

    <div class="form-container">
        <h2 style="margin-bottom:30px;text-align:center;">
            Form Tambah Struktur Pengurus
        </h2>

        <form action="proses_tambah_struktur.php" method="POST" enctype="multipart/form-data">

            <div class="form-section-title">
                <i class="fa-solid fa-users"></i> Data Pengurus
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Contoh: Muhammad Idris, S.H." required>
            </div>

            <div class="form-group">
                <label>Jabatan</label>
                <input type="text" name="jabatan" placeholder="Contoh: Ketua DPC" required>
            </div>

            <div class="form-group">
                <label>Upload Foto</label>
                <input type="file" name="foto" accept=".jpg,.jpeg,.png" required>
                <small class="file-hint">
                    * Format JPG / PNG, disarankan foto formal
                </small>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-save"></i> Simpan Data Pengurus
            </button>

        </form>
    </div>
</div>

</body>
</html>
