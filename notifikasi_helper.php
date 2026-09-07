<?php
/*
|--------------------------------------------------------------------------
| HELPER NOTIFIKASI BERSAMA (dipakai sisi user & admin)
|--------------------------------------------------------------------------
| Tabel: notifikasi(id_notifikasi, id_user, judul, pesan, is_read, created_at)
| "Notifikasi admin" = baris notifikasi yang id_user-nya milik akun ber-role
| admin / super_admin.
*/

if (!function_exists('kirim_notifikasi')) {
    /** Kirim 1 notifikasi ke satu user. Aman dipanggil kapan saja (silent fail). */
    function kirim_notifikasi(mysqli $conn, int $id_user, string $judul, string $pesan): bool
    {
        if ($id_user <= 0 || $judul === '') {
            return false;
        }
        $st = $conn->prepare(
            'INSERT INTO notifikasi (id_user, judul, pesan) VALUES (?, ?, ?)'
        );
        if (!$st) {
            return false;
        }
        $st->bind_param('iss', $id_user, $judul, $pesan);
        $ok = $st->execute();
        $st->close();
        return (bool) $ok;
    }
}

if (!function_exists('kirim_notifikasi_admin')) {
    /** Kirim notifikasi yang sama ke SEMUA akun admin / super_admin. */
    function kirim_notifikasi_admin(mysqli $conn, string $judul, string $pesan): int
    {
        $res = $conn->query(
            "SELECT id_user FROM users WHERE role IN ('admin', 'super_admin')"
        );
        if (!$res) {
            return 0;
        }
        $n = 0;
        while ($row = $res->fetch_assoc()) {
            if (kirim_notifikasi($conn, (int) $row['id_user'], $judul, $pesan)) {
                $n++;
            }
        }
        return $n;
    }
}

if (!function_exists('hitung_notifikasi_belum_dibaca')) {
    function hitung_notifikasi_belum_dibaca(mysqli $conn, int $id_user): int
    {
        if ($id_user <= 0) {
            return 0;
        }
        $st = $conn->prepare(
            'SELECT COUNT(*) c FROM notifikasi WHERE id_user = ? AND is_read = 0'
        );
        if (!$st) {
            return 0;
        }
        $st->bind_param('i', $id_user);
        $st->execute();
        $c = (int) ($st->get_result()->fetch_assoc()['c'] ?? 0);
        $st->close();
        return $c;
    }
}
