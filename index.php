<?php
session_start();
$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// JIKA SUDAH LOGIN, JANGAN BOLEHKAN BUKA HALAMAN LOGIN LAGI
if (isset($_SESSION['status_login']) && $_SESSION['status_login'] === true) {
    if ($_SESSION['role'] == 'admin') { header("Location: admin/dashboard_admin.php"); exit(); }
    if ($_SESSION['role'] == 'tutor') { header("Location: tutor/dashboard_tutor.php"); exit(); }
    if ($_SESSION['role'] == 'orang_tua') { header("Location: orangtua/dashboard_ortu.php"); exit(); }
}

$error = "";

// ==========================================
// LOGIKA PROSES LOGIN (VERSI BARU YANG BENAR)
// ==========================================
if (isset($_POST['submit_login'])) {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = mysqli_real_escape_string($koneksi, trim($_POST['password']));

    // Cari user di database
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Cek apakah passwordnya cocok
        if ($password === $data['password']) {
            
            // Simpan data ke memori (Session)
            $_SESSION['status_login'] = true;
            $_SESSION['role'] = $data['role']; 
            $_SESSION['username'] = $data['username'];
            
            // Jika role Admin
            if ($data['role'] == 'admin') {
                $_SESSION['admin_name'] = $data['full_name']; 
                header("Location: admin/dashboard_admin.php");
                exit();
            } 
           // Jika role Tutor
            else if ($data['role'] == 'tutor') {
                $_SESSION['tutor_name'] = ucwords($data['full_name']); // <-- TAMBAHKAN BARIS INI
                header("Location: tutor/attendance.php"); // Pastikan arah lokasinya benar
                exit();
            }
            // Jika role Orang Tua
            else if ($data['role'] == 'orang_tua') {
                
                $email_ortu = $data['username']; 
                $_SESSION['parent_name'] = 'Bapak/Ibu ' . ucwords($data['full_name']);

                // CARI NAMA ANAK DI TABEL STUDENTS
                $q_anak = mysqli_query($koneksi, "SELECT full_name FROM students WHERE TRIM(parent_email) = '$email_ortu' LIMIT 1");
                
                if($q_anak && mysqli_num_rows($q_anak) > 0) {
                    $data_anak = mysqli_fetch_assoc($q_anak);
                    $_SESSION['child_name'] = $data_anak['full_name']; 
                    
                    header("Location: orangtua/dashboard_ortu.php");
                    exit();
                } else {
                    $error = "Data anak untuk email ($email_ortu) tidak ditemukan!";
                }
            } else {
                $error = "Role tidak dikenali oleh sistem!";
            }
        } else {
            $error = "Username atau Password salah!";
        }
    } else {
        $error = "Akun tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Linggis LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Public Sans', sans-serif; background-color: #0d6efd; }
        .card-custom { border: none; border-radius: 1.5rem; box-shadow: 0 1rem 3rem rgba(0,0,0,0.2); overflow: hidden; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 400; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card card-custom">
                <div class="card-body p-4 p-md-5 bg-white">
                    
                    <div class="text-center mb-4">
                        <div class="d-inline-flex bg-primary-subtle text-primary p-3 rounded-circle mb-3">
                            <span class="material-symbols-outlined fs-1">school</span>
                        </div>
                        <h2 class="fw-bolder text-dark mb-1">Welcome Back!</h2>
                        <p class="text-muted small">Login ke Linggis Learning Management System</p>
                    </div>

                    <?php if($error != "") { ?>
                        <div class="alert alert-danger rounded-3 border-0 py-2 text-center small fw-medium"><?= $error ?></div>
                    <?php } ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-dark">Username / Email</label>
                            <input type="text" name="username" class="form-control bg-light border-0 py-2" placeholder="Masukkan username" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-dark">Password</label>
                            <input type="password" name="password" class="form-control bg-light border-0 py-2" placeholder="Masukkan password" required>
                        </div>
                        <button type="submit" name="submit_login" class="btn btn-primary w-100 fw-bold py-2 rounded-pill shadow-sm mb-3">
                            Sign In
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <span class="text-muted small">Orang tua belum punya akun? </span>
                        <a href="register.php" class="text-decoration-none fw-semibold small">Daftar di sini</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>