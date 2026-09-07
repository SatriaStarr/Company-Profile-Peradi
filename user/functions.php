<?php

require_once __DIR__ . '/../koneksi.php';


/*
|--------------------------------------------------------------------------
| CEK USER SUDAH LOGIN
|--------------------------------------------------------------------------
*/
function require_user(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    /*
     * User dianggap login jika:
     * 1. Ada user_id
     * 2. Role = user
     */

    if (
        empty($_SESSION['user_id']) ||
        ($_SESSION['role'] ?? '') !== 'user'
    ) {
        header('Location: login.php?pesan=belum_login');
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/
function e($v): string
{
    return htmlspecialchars(
        (string)$v,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| FORMAT UANG
|--------------------------------------------------------------------------
*/
function money($v): string
{
    return 'Rp ' . number_format(
        (float)$v,
        0,
        ',',
        '.'
    );
}


/*
|--------------------------------------------------------------------------
| AMBIL ID USER YANG LOGIN
|--------------------------------------------------------------------------
*/
function user_id(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI USER
|--------------------------------------------------------------------------
*/
function notify_user(
    int $uid,
    string $title,
    string $message
): void {

    global $conn;

    $s = $conn->prepare(
        'INSERT INTO notifikasi (id_user, judul, pesan)
         VALUES (?, ?, ?)'
    );

    if (!$s) {
        return;
    }

    $s->bind_param(
        'iss',
        $uid,
        $title,
        $message
    );

    $s->execute();
    $s->close();
}


/*
|--------------------------------------------------------------------------
| LABEL STATUS PENDAFTARAN
|--------------------------------------------------------------------------
*/
function app_status_label(string $s): string
{
    return match ($s) {

        'menunggu_verifikasi'
            => 'Menunggu Verifikasi',

        'berkas_diperiksa'
            => 'Berkas Diperiksa',

        'menunggu_pembayaran'
            => 'Menunggu Pembayaran',

        'pembayaran_diperiksa'
            => 'Pembayaran Diperiksa',

        'terdaftar'
            => 'Terdaftar',

        'selesai'
            => 'Selesai',

        'ditolak'
            => 'Ditolak',

        default
            => ucwords(
                str_replace('_', ' ', $s)
            )
    };
}


/*
|--------------------------------------------------------------------------
| CLASS BADGE STATUS
|--------------------------------------------------------------------------
*/
function badge_class(string $s): string
{
    if (
        in_array(
            $s,
            [
                'terdaftar',
                'selesai',
                'lunas',
                'valid',
                'Lulus'
            ],
            true
        )
    ) {
        return 'ok';
    }


    if (
        in_array(
            $s,
            [
                'ditolak',
                'banned',
                'Tidak Lulus'
            ],
            true
        )
    ) {
        return 'bad';
    }


    return 'wait';
}

?>