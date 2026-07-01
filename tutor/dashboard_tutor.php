<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'tutor') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

$nama_tutor = isset($_SESSION['tutor_name']) ? $_SESSION['tutor_name'] : 'Tutor Linggis';

// 1. Ambil spesialisasi
$q_tutor = mysqli_query($koneksi, "SELECT specialty FROM teachers WHERE full_name = '$nama_tutor'");
$specialty = "English Tutor";
if ($q_tutor && mysqli_num_rows($q_tutor) > 0) {
    $specialty = mysqli_fetch_assoc($q_tutor)['specialty'];
}

// 2. Hitung total kelas yang diajar
$q_kelas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM classes WHERE teacher_name = '$nama_tutor'");
$total_kelas = mysqli_fetch_assoc($q_kelas)['total'] ?? 0;

// 3. Ambil jadwal kelas
$jadwal_tutor = mysqli_query($koneksi, "SELECT * FROM classes WHERE teacher_name = '$nama_tutor' ORDER BY status DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Linggis Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Public Sans', sans-serif; background-color: #f8f9fc; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: flex; align-items: center; }
        .sidebar-width { width: 280px; min-width: 280px; }
        .text-primary-custom { color: #0d6efd; }
        .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
        .hover-bg-light:hover { background-color: #f1f5f9; }
        .icon-box { display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; border-radius: 0.75rem; }
        .search-wrapper .material-symbols-outlined { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .search-wrapper input { padding-left: 40px; border-radius: 0.75rem; }
        .shortcut-card { transition: all 0.3s ease; border: 1px solid #e2e8f0; }
        .shortcut-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; border-color: #cbd5e1; }
    </style>
</head>
<body>

<div class="d-flex vh-100 overflow-hidden">
    
    <aside class="sidebar-width bg-white border-end d-flex flex-column justify-content-between h-100">
        <div class="p-4 overflow-y-auto">
            <div class="d-flex align-items-center gap-3 mb-5">
                <div class="bg-primary p-2 rounded text-white d-flex align-items-center justify-content-center">
                    <span class="material-symbols-outlined fs-4">school</span>
                </div>
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Linggis</h1>
                    <p class="text-muted" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0;">Teacher Portal</p>
                </div>
            </div>

            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="dashboard_tutor.php">
                    <span class="material-symbols-outlined">dashboard</span><span>Dashboard</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php">
                    <span class="material-symbols-outlined">calendar_today</span><span>Attendance</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="input_notes.php
                ">
                    <span class="material-symbols-outlined">menu_book</span><span>Learning Notes</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php">
                    <span class="material-symbols-outlined">assignment_turned_in</span><span>Grades</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="#"><span class="material-symbols-outlined">settings</span><span>Settings</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Sign Out</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="position-relative search-wrapper" style="width: 350px;">
                <span class="material-symbols-outlined">search</span>
                <input class="form-control bg-light border-0" placeholder="Search students, classes, or notes..." type="text" />
            </div>

            <div class="d-flex align-items-center gap-4">
                <button class="btn btn-light position-relative p-2 d-flex align-items-center justify-content-center rounded-circle border-0 text-secondary hover-bg-light">
                    <span class="material-symbols-outlined fs-5">notifications</span>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="margin-top: 8px; margin-left: -8px;"></span>
                </button>
                <div class="vr bg-secondary opacity-25" style="height: 30px;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block">
                        <p class="mb-0 fs-6 fw-semibold text-dark"><?= htmlspecialchars($nama_tutor); ?></p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($specialty); ?></p>
                    </div>
                    <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px; object-fit: cover;" src="https://ui-avatars.com/api/?name=<?= urlencode($nama_tutor); ?>&background=0D8ABC&color=fff"/>
                </div>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="mb-4 d-flex justify-content-between align-items-end">
                <div>
                    <h2 class="fw-bolder text-dark tracking-tight mb-1">Welcome back, <?= htmlspecialchars($nama_tutor); ?>!</h2>
                    <p class="text-muted mb-0">Here is what's happening with your classes today.</p>
                </div>
                <div class="bg-white border rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined text-primary fs-5">class</span>
                    <span class="fw-bold text-dark"><?= $total_kelas ?></span> <span class="text-muted small">Total Kelas Aktif</span>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card rounded-4 shadow-sm shortcut-card h-100 bg-white">
                        <div class="card-body p-4">
                            <div class="icon-box bg-primary-soft text-primary mb-3"><span class="material-symbols-outlined fs-3">dashboard</span></div>
                            <h5 class="fw-bold text-dark mb-1">Dashboard</h5>
                            <p class="text-muted small mb-0">Activity overview</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card rounded-4 shadow-sm shortcut-card h-100 bg-white">
                        <div class="card-body p-4">
                            <div class="icon-box bg-success-subtle text-success mb-3"><span class="material-symbols-outlined fs-3">how_to_reg</span></div>
                            <h5 class="fw-bold text-dark mb-1">Attendance</h5>
                            <p class="text-muted small mb-0">Track student presence</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card rounded-4 shadow-sm shortcut-card h-100 bg-white">
                        <div class="card-body p-4">
                            <div class="icon-box bg-warning-subtle text-warning mb-3"><span class="material-symbols-outlined fs-3">menu_book</span></div>
                            <h5 class="fw-bold text-dark mb-1">Learning Notes</h5>
                            <p class="text-muted small mb-0">Lesson materials</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card rounded-4 shadow-sm shortcut-card h-100 bg-white">
                        <div class="card-body p-4">
                            <div class="icon-box text-primary mb-3" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1 !important;"><span class="material-symbols-outlined fs-3">analytics</span></div>
                            <h5 class="fw-bold text-dark mb-1">Grades</h5>
                            <p class="text-muted small mb-0">View and edit scores</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary">calendar_month</span> Jadwal Mengajar Saya
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3 border-0">ID KELAS</th>
                                <th class="px-4 py-3 border-0">NAMA KELAS & PROGRAM</th>
                                <th class="px-4 py-3 border-0">JADWAL & RUANG</th>
                                <th class="px-4 py-3 border-0 text-end">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php 
                            if(mysqli_num_rows($jadwal_tutor) == 0) {
                                echo '<tr><td colspan="4" class="text-center py-5 text-muted"><span class="material-symbols-outlined fs-1 d-block mb-2 opacity-50">event_busy</span> Belum ada jadwal kelas yang ditugaskan kepada Anda.</td></tr>';
                            }
                            while($row = mysqli_fetch_assoc($jadwal_tutor)) { 
                                $st_color = ($row['status'] == 'Ongoing' ? 'bg-success-subtle text-success' : ($row['status'] == 'Upcoming' ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary'));
                            ?>
                            <tr>
                                <td class="px-4 py-3 fw-medium text-secondary"><?= $row['class_id'] ?></td>
                                <td class="px-4 py-3">
                                    <span class="fw-bold text-dark d-block"><?= $row['class_name'] ?></span>
                                    <span class="text-muted small"><?= $row['course_program'] ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="d-block text-secondary small fw-medium">📅 <?= $row['schedule_day'] ?></span>
                                    <span class="text-muted small">📍 <?= $row['room_location'] ?></span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <span class="badge rounded-pill <?= $st_color ?> text-uppercase px-3" style="font-size: 0.65rem;"><?= $row['status'] ?></span>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>