# Setup Payment Gateway Midtrans (Sandbox)

Sistem mendukung **dua cara pembayaran** yang berjalan bersamaan:

1. **Transfer Bank (Manual)** — user transfer lalu upload bukti, admin verifikasi manual. *(selalu aktif, tidak butuh konfigurasi)*
2. **Bayar Online (Midtrans)** — kartu / Virtual Account / e-Wallet / QRIS, status otomatis. *(aktif setelah kredensial diisi)*

Selama kredensial Midtrans belum diisi, tombol "Bayar Online" otomatis disembunyikan dan sistem tetap berjalan penuh dengan pembayaran manual.

---

## 1. Buat akun Midtrans Sandbox

1. Daftar di <https://dashboard.sandbox.midtrans.com>.
2. Masuk menu **Settings → Access Keys**. Catat:
   - **Server Key** (contoh `SB-Mid-server-xxxxxxxx`)
   - **Client Key** (contoh `SB-Mid-client-xxxxxxxx`)
   - **Merchant ID** (opsional)

## 2. Isi kredensial

Edit file **`config/midtrans_keys.local.php`** (sudah ada, tidak ikut ke git):

```php
return [
    'is_production' => false,                       // WAJIB false selama testing
    'server_key'    => 'SB-Mid-server-XXXXXXXXXXXX',
    'client_key'    => 'SB-Mid-client-XXXXXXXXXXXX',
    'merchant_id'   => 'GXXXXXXXXX',
];
```

> `is_production` = `false` → memakai endpoint **sandbox**. Jangan set `true` sebelum benar-benar siap produksi.

## 3. Set URL Notifikasi (Webhook)

Di dashboard Midtrans: **Settings → Configuration → Payment Notification URL**:

```
https://<domain-anda>/WEB-DPC-PERADI-PONTIANAK/midtrans_notify.php
```

Untuk testing lokal (XAMPP), Midtrans tidak bisa memanggil `localhost`. Gunakan salah satu:
- **ngrok**: `ngrok http 80` lalu pakai URL `https://xxxx.ngrok-free.app/WEB-DPC-PERADI-PONTIANAK/midtrans_notify.php`
- atau tombol **"Cek status pembayaran"** di halaman hasil (`user/midtrans_status.php`) yang menarik status langsung dari API Midtrans (tidak bergantung webhook).

Redirect URL (Finish/Unfinish/Error) tidak perlu diisi — sudah dikirim per transaksi lewat parameter `callbacks.finish` ke `user/midtrans_status.php`.

## 4. Alur Payment Gateway

| Langkah | File |
|---|---|
| User pilih "Bayar Online Sekarang" | `user/pembayaran.php` |
| Sistem buat transaksi Snap + simpan `order_id` | `user/midtrans_charge.php` |
| User diarahkan ke halaman pembayaran Midtrans | (redirect_url dari Snap) |
| Midtrans kirim notifikasi transaksi | `midtrans_notify.php` (verifikasi signature sha512 + tarik ulang status via API) |
| User kembali → status disinkronkan & ditampilkan | `user/midtrans_status.php` |
| Admin melihat metode, order_id, payment_type, status | `admin/detail_pendaftaran.php` |

Pemetaan status Midtrans → status lokal (`pembayaran.status`):

| Midtrans `transaction_status` | Lokal |
|---|---|
| `settlement`, `capture` (accept) | `lunas` |
| `pending` | `pending` |
| `deny`, `failure`, `refund` | `ditolak` |
| `cancel` | `dibatalkan` |
| `expire` | `kadaluarsa` |

Saat `lunas`: `pendaftaran.status` otomatis jadi `terdaftar`, user & admin dapat notifikasi. Update bersifat **idempoten** (webhook boleh dipanggil berkali-kali tanpa membuat data ganda) dan **tidak menurunkan** status yang sudah `lunas`.

## 5. Kartu uji Sandbox

- Kartu sukses: `4811 1111 1111 1114`, CVV `123`, exp bebas di masa depan, OTP `112233`.
- Simulator VA / e-Wallet: <https://simulator.sandbox.midtrans.com>.

## 6. Kolom DB baru (`pembayaran`)

`metode`, `order_id` (unique), `transaction_id`, `payment_type`, `transaction_status`, `fraud_status`, `gross_amount`, `snap_token`, `midtrans_response`, `paid_at`, `updated_at`.
Enum `status` diperluas: `+ pending, kadaluarsa, dibatalkan`.
Kolom pembayaran manual (`bukti_pembayaran`, `jumlah`, `tanggal_bayar`) **tidak diubah**.

Jalankan migrasi bila belum: `database/migrasi_2026_09_07_pembayaran_gateway.sql`.
