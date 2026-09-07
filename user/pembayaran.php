<?php

session_start();

require_once __DIR__ . '/functions.php';

require_user();


// =====================================================
// 1. AMBIL ID PENDAFTARAN
// =====================================================

$id_pendaftaran = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id_pendaftaran <= 0) {
    header('Location: dashboard.php');
    exit;
}


// =====================================================
// 2. AMBIL DATA PENDAFTARAN + PEMBAYARAN
// =====================================================

$uid = user_id();

$stmt = $conn->prepare("
    SELECT
        p.id_pendaftaran,
        p.jenis_pendaftaran,
        p.status AS status_pendaftaran,
        p.tanggal_daftar,

        pb.id_pembayaran,
        pb.jumlah,
        pb.status AS status_pembayaran,
        pb.bukti_pembayaran,
        pb.tanggal_bayar

    FROM pendaftaran p

    LEFT JOIN pembayaran pb
        ON pb.id_pendaftaran = p.id_pendaftaran

    WHERE p.id_pendaftaran = ?
    AND p.id_user = ?

    LIMIT 1
");

$stmt->bind_param(
    'ii',
    $id_pendaftaran,
    $uid
);

$stmt->execute();

$result = $stmt->get_result();

$data = $result->fetch_assoc();

$stmt->close();


// =====================================================
// 3. CEK DATA
// =====================================================

if (!$data) {

    header('Location: dashboard.php');
    exit;
}


// =====================================================
// 4. CEK STATUS PEMBAYARAN
// =====================================================

$status_pembayaran = $data['status_pembayaran'] ?? 'belum_bayar';


// =====================================================
// 5. PESAN
// =====================================================

$pesan = $_GET['pesan'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pembayaran <?= e($data['jenis_pendaftaran']) ?> - PERADI Pontianak</title>

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
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f9;
            color: #333;
            font-family: 'Montserrat', sans-serif;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
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
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            margin-bottom: 25px;
        }

        .title {
            color: #1e3a8a;
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 800;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
        }

        .service {
            background: #eff6ff;
            border-left: 5px solid #dea057;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .service strong {
            color: #1e3a8a;
        }

        .bank-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .bank-title {
            font-size: 18px;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 20px;
        }

        .bank-row {
            margin-bottom: 15px;
        }

        .bank-label {
            display: block;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .bank-value {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
        }

        .nominal {
            background: #1e3a8a;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .nominal-label {
            font-size: 13px;
            opacity: .8;
            margin-bottom: 5px;
        }

        .nominal-value {
            font-size: 27px;
            font-weight: 800;
        }

        .alert {
            padding: 15px 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #334155;
        }

        input[type="file"] {
            width: 100%;
            padding: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            background: white;
        }

        .hint {
            margin-top: 8px;
            color: #64748b;
            font-size: 13px;
        }

        .btn {
            border: none;
            padding: 14px 24px;
            border-radius: 9px;
            font-family: inherit;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #1e3a8a;
            color: white;
        }

        .btn-primary:hover {
            background: #152c69;
        }

        .btn-back {
            background: #e5e7eb;
            color: #334155;
            margin-left: 8px;
        }

        .status {
            padding: 15px;
            border-radius: 10px;
            background: #fef3c7;
            color: #92400e;
            font-weight: 700;
        }

        @media(max-width: 600px) {

            .container {
                margin: 20px auto;
                padding: 15px;
            }

            .card {
                padding: 22px;
            }

            .title {
                font-size: 23px;
            }

            .nominal-value {
                font-size: 22px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <a
        href="detail_pendaftaran.php?id=<?= $id_pendaftaran ?>"
        class="back"
    >
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke Detail Pendaftaran
    </a>


    <div class="card">

        <h1 class="title">

            <i class="fa-solid fa-money-bill-transfer"></i>

            Pembayaran
            <?= e($data['jenis_pendaftaran']) ?>

        </h1>

        <p class="subtitle">
            Silakan lakukan pembayaran sesuai nominal yang tertera.
        </p>


        <?php if ($pesan === 'berhasil'): ?>

            <div class="alert alert-success">

                <i class="fa-solid fa-circle-check"></i>

                Bukti pembayaran berhasil dikirim.
                Silakan menunggu verifikasi dari admin.

            </div>

        <?php elseif ($pesan === 'gagal'): ?>

            <div class="alert alert-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                Bukti pembayaran gagal dikirim.
                Silakan coba lagi.

            </div>

        <?php elseif ($pesan === 'format'): ?>

            <div class="alert alert-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                Format file tidak diperbolehkan.
                Gunakan JPG, JPEG, atau PNG.

            </div>

        <?php elseif ($pesan === 'ukuran'): ?>

            <div class="alert alert-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                Ukuran file maksimal 2 MB.

            </div>

        <?php endif; ?>


        <div class="service">

            <strong>
                <i class="fa-solid fa-file-signature"></i>
                Jenis Pendaftaran
            </strong>

            <br><br>

            <?= e($data['jenis_pendaftaran']) ?>

        </div>


        <div class="bank-card">

            <div class="bank-title">

                <i class="fa-solid fa-building-columns"></i>

                Informasi Rekening Pembayaran

            </div>


            <div class="bank-row">

                <span class="bank-label">
                    Bank
                </span>

                <span class="bank-value">
                    Bank Kalbar
                </span>

            </div>


            <div class="bank-row">

                <span class="bank-label">
                    Nomor Rekening
                </span>

                <span class="bank-value">
                    1021374977
                </span>

            </div>


            <div class="bank-row">

                <span class="bank-label">
                    Atas Nama
                </span>

                <span class="bank-value">
                    DPC PERADI Pontianak
                </span>

            </div>


            <div class="nominal">

                <div class="nominal-label">
                    Total yang harus dibayar
                </div>

                <div class="nominal-value">

                    Rp
                    <?= number_format(
                        $data['jumlah'],
                        0,
                        ',',
                        '.'
                    ) ?>

                </div>

            </div>

        </div>


        <?php if ($status_pembayaran === 'belum_bayar'): ?>


            <form
                action="proses_pembayaran.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="id_pendaftaran"
                    value="<?= $id_pendaftaran ?>"
                >


                <div class="form-group">

                    <label for="bukti_pembayaran">

                        <i class="fa-solid fa-image"></i>

                        Bukti Pembayaran

                    </label>


                    <input
                        type="file"
                        id="bukti_pembayaran"
                        name="bukti_pembayaran"
                        accept=".jpg,.jpeg,.png"
                        required
                    >


                    <div class="hint">

                        Format JPG, JPEG, atau PNG.
                        Maksimal ukuran 2 MB.

                    </div>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="fa-solid fa-paper-plane"></i>

                    Kirim Bukti Pembayaran

                </button>


                <a
                    href="detail_pendaftaran.php?id=<?= $id_pendaftaran ?>"
                    class="btn btn-back"
                >

                    Batal

                </a>

            </form>


        <?php elseif ($status_pembayaran === 'menunggu_verifikasi'): ?>


            <div class="status">

                <i class="fa-solid fa-clock"></i>

                Bukti pembayaran sudah dikirim dan sedang
                menunggu verifikasi admin.

            </div>


        <?php elseif ($status_pembayaran === 'lunas'): ?>


            <div class="alert alert-success">

                <i class="fa-solid fa-circle-check"></i>

                Pembayaran Anda sudah diverifikasi dan dinyatakan
                <strong>LUNAS</strong>.

            </div>


        <?php endif; ?>

    </div>

</div>

</body>

</html>