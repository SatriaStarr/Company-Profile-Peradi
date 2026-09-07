<?php
session_start();
include 'koneksi.php';

// Cek Login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php?pesan=belum_login");
    exit;
}

// Hitung Notif
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE status='pending'");
$total_pending = ($q_pending) ? mysqli_fetch_assoc($q_pending)['total'] : 0;

// Ambil Data Peraturan
$query = mysqli_query($conn, "SELECT * FROM peraturan ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peraturan - PERADI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 0; }
        a { text-decoration: none; }

        /* SIDEBAR */
        .sidebar {
            width: 250px; background-color: #1e3a8a; color: white;
            position: fixed; top: 0; left: 0; height: 100%; z-index: 100;
            display: flex; flex-direction: column; transition: 0.3s;
        }
        .logo-section { padding: 20px; display: flex; align-items: center; gap: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-text h2 { font-size: 1.4rem; font-weight: 800; margin: 0; line-height: 1; }
        .logo-text span { font-size: 0.75rem; letter-spacing: 1px; color: #dea057; }
        .nav-links { list-style: none; padding: 15px 0; margin: 0; flex: 1; }
        .nav-links li a {
            display: flex; align-items: center; gap: 15px; padding: 12px 25px;
            color: rgba(255,255,255,0.8); font-size: 0.9rem; font-weight: 500;
            transition: 0.3s; border-left: 4px solid transparent;
        }
        .nav-links li a:hover, .nav-links li.active a {
            background-color: #152c69; color: white; border-left-color: #dea057;
        }
        .logout-section { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-section a { color: #ef4444; display: flex; align-items: center; gap: 10px; font-weight: 600; }
        .badge-notif { background: #ef4444; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 10px; margin-left: auto; }

        /* MAIN CONTENT */
        .main-content { margin-left: 250px; padding: 30px; min-height: 100vh; }
        .content-header {
            background: white; padding: 20px 30px; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .header-title h1 { font-size: 1.5rem; font-weight: 700; color: #1e3a8a; margin: 0; }
        .header-title p { color: #888; margin: 5px 0 0 0; font-size: 0.9rem; }

        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .table thead th { background-color: #f8f9fa; color: #64748b; font-weight: 600; font-size: 0.85rem; }
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; margin-right: 5px; }
        
        /* PDF ICON BADGE */
        .pdf-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fee2e2;
            color: #991b1b;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .pdf-badge i {
            color: #dc2626;
        }

        /* PDF VIEWER */
        .viewer-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .viewer-container {
            background: white;
            border-radius: 12px;
            width: 90%;
            height: 90%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .viewer-header {
            padding: 20px;
            background: #1e3a8a;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .viewer-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .viewer-close {
            cursor: pointer;
            font-size: 1.5rem;
            transition: transform 0.2s;
        }
        .viewer-close:hover {
            transform: scale(1.2);
        }
        .viewer-body {
            flex: 1;
            padding: 0;
            background: #f3f4f6;
        }
        .viewer-body iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* DELETE MODAL */
        #confirmDeleteModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .modal-content-delete {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            text-align: center;
        }
        .modal-content-delete i {
            font-size: 3rem;
            color: #ef4444;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="main-content">
        <?php if(isset($_GET['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa-solid fa-check-circle"></i>
                <?php 
                    if($_GET['pesan'] == 'berhasil_tambah') echo 'Peraturan berhasil ditambahkan!';
                    elseif($_GET['pesan'] == 'berhasil_edit') echo 'Peraturan berhasil diupdate!';
                    elseif($_GET['pesan'] == 'berhasil_hapus') echo 'Peraturan berhasil dihapus!';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <div class="header-title">
                <h1>Peraturan</h1>
                <p>Kelola peraturan resmi PERADI.</p>
            </div>
            <a href="tambah_peraturan.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Tambah Peraturan
            </a>
        </div>

        <!-- TABEL DATA -->
        <div class="card">
            <h5 class="mb-3">Daftar Peraturan</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Judul</th>
                            <th width="200">File PDF</th>
                            <th width="120">Tanggal</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query) > 0):
                            while ($row = mysqli_fetch_assoc($query)):
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['judul']); ?></td>
                            <td>
                                <?php if (!empty($row['file_pdf'])): ?>
                                    <span class="pdf-badge">
                                        <i class="fa-solid fa-file-pdf"></i>
                                        <?php echo htmlspecialchars($row['file_pdf']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if (!empty($row['file_pdf'])): ?>
                                <a href="javascript:void(0)" 
                                   onclick="openViewer('<?php echo htmlspecialchars($row['judul'], ENT_QUOTES); ?>', '../uploads/peraturan/<?php echo $row['file_pdf']; ?>')" 
                                   class="btn btn-info btn-sm btn-action" 
                                   title="Lihat PDF">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <?php endif; ?>
                                <a href="edit_peraturan.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-warning btn-sm btn-action" 
                                   title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="#" 
                                   onclick="openDeleteModal(<?php echo $row['id']; ?>)" 
                                   class="btn btn-danger btn-sm btn-action" 
                                   title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile; 
                        else: 
                        ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding:30px;">Belum ada peraturan.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PDF VIEWER MODAL -->
    <div class="viewer-overlay" id="pdfViewer">
        <div class="viewer-container">
            <div class="viewer-header">
                <h3 id="viewerTitle">Dokumen</h3>
                <span class="viewer-close" onclick="closeViewer()">
                    <i class="fa-solid fa-xmark"></i>
                </span>
            </div>
            <div class="viewer-body">
                <iframe id="viewerFrame"></iframe>
            </div>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div id="confirmDeleteModal">
        <div class="modal-content-delete">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <h4>Konfirmasi Hapus</h4>
            <p>Apakah Anda yakin ingin menghapus peraturan ini?</p>
            <div style="margin-top: 20px;">
                <a href="#" id="confirmDeleteYes" class="btn btn-danger" style="margin-right: 10px;">Ya, Hapus</a>
                <button onclick="closeDeleteModal()" class="btn btn-secondary">Batal</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function openViewer(title, fileUrl) {
        document.getElementById('viewerTitle').innerText = title;
        document.getElementById('viewerFrame').src = fileUrl;
        document.getElementById('pdfViewer').style.display = 'flex';
    }

    function closeViewer() {
        document.getElementById('viewerFrame').src = '';
        document.getElementById('pdfViewer').style.display = 'none';
    }

    function openDeleteModal(id) {
        document.getElementById('confirmDeleteYes').href = 'hapus_peraturan.php?id=' + id;
        document.getElementById('confirmDeleteModal').style.display = 'flex';
    }
    
    function closeDeleteModal() {
        document.getElementById('confirmDeleteModal').style.display = 'none';
    }
    </script>

</body>
</html>