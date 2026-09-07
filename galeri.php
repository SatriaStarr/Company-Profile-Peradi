<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - DPC PERADI Pontianak</title>
    
    <style>
        /* --- CSS KHUSUS HALAMAN GALERI --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background-color: #f8f9fa; color: #333; }
        
        /* HEADER PAGE */
        .page-header { 
            position: relative; width: 100%; height: 50vh; 
            background-image: url('https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=2070&auto=format&fit=crop'); 
            background-size: cover; background-position: center; 
            display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; 
            margin-top: 0; 
        }
        .page-header::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(30, 58, 138, 0.9), rgba(30, 58, 138, 0.6)); z-index: 1; }
        .header-content { position: relative; z-index: 2; color: white; margin-top: 20px; }
        .header-title { font-size: 3rem; font-weight: 800; text-transform: uppercase; margin-bottom: 10px; }
        .header-subtitle { font-size: 1.1rem; letter-spacing: 3px; font-weight: 400; text-transform: uppercase; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 15px; display: inline-block;}

        /* CONTAINER UTAMA */
        .container { max-width: 1200px; margin: 80px auto; padding: 0 20px; }
        
        /* JUDUL SECTION */
        .section-header { text-align: center; margin-bottom: 60px; }
        .section-header h2 { font-size: 2rem; color: #1e3a8a; margin-bottom: 10px; font-weight: 800; text-transform: uppercase; }
        .section-header p { color: #666; max-width: 600px; margin: 0 auto; line-height: 1.6; }

        /* GRID SYSTEM (3 KOLOM) */
        .gallery-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
            gap: 40px; 
        }

        /* CARD STYLE */
        .gallery-card { 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #eee;
        }

        .gallery-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.15); 
        }

        /* FOTO DI ATAS */
        .card-image { 
            height: 250px; 
            width: 100%; 
            overflow: hidden; 
        }
        .card-image img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.5s; 
        }
        .gallery-card:hover .card-image img { 
            transform: scale(1.1); 
        }

        /* CONTENT TEKS DI BAWAH */
        .card-content { 
            padding: 30px; 
            text-align: center; 
        }

        .card-title { 
            font-size: 1.2rem; 
            font-weight: 700; 
            color: #1e3a8a; 
            margin-bottom: 10px; 
            line-height: 1.4;
        }

        .card-date { 
            font-size: 0.85rem; 
            color: #dea057; /* Warna Emas */
            font-weight: 600; 
            margin-bottom: 15px; 
            display: block; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card-desc { 
            font-size: 0.95rem; 
            color: #666; 
            line-height: 1.6; 
            margin-bottom: 0;
        }

        /* --- CSS TAMBAHAN UNTUK TOMBOL PAGINATION --- */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 60px; /* Jarak dari grid */
        }

        .pagination-link {
            display: inline-block;
            padding: 12px 25px;
            border: 1px solid #ddd;
            background-color: white;
            color: #555;
            text-decoration: none;
            font-size: 1rem;
            transition: 0.3s;
            margin: 0; /* Rapat tanpa celah */
        }

        /* Border overlapping fix */
        .pagination-link + .pagination-link {
            border-left: none; 
        }

        .pagination-link:hover {
            background-color: #f9f9f9;
            color: #1e3a8a;
        }

        /* Style untuk halaman aktif (Nomor 1) - DIUBAH JADI BIRU */
        .pagination-link.active {
            background-color: #1e3a8a; /* Biru Tua (Sesuai Footer) */
            color: white;
            border-color: #1e3a8a;
        }

        @media (max-width: 768px) {
            .header-title { font-size: 2rem; }
            .gallery-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <header class="page-header">
        <div class="header-content">
            <h1 class="header-title">JEJAK VISUAL</h1>
            <p class="header-subtitle">Dokumentasi Kegiatan DPC PERADI Pontianak</p>
        </div>
    </header>

    <div class="container">
        
        <div class="section-header">
            <h2>Dokumentasi Kegiatan</h2>
            <p>Berbagai momen penting dalam perjalanan organisasi, kegiatan sosial, dan acara resmi DPC PERADI Pontianak.</p>
        </div>

        <div class="gallery-grid">
            
            <div class="gallery-card">
                <div class="card-image">
                    <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?q=80&w=1932&auto=format&fit=crop" alt="Musyawarah">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Musyawarah Cabang 2025</h3>
                    <span class="card-date">15 Januari 2025</span>
                    <p class="card-desc">
                        Pelaksanaan Musyawarah Cabang untuk menentukan arah kebijakan organisasi dan pemilihan pengurus baru periode mendatang.
                    </p>
                </div>
            </div>

            <div class="gallery-card">
                <div class="card-image">
                    <img src="https://images.unsplash.com/photo-1577962917302-cd874c4e31d2?q=80&w=1932&auto=format&fit=crop" alt="Pelantikan">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Pelantikan Anggota Baru</h3>
                    <span class="card-date">20 Februari 2025</span>
                    <p class="card-desc">
                        Prosesi pelantikan dan pengambilan sumpah advokat baru di wilayah hukum Pengadilan Tinggi Pontianak.
                    </p>
                </div>
            </div>

            <div class="gallery-card">
                <div class="card-image">
                    <img src="https://images.unsplash.com/photo-1575089976121-8ed7b2a54265?q=80&w=1974&auto=format&fit=crop" alt="Bakti Sosial">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Bakti Sosial & Penyuluhan</h3>
                    <span class="card-date">10 Maret 2025</span>
                    <p class="card-desc">
                        Kegiatan pengabdian masyarakat berupa penyuluhan hukum gratis dan pembagian bantuan sosial bagi warga membutuhkan.
                    </p>
                </div>
            </div>

            <div class="gallery-card">
                <div class="card-image">
                    <img src="https://images.unsplash.com/photo-1589829085413-56de8ae18c73?q=80&w=2000&auto=format&fit=crop" alt="Diskusi">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Diskusi Hukum Nasional</h3>
                    <span class="card-date">05 April 2025</span>
                    <p class="card-desc">
                        Seminar dan diskusi interaktif membahas isu-isu hukum terkini bersama pakar hukum nasional dan akademisi.
                    </p>
                </div>
            </div>

            <div class="gallery-card">
                <div class="card-image">
                    <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=2070&auto=format&fit=crop" alt="Rapat Kerja">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Rapat Kerja Wilayah</h3>
                    <span class="card-date">12 Mei 2025</span>
                    <p class="card-desc">
                        Evaluasi program kerja dan penyusunan strategi advokasi untuk meningkatkan pelayanan hukum di Kalimantan Barat.
                    </p>
                </div>
            </div>

            <div class="gallery-card">
                <div class="card-image">
                    <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?q=80&w=2069&auto=format&fit=crop" alt="Kunjungan">
                </div>
                <div class="card-content">
                    <h3 class="card-title">Kunjungan Kelembagaan</h3>
                    <span class="card-date">25 Juni 2025</span>
                    <p class="card-desc">
                        Silaturahmi dan kunjungan kerja ke instansi penegak hukum guna mempererat sinergitas antar lembaga.
                    </p>
                </div>
            </div>

        </div>

        <div class="pagination-container">
            <a href="#" class="pagination-link">Previous</a>
            <a href="#" class="pagination-link active">1</a>
            <a href="#" class="pagination-link">2</a>
            <a href="#" class="pagination-link">Next</a>
        </div>

    </div>

    <?php include 'footer.php'; ?>

</body>
</html>