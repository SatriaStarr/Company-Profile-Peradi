<?php
session_start();
include 'koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php?pesan=belum_login");
    exit;
}

if (!in_array($_SESSION['role'] ?? '', ['admin', 'super_admin'], true)) {
    header("Location: index.php?pesan=no_access");
    exit;
}

// 2. Hitung Notif (Wajib ada)
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE status='pending' AND role='admin'");
$total_pending = ($q_pending) ? mysqli_fetch_assoc($q_pending)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Admin - PERADI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* --- GLOBAL STYLE --- */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        a {
            text-decoration: none;
        }

        /* ===== SIDEBAR STYLE (DITAMBAHKAN KEMBALI) ===== */
        .sidebar {
            width: 250px;
            background-color: #1e3a8a;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: 0.3s;
        }

        .logo-section {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-text h2 {
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0;
            line-height: 1;
        }

        .logo-text span {
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #dea057;
        }

        .nav-links {
            list-style: none;
            padding: 15px 0;
            margin: 0;
            flex: 1;
        }

        .nav-links li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-links li a:hover,
        .nav-links li.active a {
            background-color: #152c69;
            color: white;
            border-left-color: #dea057;
        }

        .logout-section {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-section a {
            color: #ef4444;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .logout-section a:hover {
            color: #ffadad;
        }

        /* Badge Notifikasi */
        .badge-notif {
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            transition: 0.3s;
        }

        .content-header {
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
        }

        .header-title p {
            color: #888;
            margin: 5px 0 0 0;
            font-size: 0.9rem;
        }

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .table thead th {
            background-color: #f8f9fa;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 2px solid #e2e8f0;
            padding: 15px;
            text-transform: uppercase;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .bg-pending {
            background: #fff7ed;
            color: #c2410c;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            border: none;
            color: white;
            margin-right: 5px;
            display: inline-flex;
            gap: 5px;
            align-items: center;
            transition: 0.3s;
        }

        .btn-terima {
            background: #10b981;
        }

        .btn-terima:hover {
            background: #059669;
        }

        .btn-tolak {
            background: #ef4444;
        }

        .btn-tolak:hover {
            background: #dc2626;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <div class="header-title">
                <h1>Verifikasi Admin</h1>
                <p>Setujui atau tolak pendaftaran admin baru.</p>
            </div>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($conn, "SELECT * FROM users WHERE status='pending' AND role='admin' ORDER BY created_at DESC");

                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                // Cek apakah kolomnya id atau id_user
                                $id_user = isset($row['id']) ? $row['id'] : (isset($row['id_user']) ? $row['id_user'] : 0);
                        ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                                    <td>@<?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td><span class="badge-status bg-pending">Menunggu</span></td>
                                    <td>
                                        <a href="proses_verifikasi.php?id=<?php echo $id_user; ?>&aksi=terima"
                                            class="btn-action btn-terima"
                                            onclick="return confirm('Yakin ingin menerima?')">
                                            <i class="fa-solid fa-check"></i> Terima
                                        </a>

                                        <a href="proses_verifikasi.php?id=<?php echo $id_user; ?>&aksi=tolak"
                                            class="btn-action btn-tolak"
                                            onclick="return confirm('Yakin ingin menolak?')">
                                            <i class="fa-solid fa-xmark"></i> Tolak
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center text-muted py-5">Tidak ada data pending.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>