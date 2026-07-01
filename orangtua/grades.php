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
// PENGAMANAN SESSION
// ==========================================
$nama_ortu = isset($_SESSION['parent_name']) ? $_SESSION['parent_name'] : 'Bapak/Ibu';
$nama_anak = isset($_SESSION['child_name']) ? $_SESSION['child_name'] : ''; 

if ($nama_anak == '') {
    die("<div style='text-align:center; margin-top:100px; font-family:sans-serif; color:#333;'>
            <h2 style='color:#dc3545;'>🚨 Fatal Error: Data Siswa Tidak Ditemukan!</h2>
            <p>Sistem gagal mendapatkan data nama anak. Silakan logout dan login kembali.</p>
            <a href='../logout.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background-color:#0d6efd; color:white; text-decoration:none; border-radius:5px;'>Kembali ke Login</a>
         </div>");
}

// 1. Ambil Rata-rata per Skill
$grammar = 0; $speaking = 0; $writing = 0; $reading = 0;
$q_avg = mysqli_query($koneksi, "SELECT AVG(grammar) as g, AVG(speaking) as s, AVG(writing) as w, AVG(reading) as r 
                                 FROM grades WHERE student_name = '$nama_anak'");
if($q_avg) {
    $d_avg = mysqli_fetch_assoc($q_avg);
    $grammar = round($d_avg['g'] ?? 0);
    $speaking = round($d_avg['s'] ?? 0);
    $writing = round($d_avg['w'] ?? 0);
    $reading = round($d_avg['r'] ?? 0);
}

// 2. Ambil Riwayat Nilai Terbaru
$q_riwayat = mysqli_query($koneksi, "SELECT * FROM grades WHERE student_name = '$nama_anak' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nilai & Evaluasi - Linggis Parent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Public Sans', sans-serif; background-color: #f8f9fc; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: flex; align-items: center; }
        .sidebar-width { width: 280px; min-width: 280px; }
        .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
        .text-primary-custom { color: #0d6efd; }
        .hover-bg-light:hover { background-color: #f1f5f9; }
        .card-grade { border-radius: 20px; border: none; }
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
                <div><h1 class="fs-5 fw-bold mb-0">Linggis Parent</h1><p class="text-muted" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0;">Student Progress</p></div>
            </div>
            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded" href="dashboard_ortu.php"><span class="material-symbols-outlined">home</span><span>Beranda</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded" href="attendance.php"><span class="material-symbols-outlined">fact_check</span><span>Kehadiran Anak</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="grades.php"><span class="material-symbols-outlined">military_tech</span><span>Nilai & Evaluasi</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="catatan_tutor.php"><span class="material-symbols-outlined">menu_book</span><span>Catatan Tutor</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded" href="payments.php"><span class="material-symbols-outlined">receipt_long</span><span>Tagihan & Pembayaran</span></a>
            </nav>
        </div>
        
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="profil.php">
                <span class="material-symbols-outlined">manage_accounts</span><span>Profil Saya</span>
            </a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php">
                <span class="material-symbols-outlined">logout</span><span>Keluar</span>
            </a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto bg-light">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-end">
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= htmlspecialchars($nama_ortu); ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Orang Tua / Wali</p>
                </div>
                <img class="rounded-circle border" style="width: 40px; height: 40px;" src="https://ui-avatars.com/api/?name=<?= urlencode($nama_ortu); ?>&background=EBF5FB&color=0D8ABC"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="mb-4">
                <h2 class="fw-bolder text-dark mb-1">Nilai & Evaluasi Belajar</h2>
                <p class="text-muted">Perbandingan capaian skill bahasa Inggris untuk <strong><?= htmlspecialchars(ucwords($nama_anak)); ?></strong>.</p>
            </div>

            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="card card-grade shadow-sm bg-white p-4 h-100">
                        <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-primary">bar_chart</span> Grafik Kemajuan Skill
                        </h5>
                        <div style="height: 300px; position: relative;">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card card-grade shadow-sm bg-white h-100 overflow-hidden">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-0">Riwayat Nilai Terbaru</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-secondary small text-uppercase">
                                        <tr>
                                            <th class="px-4 py-3 border-0">Kelas</th>
                                            <th class="px-4 py-3 border-0 text-center">Rata-rata</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($q_riwayat && mysqli_num_rows($q_riwayat) > 0) {
                                            while($row = mysqli_fetch_assoc($q_riwayat)) { 
                                                $avg = ($row['grammar'] + $row['speaking'] + $row['writing'] + $row['reading']) / 4;
                                        ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($row['class_name']) ?></span>
                                                    <small class="text-muted"><?= date('d M Y', strtotime($row['created_at'] ?? 'now')) ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 8px; width: 100px; margin: 0 auto 5px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $avg ?>%"></div>
                                                    </div>
                                                    <span class="fw-bold text-primary"><?= number_format($avg, 1) ?></span>
                                                </td>
                                            </tr>
                                        <?php } } else { ?>
                                            <tr><td colspan="2" class="text-center py-5 text-muted small">Belum ada data nilai.</td></tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const ctx = document.getElementById('barChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Grammar', 'Speaking', 'Writing', 'Reading'],
            datasets: [{
                label: 'Skor Skill',
                data: [<?= $grammar ?>, <?= $speaking ?>, <?= $writing ?>, <?= $reading ?>],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.6)', 
                    'rgba(25, 135, 84, 0.6)',  
                    'rgba(255, 193, 7, 0.6)',  
                    'rgba(13, 202, 240, 0.6)'  
                ],
                borderColor: [
                    '#0d6efd', '#198754', '#ffc107', '#0dcaf0'
                ],
                borderWidth: 2,
                borderRadius: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
</script>
</body>
</html>