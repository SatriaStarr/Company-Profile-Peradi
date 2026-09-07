<?php
session_start();
include 'koneksi.php';

// 1. Cek Keamanan
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php?pesan=belum_login");
    exit;
}

// 2. Hitung Data Pending
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE status='pending'");
$total_pending = ($q_pending) ? mysqli_fetch_assoc($q_pending)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Advokat - Admin PERADI</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ========================================
           GLOBAL STYLES
        ======================================== */
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

        /* ========================================
           MAIN CONTENT AREA
        ======================================== */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }

        /* ========================================
           ALERT NOTIFICATION
        ======================================== */
        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert strong {
            font-weight: 600;
        }

        .btn-close {
            background: transparent;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.6;
            margin-left: auto;
        }

        .btn-close:hover {
            opacity: 1;
        }

        /* ========================================
           PAGE HEADER
        ======================================== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3a8a;
        }

        .page-header p {
            margin: 5px 0 0 0;
            color: #888;
            font-size: 0.9rem;
        }

        /* ========================================
           HEADER ACTIONS (SEARCH & BUTTONS)
        ======================================== */
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
            background: #f8f9fa;
            width: 250px;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .search-box input:focus {
            border-color: #1e3a8a;
            width: 280px;
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        /* ========================================
           CUSTOM BUTTONS
        ======================================== */
        .btn-custom-add,
        .btn-custom-csv {
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
            transition: all 0.3s;
            color: white;
            cursor: pointer;
        }

        .btn-custom-add {
            background: #10b981;
        }

        .btn-custom-add:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-custom-csv {
            background: #1e3a8a;
        }

        .btn-custom-csv:hover {
            background: #152c69;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
        }

        /* ========================================
           TABLE CONTAINER
        ======================================== */
        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* ========================================
           TABLE STYLING
        ======================================== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #64748b;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #334155;
            font-size: 0.95rem;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        /* ========================================
           STATUS BADGES
        ======================================== */
        .badge-status {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .bg-aktif {
            background: #dcfce7;
            color: #166534;
        }

        .bg-cuti {
            background: #fef9c3;
            color: #854d0e;
        }

        .bg-non_aktif {
            background: #fee2e2;
            color: #991b1b;
        }

        .bg-meninggal {
            background: #f1f5f9;
            color: #475569;
        }

        /* ========================================
           ACTION BUTTONS
        ======================================== */
        .action-btn {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            margin-right: 5px;
            color: white;
            font-size: 0.8rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-view {
            background: #3b82f6;
        }

        .btn-view:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: #f59e0b;
        }

        .btn-edit:hover {
            background: #d97706;
            transform: translateY(-2px);
        }

        .btn-del {
            background: #ef4444;
        }

        .btn-del:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        /* ========================================
           MODAL STYLING
        ======================================== */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-dialog {
            position: relative;
            margin: 10% auto;
            max-width: 500px;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e3a8a;
            margin: 0;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            padding: 15px 25px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        .form-text {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .btn-primary {
            background: #1e3a8a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #152c69;
            transform: translateY(-2px);
        }

        /* ========================================
           RESPONSIVE DESIGN
        ======================================== */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                justify-content: stretch;
            }

            .search-box input {
                width: 100%;
            }

            .search-box input:focus {
                width: 100%;
            }

            .btn-custom-add,
            .btn-custom-csv {
                flex: 1;
                justify-content: center;
            }

            table {
                font-size: 0.85rem;
            }

            th,
            td {
                padding: 10px;
            }
        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="main-content">

        <!-- ALERT NOTIFIKASI -->
        <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'import_selesai'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle"></i>
                <strong>Import Selesai!</strong> Sukses: <?php echo $_GET['sukses']; ?>, Gagal: <?php echo $_GET['gagal']; ?>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- PAGE HEADER -->
        <div class="page-header">
            <div>
                <h1>Data Advokat</h1>
                <p>Kelola data anggota PERADI.</p>
            </div>

            <div class="header-actions">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari Nama / NIA...">
                </div>

                <a href="tambah_advokat.php" class="btn-custom-add" title="Tambah Manual">
                    <i class="fa-solid fa-plus"></i> <span class="d-none d-md-inline">Baru</span>
                </a>

                <button type="button" class="btn-custom-csv" data-bs-toggle="modal" data-bs-target="#modalImport" title="Import Excel/CSV">
                    <i class="fa-solid fa-file-csv"></i> <span class="d-none d-md-inline">Import</span>
                </button>
            </div>
        </div>

        <!-- TABLE CARD -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIA</th>
                            <th>Nama Lengkap</th>
                            <th>Kantor Hukum</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($conn, "SELECT * FROM data_advokat ORDER BY id_advokat ASC");
                        while ($row = mysqli_fetch_assoc($query)) {
                            $s = $row['status_keanggotaan'];
                            $badge = ($s == 'aktif') ? 'bg-aktif' : (($s == 'cuti') ? 'bg-cuti' : (($s == 'non_aktif') ? 'bg-non_aktif' : 'bg-meninggal'));
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong style="color:#1e3a8a;"><?php echo htmlspecialchars($row['nia']); ?></strong></td>
                                <td>
                                    <div style="font-weight:600;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['no_hp']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['nama_kantor_hukum']); ?></td>
                                <td><span class="badge-status <?php echo $badge; ?>"><?php echo ucfirst($s); ?></span></td>
                                <td>
                                    <a href="detail_advokat.php?id=<?php echo $row['id_advokat']; ?>" class="action-btn btn-view" title="Lihat Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="edit_advokat.php?id=<?php echo $row['id_advokat']; ?>" class="action-btn btn-edit" title="Edit Data">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="#" class="action-btn btn-del" onclick="openDeleteModal(<?= $row['id_advokat']; ?>)" title="Hapus Advokat">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr id="noResultRow" style="display: none;">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fa-solid fa-inbox fa-2x mb-2" style="opacity: 0.3;"></i>
                                <p>Data tidak ditemukan.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL IMPORT CSV -->
    <div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="proses_import.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Import CSV</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih File CSV</label>
                            <input type="file" name="file_csv" class="form-control" accept=".csv" required>
                            <div class="form-text">Gunakan format .csv dengan pemisah titik koma (;).</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="import" class="btn btn-primary">
                            <i class="fa-solid fa-upload"></i> Upload & Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SEARCH FUNCTIONALITY -->
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#dataTable tbody tr:not(#noResultRow)');
            let hasResult = false;

            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                    hasResult = true;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('noResultRow').style.display = hasResult ? 'none' : '';
        });
    </script>

    <!-- DELETE MODAL -->
    <?php include __DIR__ . '/partials/confirm_delete_modal.php'; ?>
    <script>
        function openDeleteModal(id) {
            const modal = document.getElementById('confirmDeleteModal');
            const yesBtn = document.getElementById('confirmDeleteYes');
            if (!modal || !yesBtn) return;
            yesBtn.href = 'hapus_advokat.php?id=' + id;
            modal.style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('confirmDeleteModal').style.display = 'none';
        }
    </script>

</body>

</html>