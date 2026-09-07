<?php
session_start();
include 'koneksi.php';

// 1. Cek Login (Keamanan)
if (!isset($_SESSION['status_login'])) {
    header("Location: index.php");
    exit;
}

// 2. Cek apakah ada ID di URL?
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // --- LANGKAH PENTING: AMBIL DATA DULU SEBELUM DIHAPUS ---
    // Kita butuh nama filenya untuk dihapus dari folder
    $query_cek = mysqli_query($conn, "SELECT * FROM data_advokat WHERE id_advokat = '$id'");
    $data = mysqli_fetch_assoc($query_cek);

    // Jika data ditemukan
    if ($data) {
        
        // A. HAPUS FOTO PROFIL (Jika ada file & filenya nyata)
        if (!empty($data['foto_profil'])) {
            $path_foto = 'uploads/' . $data['foto_profil'];
            if (file_exists($path_foto)) {
                unlink($path_foto); // Perintah sakti untuk menghapus file
            }
        }

        // B. HAPUS FILE KTPA
        if (!empty($data['file_ktpa'])) {
            $path_ktpa = 'uploads/' . $data['file_ktpa'];
            if (file_exists($path_ktpa)) {
                unlink($path_ktpa);
            }
        }

        // C. BARU HAPUS DATA DARI DATABASE
        $query_hapus = mysqli_query($conn, "DELETE FROM data_advokat WHERE id_advokat = '$id'");

        if ($query_hapus) {
            // 🔔 KIRIM SINYAL KE REALTIME SERVER (Node.js)
            $ch = curl_init("http://80.80.80.135:3000/notify-update");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);

            // Kembali ke tabel
            header("Location: data_advokat.php?pesan=sukses_hapus");
        } else {
            echo "Gagal menghapus database: " . mysqli_error($conn);
        }

    } else {
        echo "Data tidak ditemukan.";
    }

} else {
    // Jika orang iseng buka file ini tanpa ID
    header("Location: data_advokat.php");
}
?>