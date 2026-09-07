<?php
session_start();
include 'koneksi.php';

// 1. Cek ID di URL
if(!isset($_GET['id'])) {
    header("Location: data_advokat.php");
    exit;
}

// 2. Ambil Data dari Database
$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM data_advokat WHERE id_advokat = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    echo "Data tidak ditemukan.";
    exit;
}

// --- FUNGSI BANTUAN FORMAT TAMPILAN ---
// Agar jika data kosong/null, yang muncul adalah tanda "-" (Bukan kosong atau 1970)
function tampil($nilai) {
    return (!empty($nilai)) ? $nilai : '-';
}

function tampil_tanggal($tgl) {
    if (empty($tgl) || $tgl == '0000-00-00') {
        return '-';
    }
    return date('d F Y', strtotime($tgl));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Advokat - <?php echo $data['nama_lengkap']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .detail-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        /* Header Profil Biru */
        .profile-header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            padding: 40px 30px;
            color: white;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.3);
            object-fit: cover;
            background: white;
        }

        .profile-info h1 { margin-bottom: 5px; font-size: 1.5rem; font-weight: 600; }
        .profile-info p { opacity: 0.9; margin-bottom: 5px; font-size: 0.95rem; }
        
        .badge-status {
            display: inline-block;
            padding: 5px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            font-size: 0.85rem;
            margin-top: 10px;
            font-weight: 500;
        }

        /* Grid Data */
        .data-grid {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 Kolom */
            gap: 30px;
        }

        /* Judul Per Bagian */
        .section-title {
            grid-column: span 2; /* Melebar penuh */
            font-size: 1.1rem;
            color: #1e3a8a;
            font-weight: 600;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
            margin-top: 20px;
        }
        .section-title:first-child { margin-top: 0; }

        /* Item Data */
        .data-item label {
            display: block;
            font-size: 0.85rem;
            color: #64748b; /* Abu-abu */
            margin-bottom: 5px;
        }
        .data-item span {
            font-weight: 500;
            color: #1f2937; /* Hitam soft */
            font-size: 1rem;
            display: block;
        }

        /* Kotak File Download */
        .file-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            transition: 0.3s;
        }
        .file-box:hover { background: #f1f5f9; border-color: #94a3b8; }
        .file-box a { color: #3b82f6; font-weight: 500; text-decoration: none; }

        .btn-back { margin: 20px 0; display: inline-block; color: #64748b; font-weight: 500; }
        .btn-back:hover { color: #1e3a8a; }
    </style>
</head>
<body>

    <div class="main-content" style="margin-left:0; width:100%; padding: 20px 40px;">
        
        <a href="data_advokat.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Advokat
        </a>

        <div class="detail-container">
            
            <div class="profile-header">
                <?php 
                    // Cek Foto: Kalau kosong pakai avatar default (inisial nama)
                    $foto = (!empty($data['foto_profil'])) ? 'uploads/'.$data['foto_profil'] : 'https://ui-avatars.com/api/?name='.$data['nama_lengkap'].'&background=random';
                ?>
                <img src="<?php echo $foto; ?>" alt="Foto Profil" class="profile-img">
                
                <div class="profile-info">
                    <h1><?php echo tampil($data['nama_lengkap']); ?></h1>
                    <p><i class="fa-solid fa-id-badge"></i> NIA: <?php echo tampil($data['nia']); ?></p>
                    <p><i class="fa-solid fa-briefcase"></i> <?php echo tampil($data['nama_kantor_hukum']); ?></p>
                    <span class="badge-status">
                        <?php echo strtoupper(str_replace('_', ' ', tampil($data['status_keanggotaan']))); ?>
                    </span>
                </div>
            </div>

            <div class="data-grid">
                
                <div class="section-title">Identitas Pribadi</div>
                
                <div class="data-item">
                    <label>NIA (Nomor Induk Advokat)</label>
                    <span><?php echo tampil($data['nia']); ?></span>
                </div>
                <div class="data-item">
                    <label>NIK (KTP)</label>
                    <span><?php echo tampil($data['nik']); ?></span>
                </div>
                <div class="data-item">
                    <label>Nama Lengkap & Gelar</label>
                    <span><?php echo tampil($data['nama_lengkap']); ?></span>
                </div>
                <div class="data-item">
                    <label>Tempat, Tanggal Lahir</label>
                    <span>
                        <?php echo tampil($data['tempat_lahir']); ?>, 
                        <?php echo tampil_tanggal($data['tanggal_lahir']); ?>
                    </span>
                </div>
                <div class="data-item">
                    <label>Jenis Kelamin</label>
                    <span><?php echo ($data['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></span>
                </div>
                <div class="data-item">
                    <label>Agama</label>
                    <span><?php echo tampil($data['agama']); ?></span>
                </div>
                <div class="data-item" style="grid-column: span 2;">
                    <label>Alamat Domisili</label>
                    <span><?php echo tampil($data['alamat_domisili']); ?></span>
                </div>


                <div class="section-title">Kontak & Kantor</div>
                
                <div class="data-item">
                    <label>Nama Kantor Hukum</label>
                    <span><?php echo tampil($data['nama_kantor_hukum']); ?></span>
                </div>
                <div class="data-item">
                    <label>Nomor HP / WhatsApp</label>
                    <span><?php echo tampil($data['no_hp']); ?></span>
                </div>
                <div class="data-item">
                    <label>Alamat Email</label>
                    <span><?php echo tampil($data['email']); ?></span>
                </div>
                <div class="data-item" style="grid-column: span 2;">
                    <label>Alamat Kantor</label>
                    <span><?php echo tampil($data['alamat_kantor']); ?></span>
                </div>


                <div class="section-title">Legalitas & Dokumen</div>

                <div class="data-item">
                    <label>Universitas Asal</label>
                    <span><?php echo tampil($data['universitas_asal']); ?></span>
                </div>
                <div class="data-item">
                    <label>Nomor BAS (Berita Acara Sumpah)</label>
                    <span><?php echo tampil($data['nomor_bas']); ?></span>
                </div>
                <div class="data-item">
                    <label>Tanggal Sumpah PT</label>
                    <span><?php echo tampil_tanggal($data['tanggal_sumpah_pt']); ?></span>
                </div>
                <div class="data-item">
                    <label>Status Keanggotaan</label>
                    <span><?php echo ucfirst(str_replace('_', ' ', tampil($data['status_keanggotaan']))); ?></span>
                </div>
                
                <div class="data-item" style="grid-column: span 2; display: flex; gap: 20px; margin-top: 15px;">
                    <?php if(!empty($data['file_ktpa'])): ?>
                    <div class="file-box" style="flex:1;">
                        <i class="fa-solid fa-file-pdf fa-2x" style="color:#ef4444; margin-bottom:10px;"></i><br>
                        <a href="uploads/<?php echo $data['file_ktpa']; ?>" target="_blank">Lihat Kartu Advokat (KTPA)</a>
                    </div>
                    <?php else: ?>
                    <div class="file-box" style="flex:1; opacity: 0.5;">
                        <i class="fa-solid fa-file-circle-xmark fa-2x" style="color:#94a3b8; margin-bottom:10px;"></i><br>
                        <span style="font-size:0.9rem; color:#64748b;">Belum ada KTPA</span>
                    </div>
                    <?php endif; ?>

                    </div>

            </div>
        </div>
        <br><br>
    </div>

</body>
</html>