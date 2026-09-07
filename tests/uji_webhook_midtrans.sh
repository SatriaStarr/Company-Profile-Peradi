#!/bin/bash
# Uji endpoint webhook midtrans_notify.php end-to-end secara LOKAL
# (pakai dummy server key + signature sah; panggilan API nyata akan gagal &
#  webhook fallback ke body notifikasi yang sudah lolos signature).
set -e
BASE="http://127.0.0.1:8080"
ROOT="C:/xampp/htdocs/WEB-DPC-PERADI-PONTIANAK"
MYSQL="/c/xampp/mysql/bin/mysql -uroot db_peradi"
CFG="$ROOT/config/midtrans_keys.local.php"
BACKUP="$CFG.bak_test"

DUMMY_KEY="SB-Mid-server-DUMMYTESTKEY000000000000"

cleanup() {
  [ -f "$BACKUP" ] && mv "$BACKUP" "$CFG"
  $MYSQL -e "DELETE FROM pendaftaran WHERE id_pendaftaran IN (SELECT id_pendaftaran FROM pembayaran WHERE order_id=\"WHTEST-001\");" 2>/dev/null; $MYSQL -e "DELETE FROM pembayaran WHERE order_id=\"WHTEST-001\";" 2>/dev/null; true
}
trap cleanup EXIT

# 1. pasang dummy key sementara
cp "$CFG" "$BACKUP"
cat > "$CFG" <<PHP
<?php
return ['is_production' => false, 'server_key' => '$DUMMY_KEY', 'client_key' => 'SB-Mid-client-DUMMY', 'merchant_id' => ''];
PHP

# 2. buat pendaftaran + pembayaran midtrans 'pending'
$MYSQL -e "
INSERT INTO pendaftaran (id_user,jenis_pendaftaran,status) VALUES (8,'PKPA','menunggu_verifikasi');
SET @tp=LAST_INSERT_ID();
INSERT INTO pembayaran (id_pendaftaran,metode,order_id,jumlah,gross_amount,status,transaction_status)
  VALUES (@tp,'midtrans','WHTEST-001',2500000,2500000,'pending','pending');
SELECT @tp;"
TP=$($MYSQL -N -e "SELECT id_pendaftaran FROM pembayaran WHERE order_id='WHTEST-001'")
echo "pendaftaran uji id=$TP"

send() {
  local trx="$1" fraud="$2" gross="$3" code="$4"
  local sig=$(php -r "echo hash('sha512','WHTEST-001'.'$code'.'$gross'.'$DUMMY_KEY');")
  local body=$(php -r "echo json_encode(['order_id'=>'WHTEST-001','status_code'=>'$code','gross_amount'=>'$gross','transaction_status'=>'$trx','fraud_status'=>'$fraud','transaction_id'=>'TRX-999','payment_type'=>'bank_transfer','signature_key'=>'$sig']);")
  curl -s -o /dev/null -w "%{http_code}" -X POST -H "Content-Type: application/json" -d "$body" "$BASE/midtrans_notify.php"
}

echo
echo "--- A. notif signature SALAH -> harus 403 ---"
BAD=$(php -r "echo json_encode(['order_id'=>'WHTEST-001','status_code'=>'200','gross_amount'=>'2500000.00','transaction_status'=>'settlement','signature_key'=>'ngasal']);")
echo "  HTTP: $(curl -s -o /dev/null -w '%{http_code}' -X POST -H 'Content-Type: application/json' -d "$BAD" "$BASE/midtrans_notify.php")  (harap 403)"

echo "--- B. settlement (signature sah) -> pembayaran lunas, pendaftaran terdaftar ---"
echo "  HTTP: $(send settlement accept 2500000.00 200)  (harap 200)"
$MYSQL -e "SELECT pb.status bayar, pb.paid_at, p.status pdftr FROM pembayaran pb JOIN pendaftaran p ON p.id_pendaftaran=pb.id_pendaftaran WHERE pb.order_id='WHTEST-001'\G"

echo "--- C. kirim ULANG settlement (idempoten) -> tidak buat notifikasi ganda ---"
echo "  HTTP: $(send settlement accept 2500000.00 200)"
$MYSQL -N -e "SELECT COUNT(*) FROM notifikasi WHERE id_user=8 AND judul='Pembayaran berhasil'" | sed 's/^/  notifikasi "Pembayaran berhasil": /'

echo "--- D. lalu 'expire' datang -> TIDAK menurunkan status lunas ---"
echo "  HTTP: $(send expire '' 2500000.00 202)"
$MYSQL -N -e "SELECT status FROM pembayaran WHERE order_id='WHTEST-001'" | sed 's/^/  status pembayaran (harap tetap lunas): /'

echo "--- E. nominal beda -> ditolak ---"
echo "  HTTP: $(send settlement accept 999.00 200)  (harap 422)"

# bersihkan notifikasi uji
$MYSQL -e "DELETE FROM notifikasi WHERE id_user=8 AND id_notifikasi>3;"
echo
echo "SELESAI."
