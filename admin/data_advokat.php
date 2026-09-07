<?php
require_once __DIR__ . '/partials/admin_auth.php';

$pesan_map = [
    'sukses'         => ['success', 'Data advokat berhasil ditambahkan.'],
    'sukses_edit'    => ['success', 'Data advokat berhasil diperbarui.'],
    'sukses_hapus'   => ['success', 'Data advokat berhasil dihapus.'],
    'gagal'          => ['danger',  'Terjadi kesalahan. Silakan coba lagi.'],
];
$flash = $pesan_map[$_GET['pesan'] ?? ''] ?? null;

if (($_GET['pesan'] ?? '') === 'import_selesai') {
    $flash = ['success', 'Import selesai. Baru: ' . (int) ($_GET['baru'] ?? $_GET['sukses'] ?? 0)
        . ', diperbarui: ' . (int) ($_GET['update'] ?? 0)
        . ', dilewati: ' . (int) ($_GET['lewati'] ?? 0)
        . ', gagal: ' . (int) ($_GET['gagal'] ?? 0) . '.'];
}

/* ---------- filter + pagination ---------- */
$q      = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$valid_status = ['aktif', 'cuti', 'non_aktif', 'meninggal'];
if (!in_array($status, $valid_status, true)) {
    $status = '';
}

$per_page = 15;
$page     = max(1, (int) ($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$where  = ['1=1'];
$params = [];
$types  = '';

if ($q !== '') {
    $where[]  = '(nama_lengkap LIKE ? OR nia LIKE ? OR nik LIKE ? OR nama_kantor_hukum LIKE ? OR no_hp LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
    $types .= 'sssss';
}
if ($status !== '') {
    $where[]  = 'status_keanggotaan = ?';
    $params[] = $status;
    $types   .= 's';
}
$where_sql = implode(' AND ', $where);

$total = 0;
if ($st = mysqli_prepare($conn, "SELECT COUNT(*) n FROM data_advokat WHERE $where_sql")) {
    if ($params) {
        mysqli_stmt_bind_param($st, $types, ...$params);
    }
    mysqli_stmt_execute($st);
    $total = (int) (mysqli_fetch_assoc(mysqli_stmt_get_result($st))['n'] ?? 0);
    mysqli_stmt_close($st);
}
$total_page = max(1, (int) ceil($total / $per_page));
if ($page > $total_page) {
    $page = $total_page;
    $offset = ($page - 1) * $per_page;
}

$rows = [];
$sql = "SELECT id_advokat, nia, nama_lengkap, no_hp, nama_kantor_hukum, jenis_kelamin,
               foto_profil, status_keanggotaan
        FROM data_advokat WHERE $where_sql
        ORDER BY nama_lengkap ASC
        LIMIT ? OFFSET ?";
if ($st = mysqli_prepare($conn, $sql)) {
    $p = $params;
    $t = $types . 'ii';
    array_push($p, $per_page, $offset);
    mysqli_stmt_bind_param($st, $t, ...$p);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = $r;
    }
    mysqli_stmt_close($st);
}

function adv_url(int $p): string
{
    $qs = ['page' => $p];
    if (($_GET['q'] ?? '') !== '') {
        $qs['q'] = $_GET['q'];
    }
    if (($_GET['status'] ?? '') !== '') {
        $qs['status'] = $_GET['status'];
    }
    return 'data_advokat.php?' . http_build_query($qs);
}

function adv_foto_url(?string $f): string
{
    $f = trim((string) $f);
    if ($f === '' || $f === 'default.jpg') {
        return '';
    }
    $f = str_replace('\\', '/', $f);
    return str_starts_with($f, 'uploads/') ? '../' . $f : '../uploads/advokat/' . $f;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Advokat - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
body { background: #f4f6f9; color: #333; }
.main { margin-left: 250px; min-height: 100vh; }
.topbar { height: 75px; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 35px; border-bottom: 1px solid #e5e7eb; }
.topbar-title h1 { color: #1e3a8a; font-size: 22px; font-weight: 800; }
.topbar-title p { color: #777; font-size: 12px; margin-top: 4px; }
.admin-info { display: flex; align-items: center; gap: 12px; }
.admin-avatar { width: 42px; height: 42px; border-radius: 50%; background: #dea057; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; }
.admin-name { font-size: 13px; font-weight: 700; } .admin-role { font-size: 10px; color: #777; }
.content { padding: 35px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap; margin-bottom: 22px; }
.page-header h2 { color: #1e3a8a; font-size: 21px; font-weight: 800; }
.page-header p { color: #777; font-size: 12px; margin-top: 5px; }
.head-actions { display: flex; gap: 8px; }
.alert { padding: 13px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 12px; font-weight: 600; }
.alert.success { background: #dcfce7; color: #166534; } .alert.danger { background: #fee2e2; color: #b91c1c; }
.card { background: #fff; border-radius: 12px; padding: 22px; box-shadow: 0 5px 20px rgba(0,0,0,.05); margin-bottom: 18px; }
.filter { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.input-search { flex: 1; min-width: 220px; position: relative; }
.input-search input { width: 100%; padding: 11px 13px 11px 38px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 12px; }
.input-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #999; font-size: 13px; }
.form-control { padding: 11px 13px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 12px; background: #fff; }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 10px 14px; border-radius: 8px; border: none; background: #1e3a8a; color: #fff; text-decoration: none; font-size: 11px; font-weight: 700; cursor: pointer; }
.btn:hover { background: #152c69; }
.btn-gold { background: #dea057; } .btn-green { background: #10b981; }
.btn-danger { background: #dc2626; } .btn-icon { width: 40px; padding: 10px 0; }
.btn-sm { padding: 8px 10px; font-size: 10px; }
.total { margin: 14px 0; color: #777; font-size: 12px; }
.table-wrapper { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 780px; }
th { text-align: left; padding: 12px; background: #f8fafc; color: #666; font-size: 10px; text-transform: uppercase; white-space: nowrap; }
td { padding: 12px; border-top: 1px solid #eee; font-size: 11px; vertical-align: middle; }
.adv-cell { display: flex; align-items: center; gap: 10px; }
.adv-pic { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; background: #eef2ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; font-size: 13px; }
.adv-name { font-weight: 700; color: #1e3a8a; }
.adv-phone { color: #999; font-size: 9px; margin-top: 2px; }
.nia { font-weight: 800; color: #1e3a8a; }
.pill { display: inline-block; padding: 5px 9px; border-radius: 20px; font-size: 8px; font-weight: 800; text-transform: uppercase; }
.pill.aktif { background: #dcfce7; color: #166534; }
.pill.cuti { background: #fef9c3; color: #854d0e; }
.pill.non_aktif { background: #fee2e2; color: #991b1b; }
.pill.meninggal { background: #f1f5f9; color: #475569; }
.actions { display: flex; gap: 5px; }
.empty { text-align: center; padding: 45px 15px; color: #888; font-size: 12px; }
.empty i { display: block; font-size: 35px; color: #dea057; margin-bottom: 10px; }
.pagination { display: flex; align-items: center; justify-content: space-between; margin-top: 18px; padding-top: 15px; border-top: 1px solid #eee; flex-wrap: wrap; gap: 12px; }
.pagination-info { color: #777; font-size: 11px; }
.pagination-links { display: flex; gap: 5px; flex-wrap: wrap; }
.page-link { min-width: 34px; height: 34px; padding: 0 8px; border-radius: 7px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #e5e7eb; color: #1e3a8a; text-decoration: none; font-size: 11px; font-weight: 700; }
.page-link:hover, .page-link.active { background: #1e3a8a; color: #fff; border-color: #1e3a8a; }
.modal { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.55); z-index: 999; align-items: center; justify-content: center; padding: 20px; }
.modal.show { display: flex; }
.modal-box { width: 100%; max-width: 460px; background: #fff; border-radius: 14px; padding: 24px; }
.modal-box h3 { color: #1e3a8a; font-size: 16px; margin-bottom: 10px; }
.modal-box p { font-size: 13px; color: #475569; margin-bottom: 18px; }
.modal-foot { display: flex; justify-content: flex-end; gap: 10px; }
@media(max-width:900px) { .main { margin-left: 0; } .content { padding: 20px 16px; } .admin-name, .admin-role { display: none; } }
</style>
</head>
<body>

<?php include __DIR__ . '/partials/sidebar.php'; ?>

<main class="main">

<header class="topbar">
    <div class="topbar-title">
        <h1>Data Advokat</h1>
        <p>Data anggota DPC PERADI Pontianak</p>
    </div>
    <div class="admin-info">
        <div class="admin-avatar"><?= e(strtoupper(substr($admin_nama, 0, 1))) ?></div>
        <div>
            <div class="admin-name"><?= e($admin_nama) ?></div>
            <div class="admin-role"><?= e($admin_role) ?></div>
        </div>
    </div>
</header>

<section class="content">

    <div class="page-header">
        <div>
            <h2><i class="fa-solid fa-address-card" style="color:#dea057;"></i> Data Advokat</h2>
            <p>Kelola data anggota: cari, filter status, tambah, edit, hapus, dan lihat detail.</p>
        </div>
        <div class="head-actions">
            <a href="tambah_advokat.php" class="btn btn-green"><i class="fa-solid fa-plus"></i> Tambah</a>
            <button type="button" class="btn" onclick="document.getElementById('modalImport').classList.add('show')">
                <i class="fa-solid fa-file-csv"></i> Import CSV
            </button>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= e($flash[0]) ?>"><?= e($flash[1]) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="GET" class="filter" id="filterForm">
            <div class="input-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" id="q" value="<?= e($q) ?>" autocomplete="off"
                       placeholder="Cari nama, NIA, NIK, kantor, atau no HP...">
            </div>
            <select name="status" class="form-control" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Status</option>
                <?php foreach (['aktif' => 'Aktif', 'cuti' => 'Cuti', 'non_aktif' => 'Non-Aktif', 'meninggal' => 'Meninggal'] as $sv => $sl): ?>
                    <option value="<?= $sv ?>" <?= $status === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                <?php endforeach; ?>
            </select>
            <a href="data_advokat.php" class="btn btn-gold btn-icon" title="Reset filter" aria-label="Reset filter">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </form>

        <div class="total">Menampilkan <strong><?= count($rows) ?></strong> dari <strong><?= number_format($total) ?></strong> advokat.</div>

        <?php if ($rows): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>NIA</th><th>Nama Lengkap</th><th>Kantor Hukum</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r):
                    $foto = adv_foto_url($r['foto_profil']); ?>
                    <tr>
                        <td><span class="nia"><?= e($r['nia']) ?></span></td>
                        <td>
                            <div class="adv-cell">
                                <?php if ($foto): ?>
                                    <img src="<?= e($foto) ?>" class="adv-pic" alt="">
                                <?php else: ?>
                                    <span class="adv-pic"><?= e(strtoupper(substr($r['nama_lengkap'], 0, 1))) ?></span>
                                <?php endif; ?>
                                <div>
                                    <div class="adv-name"><?= e($r['nama_lengkap']) ?></div>
                                    <?php if ($r['no_hp']): ?><div class="adv-phone"><?= e($r['no_hp']) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= e($r['nama_kantor_hukum'] ?: '-') ?></td>
                        <td><span class="pill <?= e($r['status_keanggotaan']) ?>"><?= e(str_replace('_', ' ', $r['status_keanggotaan'])) ?></span></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-sm" href="detail_advokat.php?id=<?= e($r['id_advokat']) ?>" title="Detail"><i class="fa-solid fa-eye"></i></a>
                                <a class="btn btn-sm btn-gold" href="edit_advokat.php?id=<?= e($r['id_advokat']) ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                <button class="btn btn-sm btn-danger" title="Hapus" type="button"
                                        onclick="hapusAdvokat(<?= (int) $r['id_advokat'] ?>, '<?= e(addslashes($r['nama_lengkap'])) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <div class="pagination-info">Halaman <strong><?= $page ?></strong> dari <strong><?= $total_page ?></strong></div>
            <?php if ($total_page > 1): ?>
            <div class="pagination-links">
                <?php if ($page > 1): ?><a class="page-link" href="<?= e(adv_url($page - 1)) ?>"><i class="fa-solid fa-chevron-left"></i></a><?php endif; ?>
                <?php $s = max(1, $page - 2); $en = min($total_page, $page + 2); for ($i = $s; $i <= $en; $i++): ?>
                    <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="<?= e(adv_url($i)) ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_page): ?><a class="page-link" href="<?= e(adv_url($page + 1)) ?>"><i class="fa-solid fa-chevron-right"></i></a><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div class="empty"><i class="fa-solid fa-inbox"></i> Data advokat tidak ditemukan.</div>
        <?php endif; ?>
    </div>

</section>
</main>

<!-- MODAL HAPUS -->
<div class="modal" id="modalHapus">
    <div class="modal-box">
        <h3><i class="fa-solid fa-triangle-exclamation" style="color:#dc2626"></i> Hapus Data Advokat</h3>
        <p id="hapusText">Yakin ingin menghapus data ini?</p>
        <form method="POST" action="hapus_advokat.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="hapusId">
            <div class="modal-foot">
                <button type="button" class="btn" style="background:#e5e7eb;color:#333" onclick="document.getElementById('modalHapus').classList.remove('show')">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL IMPORT -->
<div class="modal" id="modalImport">
    <div class="modal-box">
        <h3><i class="fa-solid fa-file-csv" style="color:#1e3a8a"></i> Import CSV</h3>
        <p>Format .csv dengan pemisah titik koma (;). Baris pertama = judul kolom.</p>
        <form method="POST" action="proses_import.php" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="file" name="file_csv" accept=".csv,text/csv" required class="form-control" style="width:100%;margin-bottom:12px">
            <label style="display:flex;gap:8px;align-items:flex-start;font-size:12px;color:#475569;margin-bottom:16px;cursor:pointer;">
                <input type="checkbox" name="update_existing" value="1" style="margin-top:2px;">
                <span>Perbarui data untuk NIA yang sudah ada (berguna untuk memperbaiki data lama, mis. jenis kelamin). Tanpa dicentang, NIA yang sudah ada dilewati.</span>
            </label>
            <div class="modal-foot">
                <button type="button" class="btn" style="background:#e5e7eb;color:#333" onclick="document.getElementById('modalImport').classList.remove('show')">Batal</button>
                <button type="submit" name="import" value="1" class="btn"><i class="fa-solid fa-upload"></i> Upload &amp; Proses</button>
            </div>
        </form>
    </div>
</div>

<script>
var t;
document.getElementById('q').addEventListener('input', function () {
    clearTimeout(t);
    t = setTimeout(function () { document.getElementById('filterForm').submit(); }, 500);
});
function hapusAdvokat(id, nama) {
    document.getElementById('hapusId').value = id;
    document.getElementById('hapusText').textContent = 'Yakin ingin menghapus data advokat "' + nama + '"? Tindakan ini permanen.';
    document.getElementById('modalHapus').classList.add('show');
}
document.querySelectorAll('.modal').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m) m.classList.remove('show'); });
});
</script>
</body>
</html>
