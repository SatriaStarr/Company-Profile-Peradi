<?php
/*
 * Partial form data advokat — dipakai tambah_advokat.php & edit_advokat.php
 * Variabel yang diharapkan:
 *   $mode   : 'tambah' | 'edit'
 *   $action : URL action form
 *   $d      : array data lama (kosong utk tambah)
 *   $errors : array pesan error (opsional)
 */
$d = $d ?? [];
$errors = $errors ?? [];
$val = fn(string $k) => e($d[$k] ?? '');
$is_edit = ($mode ?? '') === 'edit';

$agama_list = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
$status_list = ['aktif' => 'Aktif', 'cuti' => 'Cuti', 'non_aktif' => 'Non-Aktif', 'meninggal' => 'Meninggal Dunia'];

function file_url_form(?string $f): ?string
{
    $f = trim((string) $f);
    if ($f === '' || $f === 'default.jpg') {
        return null;
    }
    $f = str_replace('\\', '/', $f);
    return str_starts_with($f, 'uploads/') ? '../' . $f : '../uploads/advokat/' . $f;
}
?>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
body { background: #f4f6f9; color: #333; }
.main { margin-left: 250px; min-height: 100vh; }
.topbar { height: 75px; background: #fff; display: flex; align-items: center; padding: 0 35px; border-bottom: 1px solid #e5e7eb; }
.topbar h1 { color: #1e3a8a; font-size: 22px; font-weight: 800; }
.content { padding: 35px; max-width: 900px; }
.back { display: inline-flex; align-items: center; gap: 7px; color: #1e3a8a; text-decoration: none; font-weight: 700; font-size: 12px; margin-bottom: 18px; }
.card { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 5px 20px rgba(0,0,0,.05); }
.sec { font-size: 14px; color: #1e3a8a; font-weight: 700; border-bottom: 2px solid #f1f5f9; padding-bottom: 9px; margin: 26px 0 18px; }
.sec:first-of-type { margin-top: 0; }
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px 20px; }
.fg { margin-bottom: 4px; }
.fg.full { grid-column: span 2; }
label { display: block; font-size: 11px; font-weight: 700; color: #555; margin-bottom: 6px; }
input, select, textarea { width: 100%; padding: 11px 12px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; font-size: 13px; background: #fff; font-family: inherit; }
input:focus, select:focus, textarea:focus { border-color: #1e3a8a; }
textarea { min-height: 80px; resize: vertical; }
.hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }
.prev { width: 64px; height: 64px; border-radius: 8px; object-fit: cover; border: 1px solid #e5e7eb; margin-bottom: 8px; }
.alert { padding: 13px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 12px; font-weight: 600; background: #fee2e2; color: #b91c1c; }
.alert ul { margin: 6px 0 0 18px; }
.btn { display: inline-flex; align-items: center; gap: 7px; padding: 12px 20px; border-radius: 8px; border: none; background: #1e3a8a; color: #fff; text-decoration: none; font-size: 12px; font-weight: 700; cursor: pointer; margin-top: 22px; }
.btn.gold { background: #dea057; }
.btn.plain { background: #e5e7eb; color: #333; }
@media(max-width:900px) { .main { margin-left: 0; } .content { padding: 20px 16px; } .grid { grid-template-columns: 1fr; } .fg.full { grid-column: span 1; } }
</style>

<?php include __DIR__ . '/sidebar.php'; ?>

<main class="main">
<header class="topbar"><h1><?= $is_edit ? 'Edit' : 'Tambah' ?> Data Advokat</h1></header>

<section class="content">
    <a class="back" href="data_advokat.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Data Advokat</a>

    <div class="card">
        <?php if ($errors): ?>
            <div class="alert">Perbaiki hal berikut:
                <ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?= e($d['id_advokat'] ?? '') ?>">
            <?php endif; ?>

            <div class="sec"><i class="fa-solid fa-user"></i> Identitas Pribadi</div>
            <div class="grid">
                <div class="fg"><label>NIA (Nomor Induk Advokat) *</label><input type="text" name="nia" required value="<?= $val('nia') ?>"></div>
                <div class="fg"><label>NIK *</label><input type="text" name="nik" required value="<?= $val('nik') ?>" inputmode="numeric"></div>
                <div class="fg full"><label>Nama Lengkap &amp; Gelar *</label><input type="text" name="nama_lengkap" required value="<?= $val('nama_lengkap') ?>"></div>
                <div class="fg"><label>Tempat Lahir</label><input type="text" name="tempat_lahir" value="<?= $val('tempat_lahir') ?>"></div>
                <div class="fg"><label>Tanggal Lahir</label><input type="date" name="tanggal_lahir" value="<?= $val('tanggal_lahir') ?>"></div>
                <div class="fg"><label>Jenis Kelamin</label>
                    <select name="jenis_kelamin">
                        <option value="">- Pilih -</option>
                        <option value="L" <?= ($d['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($d['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="fg"><label>Agama</label>
                    <select name="agama">
                        <option value="">- Pilih -</option>
                        <?php foreach ($agama_list as $a): ?>
                            <option value="<?= $a ?>" <?= ($d['agama'] ?? '') === $a ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg full"><label>Alamat Domisili</label><textarea name="alamat_domisili"><?= $val('alamat_domisili') ?></textarea></div>
            </div>

            <div class="sec"><i class="fa-solid fa-briefcase"></i> Kontak &amp; Kantor</div>
            <div class="grid">
                <div class="fg"><label>Nama Kantor Hukum</label><input type="text" name="nama_kantor_hukum" value="<?= $val('nama_kantor_hukum') ?>"></div>
                <div class="fg"><label>No HP / WhatsApp</label><input type="text" name="no_hp" value="<?= $val('no_hp') ?>"></div>
                <div class="fg"><label>Email</label><input type="email" name="email" value="<?= $val('email') ?>"></div>
                <div class="fg"><label>Status Keanggotaan</label>
                    <select name="status_keanggotaan">
                        <?php foreach ($status_list as $sv => $sl): ?>
                            <option value="<?= $sv ?>" <?= ($d['status_keanggotaan'] ?? 'aktif') === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg full"><label>Alamat Kantor</label><textarea name="alamat_kantor"><?= $val('alamat_kantor') ?></textarea></div>
            </div>

            <div class="sec"><i class="fa-solid fa-file-contract"></i> Legalitas &amp; Dokumen</div>
            <div class="grid">
                <div class="fg"><label>Universitas Asal</label><input type="text" name="universitas_asal" value="<?= $val('universitas_asal') ?>"></div>
                <div class="fg"><label>Nomor BAS</label><input type="text" name="nomor_bas" value="<?= $val('nomor_bas') ?>"></div>
                <div class="fg"><label>Tanggal Sumpah PT</label><input type="date" name="tanggal_sumpah_pt" value="<?= $val('tanggal_sumpah_pt') ?>"></div>
                <div class="fg"><label>Masa Berlaku KTPA</label><input type="date" name="masa_berlaku_ktpa" value="<?= $val('masa_berlaku_ktpa') ?>"></div>

                <div class="fg">
                    <label>Foto Profil (JPG/PNG, maks 3 MB)</label>
                    <?php $fp = file_url_form($d['foto_profil'] ?? ''); if ($fp): ?><img src="<?= e($fp) ?>" class="prev" alt=""><?php endif; ?>
                    <input type="file" name="foto_profil" accept="image/jpeg,image/png">
                    <?php if ($is_edit): ?><div class="hint">Kosongkan jika tidak ingin mengganti.</div><?php endif; ?>
                </div>
                <div class="fg">
                    <label>File KTPA (PDF/JPG/PNG, maks 3 MB)</label>
                    <?php if (!empty($d['file_ktpa']) && $d['file_ktpa'] !== 'default.jpg'): ?><div class="hint" style="color:#16a34a">File sudah ada.</div><?php endif; ?>
                    <input type="file" name="file_ktpa" accept="application/pdf,image/jpeg,image/png">
                </div>
                <div class="fg">
                    <label>File BAS (PDF/JPG/PNG, maks 3 MB)</label>
                    <?php if (!empty($d['file_bas'])): ?><div class="hint" style="color:#16a34a">File sudah ada.</div><?php endif; ?>
                    <input type="file" name="file_bas" accept="application/pdf,image/jpeg,image/png">
                </div>
            </div>

            <button type="submit" class="btn gold"><i class="fa-solid fa-save"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Simpan Data' ?></button>
            <a href="data_advokat.php" class="btn plain">Batal</a>
        </form>
    </div>
</section>
</main>
