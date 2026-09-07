<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DPC PERADI Pontianak - Official Web</title>

    <style>
        /* --- CSS KHUSUS HALAMAN HOME (Index) --- */
        /* (CSS Navbar, Running Text & Footer sudah dihapus karena ikut header.php/footer.php) */

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f4f6f9;
            color: #333;
            overflow-x: hidden;
        }

        /* --- HERO SECTION --- */
        .hero {
            position: relative; width: 100%; height: 100vh;
            /* Pastikan gambar gedung/landing page ada disini */
            background-image: url('admin/image/peradi-tower.jpg');
            background-size: cover; background-position: center; background-attachment: fixed;
            display: flex; align-items: center; justify-content: center; text-align: center;
            
            /* Margin 0 karena Navbar sekarang posisinya Relative */
            margin-top: 0;
        }
        .hero::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1; }

        .hero-content { position: relative; z-index: 2; color: white; padding: 0 20px; animation: fadeIn 1.2s ease-out; }
        .hero-subtitle { font-size: 1.1rem; color: #dea057; font-weight: 700; letter-spacing: 3px; margin-bottom: 15px; text-transform: uppercase; }
        .hero-title { font-size: 3.8rem; font-weight: 800; line-height: 1.1; text-transform: uppercase; margin-bottom: 25px; text-shadow: 2px 4px 10px rgba(0, 0, 0, 0.5); }
        .hero-desc { font-size: 1.1rem; font-weight: 300; max-width: 750px; margin: 0 auto 35px auto; color: #f1f5f9; line-height: 1.6; }

        .btn-cta {
            display: inline-block; padding: 15px 40px; background: #dea057; color: #1e3a8a;
            text-decoration: none; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            border-radius: 4px; transition: 0.3s; box-shadow: 0 4px 15px rgba(222, 160, 87, 0.4);
        }
        .btn-cta:hover { background: white; color: #1e3a8a; transform: translateY(-3px); }

        /* --- VISI MISI --- */
        .section-visi { padding: 100px 5%; background-color: white; }
        .section-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .visi-text h2 { font-size: 2.5rem; margin-bottom: 25px; text-transform: uppercase; color: #1e3a8a; font-weight: 800; border-left: 8px solid #dea057; padding-left: 20px; }
        .visi-text p { line-height: 1.8; color: #475569; margin-bottom: 15px; font-size: 1rem; text-align: justify; }
        .visi-img img { width: 100%; border-radius: 4px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); border: 5px solid #f4f6f9; }

        /* --- GALERI (PREVIEW DI HOME) --- */
        .section-gallery { padding: 80px 20px; background-color: #0f172a; color: white; text-align: center; }
        .gallery-title { font-size: 2.5rem; margin-bottom: 10px; text-transform: uppercase; color: white; font-weight: 800; }
        .gallery-subtitle { color: #dea057; letter-spacing: 2px; margin-bottom: 50px; display: block; font-weight: 600; text-transform: uppercase; }
        .gallery-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .gallery-item { position: relative; height: 260px; overflow: hidden; border-radius: 4px; cursor: pointer; border: 2px solid #1e3a8a; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .gallery-item:hover img { transform: scale(1.1); opacity: 0.8; }
        .gallery-overlay {
            position: absolute; bottom: 0; left: 0; width: 100%; padding: 15px;
            background: #1e3a8a; transform: translateY(100%); transition: 0.3s;
            color: white; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;
        }
        .gallery-item:hover .gallery-overlay { transform: translateY(0); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .hero-title { font-size: 2.5rem; } }
    </style>
</head>

<body>

    <?php include 'header.php'; ?>

    <section class="hero">
        <div class="hero-content">
            <p class="hero-subtitle">Sistem Informasi Manajemen</p>
            <h1 class="hero-title">DPC PERADI<br>PONTIANAK</h1>
            <p class="hero-desc">
                Wadah tunggal profesi Advokat yang bebas dan mandiri demi tegaknya hukum dan keadilan, serta menjunjung tinggi kode etik profesi.
            </p>
            <a href="#visi" class="btn-cta">Tentang Kami</a>
        </div>
    </section>

    <section id="visi" class="section-visi">
        <div class="section-container">
            <div class="visi-text">
                <h2>Visi & <span>Misi</span></h2>
                <p><strong>VISI:</strong><br>Menjadi organ negara yang bebas dan mandiri dalam melaksanakan fungsi negara dibidang penegakan hukum.</p>
                <p><strong>MISI:</strong><br>
                    1. Mengangkat Advokat dan melakukan pengawasan.<br>
                    2. Menyelenggarakan pendidikan khusus profesi Advokat.<br>
                    3. Menegakkan kode etik profesi Advokat Indonesia.
                </p>
                <br>
                <a href="struktur.php" style="color: #1e3a8a; font-weight: bold; text-decoration: none; border-bottom: 2px solid #dea057; padding-bottom: 2px;">Lihat Struktur Pengurus <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="visi-img">
                <img src="admin/image/Depan-kantor.jpeg" alt="Meeting Peradi">
            </div>
        </div>
    </section>

    <section class="section-gallery">
        <h2 class="gallery-title">Galeri Kegiatan</h2>
        <span class="gallery-subtitle">Dokumentasi Terbaru DPC PERADI Pontianak</span>

        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="admin/image/Dalam-kantor.jpeg" alt="Sidang">
                <div class="gallery-overlay">Ultah DPC Peradi</div>
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=1974&auto=format&fit=crop" alt="Rapat">
                <div class="gallery-overlay">Rapat Kerja 2025</div>
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1577962917302-cd874c4e31d2?q=80&w=2064&auto=format&fit=crop" alt="Pelantikan">
                <div class="gallery-overlay">Pelantikan Anggota</div>
            </div>
            <div class="gallery-item">
                <img src="https://images.unsplash.com/photo-1555374018-13a8994ab246?q=80&w=1915&auto=format&fit=crop" alt="Sosialisasi">
                <div class="gallery-overlay">Sosialisasi Hukum</div>
            </div>
        </div>

        <br><br>
        <a href="galeri.php" class="btn-cta" style="background:transparent; border: 2px solid #dea057; color: #dea057;">Lihat Semua Galeri</a>
    </section>

    <?php include 'footer.php'; ?>

</body>
</html>