<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status_login'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: galeri.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM galeri WHERE id='$id'");
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
    <title>Edit Galeri</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 700px;
            margin: 40px auto;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { font-weight: 500; display:block; margin-bottom:6px; }
        .form-group input { width:100%; padding:10px; }
        .btn-submit {
            background:#2563eb;
            color:#fff;
            border:none;
            padding:12px;
            width:100%;
            border-radius:6px;
            cursor:pointer;
        }
        img.preview {
            max-width:150px;
            border-radius:8px;
            margin-bottom:10px;
        }
    </style>
</head>

<body>

<div class="main-content" style="margin-left:0;width:100%;padding:40px;">

    <a href="galeri.php" style="color:#64748b;">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>

    <div class="form-container">
        <h2 style="text-align:center;margin-bottom:25px;">Edit Galeri</h2>

        <form action="proses_edit_galeri.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="id" value="<?= $data['id']; ?>">
            <input type="hidden" name="foto_lama" value="<?= $data['foto']; ?>">

            <div class="form-group">
                <label>Judul</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($data['judul']); ?>" required>
            </div>

            <div class="form-group">
                <label>Foto Saat Ini</label><br>
                <?php if ($data['foto'] && file_exists("../uploads/galeri/".$data['foto'])): ?>
                    <img src="../uploads/galeri/<?= $data['foto']; ?>" class="preview">
                <?php else: ?>
                    <small>Tidak ada foto</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Ganti Foto (Opsional)</label>
                <input type="file" name="foto" accept="image/*">
                <small style="color:#ef4444;">Biarkan kosong jika tidak diganti</small>
            </div>

            <button class="btn-submit">
                <i class="fa-solid fa-save"></i> Simpan Perubahan
            </button>
        </form>
    </div>

    </div> <!-- end main-content -->

    <!-- VIEWER MODAL -->
    <?php include __DIR__ . '/partials/viewer_modal.php'; ?>

    <!-- SCRIPT VIEWER (WAJIB DI SINI) -->
    <script>
    function openViewer(title, file) {
        document.getElementById('viewerModal').style.display = 'flex';
        document.getElementById('viewerTitle').innerText = title;

        document.getElementById('pdfFrame').style.display = 'none';
        document.getElementById('imageFrame').style.display = 'none';

        if (file.toLowerCase().endsWith('.pdf')) {
            document.getElementById('pdfFrame').src = file;
            document.getElementById('pdfFrame').style.display = 'block';
        } else {
            document.getElementById('imageFrame').src = file;
            document.getElementById('imageFrame').style.display = 'block';
        }
    }

    function closeViewer() {
        document.getElementById('viewerModal').style.display = 'none';
        document.getElementById('pdfFrame').src = '';
        document.getElementById('imageFrame').src = '';
    }
    </script>

</body>
</html>



