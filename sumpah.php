<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sumpah Advokat - DPC PERADI Pontianak</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- CSS GLOBAL --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background-color: #f8f9fa; color: #333; }

        /* HEADER PAGE */
        .page-header {
            position: relative; width: 100%; height: 50vh;
            background-image: url('https://images.unsplash.com/photo-1555374018-13a8994ab246?q=80&w=1930&auto=format&fit=crop'); 
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center; text-align: center;
            margin-top: 0; 
        }
        .page-header::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(30, 58, 138, 0.8), rgba(30, 58, 138, 0.6)); z-index: 1; }
        .header-content { position: relative; z-index: 2; color: white; margin-top: 20px; }
        .header-title { font-size: 3rem; font-weight: 800; color: #dea057; text-transform: uppercase; letter-spacing: 2px; text-shadow: 2px 2px 10px rgba(0,0,0,0.3); }
        .header-subtitle { font-size: 1.2rem; color: white; font-weight: 300; letter-spacing: 4px; margin-top: 10px; text-transform: uppercase; }

        /* KONTEN UTAMA */
        .content-section { padding: 80px 5%; max-width: 1200px; margin: 0 auto; }
        
        /* Judul Section Rata Kiri */
        .section-title { font-size: 1.8rem; color: #1e3a8a; margin-bottom: 20px; border-left: 5px solid #dea057; padding-left: 15px; font-weight: 700; text-transform: uppercase; }
        
        /* Judul Section Rata Tengah */
        .section-title-center { 
            text-align: center; 
            font-size: 1.8rem; 
            color: #1e3a8a; 
            margin-bottom: 40px; 
            font-weight: 700; 
            text-transform: uppercase; 
            position: relative;
        }
        .section-title-center::after {
            content: ''; display: block; width: 60px; height: 4px; background: #dea057; 
            margin: 15px auto 0; border-radius: 2px;
        }

        .text-content { font-size: 1rem; line-height: 1.8; color: #555; text-align: justify; margin-bottom: 40px; }

        /* --- PENGUMUMAN BOX (Biru Gradasi) --- */
        .pengumuman-box {
            background: linear-gradient(135deg, #1e3a8a 0%, #0d2566 100%);
            color: white;
            padding: 35px 40px;
            border-radius: 12px;
            /* Margin disesuaikan agar rapi di posisi baru */
            margin-bottom: 80px; 
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.25);
            flex-wrap: wrap;
            gap: 20px;
            border-left: 5px solid #dea057;
        }

        .pengumuman-text h3 {
            font-size: 1.6rem;
            color: #dea057;
            margin-bottom: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .pengumuman-text p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
            max-width: 700px;
        }

        .btn-pengumuman {
            background: #dea057;
            color: #1e3a8a;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.9rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(222, 160, 87, 0.3);
        }
        .btn-pengumuman:hover {
            background: white;
            transform: translateY(-3px);
        }

        /* VISUALISASI ALUR */
        .alur-box { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 60px; flex-wrap: wrap; }
        .alur-step { flex: 1; background: white; padding: 30px 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 8px; border-bottom: 4px solid #ddd; transition: 0.3s; min-width: 200px; }
        .alur-step:hover { transform: translateY(-5px); border-bottom-color: #dea057; }
        .step-number { font-size: 2.5rem; font-weight: 800; color: #f0f0f0; margin-bottom: 10px; }
        .step-icon { color: #dea057; font-size: 2rem; margin-bottom: 15px; }
        .alur-step h4 { margin-bottom: 10px; font-size: 1.1rem; color: #333; }
        .alur-step p { font-size: 0.85rem; color: #666; }

        /* SYARAT LIST */
        .syarat-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-bottom: 60px; }
        .syarat-col h3 { font-size: 1.3rem; margin-bottom: 20px; color: #1e3a8a; border-bottom: 2px solid #dea057; padding-bottom: 10px; display: inline-block; font-weight: 700; }
        .check-list { list-style: none; }
        .check-list li { margin-bottom: 15px; display: flex; align-items: flex-start; gap: 12px; color: #555; font-size: 0.95rem; }
        .check-list i { color: #dea057; font-size: 1.1rem; margin-top: 3px; }

        @media (max-width: 768px) {
            .alur-box { flex-direction: column; }
            .pengumuman-box { text-align: center; justify-content: center; }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <header class="page-header">
        <div class="header-content">
            <h1 class="header-title">PENGANGKATAN & SUMPAH</h1>
            <p class="header-subtitle">Langkah Terakhir Menjadi Officium Nobile</p>
        </div>
    </header>

    <div class="content-section">
        
        <h2 class="section-title">Proses Pelantikan</h2>
        <p class="text-content">
            Berdasarkan UU No. 18 Tahun 2003 tentang Advokat, seseorang yang telah memenuhi persyaratan dapat diangkat sebagai Advokat oleh Organisasi Advokat dan selanjutnya wajib bersumpah di hadapan Sidang Terbuka Pengadilan Tinggi di wilayah domisili hukumnya sebelum menjalankan profesi.
        </p>

        <div class="pengumuman-box">
            <div class="pengumuman-text">
                <h3><i class="fa-solid fa-gavel"></i> Pengumuman Sumpah Advokat</h3>
                <p>
                    Informasi jadwal, lokasi, dan daftar nama calon advokat yang telah terverifikasi untuk mengikuti 
                    Sidang Terbuka Pengambilan Sumpah di Pengadilan Tinggi Pontianak periode ini.
                </p>
            </div>
            
            <a href="user/login.php" target="_blank" class="btn-pengumuman">
                <i class="fa-solid fa-file-arrow-down"></i> Unduh Pengumuman
            </a>
        </div>
        <h2 class="section-title-center">Alur Menjadi Advokat</h2>
        
        <div class="alur-box">
            <div class="alur-step">
                <div class="step-number">01</div>
                <div class="step-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <h4>Lulus UPA</h4>
                <p>Memiliki sertifikat kelulusan Ujian Profesi Advokat yang sah.</p>
            </div>
            <div class="alur-step">
                <div class="step-number">02</div>
                <div class="step-icon"><i class="fa-solid fa-briefcase"></i></div>
                <h4>Magang 2 Tahun</h4>
                <p>Magang terus-menerus di kantor advokat minimal 2 tahun.</p>
            </div>
            <div class="alur-step">
                <div class="step-number">03</div>
                <div class="step-icon"><i class="fa-solid fa-file-contract"></i></div>
                <h4>Verifikasi Berkas</h4>
                <p>Pengumpulan berkas ke DPC untuk diverifikasi DPN PERADI.</p>
            </div>
            <div class="alur-step">
                <div class="step-number">04</div>
                <div class="step-icon"><i class="fa-solid fa-gavel"></i></div>
                <h4>Sumpah PT</h4>
                <p>Pengambilan sumpah di Pengadilan Tinggi Pontianak.</p>
            </div>
        </div>

        <div class="syarat-container">
            <div class="syarat-col">
                <h3><i class="fa-solid fa-user-check"></i> Syarat Umum</h3>
                <ul class="check-list">
                    <li><i class="fa-solid fa-check"></i> Warga Negara Indonesia (WNI).</li>
                    <li><i class="fa-solid fa-check"></i> Bertempat tinggal di Indonesia.</li>
                    <li><i class="fa-solid fa-check"></i> Tidak berstatus sebagai Pegawai Negeri Sipil atau Pejabat Negara.</li>
                    <li><i class="fa-solid fa-check"></i> Berusia sekurang-kurangnya 25 (dua puluh lima) tahun.</li>
                    <li><i class="fa-solid fa-check"></i> Berijazah Sarjana Hukum (S.H).</li>
                </ul>
            </div>
            
            <div class="syarat-col">
                <h3><i class="fa-solid fa-folder-open"></i> Syarat Administrasi</h3>
                <ul class="check-list">
                    <li><i class="fa-solid fa-check"></i> Fotokopi KTP & Kartu Keluarga (Legalisir).</li>
                    <li><i class="fa-solid fa-check"></i> Fotokopi Ijazah S1 Hukum (Legalisir Basah).</li>
                    <li><i class="fa-solid fa-check"></i> Sertifikat PKPA & UPA (Asli & Fotokopi).</li>
                    <li><i class="fa-solid fa-check"></i> Surat Keterangan Magang dari Kantor Advokat.</li>
                    <li><i class="fa-solid fa-check"></i> SKCK Asli (Keperluan: Pengangkatan Advokat).</li>
                    <li><i class="fa-solid fa-check"></i> Surat Pernyataan Tidak Pernah Dipidana (Materai).</li>
                </ul>
            </div>
        </div>

    </div>

    <?php include 'footer.php'; ?>

</body>
</html>