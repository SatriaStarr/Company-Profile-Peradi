<?php
session_start();
include 'koneksi.php';

// Cek Login
if (!isset($_SESSION['status_login'])) {
    header("Location: index.php");
    exit;
}

// Cek ID di URL
if (!isset($_GET['id'])) {
    header("Location: data_advokat.php");
    exit;
}

// Ambil Data Lama
$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM data_advokat WHERE id_advokat = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ada
if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Advokat - PERADI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    
    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 900px;
            margin: 0 auto;
        }
        .form-section-title {
            color: #f59e0b; /* Kuning (Warna Edit) */
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
            margin-top: 30px;
        }
        .form-section-title:first-child { margin-top: 0; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;
        }
        
        /* Preview Foto */
        .img-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; border: 1px solid #ddd; margin-bottom: 10px; }
        .file-hint { font-size: 0.8rem; color: #ef4444; margin-top: 5px; display: block; }

        .btn-submit {
            background: #f59e0b; /* Kuning */
            color: white; padding: 12px 30px; border: none; border-radius: 5px;
            font-size: 1rem; cursor: pointer; width: 100%; margin-top: 20px; font-weight: 600;
        }
        .btn-submit:hover { background: #d97706; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #64748b; }
    </style>
</head>
<body>

    <div class="main-content" style="margin-left: 0; width: 100%; padding: 40px;">
        <a href="data_advokat.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Batal & Kembali</a>

        <div class="form-container">
            <h2 style="margin-bottom: 30px; text-align: center;">Edit Data Anggota</h2>

            <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="id" value="<?php echo $data['id_advokat']; ?>">
                
                <input type="hidden" name="foto_lama" value="<?php echo $data['foto_profil']; ?>">
                <input type="hidden" name="ktpa_lama" value="<?php echo $data['file_ktpa']; ?>">

                <div class="form-section-title"><i class="fa-solid fa-user-pen"></i> Identitas Pribadi</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?php echo $data['nama_lengkap']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>NIK</label>
                        <input type="number" name="nik" value="<?php echo $data['nik']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="<?php echo $data['tempat_lahir']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?php echo $data['tanggal_lahir']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin">
                            <option value="L" <?php if($data['jenis_kelamin'] == 'L') echo 'selected'; ?>>Laki-laki</option>
                            <option value="P" <?php if($data['jenis_kelamin'] == 'P') echo 'selected'; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Agama</label>
                        <select name="agama">
                            <?php 
                                $agama = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
                                foreach($agama as $a){
                                    $selected = ($data['agama'] == $a) ? 'selected' : '';
                                    echo "<option value='$a' $selected>$a</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Domisili</label>
                    <textarea name="alamat_domisili"><?php echo $data['alamat_domisili']; ?></textarea>
                </div>

                <div class="form-section-title"><i class="fa-solid fa-briefcase"></i> Kantor & Kontak</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Kantor Hukum</label>
                        <input type="text" name="nama_kantor_hukum" value="<?php echo $data['nama_kantor_hukum']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>No HP / WA</label>
                        <input type="text" name="no_hp" value="<?php echo $data['no_hp']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $data['email']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Status Keanggotaan</label>
                        <select name="status_keanggotaan">
                            <option value="aktif" <?php if($data['status_keanggotaan']=='aktif') echo 'selected'; ?>>Aktif</option>
                            <option value="cuti" <?php if($data['status_keanggotaan']=='cuti') echo 'selected'; ?>>Cuti</option>
                            <option value="non_aktif" <?php if($data['status_keanggotaan']=='non_aktif') echo 'selected'; ?>>Non-Aktif</option>
                            <option value="meninggal" <?php if($data['status_keanggotaan']=='meninggal') echo 'selected'; ?>>Meninggal Dunia</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Kantor</label>
                    <textarea name="alamat_kantor"><?php echo $data['alamat_kantor']; ?></textarea>
                </div>

                <div class="form-section-title"><i class="fa-solid fa-file-contract"></i> Legalitas & Update File</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>NIA (Nomor Induk Advokat)</label>
                        <input type="text" name="nia" value="<?php echo $data['nia']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Universitas Asal</label>
                        <input type="text" name="universitas_asal" value="<?php echo $data['universitas_asal']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Nomor BAS</label>
                        <input type="text" name="nomor_bas" value="<?php echo $data['nomor_bas']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Sumpah PT</label>
                        <input type="date" name="tanggal_sumpah_pt" value="<?php echo $data['tanggal_sumpah_pt']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Ganti Foto Profil</label>
                        <?php if($data['foto_profil']): ?>
                            <img src="uploads/<?php echo $data['foto_profil']; ?>" class="img-preview">
                        <?php endif; ?>
                        <input type="file" name="foto_profil" accept="image/*">
                        <small class="file-hint">*Biarkan kosong jika tidak ingin mengganti foto.</small>
                    </div>

                    <div class="form-group">
                        <label>Ganti File KTPA (PDF/JPG)</label>
                        <?php if($data['file_ktpa']): ?>
                            <small style="color:#2ecc71;"><i class="fa-solid fa-check"></i> File sudah ada</small><br>
                        <?php endif; ?>
                        <input type="file" name="file_ktpa" accept=".pdf,.jpg,.png">
                        <small class="file-hint">*Biarkan kosong jika file lama masih berlaku.</small>
                    </div>
                </div>

                <button type="submit" class="btn-submit"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
            </form>
        </div>
    </div>

</body>
</html>