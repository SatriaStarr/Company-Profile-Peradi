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

// Proses Tambah/Edit
if(isset($_POST['simpan'])) {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $urutan = (int)$_POST['urutan'];
    
    if(isset($_POST['id']) && $_POST['id'] != '') {
        // Update
        $id = (int)$_POST['id'];
        mysqli_query($conn, "UPDATE running_text SET pesan='$pesan', icon='$icon', is_active=$is_active, urutan=$urutan WHERE id=$id");
        $msg = "berhasil_edit";
    } else {
        // Insert
        mysqli_query($conn, "INSERT INTO running_text (pesan, icon, is_active, urutan) VALUES ('$pesan', '$icon', $is_active, $urutan)");
        $msg = "berhasil_tambah";
    }
    header("Location: kelola_running_text.php?pesan=$msg");
    exit;
}

// Proses Hapus
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM running_text WHERE id=$id");
    header("Location: kelola_running_text.php?pesan=berhasil_hapus");
    exit;
}

// Ambil Data untuk Edit
$edit_data = null;
if(isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM running_text WHERE id=$id");
    $edit_data = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Running Text - PERADI</title>
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
        }
        .header-title h1 { font-size: 1.5rem; font-weight: 700; color: #1e3a8a; margin: 0; }
        .header-title p { color: #888; margin: 5px 0 0 0; font-size: 0.9rem; }

        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .table thead th { background-color: #f8f9fa; color: #64748b; font-weight: 600; font-size: 0.85rem; }
        .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; margin-right: 5px; }
        .badge-active { background: #dcfce7; color: #166534; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; }
        .badge-inactive { background: #fee2e2; color: #991b1b; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/partials/sidebar.php'; ?>

    <div class="main-content">
        <?php if(isset($_GET['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa-solid fa-check-circle"></i>
                <?php 
                    if($_GET['pesan'] == 'berhasil_tambah') echo 'Running text berhasil ditambahkan!';
                    elseif($_GET['pesan'] == 'berhasil_edit') echo 'Running text berhasil diupdate!';
                    elseif($_GET['pesan'] == 'berhasil_hapus') echo 'Running text berhasil dihapus!';
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="content-header">
            <div class="header-title">
                <h1>Kelola Running Text</h1>
                <p>Atur pesan berjalan di halaman utama.</p>
            </div>
        </div>

        <!-- FORM TAMBAH/EDIT -->
        <div class="card">
            <h5 class="mb-3"><?php echo $edit_data ? 'Edit Running Text' : 'Tambah Running Text Baru'; ?></h5>
            <form method="POST">
                <?php if($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-9">
                        <label>Pesan <span class="text-danger">*</span></label>
                        <textarea name="pesan" class="form-control" rows="3" required><?php echo $edit_data['pesan'] ?? ''; ?></textarea>
                    </div>
                    <div class="col-md-3">
                        <label>Icon</label>
                        <input type="text" name="icon" class="form-control" value="<?php echo $edit_data['icon'] ?? '📢'; ?>" placeholder="📢">
                        <small class="text-muted">Emoji icon</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Urutan</label>
                        <input type="number" name="urutan" class="form-control" value="<?php echo $edit_data['urutan'] ?? 0; ?>" min="0">
                    </div>
                    <div class="col-md-6">
                        <label>Status</label><br>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                <?php echo (!$edit_data || $edit_data['is_active']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" name="simpan" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Simpan
                    </button>
                    <?php if($edit_data): ?>
                        <a href="kelola_running_text.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- TABEL DATA -->
        <div class="card">
            <h5 class="mb-3">Daftar Running Text</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Icon</th>
                            <th>Pesan</th>
                            <th width="80">Urutan</th>
                            <th width="100">Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($conn, "SELECT * FROM running_text ORDER BY urutan ASC, id DESC");
                        while($row = mysqli_fetch_assoc($query)):
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><span style="font-size: 1.5rem;"><?php echo $row['icon']; ?></span></td>
                            <td><?php echo htmlspecialchars($row['pesan']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo $row['urutan']; ?></span></td>
                            <td>
                                <?php if($row['is_active']): ?>
                                    <span class="badge-active">Aktif</span>
                                <?php else: ?>
                                    <span class="badge-inactive">Non-Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm btn-action">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm btn-action" 
                                   onclick="return confirm('Yakin hapus running text ini?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>