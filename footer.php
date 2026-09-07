<style>
    /* --- CSS KHUSUS FOOTER --- */
    footer {
        background-color: #1e3a8a; /* Biru Tua Solid */
        color: white;
        padding-top: 60px;
        padding-bottom: 30px;
        border-top: 5px solid #dea057; /* Aksen Emas di atas */
        font-family: 'Montserrat', sans-serif;
        margin-top: auto;
        position: relative;
    }

    /* --- BAGIAN LOGO --- */
    .footer-branding {
        text-align: center;
        margin-bottom: 50px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 30px;
        width: 100%;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    .footer-branding h3 {
        margin-top: 15px;
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: white;
    }

    /* --- LAYOUT 3 KOLOM --- */
    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 40px;
    }

    /* Kolom Footer */
    .footer-col {
        flex: 1;
        min-width: 280px;
    }

    /* Judul Per Bagian */
    .footer-heading {
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 25px;
        color: #dea057;
        position: relative;
    }

    /* Isi Konten Text */
    .footer-text {
        font-size: 0.9rem;
        line-height: 1.8;
        color: #e2e8f0;
        margin-bottom: 15px;
    }

    /* List Hubungi (Icon + Text) */
    .contact-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .contact-list li {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
        font-size: 0.9rem;
        color: #e2e8f0;
        line-height: 1.6;
    }
    .contact-list i {
        color: white;
        margin-right: 15px;
        font-size: 1.1rem;
        margin-top: 3px;
        width: 20px;
        text-align: center;
    }

    /* Social Media Icons */
    .social-links {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    .social-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        text-decoration: none;
        transition: 0.3s;
        font-size: 1.2rem;
    }
    .social-icon:hover {
        background-color: #dea057;
        border-color: #dea057;
        color: #1e3a8a;
    }

    /* Jam Operasional */
    .operational-time h4 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 5px;
        color: white;
    }
    .operational-time p {
        font-size: 0.9rem;
        color: #e2e8f0;
        margin-bottom: 15px;
    }

    /* Copyright Bawah */
    .copyright-area {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 50px;
        padding-top: 25px;
        text-align: center;
        font-size: 0.9rem;
        color: #cbd5e1;
        line-height: 1.8;
    }
    .copyright-area strong {
        color: #dea057;
        font-weight: 700;
    }

    /* --- TOMBOL SCROLL TOP (POJOK KANAN BAWAH) --- */
    .btn-scroll-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: #dea057; /* Warna Emas Sesuai Gambar */
        color: #1e3a8a; /* Icon Biru Tua agar kontras */
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        z-index: 9999;
        opacity: 0; /* Hidden default */
        visibility: hidden;
        transition: all 0.4s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        border: none;
        outline: none;
        cursor: pointer;
        border-radius: 0; /* Kotak Persegi (Tajam) sesuai gambar */
    }

    /* Class saat tombol muncul */
    .btn-scroll-top.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .btn-scroll-top:hover {
        background-color: #c58d4a; 
        color: white;
        transform: translateY(-3px);
    }

    @media (max-width: 768px) {
        .footer-container {
            flex-direction: column;
        }
        .footer-col {
            margin-bottom: 20px;
        }
        .btn-scroll-top {
            bottom: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
        }
    }
</style>

<footer>
    
    <div class="footer-branding">
        <i class="fa-solid fa-scale-balanced fa-4x" style="color: #dea057;"></i>
        <h3>DPC PERADI PONTIANAK</h3>
    </div>

    <div class="footer-container">
        
        <div class="footer-col">
            <h3 class="footer-heading">HUBUNGI</h3>
            <ul class="contact-list">
                <li>
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Jl. Sultan Abdurrahman No. 12 D Akcaya, Pontianak, Kalimantan Barat</span>
                </li>
                <li>
                    <i class="fa-solid fa-phone"></i>
                    <span>0812-8570-1976</span>
                </li>
                <li>
                    <i class="fa-solid fa-envelope"></i>
                    <span>dpcperadipontianak@gmail.com</span>
                </li>
            </ul>
        </div>

        <div class="footer-col">
            <h3 class="footer-heading">IKUTI KAMI</h3>
            <p class="footer-text">
                Dapatkan informasi terkini mengenai kegiatan, berita, dan pengumuman resmi dari DPC PERADI Pontianak melalui media sosial kami.
            </p>
            <div class="social-links">
                <a href="https://www.facebook.com/share/1DAwJbj2Xu/" class="social-icon" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/dpcperadipontianak?igsh=MWx2aHMxemtqeXlnMg==" class="social-icon" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@dpcperadipnk01?_r=1&_t=ZS-93CSzfoVJdB" class="social-icon" target="_blank"><i class="fa-brands fa-tiktok"></i></a>
            </div>
        </div>

        <div class="footer-col">
            <h3 class="footer-heading">JAM OPERASIONAL</h3>
            <div class="operational-time">
                <h4>SENIN - JUMAT</h4>
                <p>09.00 WIB - 16.00 WIB</p>
                
                <h4>SABTU - MINGGU</h4>
                <p>Tutup</p>
            </div>
        </div>

    </div>

    <div class="copyright-area">
        &copy; <?php echo date("Y"); ?> DPC PERADI Pontianak. All Rights Reserved. <br>
        Developed by <strong>Polnep Interns</strong>
    </div>

    <a href="#" class="btn-scroll-top" id="scrollTopBtn">
        <i class="fa-solid fa-angles-up fa-lg"></i>
    </a>

</footer>

<script>
    const scrollTopBtn = document.getElementById("scrollTopBtn");

    window.onscroll = function() {
        // Munculkan tombol jika scroll lebih dari 300px
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            scrollTopBtn.classList.add("show");
        } else {
            scrollTopBtn.classList.remove("show");
        }
    };

    scrollTopBtn.addEventListener("click", function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
</script>