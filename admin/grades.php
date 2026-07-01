<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['hapus_id'])) {
    $id_h = $_GET['hapus_id'];
    mysqli_query($koneksi, "DELETE FROM grades WHERE id = '$id_h'");
    header("Location: grades.php");
    exit();
}

// --- LOGIKA EDIT NILAI DARI ADMIN ---
if (isset($_POST['submit_edit'])) {
    $id_e = $_POST['id_db'];
    $g = $_POST['grammar'];
    $s = $_POST['speaking'];
    $w = $_POST['writing'];
    $r = $_POST['reading'];

    mysqli_query($koneksi, "UPDATE grades SET grammar='$g', speaking='$s', writing='$w', reading='$r' WHERE id='$id_e'");
    header("Location: grades.php");
    exit();
}

// --- PENCARIAN & FILTER ---
$kw = isset($_GET['search']) ? $_GET['search'] : "";
$f_kelas = isset($_GET['kelas']) ? $_GET['kelas'] : "";

$query = "SELECT * FROM grades WHERE 1=1";
if ($kw != "") { $query .= " AND student_name LIKE '%$kw%'"; }
if ($f_kelas != "") { $query .= " AND class_name = '$f_kelas'"; }
$query .= " ORDER BY class_name ASC, student_name ASC";

$result = mysqli_query($koneksi, $query);

// Dropdown filter kelas
$q_kelas = mysqli_query($koneksi, "SELECT DISTINCT class_name FROM grades ORDER BY class_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Grades & Progress - Linggis Admin</title>
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
        .score-box { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 0.5rem; padding: 0.25rem 0.5rem; display: inline-block; min-width: 45px; text-align: center; }
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
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php"><span class="material-symbols-outlined">how_to_reg</span><span>Attendance Record</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php"><span class="material-symbols-outlined">payments</span><span>Payments & Reports</span></a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="grades.php"><span class="material-symbols-outlined">assignment_turned_in</span><span>Grades & Progress</span></a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="admin_accounts.php"><span class="material-symbols-outlined">admin_panel_settings</span><span>Admin Accounts</span></a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="settings.php"><span class="material-symbols-outlined">settings</span><span>Settings</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../index.php"><span class="material-symbols-outlined">logout</span><span>Sign Out</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="position-relative search-wrapper" style="width: 350px;">
                <form method="GET" action="">
                    <span class="material-symbols-outlined" style="position: absolute; z-index: 10; padding-top: 8px;">search</span>
                    <input class="form-control bg-light border-0" name="search" placeholder="Cari nama siswa..." type="text" value="<?= htmlspecialchars($kw); ?>" />
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
                    <h2 class="fw-bolder text-dark mb-1">Grades & Progress</h2>
                    <p class="text-muted mb-0">Pantau rincian nilai siswa di 4 keahlian bahasa (Diinput oleh Tutor).</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <form method="GET" class="d-flex gap-2">
                        <?php if($kw != "") { ?><input type="hidden" name="search" value="<?= htmlspecialchars($kw); ?>"><?php } ?>
                        <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            <?php 
                            if($q_kelas) {
                                while($k = mysqli_fetch_assoc($q_kelas)) { 
                                    $sel = ($f_kelas == $k['class_name']) ? 'selected' : '';
                                    echo "<option value='".$k['class_name']."' $sel>".$k['class_name']."</option>";
                                } 
                            }
                            ?>
                        </select>
                        <?php if($f_kelas != "" || $kw != "") { ?>
                            <a href="grades.php" class="btn btn-sm btn-outline-danger d-flex align-items-center"><span class="material-symbols-outlined" style="font-size: 1rem;">close</span></a>
                        <?php } ?>
                    </form>
                    <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"><span class="material-symbols-outlined" style="font-size: 1.1rem;">download</span> Export Laporan</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3 border-0">NAMA SISWA</th>
                                <th class="px-4 py-3 border-0">KELAS & TUTOR</th>
                                <th class="px-4 py-3 border-0 text-center">DETAIL NILAI (SKILL)</th>
                                <th class="px-4 py-3 border-0 text-center">RATA-RATA</th>
                                <th class="px-4 py-3 border-0 text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php 
                            if(mysqli_num_rows($result) == 0) {
                                echo '<tr><td colspan="5" class="text-center py-5 text-muted">Belum ada data nilai yang diinput oleh Tutor.</td></tr>';
                            }
                            while($row = mysqli_fetch_assoc($result)) { 
                                // KALKULASI RATA-RATA OTOMATIS
                                $total = $row['grammar'] + $row['speaking'] + $row['writing'] + $row['reading'];
                                $rata_rata = $total / 4;
                                
                                // PENENTUAN HURUF MUTU OTOMATIS
                                $huruf = 'E'; $badge = 'bg-danger';
                                if($rata_rata >= 90) { $huruf = 'A'; $badge = 'bg-success'; }
                                elseif($rata_rata >= 80) { $huruf = 'B'; $badge = 'bg-primary'; }
                                elseif($rata_rata >= 70) { $huruf = 'C'; $badge = 'bg-warning text-dark'; }
                                elseif($rata_rata >= 60) { $huruf = 'D'; $badge = 'bg-secondary'; }
                            ?>
                            <tr>
                                <td class="px-4 py-3 fw-bold text-dark"><?= $row['student_name'] ?></td>
                                <td class="px-4 py-3">
                                    <span class="d-block text-secondary fw-medium" style="font-size: 0.85rem;"><?= $row['class_name'] ?></span>
                                    <span class="text-muted small">Tutor: <?= $row['input_by'] ?></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="d-flex justify-content-center gap-2" style="font-size: 0.75rem;">
                                        <div><span class="text-muted d-block" style="font-size: 0.65rem;">Gra</span><div class="score-box fw-bold text-primary"><?= $row['grammar'] ?></div></div>
                                        <div><span class="text-muted d-block" style="font-size: 0.65rem;">Spe</span><div class="score-box fw-bold text-success"><?= $row['speaking'] ?></div></div>
                                        <div><span class="text-muted d-block" style="font-size: 0.65rem;">Wri</span><div class="score-box fw-bold text-warning"><?= $row['writing'] ?></div></div>
                                        <div><span class="text-muted d-block" style="font-size: 0.65rem;">Rea</span><div class="score-box fw-bold text-info"><?= $row['reading'] ?></div></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="fs-5 fw-bolder me-2"><?= number_format($rata_rata, 1) ?></span>
                                    <span class="badge rounded-pill <?= $badge ?> px-2 fs-6"><?= $huruf ?></span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button class="btn btn-sm btn-light text-primary" data-bs-toggle="modal" data-bs-target="#editGrade<?= $row['id'] ?>"><span class="material-symbols-outlined" style="font-size: 1.1rem;">edit</span></button>
                                    <a href="?hapus_id=<?= $row['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Hapus data nilai ini?')"><span class="material-symbols-outlined" style="font-size: 1.1rem;">delete</span></a>
                                </td>
                            </tr>

                            <div class="modal fade" id="editGrade<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-bottom-0 pb-0"><h1 class="modal-title fs-6 fw-bold">Edit Nilai: <?= $row['student_name'] ?></h1><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body pt-3">
                                            <form method="POST">
                                                <input type="hidden" name="id_db" value="<?= $row['id'] ?>">
                                                <div class="mb-3"><label class="form-label small fw-medium">Grammar</label><input type="number" name="grammar" class="form-control" value="<?= $row['grammar'] ?>" min="0" max="100"></div>
                                                <div class="mb-3"><label class="form-label small fw-medium">Speaking</label><input type="number" name="speaking" class="form-control" value="<?= $row['speaking'] ?>" min="0" max="100"></div>
                                                <div class="mb-3"><label class="form-label small fw-medium">Writing</label><input type="number" name="writing" class="form-control" value="<?= $row['writing'] ?>" min="0" max="100"></div>
                                                <div class="mb-4"><label class="form-label small fw-medium">Reading</label><input type="number" name="reading" class="form-control" value="<?= $row['reading'] ?>" min="0" max="100"></div>
                                                <button type="submit" name="submit_edit" class="btn btn-primary w-100 fw-semibold">Update Nilai</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
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