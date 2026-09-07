<?php
session_start();
include 'koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php");
    exit;
}

// CEK ID
if (!isset($_GET['id'])) {
    header("Location: struktur_pengurus.php");
    exit;
}

$id = intval($_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM struktur_pengurus WHERE id = $id");
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
    <title>Edit Struktur Pengurus</title>

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

        .preview-img {
            margin-top: 10px;
            max-width: 120px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
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
        <i class="fa-solid fa-arrow-left"></i> Batal & Kembali
    </a>

    <div class="form-container">
        <h2 style="margin-bottom:30px;text-align:center;">
            Edit Struktur Pengurus
        </h2>

        <form action="proses_edit_struktur.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="id" value="<?= $data['id']; ?>">
            <input type="hidden" name="foto_lama" value="<?= $data['foto']; ?>">

            <div class="form-section-title">
                <i class="fa-solid fa-user-pen"></i> Data Pengurus
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama"
                       value="<?= htmlspecialchars($data['nama']); ?>" required>
            </div>

            <div class="form-group">
                <label>Jabatan</label>
                <input type="text" name="jabatan"
                       value="<?= htmlspecialchars($data['jabatan']); ?>" required>
            </div>

            <div class="form-group">
                <label>Ganti Foto (Opsional)</label>

                <?php if (!empty($data['foto']) && file_exists("../uploads/struktur/".$data['foto'])): ?>
                    <img src="../uploads/struktur/<?= $data['foto']; ?>" class="preview-img">
                <?php endif; ?>

                <input type="file" name="foto" accept=".jpg,.jpeg,.png">
                <small class="file-hint">
                    * Biarkan kosong jika tidak ingin mengganti foto
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
