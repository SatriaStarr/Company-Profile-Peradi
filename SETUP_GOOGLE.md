# Setup Login dengan Google (OAuth 2.0)

Login username/password **tetap berjalan seperti biasa**. Tombol "Login dengan Google"
hanya muncul di `user/login.php` setelah kredensial diisi.

---

## 1. Buat OAuth Client di Google Cloud Console

1. Buka <https://console.cloud.google.com/> → buat / pilih **Project**.
2. **APIs & Services → OAuth consent screen**
   - User Type: **External**
   - Isi App name, User support email, Developer contact.
   - **Scopes**: tambahkan `openid`, `.../auth/userinfo.email`, `.../auth/userinfo.profile`.
   - Selama status **Testing**, tambahkan email penguji di **Test users**
     (kalau tidak, hanya akun itu yang bisa login).
3. **APIs & Services → Credentials → Create Credentials → OAuth client ID**
   - Application type: **Web application**
   - **Authorized JavaScript origins**:
     `http://localhost` (sesuaikan host/port; produksi pakai `https://domain-anda`)
   - **Authorized redirect URIs** (WAJIB sama persis dengan yang dipakai aplikasi):
     `http://localhost/WEB-DPC-PERADI-PONTIANAK/user/google_callback.php`
4. Salin **Client ID** dan **Client Secret**.

## 2. Isi kredensial

Edit **`config/google_keys.local.php`** (sudah ada, tidak ikut git):

```php
return [
    'client_id'     => 'xxxxxxx-xxxx.apps.googleusercontent.com',
    'client_secret' => 'GOCSPX-xxxxxxxx',
    'redirect_uri'  => '',   // kosong = otomatis; isi manual bila host berbeda
];
```

> Redirect URI otomatis = `scheme://host/WEB-DPC-PERADI-PONTIANAK/user/google_callback.php`.
> Nilai ini harus **identik** dengan yang didaftarkan di Google Console
> (termasuk http vs https dan ada/tidaknya port).

## 3. Cara kerja

| Langkah | File |
|---|---|
| User klik "Login dengan Google" | `user/login.php` (tombol muncul jika `google_aktif()`) |
| Redirect ke Google + simpan `state` (anti-CSRF) | `user/google_login.php` |
| Google callback → verifikasi `state`, tukar `code`→token, ambil profil | `user/google_callback.php` |
| Cari user: **google_id → email**. Belum ada → buat akun baru `role=user`, `status=active` | `user/google_callback.php` |
| Email sudah terdaftar (login biasa) → `google_id` **ditautkan** ke akun itu (tidak buat akun ganda) | `user/google_callback.php` |
| Session dibuat SAMA seperti login biasa (`user_id`, `role=user`, `user_login=true`) → ke `dashboard.php` | `user/google_callback.php` |

Keamanan:
- `state` acak per permintaan, diverifikasi `hash_equals` di callback (proteksi CSRF).
- Email harus `email_verified = true` dari Google.
- **Password Google tidak disimpan.** Kolom `password` diisi hash acak.
- Semua query DB memakai prepared statement.
- `google_id` (`sub` dari Google) UNIK per akun Google → mencegah akun ganda.

## 4. Uji

Login username/password: tetap seperti biasa.
Login Google: klik tombol → pilih akun → kembali ke dashboard user.
Kolom DB: `users.google_id` (sudah ada).
