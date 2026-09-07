<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Pengurus - DPC PERADI Pontianak</title>
    
    <style>
        /* --- CSS KHUSUS HALAMAN STRUKTUR --- */
        /* (CSS Navbar & Footer sudah dihapus karena ikut header.php) */

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background-color: #f9f9f9; color: #333; }

        /* HERO HEADER */
        .page-header {
            position: relative; width: 100%; height: 50vh;
            background-image: url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=2084&auto=format&fit=crop');
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center; text-align: center;
            
            /* Margin 0 karena Navbar di header.php sudah Relative */
            margin-top: 0; 
        }
        .page-header::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(30, 58, 138, 0.7); z-index: 1; }
        .header-content { position: relative; z-index: 2; color: white; margin-top: 20px; }
        .header-title { font-size: 3rem; font-weight: 800; text-transform: uppercase; }

        /* STRUKTUR CONTENT (Grid Pengurus) */
        .container { max-width: 1000px; margin: 50px auto; padding: 0 20px; text-align: center; }
        
        /* Grid Layout */
        .pengurus-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-top: 40px; }
        
        /* Card Style */
        .pengurus-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: 0.3s; border-bottom: 4px solid transparent; }
        .pengurus-card:hover { transform: translateY(-10px); border-bottom-color: #dea057; }
        
        /* Foto & Teks */
        .foto-profil { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 20px; border: 4px solid #dea057; }
        .nama { font-weight: 700; font-size: 1.1rem; margin-bottom: 5px; color: #333; }
        .jabatan { color: #1e3a8a; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; }
        
        /* Ketua Section (Lebih Besar) */
        .ketua-section { margin-bottom: 50px; }
        .ketua-card { max-width: 350px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 1px solid #eee; }
        .ketua-card .foto-profil { width: 150px; height: 150px; }
        .ketua-card .nama { font-size: 1.4rem; }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <header class="page-header">
        <div class="header-content">
            <h1 class="header-title">Struktur Kepengurusan</h1>
            <p style="color: #dea057; letter-spacing: 2px; font-weight:600;">PERIODE 2023 - 2027</p>
        </div>
    </header>

    <div class="container">
        
        <div class="ketua-section">
            <div class="ketua-card">
                <img src="admin/image/FOTO 1 1.png" alt="Ketua" class="foto-profil">
                <h3 class="nama">Nama Ketua DPC</h3>
                <p class="jabatan">Ketua DPC</p>
            </div>
        </div>

        <div class="pengurus-grid">
            <div class="pengurus-card">
                <img src="admin/image/FOTO 2 2.png" alt="Sekretaris" class="foto-profil">
                <h3 class="nama">Nama Sekretaris</h3>
                <p class="jabatan">Sekretaris</p>
            </div>
            <div class="pengurus-card">
                <img src="admin/image/FOTO 2 2.png" alt="Bendahara" class="foto-profil">
                <h3 class="nama">Nama Bendahara</h3>
                <p class="jabatan">Bendahara</p>
            </div>
            <div class="pengurus-card">
                <img src="admin/image/FOTO 2 2.png" alt="Bidang" class="foto-profil">
                <h3 class="nama">Nama Kabid</h3>
                <p class="jabatan">Ketua Bidang Pendidikan</p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>