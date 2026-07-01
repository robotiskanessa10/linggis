<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

// --- LOGIKA HAPUS ABSENSI (Opsional untuk Admin) ---
if (isset($_GET['hapus_id'])) {
    $id_h = $_GET['hapus_id'];
    mysqli_query($koneksi, "DELETE FROM attendance WHERE id = '$id_h'");
    header("Location: attendance.php");
    exit();
}

// --- FITUR PENCARIAN & FILTER ---
$kw = isset($_GET['search']) ? $_GET['search'] : "";
$f_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : "";
$f_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : "";

$query = "SELECT * FROM attendance WHERE 1=1";
if ($kw != "") { $query .= " AND (student_name LIKE '%$kw%' OR input_by LIKE '%$kw%')"; }
if ($f_kelas != "") { $query .= " AND class_name = '$f_kelas'"; }
if ($f_tanggal != "") { $query .= " AND tanggal = '$f_tanggal'"; }
$query .= " ORDER BY tanggal DESC, class_name ASC, student_name ASC";

$result = mysqli_query($koneksi, $query);

// Ambil daftar kelas untuk dropdown filter
$q_kelas = mysqli_query($koneksi, "SELECT class_name FROM classes ORDER BY class_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance Record - Linggis Admin</title>
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
                <div><h1 class="fs-5 fw-bold mb-0">Linggis Admin</h1><p class="text-muted" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0;">LMS Management</p></div>
            </div>

            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="dashboard_admin.php"><span class="material-symbols-outlined">dashboard</span><span>Overview</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="students.php"><span class="material-symbols-outlined">group</span><span>Students</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="teachers.php"><span class="material-symbols-outlined">person_pin_circle</span><span>Teachers</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="manage_notes.php">
                  <span class="material-symbols-outlined">menu_book</span>
                  <span>Learning Notes</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="classes.php"><span class="material-symbols-outlined">calendar_month</span><span>Classes & Schedule</span></a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="attendance.php"><span class="material-symbols-outlined">how_to_reg</span><span>Attendance Record</span></a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php"><span class="material-symbols-outlined">payments</span><span>Payments & Reports</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php"><span class="material-symbols-outlined">assignment_turned_in</span><span>Grades & Progress</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="admin_accounts.php"><span class="material-symbols-outlined">admin_panel_settings</span><span>Admin Accounts</span></a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="settings.php">
                <span class="material-symbols-outlined">settings</span><span>Settings</span>
            </a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php">
                <span class="material-symbols-outlined">logout</span><span>Sign Out</span>
            </a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="position-relative search-wrapper" style="width: 350px;">
                <form method="GET" action="">
                    <span class="material-symbols-outlined" style="position: absolute; z-index: 10; padding-top: 8px;">search</span>
                    <input class="form-control bg-light border-0" name="search" placeholder="Cari nama siswa atau tutor..." type="text" value="<?= htmlspecialchars($kw); ?>" />
                </form>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Super Administrator</p>
                </div>
                <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px; object-fit: cover;" src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin'); ?>&background=0D8ABC&color=fff"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bolder text-dark mb-1">Rekap Absensi Siswa</h2>
                    <p class="text-muted mb-0">Pantau kehadiran siswa dari seluruh kelas yang dilaporkan oleh Tutor.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <form method="GET" class="d-flex gap-2">
                        <?php if($kw != "") { ?><input type="hidden" name="search" value="<?= htmlspecialchars($kw); ?>"><?php } ?>
                        
                        <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            <?php 
                            mysqli_data_seek($q_kelas, 0);
                            while($k = mysqli_fetch_assoc($q_kelas)) { 
                                $sel = ($f_kelas == $k['class_name']) ? 'selected' : '';
                                echo "<option value='".$k['class_name']."' $sel>".$k['class_name']."</option>";
                            } 
                            ?>
                        </select>
                        
                        <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= htmlspecialchars($f_tanggal) ?>" onchange="this.form.submit()">
                        
                        <?php if($f_kelas != "" || $f_tanggal != "" || $kw != "") { ?>
                            <a href="attendance.php" class="btn btn-sm btn-outline-danger d-flex align-items-center"><span class="material-symbols-outlined" style="font-size: 1rem;">close</span></a>
                        <?php } ?>
                    </form>
                    <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"><span class="material-symbols-outlined" style="font-size: 1.1rem;">download</span> Export Data</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3 border-0">TANGGAL</th>
                                <th class="px-4 py-3 border-0">NAMA SISWA</th>
                                <th class="px-4 py-3 border-0">KELAS</th>
                                <th class="px-4 py-3 border-0">TUTOR PENGINPUT</th>
                                <th class="px-4 py-3 border-0">STATUS</th>
                                <th class="px-4 py-3 border-0 text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php 
                            if(mysqli_num_rows($result) == 0) {
                                echo '<tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data absensi yang sesuai kriteria.</td></tr>';
                            }
                            while($row = mysqli_fetch_assoc($result)) { 
                                // Pewarnaan Badge Status
                                $st_color = 'bg-secondary';
                                if($row['status_hadir'] == 'Hadir') $st_color = 'bg-success-subtle text-success';
                                if($row['status_hadir'] == 'Izin') $st_color = 'bg-warning-subtle text-warning';
                                if($row['status_hadir'] == 'Sakit') $st_color = 'bg-info-subtle text-info';
                                if($row['status_hadir'] == 'Alfa') $st_color = 'bg-danger-subtle text-danger';
                            ?>
                            <tr>
                                <td class="px-4 py-3 fw-medium text-secondary" style="font-size: 0.85rem;"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                <td class="px-4 py-3 fw-bold text-dark"><?= $row['student_name'] ?></td>
                                <td class="px-4 py-3 text-secondary" style="font-size: 0.85rem;"><?= $row['class_name'] ?></td>
                                <td class="px-4 py-3 text-secondary" style="font-size: 0.85rem;"><span class="material-symbols-outlined fs-6 align-middle me-1 opacity-50">person</span><?= $row['input_by'] ?></td>
                                <td class="px-4 py-3"><span class="badge rounded-pill <?= $st_color ?> px-3" style="font-size: 0.7rem;"><?= $row['status_hadir'] ?></span></td>
                                <td class="px-4 py-3 text-end">
                                    <a href="?hapus_id=<?= $row['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Hapus rekam absensi ini?')"><span class="material-symbols-outlined" style="font-size: 1.1rem;">delete</span></a>
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