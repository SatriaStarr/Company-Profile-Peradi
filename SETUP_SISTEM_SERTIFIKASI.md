# Setup Sistem Sertifikasi Advokat

## 1. Database
1. Buka phpMyAdmin.
2. Pilih database `db_peradi` (sesuai `koneksi.php`).
3. Import `database/sertifikasi.sql`.
4. Pastikan tabel lama seperti `users`, `data_advokat`, `galeri`, `struktur_pengurus`, `layanan`, `peraturan`, `running_text` tetap ada.
5. Jika `users` belum punya `email`, `status`, atau `created_at`, tambahkan sesuai komentar di SQL.

## 2. QRIS
Simpan QRIS kantor yang asli sebagai:
`uploads/qris/qris.png`

Sistem ini memakai **QRIS statis + upload bukti pembayaran**. Ini berbeda dari payment gateway dinamis. Untuk QRIS dinamis/payment gateway seperti Midtrans/Xendit dibutuhkan akun merchant dan kredensial API resmi.

## 3. Folder upload
Pastikan folder berikut dapat ditulis PHP:
- `uploads/qris`
- `uploads/sertifikasi`
- `uploads/pembayaran`
- `uploads/sertifikat`

## 4. User
- Daftar di `user/register.php`
- Login di `user/login.php`
- Login Google tersedia melalui `user/google_login.php`, tetapi `user/google_config.php` harus diisi Client ID dan Client Secret dari Google Cloud.

## 5. Admin
- Login admin melalui `admin/login.php`.
- Admin dapat membuka menu **Peserta Sertifikasi** untuk memeriksa berkas, pembayaran, memasukkan hasil, dan mengunggah sertifikat.

## 6. Alur sistem
User daftar -> pilih PKPA/UPA -> upload berkas -> bayar QRIS -> upload bukti -> admin verifikasi -> admin input hasil -> admin upload sertifikat -> user menerima notifikasi dan mengunduh sertifikat.
