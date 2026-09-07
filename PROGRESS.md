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
- [x] 5. Audit Data Advokat
- [x] 6. Audit Detail Pendaftaran
- [x] 7. Perbaiki path upload/file (404)
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

### 5. Audit Data Advokat — SELESAI (sekaligus menuntaskan item 7/8/9 utk modul ini)
- Baru `upload_helper.php` (root): `simpan_upload()` (validasi MIME finfo + whitelist ekstensi + batas ukuran + nama acak `random_bytes` + simpan ke `uploads/<subdir>/` di ROOT), `hapus_upload()`, `url_upload()`. Ini standar upload untuk item 8.
- Folder baru `uploads/advokat/`.
- `data_advokat.php` ditulis ulang: pakai `admin_auth` (cek ROLE, sebelumnya tidak), sidebar+topbar konsisten dashboard, **pencarian server-side** (nama/NIA/NIK/kantor/HP) auto-submit, filter status auto-submit, reset ikon, **pagination** (15/hal). Sebelumnya load 352 baris + search JS.
- `detail_advokat.php` ditulis ulang: `admin_auth` (sebelumnya TANPA cek login sama sekali), sidebar, path file benar (`../uploads/...`, sebelumnya `uploads/...` -> 404), tampil semua kolom termasuk `file_bas` & `masa_berlaku_ktpa`.
- `tambah_advokat.php` + `edit_advokat.php` + partial `form_advokat.php`: form lengkap sesuai kolom DB nyata, CSRF, sidebar konsisten.
- `proses_tambah.php` / `proses_edit.php` ditulis ulang: **prepared statement** (sebelumnya string interpolation = SQLi), **cek auth** (sebelumnya TANPA auth = siapa pun bisa POST), CSRF, upload via `simpan_upload` ke `uploads/advokat/`, validasi, cek NIA duplikat (errno 1062), rollback file jika gagal. `jenis_kelamin` disimpan sebagai `L`/`P` sesuai enum.
- `hapus_advokat.php`: POST + CSRF + auth (sebelumnya GET tanpa role), prepared DELETE, hapus file terkait, **hapus panggilan cURL ke server eksternal `http://80.80.80.135:3000`**.
- `proses_import.php`: auth + CSRF + validasi file, prepared statement, **perbaikan bug**: `jenis_kelamin` dulu diisi "Laki-laki"/"Perempuan" ke kolom `enum('L','P')` -> tersimpan `''`. Kini map ke `L`/`P`. `foto_profil` dibiarkan NULL (dulu 'default.jpg' padahal file tidak ada).
- Uji: tambah/edit/hapus/duplikat-NIA/import CSV/pencarian/pagination + guard CSRF (419) semua OK.
- **CATATAN DATA LAMA**: 336 baris advokat existing punya `jenis_kelamin=''` akibat bug import lama. Perlu re-import CSV (importer sekarang skip NIA duplikat) atau perbaikan manual — tidak bisa saya tebak datanya.

### 6. Audit Detail Pendaftaran — SELESAI
- **BUG BESAR diperbaiki (status enum)**: `pendaftaran.status` di DB = `enum('menunggu','diproses','diterima','ditolak','selesai')` TAPI seluruh aplikasi pakai alur 7 status (`menunggu_verifikasi`, `berkas_diperiksa`, `menunggu_pembayaran`, `pembayaran_diperiksa`, `terdaftar`, `selesai`, `ditolak`). Akibat: admin ubah status -> tersimpan `''` -> alur pendaftaran tidak jalan. Solusi: `database/migrasi_2026_09_07_status_pendaftaran.sql` mengubah enum ke 8 nilai (+ alias `menunggu`) & perbaiki baris `''`. Insert di `proses_pendaftaran*.php` diubah `'menunggu'` -> `'menunggu_verifikasi'`. Diuji: ubah status ke `berkas_diperiksa` kini tersimpan benar.
- **BUG (tabel `berkas` tidak ada)**: `detail_pendaftaran.php` query `FROM berkas` (dijaga tableExists -> selalu kosong). Berkas sebenarnya = kolom di `pendaftaran_pkpa`/`pendaftaran_upa`. Diubah: baca `$peserta` + `$berkas` dari tabel yang benar sesuai jenis.
- **Data Peserta**: card "Data Peserta" kini menampilkan NIK, TTL, pekerjaan, no HP, alamat dari formulir PKPA/UPA (sebelumnya hanya ID/nama/email/jenis).
- **BUG path bukti pembayaran (item 7)**: link `../user/uploads/bukti_pembayaran/<basename>` -> diganti helper `fileUrl()` yang menangani path tersimpan (`uploads/pembayaran/x`, `user/uploads/...`, atau nama file saja). Sama untuk link berkas (dulu `../uploads/<file>` -> dobel `uploads/`).
- **BUG kolom `sertifikat.tanggal_terbit` tidak ada**: kode sudah memakainya (dijaga columnExists). `database/migrasi_2026_09_07_sertifikat_tanggal_terbit.sql` menambah kolom `DATE NULL`.
- **BUG kolom `pendaftaran_pkpa.foto`**: `proses_pendaftaran_pkpa.php` insert ke kolom `foto` (tidak ada) -> diperbaiki ke `foto_calon_advokat`. (INSERT `pendaftaran_upa` masih tidak sinkron dengan skema — ditangani di item 10.)
- Uji: detail render id 1 & 2, ubah status jalan.
- **SISA untuk item 9**: form POST di `detail_pendaftaran.php` belum pakai CSRF.

### 7. Perbaiki path upload/file (404) — SELESAI
- **`user/detail_pendaftaran.php` sebelumnya FATAL ERROR**: query `SELECT * FROM berkas` (tabel tidak ada) + kolom salah (`py.bukti_bayar`, `p.jenis`, `p.tanggal`, `catatan_admin`, `py.catatan`). Ditulis ulang total: baca kolom nyata (`bukti_pembayaran`, `jenis_pendaftaran`, `tanggal_daftar`, `catatan`), berkas dari `pendaftaran_pkpa`/`pendaftaran_upa`, path file via helper `file_href()`. Link pembayaran -> `pembayaran.php` (transfer bank, sesuai item 14), sertifikat -> `../uploads/sertifikat/<file>`.
- **`user/upload_berkas.php`**: dulu INSERT ke tabel `berkas` (tidak ada) + `uploads/berkas/`. Kini redirect ke detail (berkas diunggah saat isi formulir pendaftaran).
- `user/dashboard.php`: `$pendaftaran['jenis']` -> `jenis_pendaftaran` (dulu selalu tampil "-").
- **File PHP hilang**: audit seluruh `href/action="*.php"` -> hanya `profil.php` yang hilang (dilink dari dashboard user). Dibuat `user/profil.php` (edit nama/email/password, cek email unik, verifikasi password lama).
- Tidak ditemukan referensi ke file fiktif `detail_pembayaran.php`. `detail_peserta.php` & `hasil_pengumuman.php` sudah diperbaiki di item 1 & 3.
- Link file CMS admin (`../uploads/galeri|struktur|peraturan|layanan/`) sudah benar (folder ada di root) — tidak diubah.
- Uji: semua halaman user render tanpa error; detail pendaftaran id 1 & 2 OK.
