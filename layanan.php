<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan - DPC PERADI Pontianak</title>
    
    <style>
        /* --- CSS KHUSUS HALAMAN LAYANAN --- */
        /* (CSS Navbar & Footer dihapus karena sudah dipanggil lewat PHP) */

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background-color: #f9f9f9; color: #333; scroll-behavior: smooth; }
        
        /* HEADER PAGE (Gambar Judul Halaman) */
        .page-header { 
            position: relative; width: 100%; height: 50vh; 
            background-image: url('https://images.unsplash.com/photo-1450101499163-c8848c66ca85?q=80&w=2070&auto=format&fit=crop'); 
            background-size: cover; background-position: center; 
            display: flex; align-items: center; justify-content: center; text-align: center; 
            
            /* Margin 0 karena Navbar di header.php sudah Relative */
            margin-top: 0; 
        }
        .page-header::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(30, 58, 138, 0.7); z-index: 1; }
        .header-content { position: relative; z-index: 2; color: white; margin-top: 20px; }
        .header-title { font-size: 3rem; font-weight: 800; text-transform: uppercase; }

        /* KONTEN LAYANAN */
        .service-section { padding: 80px 20px; border-bottom: 1px solid #ddd; }
        .container { max-width: 900px; margin: 0 auto; }
        .service-title { 
            font-size: 2rem; color: #1e3a8a; margin-bottom: 20px; 
            border-left: 5px solid #dea057; padding-left: 20px; 
            text-transform: uppercase; font-weight: 700;
        }
        .service-desc { line-height: 1.8; color: #555; margin-bottom: 20px; font-size: 1rem; }
        
        .btn-daftar { 
            display: inline-block; padding: 12px 30px; background: #1e3a8a; 
            color: white; text-decoration: none; font-weight: bold; margin-top: 10px;
            border-radius: 4px; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-daftar:hover { background: #dea057; color: #1e3a8a; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <header class="page-header">
        <div class="header-content">
            <h1 class="header-title">Layanan & Program</h1>
            <p style="color: #dea057; letter-spacing: 2px; font-weight: 600;">PENGEMBANGAN PROFESI ADVOKAT</p>
        </div>
    </header>

    <section id="pkpa" class="service-section">
        <div class="container">
            <h2 class="service-title">PKPA (Pendidikan Khusus Profesi Advokat)</h2>
            <p class="service-desc">
                Program pendidikan yang diselenggarakan oleh PERADI bekerjasama dengan Universitas terakreditasi. 
                Tujuannya adalah membekali calon advokat dengan keahlian hukum dan kode etik profesi sebelum mengikuti ujian.
            </p>
            <p class="service-desc" style="background: #eef2ff; padding: 20px; border-radius: 5px; border-left: 4px solid #1e3a8a;">
                <strong>Syarat Pendaftaran:</strong><br>
                1. Fotocopy Ijazah S1 Hukum (Legalisir)<br>
                2. Pas Foto 3x4 & 4x6<br>
                3. Mengisi Formulir Pendaftaran
            </p>
            <a href="#" class="btn-daftar">Daftar PKPA Sekarang</a>
        </div>
    </section>

    <section id="upa" class="service-section" style="background-color: #fff;">
        <div class="container">
            <h2 class="service-title">UPA (Ujian Profesi Advokat) & Konsultasi</h2>
            <p class="service-desc">
                Ujian Profesi Advokat adalah tahap selanjutnya setelah lulus PKPA. Kami juga menyediakan layanan konsultasi 
                bagi calon peserta ujian untuk persiapan materi dan administrasi agar lulus dengan nilai terbaik.
            </p>
            <a href="#" class="btn-daftar">Info Jadwal UPA</a>
        </div>
    </section>

    <section id="sumpah" class="service-section">
        <div class="container">
            <h2 class="service-title">Pengangkatan & Pengambilan Sumpah</h2>
            <p class="service-desc">
                Prosesi sakral pelantikan Advokat yang telah memenuhi seluruh persyaratan UU Advokat. 
                Dilakukan di hadapan Ketua Pengadilan Tinggi setempat sebagai syarat mutlak untuk beracara di pengadilan.
            </p>
            <a href="#" class="btn-daftar">Cek Syarat Sumpah</a>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</body>
</html>