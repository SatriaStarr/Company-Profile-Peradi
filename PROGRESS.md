# Progress Perbaikan WEB-DPC-PERADI-PONTIANAK

Acuan utama: **database `db_peradi` yang sebenarnya** + kode terbaru. Dokumentasi `.md` lama TIDAK dijadikan acuan bila berbeda.

## Skema database nyata (ringkas)

| Tabel | Kolom penting |
|---|---|
| `users` | id_user, username, email, google_id, password, nama_lengkap, role(super_admin/admin/user), status(active/pending/banned), created_at |
| `pendaftaran` | id_pendaftaran, id_user, **jenis_pendaftaran**(PKPA/UPA), status(menunggu/diproses/diterima/ditolak/selesai), tanggal_daftar, catatan |
| `pendaftaran_pkpa` | id_pkpa, id_pendaftaran, nama_lengkap, nik, tempat_lahir, tanggal_lahir, pekerjaan, alamat, no_hp, foto_calon_advokat, foto_ijazah, foto_ktp, tanda_tangan, created_at |
| `pendaftaran_upa` | id_upa, id_pendaftaran, nama_lengkap, nik, tempat_lahir, tanggal_lahir, pekerjaan, alamat, no_hp, foto_ktp, tanda_tangan, created_at |
| `pembayaran` | id_pembayaran, id_pendaftaran, jumlah, status(belum_bayar/menunggu_verifikasi/lunas/ditolak), **bukti_pembayaran**, tanggal_bayar, created_at |
| `hasil` | id_hasil, id_pendaftaran, nilai, status_lulus(Lulus/Tidak Lulus/Belum Diumumkan), catatan, created_at |
| `sertifikat` | id_sertifikat, id_pendaftaran, nomor_sertifikat, file_sertifikat, created_at |
| `notifikasi` | id_notifikasi, id_user, judul, pesan, is_read, created_at |
| `pengumuman` | id_pengumuman, judul, isi, tanggal_publish, status(draft/publikasi) |
| `data_advokat` | id_advokat, nia, nama_lengkap, nik, ... foto_profil, file_ktpa, file_bas, status_keanggotaan |

Folder upload sebenarnya: `uploads/` di ROOT project (bukan di dalam `admin/`).
Subfolder: galeri, layanan, pembayaran, peraturan, qris, sertifikasi, sertifikat, struktur.

---

## Status checklist

- [x] 1. Sentralisasi sidebar admin
- [x] 2. Rapikan Data User
- [x] 3. Fitur Pengumuman
- [x] 4. Fitur Notifikasi
- [ ] 5. Audit Data Advokat
- [ ] 6. Audit Detail Pendaftaran
- [ ] 7. Perbaiki path upload/file (404)
- [ ] 8. Keamanan upload file
- [ ] 9. Keamanan sistem admin & user
- [ ] 10. Testing end-to-end
- [ ] 11. Jangan ubah file yang sudah berjalan tanpa alasan
- [ ] 12. Pakai DB & kode nyata sebagai acuan
- [ ] 13. Payment Gateway Midtrans Sandbox
- [ ] 14. Pembayaran manual tetap dipertahankan
- [ ] 15. Alur Payment Gateway lengkap
- [ ] 16. Callback/webhook Midtrans
- [ ] 17. Penyesuaian DB Payment Gateway
- [ ] 18. Testing pembayaran menyeluruh
- [ ] 19. Popup konfirmasi logout admin & user
- [ ] 20. Login dengan Google

---

## Catatan per item

### 1. Sentralisasi sidebar admin — SELESAI
- Baru: `admin/partials/sidebar.php` = satu komponen sidebar (CSS+HTML+JS), desain mengacu dashboard. Menu: Dashboard, Data Pendaftaran, Data User, Pengumuman, Notifikasi, Data Advokat, Kembali ke Website, Keluar. Ada badge notifikasi belum dibaca + tombol hamburger mobile + highlight menu aktif otomatis.
- Baru: `admin/partials/admin_auth.php` = guard login/role + helper `e()`, `csrf_field()`, `csrf_check()` bersama (dipakai bertahap di item 9).
- Submenu lama "Kelola Halaman / Running Text / Verifikasi Admin" DIHAPUS dari sidebar sesuai instruksi. Halaman-halamannya (`galeri.php`, `struktur_pengurus.php`, `peraturan.php`, `kelola_layanan.php`, `kelola_running_text.php`, `verifikasi_admin.php`) TIDAK dihapus, hanya tidak ditautkan.
- 6 halaman sertifikasi (dashboard, data_pendaftaran, data_user, pengumuman, notifikasi, detail_pendaftaran) diubah: markup `<aside class="sidebar">` inline diganti `include partials/sidebar.php`.
- 11 halaman CMS lama sudah `include partials/sidebar.php` -> otomatis ikut desain baru.
- `admin/peserta.php` (duplikat rusak: pakai kolom `p.jenis`/`p.created_at` yang tidak ada, link ke `detail_peserta.php` yang tidak ada) -> diganti redirect 301 ke `data_pendaftaran.php`.
- Uji: semua halaman admin dirender via `php -S`, semua tampil dengan sidebar, tanpa error PHP.

### 2. Rapikan Data User — SELESAI
- `admin/data_user.php` ditulis ulang:
  - Pencarian (nama/username/email) pakai prepared statement, auto-submit saat mengetik (debounce 500ms) + tetap bisa Enter.
  - Filter role auto-submit `onchange` (tanpa tombol Search).
  - Tombol reset = ikon saja (`btn-icon`).
  - **Pagination ditambahkan** (10/halaman, mempertahankan keyword+role di link).
  - Kolom **Status** (active/pending/banned) + aksi: Detail, Edit, Aktifkan (untuk pending), Hapus (super_admin saja).
  - Hapus dilindungi: tidak bisa hapus diri sendiri, tidak bisa hapus super_admin terakhir, wajib CSRF.
- Baru `admin/detail_user.php`: profil user + daftar pendaftaran + status pembayaran + notifikasi terakhir.
- Baru `admin/edit_user.php`: edit nama/username/email + (super_admin) role/status + reset password opsional. Validasi: username unik & format, email valid, tidak menurunkan super_admin terakhir, tidak mengubah role/status diri sendiri. CSRF wajib.
- Fungsi "Verifikasi Admin" lama (approve user pending) kini tercakup oleh tombol Aktifkan + edit status. `verifikasi_admin.php` & `proses_verifikasi.php` tidak dihapus.
- Uji fungsional: edit tersimpan, guard hapus-diri-sendiri jalan, POST tanpa CSRF -> 419.

### 3. Fitur Pengumuman — SELESAI
- Admin `admin/pengumuman.php`: tambah / edit / hapus sudah berjalan (prepared statement) — diverifikasi via HTTP (add/edit/hapus OK). Tidak diubah logikanya, hanya sidebar ikut komponen baru.
- **Baru `user/pengumuman.php`**: daftar + detail pengumuman untuk user login. Hanya `status = 'publikasi'` yang tampil; akses langsung ke id draft dialihkan. Diverifikasi: draft tidak bocor ke user.
- `user/dashboard.php`: link rusak "Hasil Pengumuman -> hasil_pengumuman.php" (404) diganti "Pengumuman -> pengumuman.php" di sidebar dan quick-menu.

### 4. Fitur Notifikasi — SELESAI
- Notifikasi USER dibuat otomatis sudah ada untuk: pendaftaran, upload bukti bayar, ubah status, hasil ujian, sertifikat (diverifikasi di kode).
- Baru `notifikasi_helper.php` (root): `kirim_notifikasi()`, `kirim_notifikasi_admin()` (broadcast ke semua role admin/super_admin), `hitung_notifikasi_belum_dibaca()`.
- **Notifikasi ADMIN ditambahkan**: `proses_pendaftaran.php`, `proses_pendaftaran_pkpa.php`, `proses_pendaftaran_upa.php` -> "Pendaftaran baru"; `proses_pembayaran.php` -> "Bukti pembayaran baru". Diuji: broadcast ke 4 akun admin.
- `user/notifikasi.php` ditulis ulang: snapshot "belum dibaca" sebelum ditandai, highlight item baru, tandai semua dibaca setelah render, hapus per item. (Sebelumnya cuma 1 baris & langsung mark-all-read tanpa indikator.)
- `admin/notifikasi.php` ditulis ulang: tab "Notifikasi Saya" (default, read/unread berfungsi, mark-read saat dibuka, hapus) + tab "Semua Pengguna" (monitoring + cari). Badge sidebar (item 1) kini bermakna.
