<?php

session_start();

require_once __DIR__ . '/functions.php';

require_user();

$nama = $_SESSION['nama_lengkap'] ?? '';
$email = $_SESSION['email'] ?? '';
$username = $_SESSION['username'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pendaftaran PKPA - PERADI Pontianak</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #1e3a8a;
            text-decoration: none;
            font-weight: 600;
        }

        .card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }

        .title {
            color: #1e3a8a;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 800;
        }

        .subtitle {
            color: #777;
            margin-bottom: 30px;
        }

        .section-title {
            color: #1e3a8a;
            font-size: 18px;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eef2ff;
        }

        .user-info {
            background: #f4f6ff;
            border-left: 4px solid #dea057;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .user-info p {
            margin-bottom: 8px;
        }

        .user-info p:last-child {
            margin-bottom: 0;
        }

        .user-info strong {
            color: #1e3a8a;
        }

        .form-group {
            margin-bottom: 20px;
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
            border: 1px solid #ddd;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            background: white;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30,58,138,.1);
        }

        input[type="file"] {
            padding: 10px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .file-info {
            margin-top: 7px;
            font-size: 11px;
            color: #777;
        }

        .signature-box {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            background: #fafafa;
            padding: 10px;
        }

        #signatureCanvas {
            width: 100%;
            height: 220px;
            display: block;
            background: white;
            border-radius: 8px;
            cursor: crosshair;
            touch-action: none;
        }

        .signature-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .btn-clear {
            background: #e5e7eb;
            color: #333;
            border: none;
            padding: 10px 15px;
            border-radius: 7px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn {
            display: inline-block;
            padding: 13px 25px;
            border: none;
            border-radius: 8px;
            background: #1e3a8a;
            color: white;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .btn:hover {
            background: #152c69;
        }

        .btn-submit {
            margin-top: 20px;
            width: 100%;
            font-size: 15px;
        }

        .price {
            background: #eff6ff;
            color: #1e3a8a;
            padding: 18px;
            border-radius: 10px;
            margin-top: 30px;
            font-weight: 700;
            text-align: center;
        }

        @media(max-width: 700px) {

            .row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .card {
                padding: 22px;
            }

            .title {
                font-size: 23px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <a
        href="pendaftaran.php"
        class="back"
    >

        <i class="fa-solid fa-arrow-left"></i>

        Kembali ke Pilihan Layanan

    </a>


    <div class="card">

        <h1 class="title">

            <i class="fa-solid fa-scale-balanced"></i>

            Pendaftaran PKPA

        </h1>

        <p class="subtitle">

            Silakan lengkapi data diri dan dokumen
            untuk mengikuti Pendidikan Khusus Profesi Advokat.

        </p>


        <!-- DATA AKUN -->

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
                <strong>Email:</strong>
                <?= e($email) ?>
            </p>

        </div>


        <form
            action="proses_pendaftaran_pkpa.php"
            method="POST"
            enctype="multipart/form-data"
            id="formPKPA"
        >

            <!-- =================================================
                 DATA PRIBADI
            ================================================== -->

            <h2 class="section-title">

                <i class="fa-solid fa-user"></i>

                Data Calon Advokat

            </h2>


            <div class="form-group">

                <label>
                    Nama Lengkap
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="nama_lengkap"
                    value="<?= e($nama) ?>"
                    required
                >

            </div>


            <div class="row">

                <div class="form-group">

                    <label>
                        NIK
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="nik"
                        maxlength="16"
                        pattern="[0-9]{16}"
                        placeholder="16 digit NIK"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Pekerjaan
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="pekerjaan"
                        placeholder="Contoh: Advokat / Karyawan / Wiraswasta"
                        required
                    >

                </div>

            </div>


            <div class="row">

                <div class="form-group">

                    <label>
                        Tempat Lahir
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="tempat_lahir"
                        placeholder="Contoh: Pontianak"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Tanggal Lahir
                        <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        name="tanggal_lahir"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label>
                    Alamat
                    <span class="required">*</span>
                </label>

                <textarea
                    name="alamat"
                    rows="4"
                    placeholder="Masukkan alamat lengkap"
                    required
                ></textarea>

            </div>


            <div class="form-group">

                <label>
                    Nomor HP
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="no_hp"
                    placeholder="08xxxxxxxxxx"
                    required
                >

            </div>


            <!-- =================================================
                 DOKUMEN
            ================================================== -->

            <h2 class="section-title">

                <i class="fa-solid fa-file-lines"></i>

                Dokumen Persyaratan

            </h2>


            <div class="form-group">

                <label>
                    Foto Calon Advokat
                    <span class="required">*</span>
                </label>

                <input
                    type="file"
                    name="foto"
                    accept="image/jpeg,image/png"
                    required
                >

                <div class="file-info">
                    Format JPG/JPEG/PNG. Maksimal 2 MB.
                </div>

            </div>


            <div class="form-group">

                <label>
                    Foto / Scan Ijazah
                    <span class="required">*</span>
                </label>

                <input
                    type="file"
                    name="foto_ijazah"
                    accept="image/jpeg,image/png"
                    required
                >

                <div class="file-info">
                    Format JPG/JPEG/PNG. Maksimal 2 MB.
                </div>

            </div>


            <div class="form-group">

                <label>
                    Foto KTP
                    <span class="required">*</span>
                </label>

                <input
                    type="file"
                    name="foto_ktp"
                    accept="image/jpeg,image/png"
                    required
                >

                <div class="file-info">
                    Format JPG/JPEG/PNG. Maksimal 2 MB.
                </div>

            </div>


            <!-- =================================================
                 TANDA TANGAN
            ================================================== -->

            <h2 class="section-title">

                <i class="fa-solid fa-signature"></i>

                Tanda Tangan Calon Advokat

            </h2>


            <p
                style="
                    color:#64748b;
                    font-size:13px;
                    margin-bottom:15px;
                    line-height:1.6;
                "
            >

                Silakan tanda tangan pada kotak di bawah
                menggunakan mouse, touchpad, atau layar sentuh.

            </p>


            <div class="signature-box">

                <canvas id="signatureCanvas"></canvas>

                <div class="signature-buttons">

                    <button
                        type="button"
                        class="btn-clear"
                        id="clearSignature"
                    >

                        <i class="fa-solid fa-eraser"></i>

                        Hapus Tanda Tangan

                    </button>

                </div>

            </div>


            <!-- HASIL CANVAS -->

            <input
                type="hidden"
                name="tanda_tangan"
                id="tandaTangan"
            >


            <!-- =================================================
                 BIAYA
            ================================================== -->

            <div class="price">

                <i class="fa-solid fa-money-bill-wave"></i>

                Biaya Pendaftaran PKPA:

                <strong>
                    Rp 8.000.000
                </strong>

            </div>


            <button
                type="submit"
                class="btn btn-submit"
            >

                <i class="fa-solid fa-paper-plane"></i>

                Kirim Pendaftaran PKPA

            </button>


        </form>

    </div>

</div>


<script>

const canvas = document.getElementById('signatureCanvas');
const ctx = canvas.getContext('2d');

let drawing = false;
let hasSignature = false;


// =====================================================
// SET UKURAN CANVAS
// =====================================================

function resizeCanvas() {

    const rect = canvas.getBoundingClientRect();

    const oldData = canvas.toDataURL();

    canvas.width = rect.width;
    canvas.height = 220;

    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    ctx.strokeStyle = '#111827';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
}

resizeCanvas();


// =====================================================
// POSISI MOUSE / TOUCH
// =====================================================

function getPosition(event) {

    const rect = canvas.getBoundingClientRect();

    let clientX;
    let clientY;

    if (event.touches && event.touches.length > 0) {

        clientX = event.touches[0].clientX;
        clientY = event.touches[0].clientY;

    } else {

        clientX = event.clientX;
        clientY = event.clientY;

    }

    return {
        x: clientX - rect.left,
        y: clientY - rect.top
    };

}


// =====================================================
// MULAI GAMBAR
// =====================================================

function startDrawing(event) {

    event.preventDefault();

    drawing = true;
    hasSignature = true;

    const pos = getPosition(event);

    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);

}


// =====================================================
// MENGGAMBAR
// =====================================================

function draw(event) {

    if (!drawing) {
        return;
    }

    event.preventDefault();

    const pos = getPosition(event);

    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();

}


// =====================================================
// BERHENTI
// =====================================================

function stopDrawing(event) {

    if (event) {
        event.preventDefault();
    }

    drawing = false;

}


// =====================================================
// MOUSE
// =====================================================

canvas.addEventListener(
    'mousedown',
    startDrawing
);

canvas.addEventListener(
    'mousemove',
    draw
);

canvas.addEventListener(
    'mouseup',
    stopDrawing
);

canvas.addEventListener(
    'mouseleave',
    stopDrawing
);


// =====================================================
// TOUCH
// =====================================================

canvas.addEventListener(
    'touchstart',
    startDrawing,
    { passive: false }
);

canvas.addEventListener(
    'touchmove',
    draw,
    { passive: false }
);

canvas.addEventListener(
    'touchend',
    stopDrawing,
    { passive: false }
);


// =====================================================
// HAPUS
// =====================================================

document
    .getElementById('clearSignature')
    .addEventListener('click', function () {

        ctx.clearRect(
            0,
            0,
            canvas.width,
            canvas.height
        );

        ctx.fillStyle = '#ffffff';

        ctx.fillRect(
            0,
            0,
            canvas.width,
            canvas.height
        );

        hasSignature = false;

        document.getElementById(
            'tandaTangan'
        ).value = '';

    });


// =====================================================
// SEBELUM SUBMIT
// =====================================================

document
    .getElementById('formPKPA')
    .addEventListener('submit', function (event) {

        if (!hasSignature) {

            event.preventDefault();

            alert(
                'Silakan tanda tangan terlebih dahulu.'
            );

            return;

        }

        const signatureData =
            canvas.toDataURL('image/png');

        document.getElementById(
            'tandaTangan'
        ).value = signatureData;

    });

</script>

</body>

</html>