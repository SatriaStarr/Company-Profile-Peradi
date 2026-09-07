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
    <title>Tambah Galeri - PERADI</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">

    <style>
        /* ===== KONSISTEN DENGAN DATA ADVOKAT ===== */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 900px;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: Poppins;
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
            transition: 0.3s;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: #1e40af;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #64748b;
        }
    </style>
</head>

<body>

<div class="main-content" style="margin-left:0;width:100%;padding:40px;">

    <a href="galeri.php" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Galeri
    </a>

    <div class="form-container">
        <h2 style="margin-bottom:30px;text-align:center;">
            Form Tambah Galeri
        </h2>

        <form action="proses_tambah_galeri.php" method="POST" enctype="multipart/form-data">

            <div class="form-section-title">
                <i class="fa-solid fa-image"></i> Informasi Galeri
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Judul Foto</label>
                    <input type="text" name="judul" placeholder="Contoh: Kegiatan PKPA 2024" required>
                </div>

                <div class="form-group">
                    <label>Upload Foto (JPG / PNG)</label>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-save"></i> Simpan Galeri
            </button>

        </form>
    </div>
</div>

</body>
</html>
