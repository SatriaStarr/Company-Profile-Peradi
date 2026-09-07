<?php
// Hubungkan ke database
include 'koneksi.php';

if (isset($_POST['import'])) {

    $fileName = $_FILES['file_csv']['tmp_name'];

    if ($_FILES['file_csv']['size'] > 0) {

        $file = fopen($fileName, "r");

        // Lewati Baris Header (Judul Kolom)
        fgetcsv($file, 10000, ";");

        $berhasil = 0;
        $gagal = 0;

        while (($column = fgetcsv($file, 10000, ";")) !== FALSE) {

            // ===============================================================
            // MAPPING URUT SESUAI KOLOM EXCEL ANDA (Kiri ke Kanan)
            // ===============================================================

            // Kolom A [0] : No. (Tidak dipakai karena Auto Number)

            // Kolom B [1] : NIA
            $nia = isset($column[1]) ? mysqli_real_escape_string($conn, $column[1]) : '';

            // Kolom C [2] : NAMA LENGKAP dan GELAR
            $nama_lengkap = isset($column[2]) ? mysqli_real_escape_string($conn, $column[2]) : '';

            // Kolom D [3] : Jenis Kelamin (L/P) -> Kita ubah jadi text panjang
            $jk_kode = isset($column[3]) ? $column[3] : '';
            $jenis_kelamin = ($jk_kode == 'L') ? 'Laki-laki' : 'Perempuan';

            // Kolom E [4] : Tempat Lahir
            $tempat_lahir = isset($column[4]) ? mysqli_real_escape_string($conn, $column[4]) : '';

            // Kolom F [5] : Tanggal Lahir (Format Excel: YYYY-MM-DD)
            $tanggal_lahir = isset($column[5]) ? $column[5] : '1970-01-01';

            // Kolom G [6] : Agama
            $agama = isset($column[6]) ? mysqli_real_escape_string($conn, $column[6]) : '';

            // Kolom H [7] : Nomor Handphone
            $no_hp = isset($column[7]) ? mysqli_real_escape_string($conn, $column[7]) : '';

            // Kolom I [8] : Nama Kantor
            $nama_kantor = isset($column[8]) ? mysqli_real_escape_string($conn, $column[8]) : '';

            // Kolom J [9] : Alamat Kantor
            $alamat_kantor = isset($column[9]) ? mysqli_real_escape_string($conn, $column[9]) : '';

            // Kolom K [10] : Kota Kantor (Dilewati - tidak ada di database Anda)
            // $kota_kantor = $column[10]; 

            // Kolom L [11] : Nomor Telepon Kantor (Dilewati)
            // $telp_kantor = $column[11];

            // Kolom M [12] : Nomor Identitas Kependudukan (NIK)
            $nik = isset($column[12]) ? mysqli_real_escape_string($conn, $column[12]) : '';

            // Kolom N [13] : Alamat Rumah (Sesuai KTP)
            $alamat_domisili = isset($column[13]) ? mysqli_real_escape_string($conn, $column[13]) : '';

            // Kolom O s/d Z [14-25] : (Dilewati - karena database belum ada kolomnya)
            // Jika nanti mau dipakai, tinggal tambahkan di sini urut.


            // ===============================================================
            // DATA TAMBAHAN (DEFAULT)
            // ===============================================================
            $status = 'aktif';
            $foto   = 'default.jpg';

            // ===============================================================
            // CEK DUPLIKAT NIA (WAJIB)
            // ===============================================================
            $cek_nia = mysqli_query($conn, "SELECT nia FROM data_advokat WHERE nia='$nia'");

            if (mysqli_num_rows($cek_nia) > 0) {
                $gagal++;
                continue; // Lewati data ini
            }



            // ===============================================================
            // MASUKKAN KE DATABASE
            // ===============================================================
            $query = "INSERT INTO data_advokat (
                        nia, 
                        nama_lengkap, 
                        jenis_kelamin, 
                        tempat_lahir, 
                        tanggal_lahir, 
                        agama, 
                        no_hp, 
                        nama_kantor_hukum, 
                        alamat_kantor, 
                        nik, 
                        alamat_domisili, 
                        status_keanggotaan, 
                        foto_profil, 
                        created_at
                      ) VALUES (
                        '$nia', 
                        '$nama_lengkap', 
                        '$jenis_kelamin', 
                        '$tempat_lahir', 
                        '$tanggal_lahir', 
                        '$agama', 
                        '$no_hp', 
                        '$nama_kantor', 
                        '$alamat_kantor', 
                        '$nik', 
                        '$alamat_domisili', 
                        '$status', 
                        '$foto', 
                        NOW()
                      )";

            if (mysqli_query($conn, $query)) {
                $berhasil++;
            } else {
                $gagal++;
            }
        }

        fclose($file);
        header("Location: data_advokat.php?pesan=import_selesai&sukses=$berhasil&gagal=$gagal");
        exit;
    }
}
