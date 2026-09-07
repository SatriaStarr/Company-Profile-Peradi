<?php
/*
|--------------------------------------------------------------------------
| CEK KEASLIAN SERTIFIKAT (HALAMAN PUBLIK)
|--------------------------------------------------------------------------
| Siapa pun (pengadilan, instansi, klien) dapat memverifikasi keaslian
| sertifikat PKPA/UPA yang diterbitkan DPC PERADI Pontianak dengan
| memasukkan nomor sertifikat. Tidak perlu login.
*/

require_once __DIR__ . '/koneksi.php';

if (!function_exists('e')) {
    function e($v): string
    {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

$nomor  = trim($_GET['nomor'] ?? '');
$sudah_cari = ($nomor !== '');
$hasil  = null;
$error  = '';

if ($sudah_cari) {

    if (mb_strlen($nomor) > 100) {
        $error = 'Nomor sertifikat tidak valid.';
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                s.nomor_sertifikat,
                s.tanggal_terbit,
                s.created_at,
                p.jenis_pendaftaran,
                p.status AS status_pendaftaran,
                h.status_lulus,
                h.nilai,
                COALESCE(pk.nama_lengkap, pu.nama_lengkap, u.nama_lengkap, u.username) AS nama_peserta,
                COALESCE(pk.nik, pu.nik) AS nik
             FROM sertifikat s
             JOIN pendaftaran p          ON p.id_pendaftaran = s.id_pendaftaran
             LEFT JOIN hasil h           ON h.id_pendaftaran = p.id_pendaftaran
             LEFT JOIN users u           ON u.id_user = p.id_user
             LEFT JOIN pendaftaran_pkpa pk ON pk.id_pendaftaran = p.id_pendaftaran
             LEFT JOIN pendaftaran_upa  pu ON pu.id_pendaftaran = p.id_pendaftaran
             WHERE s.nomor_sertifikat = ?
             LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $nomor);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $hasil = $res ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);
        }
    }
}

/* samarkan sebagian NIK untuk privasi */
function samar_nik(?string $nik): string
{
    $nik = trim((string) $nik);
    if ($nik === '') {
        return '-';
    }
    $len = strlen($nik);
    if ($len <= 4) {
        return str_repeat('*', $len);
    }
    return substr($nik, 0, 2) . str_repeat('*', max(0, $len - 6)) . substr($nik, -4);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Keaslian Sertifikat - DPC PERADI Pontianak</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background: #f4f6f9; color: #333; }

        .page-header {
            position: relative; width: 100%; min-height: 38vh;
            background: linear-gradient(135deg, #1e3a8a, #294da5);
            display: flex; align-items: center; justify-content: center; text-align: center;
            padding: 60px 20px 50px;
        }
        .header-title { font-size: 2.6rem; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: 2px; }
        .header-subtitle { font-size: .95rem; color: #dea057; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 12px; }

        .wrap { max-width: 760px; margin: -45px auto 60px; padding: 0 20px; position: relative; z-index: 3; }
        .card { background: #fff; border-radius: 14px; padding: 30px; box-shadow: 0 12px 40px rgba(0,0,0,.10); }

        .lead { color: #475569; font-size: .95rem; line-height: 1.7; margin-bottom: 22px; text-align: justify; }

        form.cari { display: flex; gap: 10px; flex-wrap: wrap; }
        form.cari input[type=text] {
            flex: 1; min-width: 220px; padding: 14px 16px; border: 2px solid #e2e8f0;
            border-radius: 9px; font-size: 14px; font-family: inherit;
        }
        form.cari input[type=text]:focus { outline: none; border-color: #1e3a8a; }
        form.cari button {
            padding: 14px 26px; border: none; border-radius: 9px; background: #1e3a8a; color: #fff;
            font-weight: 700; font-size: 14px; cursor: pointer; font-family: inherit; transition: .25s;
        }
        form.cari button:hover { background: #152c69; }

        .result { margin-top: 26px; padding-top: 24px; border-top: 1px solid #eef2f7; }
        .status-line { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .status-ic { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .valid .status-ic { background: #dcfce7; color: #166534; }
        .invalid .status-ic { background: #fee2e2; color: #b91c1c; }
        .status-txt h3 { font-size: 18px; color: #1e3a8a; }
        .status-txt p { font-size: 13px; color: #64748b; margin-top: 2px; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; }
        .box { background: #f8fafc; border: 1px solid #eef2f7; padding: 14px; border-radius: 10px; }
        .box b { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
        .box span { font-weight: 600; font-size: 14px; color: #1e293b; }

        .note { margin-top: 20px; font-size: 12px; color: #94a3b8; line-height: 1.6; }
        .home-link { display: inline-block; margin-top: 22px; color: #1e3a8a; font-weight: 700; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>

<?php include __DIR__ . '/header.php'; ?>

<header class="page-header">
    <div>
        <p class="header-subtitle">DPC PERADI Pontianak</p>
        <h1 class="header-title">Cek Keaslian Sertifikat</h1>
    </div>
</header>

<div class="wrap">
    <div class="card">

        <p class="lead">
            Halaman ini digunakan untuk memverifikasi keaslian sertifikat PKPA maupun
            Berita Acara Sumpah (UPA) yang diterbitkan melalui Sistem Informasi Manajemen
            Sertifikasi Advokat DPC PERADI Pontianak. Masukkan <strong>nomor sertifikat</strong>
            yang tercantum pada dokumen untuk menampilkan data pemegang sertifikat.
        </p>

        <form class="cari" method="get" action="cek_sertifikat.php">
            <input
                type="text"
                name="nomor"
                placeholder="Contoh: 001/PKPA/DPC-PTK/2026"
                value="<?= e($nomor) ?>"
                autocomplete="off"
                required
            >
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Verifikasi</button>
        </form>

        <?php if ($sudah_cari): ?>
            <div class="result <?= $hasil ? 'valid' : 'invalid' ?>">

                <?php if ($error !== ''): ?>
                    <div class="status-line invalid">
                        <div class="status-ic"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="status-txt">
                            <h3>Permintaan Tidak Valid</h3>
                            <p><?= e($error) ?></p>
                        </div>
                    </div>

                <?php elseif ($hasil): ?>
                    <div class="status-line valid">
                        <div class="status-ic"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="status-txt">
                            <h3>Sertifikat Terverifikasi</h3>
                            <p>Nomor sertifikat ini terdaftar resmi pada DPC PERADI Pontianak.</p>
                        </div>
                    </div>

                    <div class="grid">
                        <div class="box">
                            <b>Nomor Sertifikat</b>
                            <span><?= e($hasil['nomor_sertifikat']) ?></span>
                        </div>
                        <div class="box">
                            <b>Nama Pemegang</b>
                            <span><?= e($hasil['nama_peserta'] ?: '-') ?></span>
                        </div>
                        <div class="box">
                            <b>NIK</b>
                            <span><?= e(samar_nik($hasil['nik'])) ?></span>
                        </div>
                        <div class="box">
                            <b>Jenis Sertifikasi</b>
                            <span><?= e(strtoupper((string) $hasil['jenis_pendaftaran'])) ?></span>
                        </div>
                        <div class="box">
                            <b>Hasil Ujian</b>
                            <span><?= e($hasil['status_lulus'] ?: 'Belum Diumumkan') ?></span>
                        </div>
                        <div class="box">
                            <b>Tanggal Terbit</b>
                            <span><?=
                                e($hasil['tanggal_terbit']
                                    ? date('d F Y', strtotime($hasil['tanggal_terbit']))
                                    : ($hasil['created_at']
                                        ? date('d F Y', strtotime($hasil['created_at']))
                                        : '-'))
                            ?></span>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="status-line invalid">
                        <div class="status-ic"><i class="fa-solid fa-circle-xmark"></i></div>
                        <div class="status-txt">
                            <h3>Sertifikat Tidak Ditemukan</h3>
                            <p>
                                Nomor <strong><?= e($nomor) ?></strong> tidak terdaftar dalam basis data
                                DPC PERADI Pontianak. Pastikan penulisan nomor sudah benar, atau hubungi
                                sekretariat DPC PERADI Pontianak untuk konfirmasi lebih lanjut.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <p class="note">
            Data NIK ditampilkan sebagian untuk menjaga privasi pemegang sertifikat.
            Verifikasi ini hanya memastikan bahwa nomor sertifikat tercatat resmi pada sistem
            dan tidak menggantikan legalisasi dokumen fisik.
        </p>

        <a class="home-link" href="index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
