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
- [x] 8. Keamanan upload file
- [x] 9. Keamanan sistem admin & user
- [x] 10. Testing end-to-end
- [x] 11. Jangan ubah file yang sudah berjalan tanpa alasan
- [x] 12. Pakai DB & kode nyata sebagai acuan
- [x] 13. Payment Gateway Midtrans Sandbox
- [x] 14. Pembayaran manual tetap dipertahankan
- [x] 15. Alur Payment Gateway lengkap
- [x] 16. Callback/webhook Midtrans
- [x] 17. Penyesuaian DB Payment Gateway
- [x] 18. Testing pembayaran menyeluruh
- [x] 19. Popup konfirmasi logout admin & user
- [x] 20. Login dengan Google

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

### 8. Keamanan upload file — SELESAI
- **`uploads/.htaccess` baru**: matikan directory listing + tolak eksekusi `.php/.phtml/.phar/.pl/.py/.sh/...` di seluruh `uploads/` dan subfolder (blanket anti-RCE untuk semua jalur upload).
- Upload sisi USER (bukti pembayaran, pendaftaran PKPA/UPA) SUDAH aman sebelumnya: validasi MIME `finfo`, batas ukuran, nama acak `random_bytes` (bukan nama asli user). Diverifikasi, tidak diubah.
- **CMS admin (galeri, struktur, peraturan) ditulis ulang** — sebelumnya: TANPA auth di sebagian `proses_*`/`hapus_*`, SQLi (string interpolation), upload tanpa validasi ekstensi (`proses_tambah_struktur`, semua `proses_edit_*`), `proses_edit_galeri` memakai **nama file asli user** (`time().'_'.$_FILES['foto']['name']`). Kini semua:
  - `require_once partials/admin_auth.php` (cek login + ROLE + CSRF),
  - `csrf_check()` + `csrf_field()` di form + modal hapus (hapus jadi POST, bukan GET),
  - prepared statement,
  - upload via `simpan_upload()` (MIME finfo + whitelist + ukuran + nama acak + `getimagesize` untuk gambar).
- `admin/detail_pendaftaran.php` upload sertifikat: tambah validasi MIME `application/pdf` (dulu cuma cek ekstensi dari nama).
- Uji: PNG valid tersimpan nama acak; **file PHP disamarkan `.png` (MIME diklaim image/png) DITOLAK** oleh finfo; hapus POST tanpa CSRF -> 419; `proses_*` tanpa login -> redirect.
- SISA item 9: `kelola_running_text.php` & `kelola_layanan.php` form POST belum CSRF (hanya teks/relatif rendah risiko); `detail_pendaftaran.php` form belum CSRF.

### 9. Keamanan sistem admin & user — SELESAI
- **Session & role check**: seluruh halaman admin sekarang pakai `admin_auth.php` (cek `status_login` + role admin/super_admin) atau minimal cek setara. Halaman super-admin-only (`verifikasi_admin.php`, `proses_verifikasi.php`) benar-benar cek `super_admin`.
- **CSRF** (baru `admin/partials/csrf.php`): token di semua endpoint yang mengubah data — data_user, edit_user, advokat (tambah/edit/hapus/import), galeri/struktur/peraturan (tambah/edit/hapus), pengumuman, data_pendaftaran (ubah status/hapus), detail_pendaftaran (status/hasil/sertifikat), kelola_layanan, kelola_running_text, proses_verifikasi, notifikasi. Aksi berbasis GET (`?hapus=`, `?aksi=terima`) diubah jadi POST.
- **Prepared statement**: semua modul yang ditulis ulang (advokat, CMS, user, notifikasi). `user/proses_register.php` diubah ke prepared. `admin/proses_login.php` & `admin/proses_register.php` & `user/proses_login.php` memakai `mysqli_real_escape_string` konsisten (aman dari injection; belum prepared — dicatat sebagai perbaikan opsional).
- **IDOR user**: semua query sisi user difilter `id_user` pemilik (`detail_pendaftaran`, `pembayaran`, `proses_pembayaran`, `notifikasi`, `profil`). Diverifikasi.
- **XSS**: running-text ticker publik (`header.php`) dulu `innerHTML = msg.pesan` + `json_encode` tanpa escape tag -> **XSS via konten admin**. Diperbaiki: `json_encode` + `JSON_HEX_TAG|HEX_APOS|HEX_QUOT|HEX_AMP`, render pakai `textContent`/`createElement`. Output `pesan`/`icon` di admin & publik di-`htmlspecialchars`. Modul lain sudah pakai `e()`/`htmlspecialchars` konsisten.
- **`proses_verifikasi.php`**: dulu redirect ke `../verifikasi_admin.php` (path salah, di root -> tidak ada). Diperbaiki + notifikasi ke admin yang disetujui.
- Uji: POST tanpa CSRF -> 419; dengan CSRF -> 302 & data berubah.

### 10. Testing end-to-end — SELESAI
- **BUG BESAR (pendaftaran UPA)**: `proses_pendaftaran_upa.php` INSERT ke ~26 kolom yang tidak ada di `pendaftaran_upa` -> pendaftaran UPA SELALU GAGAL. Solusi: `database/migrasi_2026_09_07_pendaftaran_upa_lengkap.sql` menambah semua kolom sesuai formulir + `nik`/`pekerjaan` jadi NULL. INSERT dirapikan (38 kolom sinkron) + `nik` diisi dari `nomor_identitas`.
- **GAP (verifikasi pembayaran)**: `admin/detail_pendaftaran.php` tidak punya tombol verifikasi pembayaran. Ditambah aksi `verifikasi_pembayaran` (Setujui Lunas / Tolak) -> update `pembayaran.status`, set `tanggal_bayar`, dorong `pendaftaran.status='terdaftar'`, kirim notifikasi user.
- **Alur diuji end-to-end via HTTP (php -S + curl):**
  - UPA: daftar user -> login -> isi formulir UPA (semua field + 4 file + tanda tangan) -> pendaftaran+pendaftaran_upa+pembayaran terbuat -> upload bukti -> admin lihat detail (NIK, alamat, Foto KTP, Tanda Tangan, Lihat Bukti tampil) -> ubah status -> input hasil (nilai/lulus) -> upload sertifikat (+tanggal_terbit) -> user lihat hasil + unduh sertifikat -> 5 notifikasi user + 2 notifikasi admin. **SEMUA OK, tanpa error.**
  - PKPA: daftar -> submit (foto_calon_advokat benar) -> bayar Rp 8.000.000 -> upload bukti -> admin verifikasi LUNAS -> pembayaran=lunas, pendaftaran=terdaftar. OK.
  - Edge: IDOR (user lain akses pendaftaran -> redirect, data tidak bocor); id tidak valid (user & admin -> redirect bersih); upload bukti milik orang lain -> diblok; pendaftaran ganda -> diarahkan ke yang ada, tidak buat baris kedua.
- Catatan: harga PKPA di alur `proses_pendaftaran_pkpa.php` = Rp 8.000.000, di `proses_pendaftaran.php` generik = Rp 2.500.000 (tidak konsisten; alur pkpa yang dipakai). Tidak diubah — keputusan harga milik DPC.

### 11. Jangan ubah file yang sudah berjalan tanpa alasan — DIPATUHI
- Setiap perubahan punya alasan tercatat di file ini.
- `user/proses_pembayaran.php`: HANYA ditambah notifikasi admin (2 baris) — logika pembayaran manual/upload bukti TIDAK diubah. Diverifikasi jalan di e2e item 10.
- Migrasi DB hanya menambah kolom / memperluas enum (tidak menghapus kolom pembayaran manual mana pun).
- File yang ditulis ulang hanya yang memang rusak/tidak aman (SQLi, tanpa auth, kolom salah, fatal error).

### 12. Pakai DB & kode nyata sebagai acuan — DIPATUHI
- Skema DB nyata didokumentasikan di bagian atas file ini & dijadikan acuan.
- Ketidaksesuaian dokumen `.md` lama vs DB yang diperbaiki: `bukti_bayar`->`bukti_pembayaran`, `jenis`->`jenis_pendaftaran`, `tanggal`->`tanggal_daftar`, `catatan_admin`->`catatan`, kolom `pendaftaran_pkpa.foto`->`foto_calon_advokat`, tabel `berkas` (tidak ada) -> `pendaftaran_pkpa`/`_upa`, enum status pendaftaran, `jenis_kelamin` enum('L','P').

### 13-18. Payment Gateway Midtrans Sandbox — SELESAI (mode: config placeholder)
- **DB**: `database/migrasi_2026_09_07_pembayaran_gateway.sql` — kolom `metode`(manual/midtrans), `order_id`(UNIQUE), `transaction_id`, `payment_type`, `transaction_status`, `fraud_status`, `gross_amount`, `snap_token`, `midtrans_response`, `paid_at`, `updated_at`. Enum `status` diperluas `+pending,kadaluarsa,dibatalkan`. Kolom manual TIDAK diubah. (item 17)
- **Config**: `config/midtrans.php` (klien Snap API via cURL, tanpa Composer) + `config/midtrans_keys.local.php` (kosong, gitignored) + `.example`. Selama `server_key` kosong -> `midtrans_aktif()`=false -> tombol "Bayar Online" disembunyikan, sistem tetap jalan penuh dengan manual. (item 14)
- **Alur** (item 15): `user/pembayaran.php` menampilkan **2 opsi** (Bayar Online / Transfer Bank Manual). "Bayar Online" -> `user/midtrans_charge.php` (buat transaksi Snap, simpan order_id, status `pending`) -> redirect ke halaman Midtrans -> kembali ke `user/midtrans_status.php` (sinkron status dari API + tampilkan hasil). Menangani pending/settlement/deny/cancel/expire/failure.
- **Webhook** (item 16): `midtrans_notify.php` (root) — verifikasi `signature_key` sha512, lalu **tarik ulang status resmi via API `/status`** (tidak percaya body mentah), lalu terapkan idempoten (transaksi + `FOR UPDATE`, cek perubahan, tidak menurunkan status `lunas`, validasi nominal). Balas HTTP 200.
- **Helper**: `pembayaran_helper.php` — `pembayaran_terapkan_notif()`, `pembayaran_sinkron_dari_midtrans()`. Saat `lunas`: `pendaftaran.status='terdaftar'` + notifikasi user & admin (sekali).
- **Admin**: `admin/detail_pendaftaran.php` menampilkan metode + order_id + transaction_id + payment_type + status transaksi untuk pembayaran gateway. Tombol verifikasi manual hanya muncul untuk `metode='manual'`.
- **Uji** (item 18): pemetaan 8 status OK; `pembayaran_terapkan_notif` idempoten (notif kedua identik -> `changed=false`, tidak ada notifikasi ganda); downgrade `lunas`->`expire` ditolak; nominal salah ditolak; dengan gateway non-aktif semua halaman tetap render & alur manual utuh; webhook tanpa keys -> 503. **Uji sandbox penuh (kartu uji + webhook) menunggu kredensial user** — instruksi di `SETUP_MIDTRANS.md`.

### 19. Popup konfirmasi logout — SELESAI
- Baru `partials/logout_modal.php` (root): modal "Yakin ingin keluar?" + tombol "Ya, Logout" / "Batal", desain mengikuti website (biru #1e3a8a, Montserrat), animasi, tutup via Esc / klik luar / Batal.
- Cara pakai: elemen logout diberi atribut `data-logout`; klik dicegat -> tampilkan modal -> "Ya, Logout" meneruskan ke `logout.php`.
- **Admin**: dimasukkan ke `admin/partials/sidebar.php` -> otomatis di SEMUA halaman admin.
- **User**: `user/dashboard.php` (satu-satunya halaman user dengan link logout).
- Mekanisme session/logout TIDAK diubah — hanya konfirmasi sebelum diteruskan.
- Uji: modal + teks muncul di semua halaman admin & dashboard user.

### 20. Login dengan Google — SELESAI (mode: config placeholder)
- Tombol "Login dengan Google" sudah ada di `user/login.php`; kini hanya tampil bila `google_aktif()` (kredensial terisi). Login username/password tidak diubah.
- **Config**: `user/google_config.php` ditulis ulang -> baca `config/google_keys.local.php` (kosong, gitignored) + `.example`. Helper `google_aktif()`, `google_client_id/secret()`, `google_redirect_uri()` (auto atau manual). Konstanta lama tetap ada (kompatibilitas).
- `user/google_login.php`: state anti-CSRF acak per request, redirect ke Google. Bila belum dikonfigurasi -> `login.php?pesan=google_off`.
- `user/google_callback.php` ditulis ulang:
  - verifikasi `state` (`hash_equals`), tukar code->token, ambil profil (butuh `email_verified`).
  - pencocokan user: **google_id -> email**. Email sudah ada (akun login biasa) -> `google_id` DITAUTKAN, tidak buat akun ganda. Belum ada -> buat akun `role=user status=active`, username unik.
  - **prepared statement** semua (dulu `mysqli_real_escape_string`).
  - **password Google TIDAK disimpan** (hash acak).
  - session dibuat SAMA dengan login biasa (`user_id`, `role=user`, `user_login=true`, `session_regenerate_id`) -> `dashboard.php`.
- Pesan error baru di `login.php`: `google_gagal`, `google_off`.
- Uji: gating (tombol hilang + redirect saat non-aktif); logika pencocokan akun (login ke-2 via google_id = user sama; email cocok -> google_id tertaut; tidak ada duplikat). Handshake OAuth penuh menunggu Client ID/Secret user -> `SETUP_GOOGLE.md`.

---

## Ringkasan migrasi DB (jalankan berurutan bila setup dari awal)
1. `database/sertifikasi.sql` (skema dasar sertifikasi — bila belum ada)
2. `database/migrasi_2026_09_07_status_pendaftaran.sql`
3. `database/migrasi_2026_09_07_sertifikat_tanggal_terbit.sql`
4. `database/migrasi_2026_09_07_pendaftaran_upa_lengkap.sql`
5. `database/migrasi_2026_09_07_pembayaran_gateway.sql`

## File konfigurasi yang perlu diisi (opsional; sistem jalan tanpa ini)
- `config/midtrans_keys.local.php`  -> lihat `SETUP_MIDTRANS.md`
- `config/google_keys.local.php`    -> lihat `SETUP_GOOGLE.md`

---

## Perbaikan tambahan (pasca item 20)

### BUG path upload pendaftaran (bagian dari item 7, ditemukan saat e2e final)
- `proses_pendaftaran_pkpa.php` / `proses_pendaftaran_upa.php` menyimpan file ke `user/uploads/...` (karena `__DIR__` = folder `user/`), padahal SEMUA kode yang menampilkan (`user/detail_pendaftaran.php`, `admin/detail_pendaftaran.php`) mengarah ke `uploads/...` di ROOT -> semua foto KTP/ijazah/pas foto/tanda tangan peserta 404.
- Diperbaiki: `__DIR__ . '/' . $folder` -> `__DIR__ . '/../' . $folder`. File existing dipindah ke `uploads/`.
- `.gitignore`: berkas berisi data pribadi peserta (`uploads/pembayaran|bukti_pembayaran|sertifikat|advokat|foto|ijazah|ktp|tanda_tangan/*`, `uploads/upa/`, `user/uploads/`) tidak lagi di-commit. Struktur folder dijaga via `.gitkeep` + `.htaccess`.
- Uji ulang e2e UPA+PKPA penuh: file tersimpan di `uploads/` root, dapat diakses (HTTP 200), alur lengkap tetap OK.

## Status akhir
**20/20 item checklist SELESAI.** Semua halaman (8 publik + 20 admin + 11 user) dirender via `php -S` tanpa error PHP. Alur end-to-end PKPA & UPA diverifikasi (daftar -> berkas -> bayar manual/gateway -> verifikasi -> hasil -> sertifikat -> notifikasi). Payment gateway Midtrans & Login Google siap-pakai begitu kredensial diisi (`SETUP_MIDTRANS.md`, `SETUP_GOOGLE.md`).

## Perbaikan lanjutan (pemeriksaan menyeluruh ke-2)
- `admin/proses_login.php` & `admin/proses_register.php`: diubah ke **prepared statement** (dulu `mysqli_real_escape_string`) + CSRF di register admin. (item 9)
- `admin/layanan_pkpa.php` / `layanan_upa.php` / `layanan_sumpah.php`: pakai `admin_auth` -> ada cek ROLE (dulu hanya `status_login`). (item 9)
- **Data advokat 336 baris `jenis_kelamin=''`**: `proses_import.php` kini punya opsi **"Perbarui data untuk NIA yang sudah ada"** (checkbox di modal Import). Re-import CSV asli dengan opsi ini akan memperbaiki jenis kelamin + mengisi kolom kosong (COALESCE, tidak menimpa data yang sudah ada dengan nilai kosong). Diuji: skip vs update.
- `user/dashboard.php`: badge merah jumlah notifikasi belum dibaca di link "Notifikasi" (sidebar + quick-menu). (item 4)

## Pemeriksaan akhir
- `php -l` seluruh file .php: **0 error**.
- 46 halaman (8 publik + 27 admin + 11 user) dirender via `php -S`: **semua OK, tanpa error/warning/notice**.
- E2E UPA penuh diulang: daftar -> berkas -> bayar -> verifikasi -> hasil -> sertifikat -> notifikasi user & admin. **OK, server log bersih**.
- IDOR: semua query sisi user difilter `id_user`. Cek role: semua halaman admin (kecuali `index.php` yang hanya redirect).
- SQLi login admin: percobaan `x' OR '1'='1` -> notfound (prepared statement).
- Data uji dibersihkan; DB kembali ke kondisi awal (5 user, 2 pendaftaran, 3 notifikasi, 336 advokat).

---

## Review "dosen pembimbing" + fitur tambahan (2026-09-08)

Peninjauan menyeluruh terhadap sistem yang berjalan, lalu menambah fitur yang masih kurang untuk sebuah "Sistem Informasi Manajemen":

### Baru: Verifikasi keaslian sertifikat (publik)
- `cek_sertifikat.php` (root) — halaman publik tanpa login. Masukkan nomor sertifikat -> tampil nama pemegang, jenis sertifikasi, hasil ujian, tanggal terbit. NIK **disamarkan sebagian** demi privasi. Query prepared statement.
- `header.php` — tautan menu **"Cek Sertifikat"** di navbar. Sekalian memperbaiki markup `<li>` menu Login yang rusak (tag `<li>`/`<a>` bersarang salah).

### Baru: Modul Laporan & Rekapitulasi (admin)
- `admin/laporan.php` — rekap **pendaftaran per status & per bulan**, **pembayaran** (jumlah transaksi + nilai per status, total pemasukan terverifikasi/lunas), **hasil ujian & sertifikat**, **keanggotaan advokat**. Filter **rentang tanggal**. Tombol **Cetak** (CSS `@media print`, tanpa sidebar) & **Unduh CSV** (BOM UTF-8).
- `admin/partials/sidebar.php` — menu **"Laporan"** (+ entry di `$__active_map`).

### Ditingkatkan: Dashboard peserta
- `user/dashboard.php` — kartu **Status Pembayaran**; **progress tracker** 5 langkah (Berkas Dikirim -> Diverifikasi -> Pembayaran -> Terdaftar -> Selesai) dengan penanda langkah selesai/aktif; blok aksi **"Lakukan Pembayaran"** (bila belum bayar) & **"Unduh Sertifikat"** (bila sudah terbit).

### Bug fix
- **`admin/detail_pendaftaran.php` — input hasil ujian menyimpan `''`**: form mengirim `status_lulus` bernilai `lulus`/`tidak_lulus`/`''`, sedangkan kolom `hasil.status_lulus` adalah `enum('Lulus','Tidak Lulus','Belum Diumumkan')`. Akibat: MySQL non-strict menyimpan `''` -> hasil tidak pernah tampil ke peserta. Diperbaiki: handler memetakan input ke nilai enum, `<select>` memakai value enum + preselect nilai tersimpan. Diuji via HTTP: tersimpan `Lulus`.

### Uji
- `php -l` seluruh file berubah: 0 error. Semua halaman baru/berubah dirender via Apache (login admin & peserta) tanpa error/warning.
- `cek_sertifikat.php`: nomor valid -> "Terverifikasi"; nomor asal -> "Tidak Ditemukan".
- `admin/laporan.php`: HTML + ekspor CSV OK; filter tanggal OK.
- `user/dashboard.php`: progress tracker + blok sertifikat tampil untuk peserta yang lulus.

### Data demo & dokumentasi laporan
- Data contoh untuk BAB IV Laporan TA (4 peserta `*.example.com` di berbagai status + 3 pengumuman + berkas placeholder di `uploads/`) dimasukkan agar screenshot lengkap.
- **`database/hapus_data_demo.sql`** — hapus seluruh data demo tersebut untuk mengembalikan DB ke kondisi bersih.
- 22 screenshot sistem (`laporan/gambar/bab4_*.png`) untuk BAB IV. `laporan/` gitignored.
