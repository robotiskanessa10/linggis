<?php
// 1. Mulai sesi untuk mengambil nama login
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: ../login.php");
    exit();
}

// 2. Koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// 3. Hitung Total Siswa otomatis
$query_students = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM students");
$data_students = mysqli_fetch_assoc($query_students);
$total_students = $data_students['total'];

// 4. Hitung Total Guru (Teachers) otomatis
$query_teachers = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM teachers");
$data_teachers = mysqli_fetch_assoc($query_teachers);
$total_teachers = $data_teachers['total'];

// 5. Hitung Total Kelas (Classes) otomatis
$query_classes = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM classes");
$data_classes = mysqli_fetch_assoc($query_classes);
$total_classes = $data_classes['total'];

// 6. SINKRONISASI REVENUE (Hitung Total Pendapatan Lunas)
$query_revenue = mysqli_query($koneksi, "SELECT SUM(amount) as total FROM payments WHERE status = 'Paid'");
$data_revenue = mysqli_fetch_assoc($query_revenue);
$total_revenue = $data_revenue['total'] ?? 0;

// 7. Ambil 5 Pendaftaran Siswa Terbaru (untuk tabel)
$recent_enrollments = mysqli_query($koneksi, "SELECT * FROM students ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Overview - Linggis Admin</title>
    
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
        .icon-box { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 0.5rem; }
        .search-wrapper .material-symbols-outlined { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .search-wrapper input { padding-left: 40px; border-radius: 0.75rem; }
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
                    <h1 class="fs-5 fw-bold mb-0">Linggis Admin</h1>
                    <p class="text-muted" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0;">LMS Management</p>
                </div>
            </div>

            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="dashboard_admin.php">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Overview</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="students.php">
                    <span class="material-symbols-outlined">group</span>
                    <span>Students</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="teachers.php">
                    <span class="material-symbols-outlined">person_pin_circle</span>
                    <span>Teachers</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="manage_notes.php">
                  <span class="material-symbols-outlined">menu_book</span>
                  <span>Learning Notes</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="classes.php">
                    <span class="material-symbols-outlined">calendar_month</span>
                    <span>Classes & Schedule</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php">
                    <span class="material-symbols-outlined">how_to_reg</span><span>Attendance Record</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php">
                    <span class="material-symbols-outlined">payments</span>
                    <span>Payments & Reports</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php">
                    <span class="material-symbols-outlined">assignment_turned_in</span>
                    <span>Grades & Progress</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="admin_accounts.php">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                    <span>Admin Accounts</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="settings.php">
                <span class="material-symbols-outlined">settings</span>
                <span>Settings</span>
            </a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php">
                <span class="material-symbols-outlined">logout</span>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="position-relative search-wrapper" style="width: 350px;">
                <form action="students.php" method="GET">
                    <span class="material-symbols-outlined" style="position: absolute; z-index: 10; padding-top: 8px;">search</span>
                    <input class="form-control bg-light border-0" name="search" placeholder="Cari siswa..." type="text" />
                </form>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block">
                        <p class="mb-0 fs-6 fw-semibold text-dark">
                            <?= isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin User'; ?>
                        </p>
                        <p class="mb-0 text-muted" style="font-size: 0.75rem;">Super Administrator</p>
                    </div>
                    <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px; object-fit: cover;" alt="Profile avatar" 
                         src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin'); ?>&background=0D8ABC&color=fff"/>
                </div>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="mb-4">
                <h2 class="fw-bolder text-dark tracking-tight mb-1">Dashboard Overview</h2>
                <p class="text-muted">Monitor school performance and manage resources at a glance.</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-box bg-primary-soft text-primary"><span class="material-symbols-outlined">group</span></div>
                            </div>
                            <p class="text-muted fw-medium mb-1" style="font-size: 0.875rem;">Total Students</p>
                            <h3 class="fw-bold mb-0"><?= number_format($total_students); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-box" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;"><span class="material-symbols-outlined">person_pin_circle</span></div>
                            </div>
                            <p class="text-muted fw-medium mb-1" style="font-size: 0.875rem;">Active Teachers</p>
                            <h3 class="fw-bold mb-0"><?= number_format($total_teachers); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-box" style="background-color: rgba(253, 126, 20, 0.1); color: #fd7e14;"><span class="material-symbols-outlined">auto_stories</span></div>
                            </div>
                            <p class="text-muted fw-medium mb-1" style="font-size: 0.875rem;">Total Classes</p>
                            <h3 class="fw-bold mb-0"><?= number_format($total_classes); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="icon-box bg-success-subtle text-success"><span class="material-symbols-outlined">account_balance_wallet</span></div>
                            </div>
                            <p class="text-muted fw-medium mb-1" style="font-size: 0.875rem;">Total Revenue</p>
                            <h3 class="fw-bold mb-0 text-success">Rp <?= number_format($total_revenue, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Student Enrollments</h5>
                    <a href="students.php" class="btn btn-primary btn-sm fw-semibold px-3 text-decoration-none">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">Student Name</th>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">Course</th>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">Enrolled Date</th>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">Status</th>
                                <th class="px-4 py-3 text-uppercase fw-bold text-end border-0">Action</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php 
                            if(mysqli_num_rows($recent_enrollments) == 0) {
                                echo '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada siswa mendaftar.</td></tr>';
                            }
                            while($row = mysqli_fetch_assoc($recent_enrollments)) { 
                                $inisial = strtoupper(substr($row['full_name'], 0, 2));
                                $status_bg = ($row['status'] == 'Active') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
                            ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary-soft text-primary-custom d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            <?= $inisial; ?>
                                        </div>
                                        <span class="fw-medium text-dark"><?= $row['full_name']; ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-secondary" style="font-size: 0.875rem;"><?= $row['course']; ?></td>
                                <td class="px-4 py-3 text-secondary" style="font-size: 0.875rem;"><?= date('M d, Y', strtotime($row['enroll_date'])); ?></td>
                                <td class="px-4 py-3">
                                    <span class="badge rounded-pill <?= $status_bg; ?> text-uppercase" style="font-size: 0.65rem;">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <a href="students.php?search=<?= urlencode($row['full_name']); ?>" class="text-secondary"><span class="material-symbols-outlined">visibility</span></a>
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