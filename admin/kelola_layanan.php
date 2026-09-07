<?php
session_start();
include 'koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: index.php?pesan=belum_login");
    exit;
}

// Hitung Notif (Wajib ada)
$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE status='pending'");
$total_pending = ($q_pending) ? mysqli_fetch_assoc($q_pending)['total'] : 0;

$success = '';
$error   = '';

// =======================
// MODE EDIT
// =======================
$editData = null;
if (isset($_GET['edit'])) {
    $kategori = mysqli_real_escape_string($conn, $_GET['edit']);
    $q = mysqli_query($conn, "SELECT * FROM layanan WHERE kategori='$kategori' LIMIT 1");
    $editData = mysqli_fetch_assoc($q);
}

// =======================
// MODE HAPUS
// =======================
if (isset($_GET['hapus'])) {
    $kategori = mysqli_real_escape_string($conn, $_GET['hapus']);

    // ambil file lama
    $old = mysqli_query($conn, "SELECT file FROM layanan WHERE kategori='$kategori'");
    $oldData = mysqli_fetch_assoc($old);

    if (!empty($oldData['file']) && file_exists("../uploads/layanan/" . $oldData['file'])) {
        unlink("../uploads/layanan/" . $oldData['file']);
    }

    mysqli_query($conn, "DELETE FROM layanan WHERE kategori='$kategori'");
    header("Location: kelola_layanan.php?pesan=berhasil_hapus");
    exit;
}

// =======================
// PROSES SIMPAN
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);
    $judul     = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

    if ($kategori && $judul && $deskripsi) {

        $file_name = null;

        if (!empty($_FILES['file']['name'])) {
            $file_name = time() . '_' . $_FILES['file']['name'];
            move_uploaded_file(
                $_FILES['file']['tmp_name'],
                '../uploads/layanan/' . $file_name
            );
        }

        $cek = mysqli_query($conn, "SELECT file FROM layanan WHERE kategori='$kategori' LIMIT 1");

        if (mysqli_num_rows($cek) > 0) {
            // UPDATE
            $old = mysqli_fetch_assoc($cek);
            if ($file_name && $old['file'] && file_exists("../uploads/layanan/" . $old['file'])) {
                unlink("../uploads/layanan/" . $old['file']);
            }

            $file_sql = $file_name ? ", file='$file_name'" : "";

            mysqli_query(
                $conn,
                "UPDATE layanan 
                 SET judul='$judul', deskripsi='$deskripsi' $file_sql
                 WHERE kategori='$kategori'"
            );
            header("Location: kelola_layanan.php?pesan=berhasil_edit");
            exit;
        } else {
            // INSERT
            mysqli_query(
                $conn,
                "INSERT INTO layanan (kategori, judul, deskripsi, file)
                 VALUES ('$kategori', '$judul', '$deskripsi', '$file_name')"
            );
            header("Location: kelola_layanan.php?pesan=berhasil_tambah");
            exit;
        }
    } else {
        $error = "Semua field wajib diisi.";
    }
}

// =======================
// AMBIL DATA UNTUK TABEL
// =======================
$layanan = mysqli_query($conn, "SELECT * FROM layanan ORDER BY kategori ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan - PERADI</title>

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
           ALERT NOTIFICATIONS
        ======================================== */
        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
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
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 30px;
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

        /* ========================================
           CONTENT CARDS
        ======================================== */
        .content-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            margin-bottom: 25px;
        }

        .content-card h5 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .content-card h5 i {
            color: #1e3a8a;
        }

        /* ========================================
           FORM STYLING
        ======================================== */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 500;
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-size: 0.9rem;
        }

        .form-group label .text-danger {
            color: #ef4444;
            margin-left: 3px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-text {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 5px;
            display: block;
        }

        /* ========================================
           BUTTONS
        ======================================== */
        .btn-save {
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-save:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }

        /* ========================================
           TABLE STYLING
        ======================================== */
        .table-responsive {
            overflow-x: auto;
        }

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
           KATEGORI BADGES
        ======================================== */
        .badge-kategori {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .badge-pkpa {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-upa {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-sumpah {
            background: #dcfce7;
            color: #166534;
        }

        /* ========================================
           ACTION BUTTONS
        ======================================== */
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            text-decoration: none;
            margin-right: 5px;
        }

        .btn-edit {
            background: #f59e0b;
            color: white;
        }

        .btn-edit:hover {
            background: #d97706;
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-2px);
        }

        /* ========================================
           FILE PREVIEW
        ======================================== */
        .file-preview {
            margin-top: 10px;
            padding: 12px;
            background: #f3f4f6;
            border-radius: 8px;
            display: none;
            align-items: center;
            gap: 10px;
        }

        .file-preview.show {
            display: flex;
        }

        .file-preview i {
            font-size: 1.5rem;
            color: #1e3a8a;
        }

        .file-info {
            flex: 1;
        }

        .file-info .file-name {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }

        .file-info .file-size {
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* ========================================
           DIVIDER
        ======================================== */
        hr {
            border: 0;
            border-top: 1px solid #e5e7eb;
            margin: 30px 0;
        }

        /* ========================================
           RESPONSIVE DESIGN
        ======================================== */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .content-card {
                padding: 20px;
            }

            .form-group {
                margin-bottom: 15px;
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

        <!-- ALERT MESSAGES -->
        <?php if (isset($_GET['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-check-circle"></i>
                <strong>Berhasil!</strong>
                <?php
                if ($_GET['pesan'] == 'berhasil_tambah') echo 'Layanan berhasil ditambahkan.';
                elseif ($_GET['pesan'] == 'berhasil_edit') echo 'Layanan berhasil diperbarui.';
                elseif ($_GET['pesan'] == 'berhasil_hapus') echo 'Layanan berhasil dihapus.';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <strong>Error!</strong> <?= $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- PAGE HEADER -->
        <div class="page-header">
            <div class="header-title">
                <h1>Kelola Layanan</h1>
                <p>Tambah atau perbarui informasi layanan PERADI.</p>
            </div>
        </div>

        <!-- FORM TAMBAH/EDIT -->
        <div class="content-card">
            <h5>
                <i class="fa-solid fa-pen-to-square"></i>
                <?php echo $editData ? 'Edit Layanan' : 'Tambah Layanan Baru'; ?>
            </h5>

            <form method="POST" enctype="multipart/form-data" id="layananForm">
                <?php if ($editData): ?>
                    <input type="hidden" name="kategori" value="<?= htmlspecialchars($editData['kategori']); ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Kategori Layanan <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select" required <?= $editData ? 'disabled' : ''; ?>>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="pkpa" <?= ($editData && $editData['kategori'] == 'pkpa') ? 'selected' : ''; ?>>PKPA</option>
                                <option value="upa" <?= ($editData && $editData['kategori'] == 'upa') ? 'selected' : ''; ?>>UPA</option>
                                <option value="sumpah" <?= ($editData && $editData['kategori'] == 'sumpah') ? 'selected' : ''; ?>>Pengangkatan & Sumpah</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Judul <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control"
                        value="<?= htmlspecialchars($editData['judul'] ?? ''); ?>"
                        placeholder="Contoh: Pendaftaran PKPA Batch 2025" required>
                </div>

                <div class="form-group">
                    <label>Deskripsi <span class="text-danger">*</span></label>
                    <textarea name="deskripsi" class="form-control" required
                        placeholder="Tulis deskripsi lengkap tentang layanan ini..."><?= htmlspecialchars($editData['deskripsi'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>File Dokumen (PDF / DOC)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" id="fileInput">
                    <small class="form-text">Upload file persyaratan atau formulir. Maksimal 5MB.</small>

                    <?php if ($editData && !empty($editData['file'])): ?>
                        <div class="file-preview show">
                            <i class="fa-solid fa-file-pdf"></i>
                            <div class="file-info">
                                <div class="file-name">File saat ini: <?= htmlspecialchars($editData['file']); ?></div>
                                <div class="file-size">Upload file baru untuk mengganti</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="file-preview" id="newFilePreview">
                        <i class="fa-solid fa-file-pdf"></i>
                        <div class="file-info">
                            <div class="file-name" id="fileName"></div>
                            <div class="file-size" id="fileSize"></div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-save"></i>
                        <?= $editData ? 'Update Layanan' : 'Simpan Layanan'; ?>
                    </button>

                    <?php if ($editData): ?>
                        <a href="kelola_layanan.php" class="btn-secondary">
                            <i class="fa-solid fa-times"></i>
                            Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- DIVIDER -->
        <hr>

        <!-- TABEL DATA LAYANAN -->
        <div class="content-card">
            <h5>
                <i class="fa-solid fa-list"></i>
                Daftar Layanan
            </h5>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th width="120">Kategori</th>
                            <th>Judul</th>
                            <th width="100">File</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        mysqli_data_seek($layanan, 0); // Reset pointer
                        while ($row = mysqli_fetch_assoc($layanan)):
                            $badgeClass = 'badge-pkpa';
                            if ($row['kategori'] == 'upa') $badgeClass = 'badge-upa';
                            if ($row['kategori'] == 'sumpah') $badgeClass = 'badge-sumpah';
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <span class="badge-kategori <?= $badgeClass; ?>">
                                        <?= strtoupper(htmlspecialchars($row['kategori'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['judul']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= substr(htmlspecialchars($row['deskripsi']), 0, 80); ?>...
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($row['file'])): ?>
                                        <a href="../uploads/layanan/<?= $row['file']; ?>" target="_blank" class="text-primary">
                                            <i class="fa-solid fa-file-pdf"></i> Lihat
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?edit=<?= $row['kategori']; ?>" class="action-btn btn-edit">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <a href="#" class="action-btn btn-delete"
                                        onclick="openDeleteModal('<?= $row['kategori']; ?>')">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                        <?php if (mysqli_num_rows($layanan) == 0): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-inbox fa-3x mb-3" style="opacity: 0.3;"></i>
                                    <p>Belum ada data layanan.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- FILE PREVIEW SCRIPT -->
    <script>
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('newFilePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            if (file) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                preview.classList.add('show');
            } else {
                preview.classList.remove('show');
            }
        });

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    </script>

    <!-- DELETE MODAL -->
    <?php include 'partials/confirm_delete_modal.php'; ?>
    <script>
        function openDeleteModal(kategori) {
            const modal = document.getElementById('confirmDeleteModal');
            const yesBtn = document.getElementById('confirmDeleteYes');

            if (!modal || !yesBtn) return;

            yesBtn.href = 'kelola_layanan.php?hapus=' + kategori;
            modal.style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('confirmDeleteModal').style.display = 'none';
        }
    </script>

</body>

</html>