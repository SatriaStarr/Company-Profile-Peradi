<?php
session_start();
// Cek Login
if (!isset($_SESSION['status_login'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Advokat Baru - PERADI</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    
    <style>
        /* CSS Khusus Form */
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            max-width: 900px;
            margin: 0 auto; /* Tengah */
        }
        
        .form-section-title {
            color: #1e3a8a;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
            margin-top: 30px;
        }
        .form-section-title:first-child { margin-top: 0; }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 Kolom */
            gap: 20px;
        }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .form-group textarea { height: 80px; resize: none; }

        .btn-submit {
            background: #1e3a8a;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: 0.3s;
        }
        .btn-submit:hover { background: #1e40af; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #64748b; }
    </style>
</head>
<body>

    <div class="main-content" style="margin-left: 0; width: 100%; padding: 40px;">
        
        <a href="data_advokat.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Kembali ke Data Advokat</a>

        <div class="form-container">
            <h2 style="margin-bottom: 30px; text-align: center;">Formulir Anggota Baru</h2>

            <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-section-title"><i class="fa-solid fa-user"></i> Identitas Pribadi</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Lengkap (Beserta Gelar)</label>
                        <input type="text" name="nama_lengkap" placeholder="Contoh: Dr. Budi, S.H., M.H." required>
                    </div>
                    <div class="form-group">
                        <label>NIK (KTP)</label>
                        <input type="number" name="nik" placeholder="16 digit NIK" required>
                    </div>
                    <div class="form-group">
                        <label>Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select name="jenis_kelamin">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Agama</label>
                        <select name="agama">
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Domisili</label>
                    <textarea name="alamat_domisili"></textarea>
                </div>

                <div class="form-section-title"><i class="fa-solid fa-briefcase"></i> Kantor & Kontak</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Kantor Hukum</label>
                        <input type="text" name="nama_kantor_hukum" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP / WA</label>
                        <input type="text" name="no_hp" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Status Keanggotaan</label>
                        <select name="status_keanggotaan">
                            <option value="aktif">Aktif</option>
                            <option value="cuti">Cuti</option>
                            <option value="non_aktif">Non-Aktif</option>
                            <option value="meninggal">Meninggal Dunia</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Kantor</label>
                    <textarea name="alamat_kantor"></textarea>
                </div>

                <div class="form-section-title"><i class="fa-solid fa-file-contract"></i> Legalitas & Berkas</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>NIA (Nomor Induk Advokat)</label>
                        <input type="text" name="nia" placeholder="99.00000" required>
                    </div>
                    <div class="form-group">
                        <label>Universitas Asal (S1)</label>
                        <input type="text" name="universitas_asal">
                    </div>
                    <div class="form-group">
                        <label>Nomor BAS (Berita Acara Sumpah)</label>
                        <input type="text" name="nomor_bas">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Sumpah PT</label>
                        <input type="date" name="tanggal_sumpah_pt">
                    </div>
                    <div class="form-group">
                        <label>Foto Profil (JPG/PNG)</label>
                        <input type="file" name="foto_profil" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Scan Kartu Advokat (KTPA)</label>
                        <input type="file" name="file_ktpa" accept=".pdf,.jpg,.png">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Simpan Data Anggota</button>
            </form>
        </div>
    </div>

</body>
</html>