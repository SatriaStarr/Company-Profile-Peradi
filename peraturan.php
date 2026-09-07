<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peraturan - DPC PERADI Pontianak</title>
    
    <style>
        /* --- 1. GLOBAL STYLE --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background-color: #f8f9fa; color: #333; }

        /* --- 2. HEADER PAGE (GAMBAR) --- */
        .page-header {
            position: relative; width: 100%; height: 50vh;
            background-image: url('https://images.unsplash.com/photo-1589578527966-fdac0f44566c?q=80&w=2070&auto=format&fit=crop');
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center; text-align: center;
            margin-top: 0;
        }
        .page-header::before { 
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0, 0, 0, 0.6); z-index: 1; 
        }
        .header-content { position: relative; z-index: 2; color: white; margin-top: 20px; }
        .header-title { font-size: 3rem; font-weight: 800; color: white; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        .header-subtitle { font-size: 1rem; color: #dea057; font-weight: 700; letter-spacing: 4px; text-transform: uppercase; }

        /* --- 3. LAYOUT WRAPPER --- */
        .layout-wrapper {
            display: flex; 
            max-width: 1200px;
            margin: 50px auto; 
            min-height: 800px; /* Tinggi minimum biar gagah */
            background: white;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            align-items: flex-start;
            border-radius: 4px;
            overflow: hidden; /* Biar sudut tumpul gak bocor */
        }

        /* --- 4. SIDEBAR NAVIGASI (KIRI) --- */
        .sidebar-nav {
            width: 260px;
            background: #f8f9fa;
            border-right: 1px solid #e2e8f0;
            flex-shrink: 0;
            position: sticky; top: 0; 
            height: 800px; /* Samakan dengan min-height wrapper */
            overflow-y: auto;
        }
        .sidebar-nav::-webkit-scrollbar { width: 5px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: #cbd5e1; }

        .nav-list { list-style: none; padding: 0; margin: 0; }

        /* Judul Utama Sidebar */
        .nav-list li.main-link div {
            display: block;
            background: #1e3a8a;
            color: white;
            padding: 18px 15px;
            font-size: 0.9rem;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            border-left: 6px solid #dea057;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* Tombol Menu */
        .nav-list li button {
            display: block;
            width: 100%;
            padding: 14px 15px;
            color: #64748b;
            background: #f8f9fa;
            border: none;
            border-bottom: 1px solid #e2e8f0;
            border-left: 6px solid transparent;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
        }

        /* Hover Effect */
        .nav-list li button:hover {
            background: #e2e8f0;
            color: #1e3a8a;
        }

        /* ACTIVE STATE (YANG LAGI DIBUKA) - INI YANG BIKIN RAPI */
        .nav-list li button.active {
            background: white; /* Putih nyatu sama konten */
            color: #1e3a8a; /* Teks Biru */
            border-left-color: #1e3a8a; /* Garis Biru Kiri */
            font-weight: 800;
            box-shadow: -5px 0 15px rgba(0,0,0,0.05);
            margin-right: -1px; /* Trik menutupi garis pembatas */
            border-right: 1px solid white; 
            position: relative; 
            z-index: 2;
        }

        /* --- 5. AREA KONTEN (KANAN) --- */
        .content-area {
            flex: 1;
            padding: 60px;
            line-height: 1.8;
            font-size: 0.95rem;
            color: #334155;
        }

        /* Animasi Fade In Halus */
        .tab-content { display: none; animation: fadeIn 0.4s ease-in-out; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* Typography Dokumen */
        .doc-header { text-align: center; margin-bottom: 50px; border-bottom: 3px double #e2e8f0; padding-bottom: 30px; }
        .doc-title { font-size: 1.5rem; font-weight: 800; color: #1e3a8a; text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.5px; }
        .doc-subtitle { font-size: 1.1rem; font-weight: 700; color: #333; margin-top: 0; }
        
        .bab-title { 
            font-size: 1.2rem; font-weight: 800; color: #1e3a8a; 
            margin-bottom: 25px; padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .pasal-title { font-weight: 800; color: #1e3a8a; margin-top: 30px; display: block; font-size: 1rem; }
        .text-justify { text-align: justify; margin-bottom: 15px; }
        .poin-list { padding-left: 25px; margin-top: 10px; margin-bottom: 20px; }
        .poin-list li { margin-bottom: 8px; }

        @media (max-width: 768px) {
            .layout-wrapper { flex-direction: column; height: auto; }
            .sidebar-nav { width: 100%; border-right: none; position: static; height: auto; max-height: 250px; }
            .content-area { padding: 30px; }
            .nav-list li button.active { border-right: none; border-bottom: 4px solid #1e3a8a; }
        }
    </style>
</head>
<body>

    <?php include 'header.php'; ?>

    <?php $doc = isset($_GET['doc']) ? $_GET['doc'] : 'uu18'; ?>

    <header class="page-header">
        <div class="header-content">
            <h1 class="header-title">HUKUM & PERATURAN</h1>
            <p class="header-subtitle">Dasar Hukum, Kode Etik & Pedoman Organisasi</p>
        </div>
    </header>

    <div class="layout-wrapper">
        
        <div class="sidebar-nav">
            <ul class="nav-list">
                
                <?php if($doc == 'uu18'): ?>
                    <li class="main-link"><div>Undang-Undang NO. 18 2003</div></li>
                    <li><button class="tab-link active" onclick="openTab(event, 'judul')">UU NO. 18 2003</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab1')">BAB I</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab2')">BAB II</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab3')">BAB III</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab4')">BAB IV</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab5')">BAB V</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab6')">BAB VI</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab7')">BAB VII</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab8')">BAB VIII</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab9')">BAB IX</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab10')">BAB X</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab11')">BAB XI</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab12')">BAB XII</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab13')">BAB XIII</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'penjelasan')">PENJELASAN</button></li>

                <?php elseif($doc == 'kodeetik'): ?>
                    <li class="main-link"><div>KODE ETIK</div></li>
                    <li><button class="tab-link active" onclick="openTab(event, 'pembukaan')">PEMBUKAAN</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab1')">BAB I</button></li>
                    <li><button class="tab-link" onclick="openTab(event, 'bab2')">BAB II</button></li>

                <?php else: ?>
                    <li class="main-link"><div>INFO</div></li>
                <?php endif; ?>

            </ul>
        </div>

        <div class="content-area">
            
            <?php if($doc == 'uu18'): ?>
                <div id="judul" class="tab-content active">
                    <div class="doc-header">
                        <h2 class="doc-title">UNDANG-UNDANG REPUBLIK INDONESIA<br>NOMOR 18 TAHUN 2003</h2>
                        <h3 class="doc-subtitle">TENTANG ADVOKAT</h3>
                    </div>
                    <p style="text-align:center; font-weight:700; margin-bottom:20px;">DENGAN RAHMAT TUHAN YANG MAHA ESA<br>PRESIDEN REPUBLIK INDONESIA,</p>
                    <p><strong>Menimbang:</strong></p>
                    <ol type="a" class="poin-list text-justify">
                        <li>Bahwa Negara Republik Indonesia, sebagai negara hukum berdasarkan Pancasila dan Undang-Undang Dasar Negara Republik Indonesia Tahun 1945...</li>
                        <li>Bahwa kekuasaan kehakiman yang bebas dari segala campur tangan dan pengaruh dari luar...</li>
                    </ol>
                    <br><p style="text-align:center; font-weight:700;">MEMUTUSKAN: Menetapkan UNDANG-UNDANG TENTANG ADVOKAT.</p>
                </div>

                <div id="bab1" class="tab-content">
                    <h3 class="bab-title">BAB I: KETENTUAN UMUM</h3>
                    <span class="pasal-title">Pasal 1</span>
                    <p class="text-justify">Dalam Undang-undang ini yang dimaksud dengan:</p>
                    <ol class="poin-list">
                        <li><strong>Advokat</strong> adalah orang yang berprofesi memberi jasa hukum...</li>
                        <li><strong>Jasa Hukum</strong> adalah jasa yang diberikan Advokat...</li>
                    </ol>
                </div>

                <div id="bab2" class="tab-content">
                    <h3 class="bab-title">BAB II: PENGANGKATAN ADVOKAT</h3>
                    <span class="pasal-title">Pasal 2</span>
                    <ol class="poin-list">
                        <li>Yang dapat diangkat sebagai Advokat adalah sarjana yang berlatar belakang pendidikan tinggi hukum...</li>
                        <li>Pengangkatan Advokat dilakukan oleh Organisasi Advokat.</li>
                    </ol>
                    <span class="pasal-title">Pasal 3</span>
                    <p class="text-justify">Syarat menjadi advokat:</p>
                    <ol type="a" class="poin-list">
                        <li>Warga Negara Republik Indonesia;</li>
                        <li>Bertempat tinggal di Indonesia;</li>
                    </ol>
                </div>

                <div id="bab3" class="tab-content">
                    <h3 class="bab-title">BAB III: PENGAWASAN</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB III di sini)</em></p>
                </div>

                <div id="bab4" class="tab-content">
                    <h3 class="bab-title">BAB IV: PENINDAKAN</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB IV di sini)</em></p>
                </div>

                <div id="bab5" class="tab-content">
                    <h3 class="bab-title">BAB V: HONORARIUM</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB V di sini)</em></p>
                </div>

                <div id="bab6" class="tab-content">
                    <h3 class="bab-title">BAB VI: BANTUAN HUKUM CUMA-CUMA</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB VI di sini)</em></p>
                </div>

                <div id="bab7" class="tab-content">
                    <h3 class="bab-title">BAB VII: KODE ETIK DAN DEWAN KEHORMATAN</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB VII di sini)</em></p>
                </div>

                <div id="bab8" class="tab-content">
                    <h3 class="bab-title">BAB VIII: ORGANISASI ADVOKAT</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB VIII di sini)</em></p>
                </div>

                <div id="bab9" class="tab-content">
                    <h3 class="bab-title">BAB IX: KETENTUAN PIDANA</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB IX di sini)</em></p>
                </div>

                <div id="bab10" class="tab-content">
                    <h3 class="bab-title">BAB X: KETENTUAN PERALIHAN</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB X di sini)</em></p>
                </div>

                <div id="bab11" class="tab-content">
                    <h3 class="bab-title">BAB XI: KETENTUAN LAIN-LAIN</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB XI di sini)</em></p>
                </div>

                <div id="bab12" class="tab-content">
                    <h3 class="bab-title">BAB XII: KETENTUAN PENUTUP</h3>
                    <p class="text-justify"><em>(Silakan isi teks lengkap BAB XII di sini)</em></p>
                </div>

                <div id="bab13" class="tab-content">
                    <h3 class="bab-title">BAB XIII</h3>
                    <p class="text-justify"><em>(Jika ada)</em></p>
                </div>
                
                <div id="penjelasan" class="tab-content">
                    <h3 class="bab-title">PENJELASAN</h3>
                    <p class="text-justify">Penjelasan atas Undang-Undang Republik Indonesia Nomor 18 Tahun 2003...</p>
                </div>


            <?php elseif($doc == 'kodeetik'): ?>
                <div id="pembukaan" class="tab-content active">
                    <div class="doc-header">
                        <h2 class="doc-title">KODE ETIK ADVOKAT</h2>
                        <h3 class="doc-subtitle">PEMBUKAAN</h3>
                    </div>
                    <p class="text-justify">Isi pembukaan kode etik...</p>
                </div>
                <div id="bab1" class="tab-content">
                    <h3 class="bab-title">BAB I: KETENTUAN UMUM</h3>
                    <p>Isi Bab 1 Kode Etik...</p>
                </div>
                <div id="bab2" class="tab-content">
                    <h3 class="bab-title">BAB II: KEPRIBADIAN</h3>
                    <p>Isi Bab 2 Kode Etik...</p>
                </div>

            <?php else: ?>
                <div style="text-align:center; padding:100px 20px;">
                    <i class="fa-solid fa-lock fa-4x" style="color:#ddd; margin-bottom:20px;"></i>
                    <h3 style="color:#555;">Dokumen Terbatas</h3>
                    <p>Silakan login untuk mengakses dokumen ini.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function openTab(evt, tabName) {
            // Sembunyikan semua
            var tabcontent = document.getElementsByClassName("tab-content");
            for (var i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
                tabcontent[i].classList.remove("active");
            }

            // Matikan class active di tombol
            var tablinks = document.getElementsByClassName("tab-link");
            for (var i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Munculkan yang dipilih
            document.getElementById(tabName).style.display = "block";
            setTimeout(() => { document.getElementById(tabName).classList.add("active"); }, 10);
            
            // Nyalakan tombol yang diklik
            evt.currentTarget.className += " active";
        }
    </script>

</body>
</html>