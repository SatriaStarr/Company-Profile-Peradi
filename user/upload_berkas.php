<?php

session_start();

require_once __DIR__ . '/functions.php';

require_user();


// =====================================================
// 1. USER
// =====================================================

$uid = user_id();


// =====================================================
// 2. ID PENDAFTARAN
// =====================================================

$id = (int) (
    $_GET['id']
    ?? $_POST['id_pendaftaran']
    ?? 0
);


if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}


// =====================================================
// 3. CEK PENDAFTARAN MILIK USER
// =====================================================

$q = $conn->prepare("
    SELECT *
    FROM pendaftaran
    WHERE id_pendaftaran = ?
      AND id_user = ?
    LIMIT 1
");

$q->bind_param(
    'ii',
    $id,
    $uid
);

$q->execute();

$result = $q->get_result();

$pendaftaran = $result->fetch_assoc();

$q->close();


if (!$pendaftaran) {
    header('Location: dashboard.php');
    exit;
}


// =====================================================
// 4. PESAN
// =====================================================

$msg = '';


// =====================================================
// 5. PROSES UPLOAD
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jenis = trim(
        $_POST['jenis_berkas'] ?? ''
    );


    $f = $_FILES['file'] ?? null;


    // -------------------------------------------------
    // Jenis berkas yang diperbolehkan
    // -------------------------------------------------

    $allowed_jenis = [
        'KTP',
        'Ijazah S1 Hukum',
        'Pas Foto',
        'Formulir Pendaftaran',
        'Bukti Pembayaran',
        'Dokumen Lainnya'
    ];


    // -------------------------------------------------
    // Extension yang diperbolehkan
    // -------------------------------------------------

    $allowed_extensions = [
        'pdf',
        'jpg',
        'jpeg',
        'png'
    ];


    // -------------------------------------------------
    // Validasi jenis
    // -------------------------------------------------

    if (!in_array($jenis, $allowed_jenis, true)) {

        $msg = 'Jenis berkas tidak valid.';

    } else {

        // -------------------------------------------------
        // Validasi file
        // -------------------------------------------------

        $ok =
            $f &&
            isset($f['error']) &&
            $f['error'] === UPLOAD_ERR_OK &&
            isset($f['size']) &&
            $f['size'] <= (5 * 1024 * 1024);


        $ext = '';

        if ($f) {
            $ext = strtolower(
                pathinfo(
                    $f['name'],
                    PATHINFO_EXTENSION
                )
            );
        }


        if (
            !$ok ||
            !in_array(
                $ext,
                $allowed_extensions,
                true
            )
        ) {

            $msg =
                'Berkas tidak valid. ' .
                'Maksimal 5 MB dan format PDF/JPG/PNG.';

        } else {

            // -------------------------------------------------
            // Folder upload ROOT PROJECT
            // -------------------------------------------------

            $dir = __DIR__ . '/../uploads/berkas';


            if (!is_dir($dir)) {

                if (!mkdir($dir, 0755, true)) {

                    $msg =
                        'Folder upload gagal dibuat.';

                }
            }


            if ($msg === '') {

                // -------------------------------------------------
                // Nama file
                // -------------------------------------------------

                $name =
                    'berkas_' .
                    $id .
                    '_' .
                    time() .
                    '_' .
                    bin2hex(random_bytes(5)) .
                    '.' .
                    $ext;


                $destination =
                    $dir .
                    DIRECTORY_SEPARATOR .
                    $name;


                // -------------------------------------------------
                // Path yang disimpan ke database
                // -------------------------------------------------

                $relative_path =
                    'uploads/berkas/' .
                    $name;


                // -------------------------------------------------
                // Pindahkan file
                // -------------------------------------------------

                if (
                    move_uploaded_file(
                        $f['tmp_name'],
                        $destination
                    )
                ) {

                    try {

                        $conn->begin_transaction();


                        // -------------------------------------------------
                        // Simpan ke tabel BERKAS
                        // -------------------------------------------------

                        $s = $conn->prepare("
                            INSERT INTO berkas
                            (
                                id_pendaftaran,
                                jenis_berkas,
                                file_berkas
                            )
                            VALUES
                            (?, ?, ?)
                        ");


                        $s->bind_param(
                            'iss',
                            $id,
                            $jenis,
                            $relative_path
                        );


                        $s->execute();

                        $s->close();


                        $conn->commit();


                        // -------------------------------------------------
                        // Selesai
                        // -------------------------------------------------

                        header(
                            'Location: detail_pendaftaran.php?id=' .
                            $id .
                            '&pesan=berkas_berhasil'
                        );

                        exit;


                    } catch (Throwable $e) {

                        try {
                            $conn->rollback();
                        } catch (Throwable $rollbackError) {
                        }


                        if (file_exists($destination)) {
                            unlink($destination);
                        }


                        $msg =
                            'Gagal menyimpan data berkas: ' .
                            $e->getMessage();
                    }


                } else {

                    $msg =
                        'Gagal menyimpan file ke server.';
                }
            }
        }
    }
}

?>
<!doctype html>
<html lang="id">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Upload Berkas - PERADI Pontianak</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .card {
            max-width: 650px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }

        h1 {
            color: #1e3a8a;
            margin-top: 0;
        }

        .field {
            margin: 18px 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        select,
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
        }

        button,
        .back {
            display: inline-block;
            padding: 12px 16px;
            border: 0;
            border-radius: 8px;
            background: #1e3a8a;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }

        .back {
            background: #ddd;
            color: #333;
        }

        .alert {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .hint {
            margin-top: 7px;
            font-size: 13px;
            color: #64748b;
        }

    </style>

</head>

<body>

<div class="card">

    <h1>Upload Berkas</h1>


    <?php if ($msg): ?>

        <div class="alert">
            <?= e($msg) ?>
        </div>

    <?php endif; ?>


    <form
        method="post"
        enctype="multipart/form-data"
    >

        <input
            type="hidden"
            name="id_pendaftaran"
            value="<?= $id ?>"
        >


        <div class="field">

            <label for="jenis_berkas">
                Jenis Berkas
            </label>

            <select
                name="jenis_berkas"
                id="jenis_berkas"
                required
            >

                <option value="">
                    Pilih
                </option>

                <option value="KTP">
                    KTP
                </option>

                <option value="Ijazah S1 Hukum">
                    Ijazah S1 Hukum
                </option>

                <option value="Pas Foto">
                    Pas Foto
                </option>

                <option value="Formulir Pendaftaran">
                    Formulir Pendaftaran
                </option>

                <option value="Bukti Pembayaran">
                    Bukti Pembayaran
                </option>

                <option value="Dokumen Lainnya">
                    Dokumen Lainnya
                </option>

            </select>

        </div>


        <div class="field">

            <label for="file">
                File
            </label>

            <input
                type="file"
                name="file"
                id="file"
                accept=".pdf,.jpg,.jpeg,.png"
                required
            >

            <div class="hint">
                Maksimal 5 MB. Format PDF, JPG, JPEG, atau PNG.
            </div>

        </div>


        <button type="submit">
            Upload
        </button>

        <a
            class="back"
            href="detail_pendaftaran.php?id=<?= $id ?>"
        >
            Kembali
        </a>

    </form>

</div>

</body>

</html>