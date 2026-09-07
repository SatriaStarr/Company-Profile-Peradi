<?php
session_start();
include 'koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php?pesan=belum_login");
    exit;
}

// KATEGORI LAYANAN
$kategori = 'sumpah';

// AMBIL DATA
$query = mysqli_query(
    $conn,
    "SELECT * FROM layanan WHERE kategori='$kategori' LIMIT 1"
);

$data = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Layanan Sumpah Advokat</title>

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS UTAMA -->
    <link rel="stylesheet" href="dashboard.css">

    <!-- ICON -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .content-box {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 10px 18px;
            background: #3b82f6;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-download:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .empty-data {
            color: #666;
            font-style: italic;
        }
    </style>
</head>

<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="page-header">
        <div>
            <h1><?= htmlspecialchars($data['judul'] ?? 'Pengangkatan & Sumpah Advokat'); ?></h1>
            <p>Informasi Pengangkatan & Sumpah Advokat</p>
        </div>
    </div>

    <!-- KONTEN -->
    <div class="recent-section">
        <div class="content-box">

            <?php if ($data): ?>

                <p><?= nl2br(htmlspecialchars($data['deskripsi'])); ?></p>

                <?php if (!empty($data['file'])): ?>
                    <a href="../uploads/layanan/<?= htmlspecialchars($data['file']); ?>"
                       class="btn-download"
                       download>
                        <i class="fa-solid fa-download"></i> Unduh File
                    </a>
                <?php endif; ?>

            <?php else: ?>

                <p class="empty-data">
                    Data layanan Pengangkatan & Sumpah Advokat belum tersedia.
                </p>

            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>

</html>
