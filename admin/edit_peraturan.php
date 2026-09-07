<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status_login'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: peraturan.php");
    exit;
}

$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM peraturan WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Peraturan - PERADI</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">

    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 800px;
            margin: 0 auto;
        }
        .form-section-title {
            color: #f59e0b;
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
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: Poppins;
        }
        .file-hint {
            font-size: 0.8rem;
            color: #ef4444;
            margin-top: 5px;
            display: block;
        }
        .btn-submit {
            background: #f59e0b;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            font-weight: 600;
        }
        .btn-submit:hover {
            background: #d97706;
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

    <a href="peraturan.php" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Batal & Kembali
    </a>

    <div class="form-container">
        <h2 style="margin-bottom:30px;text-align:center;">Edit Peraturan</h2>

        <form action="proses_edit_peraturan.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="id" value="<?= $data['id']; ?>">
            <input type="hidden" name="file_lama" value="<?= $data['file_pdf']; ?>">

            <div class="form-section-title">
                <i class="fa-solid fa-file-pen"></i> Informasi Peraturan
            </div>

            <div class="form-group">
                <label>Judul Peraturan</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($data['judul']); ?>" required>
            </div>

            <div class="form-group">
                <label>Isi / Deskripsi Peraturan</label>
                <textarea name="isi" rows="5"><?= htmlspecialchars($data['isi']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Ganti File PDF (Opsional)</label>
                <?php if ($data['file_pdf']) : ?>
                    <small style="color:#22c55e;">
                        <i class="fa-solid fa-check"></i> File PDF sudah ada
                    </small><br>
                <?php endif; ?>
                <input type="file" name="file_pdf" accept=".pdf">
                <small class="file-hint">
                    * Biarkan kosong jika tidak ingin mengganti file
                </small>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-save"></i> Simpan Perubahan
            </button>
        </form>
    </div>

</div>

</body>
</html>
