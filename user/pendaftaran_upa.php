<?php

session_start();

require_once __DIR__ . '/functions.php';

require_user();

$nama  = $_SESSION['nama_lengkap'] ?? '';
$email = $_SESSION['email'] ?? '';
$username = $_SESSION['username'] ?? '';

$pesan = $_GET['pesan'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Pendaftaran UPA - PERADI Pontianak</title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #1e3a8a;
            text-decoration: none;
            font-weight: 700;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }

        .title {
            color: #1e3a8a;
            margin: 0 0 10px;
            font-size: 28px;
            font-weight: 800;
        }

        .subtitle {
            color: #777;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            background: #fee2e2;
            color: #b91c1c;
            font-weight: 600;
        }

        .user-info {
            background: #f4f6ff;
            border-left: 4px solid #dea057;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .user-info p {
            margin: 0 0 8px;
        }

        .user-info p:last-child {
            margin-bottom: 0;
        }

        .user-info strong {
            color: #1e3a8a;
        }

        .section-title {
            margin: 35px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            color: #1e3a8a;
            font-size: 18px;
            font-weight: 800;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .full {
            grid-column: 1 / -1;
        }

        .form-group {
            margin-bottom: 5px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }

        .required {
            color: #dc2626;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30,58,138,.1);
        }

        .radio-group {
            display: flex;
            gap: 25px;
            align-items: center;
            min-height: 45px;
        }

        .radio-group label {
            margin: 0;
            font-weight: 500;
        }

        .radio-group input {
            width: auto;
            margin-right: 7px;
        }

        .file-box {
            padding: 15px;
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            background: #fafafa;
        }

        .file-box small {
            display: block;
            margin-top: 7px;
            color: #777;
        }

        .signature-box {
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px;
            background: #fafafa;
        }

        #signatureCanvas {
            width: 100%;
            height: 220px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: crosshair;
            touch-action: none;
        }

        .signature-buttons {
            margin-top: 10px;
        }

        .btn-clear {
            padding: 9px 15px;
            border: none;
            border-radius: 7px;
            background: #dc2626;
            color: white;
            cursor: pointer;
            font-weight: 600;
        }

        .agreement {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }

        .agreement label {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            line-height: 1.6;
        }

        .agreement input {
            width: auto;
            margin-top: 4px;
        }

        .submit-area {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .btn {
            display: inline-block;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        .btn:hover {
            background: #152c69;
        }

        .price {
            background: #eff6ff;
            color: #1e3a8a;
            padding: 18px;
            border-radius: 10px;
            margin-top: 25px;
            font-weight: 800;
            font-size: 17px;
        }

        @media(max-width: 768px) {

            .container {
                margin: 20px auto;
            }

            .card {
                padding: 22px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

            .submit-area {
                justify-content: stretch;
            }

            .submit-area .btn {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <a href="pendaftaran.php" class="back">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Pilihan Layanan
    </a>

    <div class="card">

        <h1 class="title">
            <i class="fa-solid fa-file-circle-check"></i>
            Pendaftaran UPA
        </h1>

        <p class="subtitle">
            Silakan lengkapi seluruh data peserta Ujian Profesi Advokat
            dan upload dokumen yang diperlukan.
        </p>

        <?php if ($pesan === 'sudah_daftar'): ?>

            <div class="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                Anda sudah memiliki pendaftaran UPA yang masih aktif.
            </div>

        <?php elseif ($pesan === 'data_tidak_lengkap'): ?>

            <div class="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                Semua data wajib harus diisi.
            </div>

        <?php elseif ($pesan === 'nik'): ?>

            <div class="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                Nomor identitas harus terdiri dari 16 digit.
            </div>

        <?php elseif ($pesan === 'upload'): ?>

            <div class="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                Terjadi masalah saat mengupload dokumen.
            </div>

        <?php elseif ($pesan === 'ttd'): ?>

            <div class="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                Tanda tangan wajib diisi.
            </div>

        <?php endif; ?>


        <div class="user-info">

            <p>
                <strong>Nama Akun:</strong>
                <?= e($nama) ?>
            </p>

            <p>
                <strong>Username:</strong>
                <?= e($username) ?>
            </p>

            <p>
                <strong>Email Akun:</strong>
                <?= e($email) ?>
            </p>

        </div>


        <form action="proses_pendaftaran_upa.php"
              method="POST"
              enctype="multipart/form-data">


            <!-- ========================================= -->
            <!-- DATA PRIBADI -->
            <!-- ========================================= -->

            <div class="section-title">
                <i class="fa-solid fa-user"></i>
                Data Pribadi
            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Nama Lengkap Tanpa Gelar
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="nama_lengkap"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Nama Lengkap + Gelar
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="nama_lengkap_gelar"
                           placeholder="Contoh: Dr. Budi Santoso, S.H., M.H."
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Jenis Identitas
                        <span class="required">*</span>
                    </label>

                    <select name="jenis_identitas" required>

                        <option value="">
                            -- Pilih Identitas --
                        </option>

                        <option value="KTP">
                            KTP
                        </option>

                        <option value="SIM">
                            SIM
                        </option>

                        <option value="Paspor">
                            Paspor
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Nomor Kartu Identitas
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="nomor_identitas"
                           maxlength="16"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Masa Berakhir Identitas
                    </label>

                    <input type="date"
                           name="masa_berakhir_identitas">

                    <small>
                        Tidak perlu diisi apabila menggunakan KTP.
                    </small>

                </div>


                <div class="form-group">

                    <label>
                        Jenis Kelamin
                        <span class="required">*</span>
                    </label>

                    <div class="radio-group">

                        <label>
                            <input type="radio"
                                   name="jenis_kelamin"
                                   value="Laki-Laki"
                                   required>
                            Laki-Laki
                        </label>

                        <label>
                            <input type="radio"
                                   name="jenis_kelamin"
                                   value="Perempuan">
                            Perempuan
                        </label>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Tempat Lahir
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="tempat_lahir"
                           placeholder="Kota, Provinsi"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Tanggal Lahir
                        <span class="required">*</span>
                    </label>

                    <input type="date"
                           name="tanggal_lahir"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Nomor HP / WhatsApp
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="no_hp"
                           placeholder="08xxxxxxxxxx"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Email
                        <span class="required">*</span>
                    </label>

                    <input type="email"
                           name="email"
                           value="<?= e($email) ?>"
                           required>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- ALAMAT -->
            <!-- ========================================= -->

            <div class="section-title">
                <i class="fa-solid fa-location-dot"></i>
                Alamat Lengkap
            </div>

            <div class="form-grid">

                <div class="form-group full">

                    <label>
                        Alamat Lengkap
                        <span class="required">*</span>
                    </label>

                    <textarea name="alamat"
                              rows="3"
                              required></textarea>

                </div>


                <div class="form-group">

                    <label>RT</label>

                    <input type="text"
                           name="rt">

                </div>


                <div class="form-group">

                    <label>RW</label>

                    <input type="text"
                           name="rw">

                </div>


                <div class="form-group">

                    <label>No. Rumah</label>

                    <input type="text"
                           name="no_rumah">

                </div>


                <div class="form-group">

                    <label>Kelurahan</label>

                    <input type="text"
                           name="kelurahan">

                </div>


                <div class="form-group">

                    <label>Kecamatan</label>

                    <input type="text"
                           name="kecamatan">

                </div>


                <div class="form-group">

                    <label>Kota</label>

                    <input type="text"
                           name="kota"
                           required>

                </div>


                <div class="form-group">

                    <label>Provinsi</label>

                    <input type="text"
                           name="provinsi"
                           required>

                </div>


                <div class="form-group">

                    <label>Kode Pos</label>

                    <input type="text"
                           name="kode_pos">

                </div>

            </div>


            <!-- ========================================= -->
            <!-- UJIAN -->
            <!-- ========================================= -->

            <div class="section-title">
                <i class="fa-solid fa-building-columns"></i>
                Data Ujian
            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Kota Tempat Mengikuti Ujian
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="kota_ujian"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        DPC PERADI yang Dipilih
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="dpc_dipilih"
                           value="DPC PERADI Pontianak"
                           required>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- KEBUTUHAN KHUSUS -->
            <!-- ========================================= -->

            <div class="section-title">
                <i class="fa-solid fa-wheelchair"></i>
                Kebutuhan Khusus / Difabel
            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Membutuhkan Fasilitas Khusus?
                        <span class="required">*</span>
                    </label>

                    <select name="kebutuhan_khusus"
                            id="kebutuhan_khusus"
                            required>

                        <option value="">
                            -- Pilih --
                        </option>

                        <option value="Tidak">
                            Tidak
                        </option>

                        <option value="Ya">
                            Ya
                        </option>

                    </select>

                </div>


                <div class="form-group">

                    <label>
                        Jika Ya, Jelaskan
                    </label>

                    <textarea name="keterangan_kebutuhan"
                              rows="3"
                              placeholder="Jelaskan kondisi dan kebutuhan fasilitas"></textarea>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- PENDIDIKAN -->
            <!-- ========================================= -->

            <div class="section-title">
                <i class="fa-solid fa-graduation-cap"></i>
                Latar Belakang Pendidikan S1
            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Tahun Lulus
                        <span class="required">*</span>
                    </label>

                    <input type="number"
                           name="tahun_lulus"
                           min="1900"
                           max="2100"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Nomor Ijazah
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="no_ijazah"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Fakultas
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="fakultas"
                           value="Hukum"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Nama Universitas
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="nama_universitas"
                           required>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- PKPA -->
            <!-- ========================================= -->

            <div class="section-title">
                <i class="fa-solid fa-scale-balanced"></i>
                Data PKPA yang Telah Diikuti
            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Nama yang Tercetak pada Sertifikat PKPA
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="nama_sertifikat_pkpa"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Nomor Sertifikat PKPA
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="nomor_sertifikat_pkpa"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Tanggal Sertifikat PKPA
                        <span class="required">*</span>
                    </label>

                    <input type="date"
                           name="tanggal_sertifikat_pkpa"
                           required>

                </div>


                <div class="form-group">

                    <label>
                        Nama Pelaksana PKPA
                        <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="pelaksana_pkpa"
                           required>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- DOKUMEN -->
            <!-- ========================================= -->

            <div class="section-title">
                <i class="fa-solid fa-file-arrow-up"></i>
                Dokumen Pendaftaran
            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Foto Peserta 3x4
                        <span class="required">*</span>
                    </label>

                    <div class="file-box">

                        <input type="file"
                               name="foto"
                               accept="image/jpeg,image/png"
                               required>

                        <small>
                            JPG/PNG, maksimal 2 MB.
                        </small>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Foto/Scan KTP
                        <span class="required">*</span>
                    </label>

                    <div class="file-box">

                        <input type="file"
                               name="foto_ktp"
                               accept="image/jpeg,image/png"
                               required>

                        <small>
                            JPG/PNG, maksimal 2 MB.
                        </small>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Ijazah S1 Hukum
                        <span class="required">*</span>
                    </label>

                    <div class="file-box">

                        <input type="file"
                               name="foto_ijazah"
                               accept="image/jpeg,image/png,application/pdf"
                               required>

                        <small>
                            JPG/PNG/PDF, maksimal 2 MB.
                        </small>

                    </div>

                </div>


                <div class="form-group">

                    <label>
                        Sertifikat PKPA
                        <span class="required">*</span>
                    </label>

                    <div class="file-box">

                        <input type="file"
                               name="sertifikat_pkpa"
                               accept="image/jpeg,image/png,application/pdf"
                               required>

                        <small>
                            JPG/PNG/PDF, maksimal 2 MB.
                        </small>

                    </div>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- TANDA TANGAN -->
            <!-- ========================================= -->

            <div class="section-title">
                <i class="fa-solid fa-signature"></i>
                Tanda Tangan Peserta
            </div>

            <div class="signature-box">

                <p>
                    Silakan tanda tangan menggunakan mouse,
                    touchpad, atau layar sentuh.
                </p>

                <canvas id="signatureCanvas"></canvas>

                <input type="hidden"
                       name="tanda_tangan"
                       id="tandaTangan">

                <div class="signature-buttons">

                    <button type="button"
                            class="btn-clear"
                            onclick="clearSignature()">

                        <i class="fa-solid fa-eraser"></i>
                        Hapus Tanda Tangan

                    </button>

                </div>

            </div>


            <!-- ========================================= -->
            <!-- PERSETUJUAN -->
            <!-- ========================================= -->

            <div class="agreement">

                <label>

                    <input type="checkbox"
                           name="persetujuan"
                           value="1"
                           required>

                    <span>
                        Saya menyatakan bahwa seluruh data dan dokumen
                        yang saya berikan adalah benar dan sah serta
                        bersedia mematuhi seluruh ketentuan pendaftaran
                        dan pelaksanaan Ujian Profesi Advokat.
                    </span>

                </label>

            </div>


            <div class="price">

                <i class="fa-solid fa-money-bill-wave"></i>

                Biaya Pendaftaran UPA:
                Rp 750.000

            </div>


            <div class="submit-area">

                <button type="submit"
                        class="btn">

                    <i class="fa-solid fa-paper-plane"></i>
                    Kirim Pendaftaran UPA

                </button>

            </div>

        </form>

    </div>

</div>


<script>

const canvas =
    document.getElementById('signatureCanvas');

const ctx =
    canvas.getContext('2d');

let drawing = false;


function resizeCanvas() {

    const ratio =
        Math.max(window.devicePixelRatio || 1, 1);

    canvas.width =
        canvas.offsetWidth * ratio;

    canvas.height =
        canvas.offsetHeight * ratio;

    ctx.scale(ratio, ratio);

    ctx.lineWidth = 2;

    ctx.lineCap = 'round';

    ctx.lineJoin = 'round';

}


resizeCanvas();

window.addEventListener(
    'resize',
    resizeCanvas
);


function getPosition(e) {

    const rect =
        canvas.getBoundingClientRect();

    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };

}


canvas.addEventListener(
    'pointerdown',
    function(e) {

        drawing = true;

        const pos =
            getPosition(e);

        ctx.beginPath();

        ctx.moveTo(
            pos.x,
            pos.y
        );

        canvas.setPointerCapture(e.pointerId);

    }
);


canvas.addEventListener(
    'pointermove',
    function(e) {

        if (!drawing) return;

        const pos =
            getPosition(e);

        ctx.lineTo(
            pos.x,
            pos.y
        );

        ctx.stroke();

    }
);


canvas.addEventListener(
    'pointerup',
    stopDrawing
);

canvas.addEventListener(
    'pointercancel',
    stopDrawing
);


function stopDrawing() {

    drawing = false;

}


function clearSignature() {

    ctx.clearRect(
        0,
        0,
        canvas.width,
        canvas.height
    );

    document.getElementById(
        'tandaTangan'
    ).value = '';

}


document.querySelector('form')
    .addEventListener(
        'submit',
        function(e) {

            const blank =
                document.createElement('canvas');

            blank.width =
                canvas.width;

            blank.height =
                canvas.height;

            const blankCtx =
                blank.getContext('2d');

            const original =
                canvas.toDataURL();

            const empty =
                blank.toDataURL();

            if (original === empty) {

                e.preventDefault();

                alert(
                    'Tanda tangan wajib diisi.'
                );

                return;

            }

            document.getElementById(
                'tandaTangan'
            ).value =
                canvas.toDataURL(
                    'image/png'
                );

        }
    );

</script>

</body>
</html>