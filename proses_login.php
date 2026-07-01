<?php
// Mulai sesi untuk mengingat siapa yang sedang login
session_start();

// Koneksi ke database bawaan Laragon
$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

// Cek jika database gagal terhubung
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Menangkap data yang diketik user di form HTML
if (isset($_POST['username']) && isset($_POST['password'])) {
    
    // Pakai trim() supaya kalau ada spasi nggak sengaja keikut pas ngetik, langsung dibersihkan
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = mysqli_real_escape_string($koneksi, trim($_POST['password']));

    // Mencari user di database
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    // Jika username ditemukan di database
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Cek apakah passwordnya cocok
        if ($password === $user['password']) {
            
            // Simpan data ke memori sementara (Session)
            $_SESSION['status_login'] = true; // Wajib ada untuk lolos pengecekan dashboard
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // CEK ROLE UNTUK DIARAHKAN KE RUANGAN YANG TEPAT
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard_admin.php");
                exit();
            } 
            else if ($user['role'] == 'tutor') {
                header("Location: dashboard_tutor.php");
                exit();
            } 
            else if ($user['role'] == 'orang_tua') {
                
                // ==========================================================
                // AMBIL DATA ORTU DAN ANAK
                // ==========================================================
                $email_ortu = $user['username']; 
                
                // Ambil sapaan ortu dari kolom 'full_name' di tabel users (Contoh: "Bapak/Ibu farah")
                $_SESSION['parent_name'] = 'Bapak/Ibu ' . ucwords($user['full_name']);

                // Cari nama anak di tabel students (Pakai TRIM untuk menghindari error spasi tersembunyi di database)
                $q_anak = mysqli_query($koneksi, "SELECT full_name FROM students WHERE TRIM(parent_email) = '$email_ortu' LIMIT 1");
                
                if($q_anak && mysqli_num_rows($q_anak) > 0) {
                    $data_anak = mysqli_fetch_assoc($q_anak);
                    $_SESSION['child_name'] = $data_anak['full_name']; // Simpan nama anak ke session
                    
                    // Semua aman, berangkat ke Dashboard!
                    header("Location: orangtua/dashboard_ortu.php");
                    exit();
                } else {
                    // MODE DETEKTIF: Kalau anaknya nggak ketemu, hentikan login dan kasih peringatan!
                    echo "<script>
                        alert('PERHATIAN: Login berhasil sebagai Orang Tua, TAPI sistem tidak dapat menemukan anak yang terdaftar dengan parent_email: $email_ortu di tabel students. Cek kembali databasenya!');
                        window.history.back();
                    </script>";
                    exit();
                }
            }

        } else {
            echo "<script>alert('Password yang Anda masukkan salah!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Akun tidak ditemukan!'); window.history.back();</script>";
    }
}
?>