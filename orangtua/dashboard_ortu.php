<?php
session_start();

// Cek apakah yang login benar-benar Orang Tua
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'orang_tua') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

// ==========================================
// PENGAMANAN SESSION (LANGKAH 1)
// ==========================================
// Ambil data session murni (TIDAK ADA NAMA DEFAULT LAGI)
$nama_ortu = isset($_SESSION['parent_name']) ? $_SESSION['parent_name'] : 'Bapak/Ibu';
$nama_anak = isset($_SESSION['child_name']) ? $_SESSION['child_name'] : ''; 

// Keamanan Tambahan: Kalau nama anak beneran kosong, jangan tampilkan data orang lain!
if ($nama_anak == '') {
    die("<div style='text-align:center; margin-top:100px; font-family:sans-serif; color:#333;'>
            <h2 style='color:#dc3545;'>🚨 Fatal Error: Data Siswa Tidak Ditemukan!</h2>
            <p>Sistem gagal mendapatkan data nama anak dari proses login. <br>Kemungkinan besar session <b>\$_SESSION['child_name']</b> belum di-set saat login.</p>
            <a href='../logout.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background-color:#0d6efd; color:white; text-decoration:none; border-radius:5px;'>Kembali ke Login</a>
         </div>");
}
// ==========================================

// ==========================================
// MENGAMBIL DATA DINAMIS 
// ==========================================

// 1. Ambil Total Kehadiran
$total_hadir = 0;
$q_hadir = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM attendance WHERE student_name = '$nama_anak' AND (status_hadir = 'Hadir' OR status_hadir = 'Present')");
if($q_hadir) { $total_hadir = mysqli_fetch_assoc($q_hadir)['total']; }

// 2. Ambil Rata-rata Nilai 
$rata_nilai = '-';
$q_nilai = mysqli_query($koneksi, "SELECT AVG((grammar + speaking + writing + reading) / 4) as rata FROM grades WHERE student_name = '$nama_anak'");
if($q_nilai) { 
    $d_nilai = mysqli_fetch_assoc($q_nilai);
    if($d_nilai['rata'] != null) {
        $rata_nilai = number_format($d_nilai['rata'], 1); 
    }
}

// 3. Ambil Status Tagihan
$status_tagihan = "Belum Bayar"; 
$warna_tagihan = "text-danger"; 
$q_tagihan = mysqli_query($koneksi, "SELECT status FROM payments WHERE student_name = '$nama_anak' ORDER BY id DESC LIMIT 1");
if($q_tagihan && mysqli_num_rows($q_tagihan) > 0) {
    $d_tagihan = mysqli_fetch_assoc($q_tagihan);
    $status_tagihan = $d_tagihan['status'];
    // Hijau kalau lunas/Paid, kalau tidak ya merah
    $warna_tagihan = (strtolower($status_tagihan) == 'lunas' || strtolower($status_tagihan) == 'paid') ? "text-success" : "text-danger";
}

// 4. Ambil Jadwal Kelas
$q_jadwal = mysqli_query($koneksi, "SELECT c.class_name, c.schedule_day, c.teacher_name 
                                    FROM classes c 
                                    JOIN students s ON c.course_program = s.course 
                                    WHERE s.full_name = '$nama_anak' LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Beranda Orang Tua - Linggis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Public Sans', sans-serif; background-color: #f8f9fc; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: flex; align-items: center; }
        .sidebar-width { width: 280px; min-width: 280px; }
        .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
        .text-primary-custom { color: #0d6efd; }
        .hover-bg-light:hover { background-color: #f1f5f9; }
        .icon-box { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
        .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
    </style>
</head>
<body>

<div class="d-flex vh-100 overflow-hidden">
    <aside class="sidebar-width bg-white border-end d-flex flex-column justify-content-between h-100">
        <div class="p-4 overflow-y-auto">
            <div class="d-flex align-items-center gap-3 mb-5">
                <div class="bg-primary p-2 rounded text-white d-flex align-items-center justify-content-center">
                    <span class="material-symbols-outlined fs-4">family_restroom</span>
                </div>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Linggis Parent</h1>
                    <p class="text-muted" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0;">Student Progress</p>
                </div>
            </div>

            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="dashboard_ortu.php">
                    <span class="material-symbols-outlined">home</span><span>Beranda</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php">
                    <span class="material-symbols-outlined">fact_check</span><span>Kehadiran Anak</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php">
                    <span class="material-symbols-outlined">military_tech</span><span>Nilai & Evaluasi</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="catatan_tutor.php">
                    <span class="material-symbols-outlined">menu_book</span><span>Catatan Tutor</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php">
                    <span class="material-symbols-outlined">receipt_long</span><span>Tagihan & Pembayaran</span>
                </a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="#"><span class="material-symbols-outlined">manage_accounts</span><span>Profil Saya</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Keluar</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-md-none me-2"><span class="material-symbols-outlined">menu</span></button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= htmlspecialchars($nama_ortu); ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Orang Tua / Wali</p>
                </div>
                <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px;" src="https://ui-avatars.com/api/?name=<?= urlencode($nama_ortu); ?>&background=EBF5FB&color=0D8ABC"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white overflow-hidden position-relative">
                <div class="card-body p-4 p-md-5 z-1 position-relative">
                    <h2 class="fw-bolder mb-2">Selamat Datang, <?= htmlspecialchars($nama_ortu); ?>! 👋</h2>
                    <p class="mb-0 opacity-75 fs-6">Ini adalah ringkasan progres belajar <strong><?= htmlspecialchars(ucwords($nama_anak)); ?></strong> di Linggis. Pantau terus perkembangannya secara berkala.</p>
                </div>
                <span class="material-symbols-outlined position-absolute opacity-10" style="font-size: 200px; right: -20px; bottom: -40px; z-index: 0;">school</span>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="icon-box bg-success-soft">
                                <span class="material-symbols-outlined">fact_check</span>
                            </div>
                            <div>
                                <p class="text-muted small fw-semibold mb-0 text-uppercase tracking-wider">Total Kehadiran</p>
                                <h3 class="fw-bold mb-0 text-dark"><?= $total_hadir ?> <span class="fs-6 fw-normal text-muted">Sesi</span></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="icon-box bg-info-soft">
                                <span class="material-symbols-outlined">workspace_premium</span>
                            </div>
                            <div>
                                <p class="text-muted small fw-semibold mb-0 text-uppercase tracking-wider">Rata-rata Nilai</p>
                                <h3 class="fw-bold mb-0 text-dark"><?= $rata_nilai ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="icon-box bg-warning-soft text-warning">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                            <div>
                                <p class="text-muted small fw-semibold mb-0 text-uppercase tracking-wider">Status Tagihan</p>
                                <h3 class="fw-bold mb-0 <?= $warna_tagihan ?> fs-4"><?= htmlspecialchars(ucfirst($status_tagihan)) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary">calendar_month</span> Informasi Kelas Siswa
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="px-4 py-3 border-0">JADWAL & WAKTU</th>
                                    <th class="px-4 py-3 border-0">NAMA KELAS</th>
                                    <th class="px-4 py-3 border-0">NAMA TUTOR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if($q_jadwal && mysqli_num_rows($q_jadwal) > 0) {
                                    while($row = mysqli_fetch_assoc($q_jadwal)) { ?>
                                    <tr>
                                        <td class="px-4 py-3 fw-bold text-dark"><?= htmlspecialchars($row['schedule_day']) ?></td>
                                        <td class="px-4 py-3"><span class="badge bg-primary-soft text-primary px-2 py-1 rounded"><?= htmlspecialchars($row['class_name']) ?></span></td>
                                        <td class="px-4 py-3 text-dark fw-medium"><?= htmlspecialchars($row['teacher_name']) ?></td>
                                    </tr>
                                <?php } 
                                } else { ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <span class="material-symbols-outlined d-block fs-1 mb-2 opacity-25">event_busy</span>
                                            Belum ada jadwal kelas yang tersedia untuk program kursus siswa ini.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>