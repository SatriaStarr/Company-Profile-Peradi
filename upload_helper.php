<?php
/*
|--------------------------------------------------------------------------
| HELPER UPLOAD FILE AMAN (dipakai admin & user)
|--------------------------------------------------------------------------
| - Validasi error upload
| - Batas ukuran
| - Validasi MIME asli (finfo) + ekstensi whitelist
| - Nama file acak & unik (tidak memakai nama asli user)
| - Simpan ke folder uploads/<subdir> di ROOT project
|
| Mengembalikan path relatif dari root project, mis: "uploads/advokat/ab12cd.jpg"
| Melempar RuntimeException bila gagal / tidak valid.
*/

const UPLOAD_ROOT = __DIR__ . '/uploads';

/** Peta MIME -> ekstensi yang diizinkan. */
function upload_ext_map(): array
{
    return [
        'image/jpeg'       => 'jpg',
        'image/png'        => 'png',
        'image/webp'       => 'webp',
        'application/pdf'  => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/zip'  => 'docx', // .docx sering terdeteksi sebagai zip oleh finfo
    ];
}

/**
 * @param array  $file      satu elemen dari $_FILES
 * @param string $subdir    subfolder di dalam uploads/ (mis. "advokat")
 * @param array  $allow     daftar tipe yang diizinkan: 'image', 'pdf'
 * @param int    $maxBytes  batas ukuran (default 3 MB)
 * @param bool   $required  jika false dan tidak ada file, kembalikan null
 */
function simpan_upload(
    array $file,
    string $subdir,
    array $allow = ['image'],
    int $maxBytes = 3145728,
    bool $required = true
): ?string {
    $err = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($err === UPLOAD_ERR_NO_FILE || ($file['name'] ?? '') === '') {
        if ($required) {
            throw new RuntimeException('File wajib diunggah.');
        }
        return null;
    }
    if ($err !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Gagal mengunggah file (kode ' . (int) $err . ').');
    }
    if (!is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('Sumber file tidak valid.');
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) {
        throw new RuntimeException('Ukuran file maksimal ' . round($maxBytes / 1048576, 1) . ' MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $map = upload_ext_map();

    // filter map sesuai kategori yang diizinkan
    $allowed_mimes = [];
    foreach ($map as $m => $ext) {
        $isImage = str_starts_with($m, 'image/');
        $isPdf   = $m === 'application/pdf';
        $isDoc   = in_array($m, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
        ], true);
        if (($isImage && in_array('image', $allow, true)) ||
            ($isPdf && in_array('pdf', $allow, true)) ||
            ($isDoc && in_array('doc', $allow, true))) {
            $allowed_mimes[$m] = $ext;
        }
    }

    // gate sekunder: ekstensi asli file juga harus masuk akal
    $orig_ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $sane_ext = array_unique(array_values($allowed_mimes));
    if ($orig_ext !== '' && !in_array($orig_ext, $sane_ext, true)
        && !($orig_ext === 'jpeg' && in_array('jpg', $sane_ext, true))) {
        throw new RuntimeException('Ekstensi file tidak sesuai. Diperbolehkan: ' . implode(', ', $sane_ext) . '.');
    }

    if (!isset($allowed_mimes[$mime])) {
        throw new RuntimeException('Tipe file tidak diizinkan. Diperbolehkan: ' . implode(', ', array_values($allowed_mimes)) . '.');
    }
    $ext = $allowed_mimes[$mime];

    // untuk gambar: pastikan benar-benar gambar yang bisa dibaca
    if (str_starts_with($mime, 'image/')) {
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            throw new RuntimeException('Berkas gambar rusak atau tidak valid.');
        }
    }

    $subdir = preg_replace('/[^a-z0-9_-]/i', '', $subdir);
    $dir    = UPLOAD_ROOT . '/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Folder upload tidak dapat dibuat.');
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Gagal menyimpan file.');
    }
    @chmod($dest, 0644);

    return 'uploads/' . $subdir . '/' . $name;
}

/** Hapus file upload berdasarkan path relatif root (aman: hanya di dalam uploads/). */
function hapus_upload(?string $relPath): void
{
    if (!$relPath) {
        return;
    }
    $relPath = ltrim(str_replace('\\', '/', $relPath), '/');
    if (!str_starts_with($relPath, 'uploads/') || str_contains($relPath, '..')) {
        return;
    }
    $full = __DIR__ . '/' . $relPath;
    if (is_file($full)) {
        @unlink($full);
    }
}

/**
 * Ubah nilai lama yang mungkin hanya berupa nama file ("abc.jpg") atau path
 * gaya lama ("uploads/abc.jpg") menjadi URL yang benar untuk ditampilkan,
 * relatif dari halaman di folder /admin atau /user.
 */
function url_upload(?string $stored, string $subdirFallback = 'advokat'): ?string
{
    if (!$stored) {
        return null;
    }
    $stored = str_replace('\\', '/', $stored);
    if (str_starts_with($stored, 'uploads/')) {
        return '../' . $stored;
    }
    // nilai lama: cuma nama file
    return '../uploads/' . $subdirFallback . '/' . $stored;
}
