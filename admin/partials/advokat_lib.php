<?php
/*
|--------------------------------------------------------------------------
| Ambil + validasi input form data advokat (dipakai proses_tambah & proses_edit)
|--------------------------------------------------------------------------
| return: [array $fields, array $errors]
*/
function advokat_input(mysqli $conn): array
{
    $t = fn(string $k) => trim($_POST[$k] ?? '');

    $f = [
        'nia'                => $t('nia'),
        'nama_lengkap'       => $t('nama_lengkap'),
        'nik'                => $t('nik'),
        'tempat_lahir'       => $t('tempat_lahir'),
        'tanggal_lahir'      => $t('tanggal_lahir') ?: null,
        'jenis_kelamin'      => $t('jenis_kelamin'),
        'agama'              => $t('agama'),
        'alamat_domisili'    => $t('alamat_domisili'),
        'nama_kantor_hukum'  => $t('nama_kantor_hukum'),
        'alamat_kantor'      => $t('alamat_kantor'),
        'no_hp'              => $t('no_hp'),
        'email'              => $t('email'),
        'universitas_asal'   => $t('universitas_asal'),
        'tanggal_sumpah_pt'  => $t('tanggal_sumpah_pt') ?: null,
        'nomor_bas'          => $t('nomor_bas'),
        'masa_berlaku_ktpa'  => $t('masa_berlaku_ktpa') ?: null,
        'status_keanggotaan' => $t('status_keanggotaan') ?: 'aktif',
    ];

    $errors = [];
    if ($f['nia'] === '') {
        $errors[] = 'NIA wajib diisi.';
    }
    if ($f['nama_lengkap'] === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if ($f['nik'] === '') {
        $errors[] = 'NIK wajib diisi.';
    } elseif (!preg_match('/^\d{6,20}$/', $f['nik'])) {
        $errors[] = 'NIK harus berupa angka (6-20 digit).';
    }
    if ($f['jenis_kelamin'] !== '' && !in_array($f['jenis_kelamin'], ['L', 'P'], true)) {
        $errors[] = 'Jenis kelamin tidak valid.';
    }
    if ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }
    if (!in_array($f['status_keanggotaan'], ['aktif', 'cuti', 'non_aktif', 'meninggal'], true)) {
        $errors[] = 'Status keanggotaan tidak valid.';
    }
    foreach (['tanggal_lahir', 'tanggal_sumpah_pt', 'masa_berlaku_ktpa'] as $dk) {
        if ($f[$dk] !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $f[$dk])) {
            $errors[] = 'Format tanggal tidak valid.';
            $f[$dk] = null;
        }
    }

    return [$f, $errors];
}
