<?php
session_start();
$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (isset($_POST['upload'])) {
    $id_payment = $_POST['id_payment'];
    
    // Proses File Gambar
    $nama_file = $_FILES['foto_bukti']['name'];
    $tmp_file = $_FILES['foto_bukti']['tmp_name'];
    $ukuran_file = $_FILES['foto_bukti']['size'];
    
    // Ganti nama file biar unik (Contoh: BUKTI-INV-2026-001.jpg)
    $ext = pathinfo($nama_file, PATHINFO_EXTENSION);
    $nama_baru = 'BUKTI-' . $id_payment . '-' . time() . '.' . $ext;
    
    $path = "bukti_transfer/" . $nama_baru;

    // Cek apakah yang diupload benar-benar gambar
    $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg', 'pdf');
    if (in_array(strtolower($ext), $ekstensi_diperbolehkan)) {
        if ($ukuran_file <= 2000000) { // Maksimal 2MB
            move_uploaded_file($tmp_file, $path);
            
            // Update nama file ke database
            mysqli_query($koneksi, "UPDATE payments SET bukti_bayar = '$nama_baru' WHERE id = '$id_payment'");
            
            $_SESSION['pesan'] = "Bukti transfer berhasil dikirim! Menunggu konfirmasi Admin.";
        } else {
            $_SESSION['error'] = "Ukuran file terlalu besar! Maksimal 2MB.";
        }
    } else {
        $_SESSION['error'] = "Ekstensi file tidak diperbolehkan! Hanya JPG, PNG, atau PDF.";
    }
    
    header("Location: payments.php");
    exit();
}
?>