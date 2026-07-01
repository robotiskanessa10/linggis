<?php
session_start();
$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

$error = "";
$success = "";

if (isset($_POST['submit_register'])) {
    // Data Orang Tua
    $parent_name = $_POST['parent_name'];
    $parent_email = $_POST['parent_email'];
    $password = $_POST['password'];
    
    // Data Anak (Siswa)
    $student_name = $_POST['student_name'];
    $student_email = $_POST['student_email'];
    $course = $_POST['course'];
    
    // 1. Cek apakah email orang tua sudah terdaftar di tabel users
    $cek_email = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$parent_email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $error = "Email Orang Tua sudah terdaftar! Silakan gunakan email lain atau Login.";
    } else {
        // 2. Simpan Akun Orang Tua ke tabel `users` (role: orang_tua)
        $q_user = "INSERT INTO users (username, password, full_name, role) VALUES ('$parent_email', '$password', '$parent_name', 'orang_tua')";
        
        if (mysqli_query($koneksi, $q_user)) {
            // 3. Buat ID Siswa Otomatis
            $query_last = "SELECT student_id FROM students ORDER BY id DESC LIMIT 1";
            $res_last = mysqli_query($koneksi, $query_last);
            if (mysqli_num_rows($res_last) > 0) {
                $row_last = mysqli_fetch_assoc($res_last);
                $last_num = (int) substr($row_last['student_id'], 4);
                $new_num = $last_num + 1;
            } else { $new_num = 1; }
            $id_siswa = 'LNG-' . str_pad($new_num, 3, '0', STR_PAD_LEFT);
            
            $date = date('Y-m-d');
            $status_siswa = 'Pending'; 

            // 4. Simpan Data Anak ke tabel `students`
            $q_student = "INSERT INTO students (student_id, full_name, email, parent_email, course, enroll_date, status) 
                          VALUES ('$id_siswa', '$student_name', '$student_email', '$parent_email', '$course', '$date', '$status_siswa')";
            
            if (mysqli_query($koneksi, $q_student)) {
                
                // =========================================================
                // 5. BUAT TAGIHAN OTOMATIS KE TABEL PAYMENTS (SUDAH FIX!)
                // =========================================================
                // Menentukan harga berdasarkan pilihan kursus
                $harga_kursus = 0;
                if ($course == 'General English') {
                    $harga_kursus = 500000;
                } elseif ($course == 'TOEFL Preparation') {
                    $harga_kursus = 750000;
                } elseif ($course == 'IELTS Preparation') {
                    $harga_kursus = 1000000;
                }

                // Bikin format Invoice Otomatis (Contoh: INV-2026-004)
                // Kita ambil ID terakhir dari tabel payments buat nomor urutnya
                $q_last_inv = mysqli_query($koneksi, "SELECT id FROM payments ORDER BY id DESC LIMIT 1");
                $urutan_inv = 1;
                if (mysqli_num_rows($q_last_inv) > 0) {
                    $data_inv = mysqli_fetch_assoc($q_last_inv);
                    $urutan_inv = $data_inv['id'] + 1;
                }
                $invoice_id = 'INV-' . date('Y') . '-' . str_pad($urutan_inv, 3, '0', STR_PAD_LEFT);

                // Eksekusi Simpan ke Tabel Payments sesuai kolom di HeidiSQL
                $q_tagihan = "INSERT INTO payments (invoice_id, student_name, course_program, amount, payment_date, method, status) 
                              VALUES ('$invoice_id', '$student_name', '$course', '$harga_kursus', '$date', 'Transfer Bank', 'Unpaid')";
                
                if (mysqli_query($koneksi, $q_tagihan)) {
                    $success = "Pendaftaran berhasil! Silakan Login untuk melihat tagihan kursus.";
                } else {
                    $error = "Pendaftaran berhasil, tapi gagal membuat tagihan otomatis: " . mysqli_error($koneksi);
                }
                // =========================================================

            } else {
                $error = "Gagal menyimpan data siswa: " . mysqli_error($koneksi);
            }
        } else {
            $error = "Gagal membuat akun orang tua.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pendaftaran Kursus - Linggis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Public Sans', sans-serif; background-color: #f1f5f9; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 400; display: flex; align-items: center; }
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .bg-brand { background-color: #0d6efd; color: white; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            
            <div class="text-center mb-4">
                <div class="d-inline-flex bg-brand p-3 rounded-circle mb-3 shadow-sm">
                    <span class="material-symbols-outlined fs-1">school</span>
                </div>
                <h2 class="fw-bolder text-dark mb-1">Pendaftaran Siswa Baru</h2>
                <p class="text-muted">LMS Linggis - Platform Belajar Bahasa Inggris</p>
            </div>

            <?php if($error != "") { ?>
                <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4"><?= $error ?></div>
            <?php } ?>
            
            <?php if($success != "") { ?>
                <div class="alert alert-success rounded-3 border-0 shadow-sm mb-4 text-center">
                    <h5 class="fw-bold mb-2">🎉 <?= $success ?></h5>
                    <a href="index.php" class="btn btn-success mt-2 fw-semibold px-4">Menuju Halaman Login</a>
                </div>
            <?php } else { ?>

            <div class="card card-custom">
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="">
                        <div class="row g-5">
                            
                            <div class="col-md-6 border-md-end pe-md-4">
                                <h5 class="fw-bold mb-4 d-flex align-items-center gap-2 text-primary">
                                    <span class="material-symbols-outlined">family_restroom</span> Data Orang Tua
                                </h5>
                                <p class="small text-muted mb-4">Data ini akan digunakan untuk login ke dalam Dashboard Orang Tua.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Nama Lengkap Orang Tua</label>
                                    <input type="text" name="parent_name" class="form-control bg-light border-0" placeholder="Sesuai KTP" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Alamat Email (Sebagai Username)</label>
                                    <input type="email" name="parent_email" class="form-control bg-light border-0" placeholder="ortu@email.com" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Buat Password</label>
                                    <input type="password" name="password" class="form-control bg-light border-0" placeholder="Minimal 6 karakter" required>
                                </div>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <h5 class="fw-bold mb-4 d-flex align-items-center gap-2 text-success">
                                    <span class="material-symbols-outlined">face</span> Data Calon Siswa
                                </h5>
                                <p class="small text-muted mb-4">Informasi anak yang akan didaftarkan ke dalam program kursus kami.</p>

                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Nama Lengkap Siswa</label>
                                    <input type="text" name="student_name" class="form-control bg-light border-0" placeholder="Nama lengkap anak" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Email Siswa (Opsional)</label>
                                    <input type="email" name="student_email" class="form-control bg-light border-0" placeholder="Jika ada">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-medium">Pilih Program Kursus</label>
                                    <select name="course" class="form-select bg-light border-0" required>
                                        <option value="" selected disabled>-- Silakan Pilih --</option>
                                        <option value="General English">General English (Rp 500.000)</option>
                                        <option value="TOEFL Preparation">TOEFL Preparation (Rp 750.000)</option>
                                        <option value="IELTS Preparation">IELTS Preparation (Rp 1.000.000)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                            <span class="small text-muted">Sudah punya akun? <a href="index.php" class="text-decoration-none fw-semibold">Login di sini</a></span>
                            <button type="submit" name="submit_register" class="btn btn-primary fw-semibold px-5 py-2 rounded-pill shadow-sm">
                                Daftar Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php } ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>