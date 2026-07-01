<?php
session_start();

// Cek login (Path sudah disesuaikan karena ada di folder admin)
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: ../login.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// --- LOGIKA TAMBAH DATA KELAS ---
if (isset($_POST['submit_tambah'])) {
    $c_name = $_POST['class_name'];
    $program = $_POST['course_program'];
    $t_name = $_POST['teacher_name']; 
    $schedule = $_POST['schedule_day'];
    $room = $_POST['room_location'];
    
    $query_last = "SELECT class_id FROM classes ORDER BY id DESC LIMIT 1";
    $res_last = mysqli_query($koneksi, $query_last);
    if (mysqli_num_rows($res_last) > 0) {
        $row_last = mysqli_fetch_assoc($res_last);
        $last_num = (int) substr($row_last['class_id'], 4);
        $new_num = $last_num + 1;
    } else { $new_num = 1; }
    
    $id_kelas = 'CLS-' . str_pad($new_num, 3, '0', STR_PAD_LEFT);
    $status = 'Upcoming';

    $q_insert = "INSERT INTO classes (class_id, class_name, course_program, teacher_name, schedule_day, room_location, status) 
                 VALUES ('$id_kelas', '$c_name', '$program', '$t_name', '$schedule', '$room', '$status')";
    
    if (mysqli_query($koneksi, $q_insert)) {
        header("Location: classes.php");
        exit();
    }
}

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['hapus_id'])) {
    $id_h = $_GET['hapus_id'];
    mysqli_query($koneksi, "DELETE FROM classes WHERE id = '$id_h'");
    header("Location: classes.php");
    exit();
}

// --- LOGIKA EDIT DATA ---
if (isset($_POST['submit_edit'])) {
    $id_e = $_POST['id_db'];
    $c_name = $_POST['class_name'];
    $program = $_POST['course_program'];
    $t_name = $_POST['teacher_name']; 
    $schedule = $_POST['schedule_day'];
    $room = $_POST['room_location'];
    $status = $_POST['status_kelas'];

    $q_update = "UPDATE classes SET class_name='$c_name', course_program='$program', teacher_name='$t_name', 
                 schedule_day='$schedule', room_location='$room', status='$status' WHERE id='$id_e'";
    mysqli_query($koneksi, $q_update);
    header("Location: classes.php");
    exit();
}

// --- AMBIL DATA TUTOR DARI DATABASE (NAMA KOLOM SUDAH DIGANTI JADI specialty) ---
$q_tutor = mysqli_query($koneksi, "SELECT full_name, specialty FROM teachers WHERE status = 'Active' ORDER BY full_name ASC");
$data_tutor = [];
while($t = mysqli_fetch_assoc($q_tutor)) {
    $data_tutor[] = $t; 
}

// --- FITUR PENCARIAN & FILTER ---
$kw = isset($_GET['search']) ? $_GET['search'] : "";
$f_prog = isset($_GET['program']) ? $_GET['program'] : "";
$f_stat = isset($_GET['status']) ? $_GET['status'] : "";

$query = "SELECT * FROM classes WHERE 1=1";
if ($kw != "") { $query .= " AND (class_name LIKE '%$kw%' OR class_id LIKE '%$kw%')"; }
if ($f_prog != "") { $query .= " AND course_program = '$f_prog'"; }
if ($f_stat != "") { $query .= " AND status = '$f_stat'"; }
$query .= " ORDER BY id DESC";

// --- EXPORT CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    if (ob_get_length()) ob_clean(); 
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Jadwal_Kelas_Linggis.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); 
    fputcsv($out, array('ID KELAS', 'NAMA KELAS', 'PROGRAM', 'TUTOR', 'JADWAL', 'RUANG/LOKASI', 'STATUS'), ';');
    $res_exp = mysqli_query($koneksi, $query);
    while($r = mysqli_fetch_assoc($res_exp)) {
        fputcsv($out, array($r['class_id'], $r['class_name'], $r['course_program'], $r['teacher_name'], $r['schedule_day'], $r['room_location'], $r['status']), ';');
    }
    fclose($out); 
    exit();
}

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Classes & Schedule - Linggis Admin</title>
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
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Linggis Admin</h1>
                    <p class="text-muted" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0;">LMS Management</p>
                </div>
            </div>
            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="dashboard_admin.php">
                    <span class="material-symbols-outlined">dashboard</span><span>Overview</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="students.php">
                    <span class="material-symbols-outlined">group</span><span>Students</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="teachers.php">
                    <span class="material-symbols-outlined">person_pin_circle</span><span>Teachers</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="manage_notes.php">
                  <span class="material-symbols-outlined">menu_book</span>
                  <span>Learning Notes</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="classes.php">
                    <span class="material-symbols-outlined">calendar_month</span><span>Classes & Schedule</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php">
                    <span class="material-symbols-outlined">how_to_reg</span><span>Attendance Record</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php">
                    <span class="material-symbols-outlined">payments</span><span>Payments & Reports</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php">
                    <span class="material-symbols-outlined">assignment_turned_in</span><span>Grades & Progress</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="admin_accounts.php">
                    <span class="material-symbols-outlined">admin_panel_settings</span><span>Admin Accounts</span>
                </a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="settings.php"><span class="material-symbols-outlined">settings</span><span>Settings</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Sign Out</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="position-relative search-wrapper" style="width: 350px;">
                <form method="GET" action="">
                    <span class="material-symbols-outlined" style="position: absolute; z-index: 10; padding-top: 8px;">search</span>
                    <input class="form-control bg-light border-0" name="search" placeholder="Cari nama kelas..." type="text" value="<?= htmlspecialchars($kw); ?>" />
                </form>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark">
                        <?= isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin User'; ?>
                    </p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Super Administrator</p>
                </div>
                <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px; object-fit: cover;" src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin'); ?>&background=0D8ABC&color=fff"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bolder text-dark mb-1">Data Kelas & Jadwal</h2>
                    <p class="text-muted mb-0">Kelola daftar kelas dan jadwal tutor.</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addClassModal">
                    <span class="material-symbols-outlined fs-6">add</span> Tambah Kelas
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <form method="GET" class="d-flex gap-2">
                        <select name="program" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Program</option>
                            <option value="General English" <?= ($f_prog=='General English'?'selected':'') ?>>General English</option>
                            <option value="TOEFL Preparation" <?= ($f_prog=='TOEFL Preparation'?'selected':'') ?>>TOEFL Preparation</option>
                            <option value="IELTS Preparation" <?= ($f_prog=='IELTS Preparation'?'selected':'') ?>>IELTS Preparation</option>
                        </select>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Status</option>
                            <option value="Ongoing" <?= ($f_stat=='Ongoing'?'selected':'') ?>>Ongoing</option>
                            <option value="Upcoming" <?= ($f_stat=='Upcoming'?'selected':'') ?>>Upcoming</option>
                            <option value="Completed" <?= ($f_stat=='Completed'?'selected':'') ?>>Completed</option>
                        </select>
                    </form>
                    <a href="?export=csv&search=<?= urlencode($kw) ?>&program=<?= urlencode($f_prog) ?>&status=<?= urlencode($f_stat) ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 1.1rem;">download</span> Export
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3">ID KELAS</th>
                                <th class="px-4 py-3">NAMA KELAS & PROGRAM</th>
                                <th class="px-4 py-3">PENGAJAR</th>
                                <th class="px-4 py-3">JADWAL & RUANG</th>
                                <th class="px-4 py-3">STATUS</th>
                                <th class="px-4 py-3 text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($result) == 0) {
                                echo '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data kelas.</td></tr>';
                            }
                            while($row = mysqli_fetch_assoc($result)) { 
                                $st_color = ($row['status'] == 'Ongoing' ? 'bg-success-subtle text-success' : ($row['status'] == 'Upcoming' ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary'));
                            ?>
                            <tr>
                                <td class="px-4 py-3 fw-medium text-secondary"><?= $row['class_id'] ?></td>
                                <td class="px-4 py-3">
                                    <span class="fw-medium text-dark d-block"><?= $row['class_name'] ?></span>
                                    <small class="text-muted"><?= $row['course_program'] ?></small>
                                </td>
                                <td class="px-4 py-3 text-primary fw-medium"><?= $row['teacher_name'] ?></td>
                                <td class="px-4 py-3">
                                    <span class="d-block text-secondary small fw-medium"><?= $row['schedule_day'] ?></span>
                                    <small class="text-muted"><?= $row['room_location'] ?></small>
                                </td>
                                <td class="px-4 py-3"><span class="badge rounded-pill <?= $st_color ?> text-uppercase" style="font-size: 0.65rem;"><?= $row['status'] ?></span></td>
                                <td class="px-4 py-3 text-end">
                                    <button class="btn btn-sm btn-light text-primary" data-bs-toggle="modal" data-bs-target="#editClass<?= $row['id'] ?>"><span class="material-symbols-outlined" style="font-size: 1.1rem;">edit</span></button>
                                    <a href="?hapus_id=<?= $row['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Hapus kelas ini?')"><span class="material-symbols-outlined" style="font-size: 1.1rem;">delete</span></a>
                                </td>
                            </tr>

                            <div class="modal fade" id="editClass<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-bottom-0 pb-0"><h1 class="modal-title fs-5 fw-bold">Edit Kelas</h1><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body pt-4">
                                            <form method="POST">
                                                <input type="hidden" name="id_db" value="<?= $row['id'] ?>">
                                                <div class="mb-3"><label class="form-label small fw-medium">Nama Kelas</label><input type="text" name="class_name" class="form-control" value="<?= htmlspecialchars($row['class_name']) ?>" required></div>
                                                
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-medium">Program</label>
                                                        <select name="course_program" class="form-select">
                                                            <option value="General English" <?=($row['course_program']=='General English'?'selected':'')?>>General English</option>
                                                            <option value="TOEFL Preparation" <?=($row['course_program']=='TOEFL Preparation'?'selected':'')?>>TOEFL Preparation</option>
                                                            <option value="IELTS Preparation" <?=($row['course_program']=='IELTS Preparation'?'selected':'')?>>IELTS Preparation</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col-6">
                                                        <label class="form-label small fw-medium">Tutor Pengajar</label>
                                                        <select name="teacher_name" class="form-select border-primary" required>
                                                            <option value="" disabled>Pilih Tutor...</option>
                                                            <?php foreach($data_tutor as $t): ?>
                                                                <option value="<?= $t['full_name'] ?>" <?= ($row['teacher_name'] == $t['full_name']) ? 'selected' : '' ?>>
                                                                    <?= $t['full_name'] ?> (<?= $t['specialty'] ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-3"><label class="form-label small fw-medium">Jadwal</label><input type="text" name="schedule_day" class="form-control" value="<?= htmlspecialchars($row['schedule_day']) ?>"></div>
                                                <div class="mb-3"><label class="form-label small fw-medium">Ruang/Link</label><input type="text" name="room_location" class="form-control" value="<?= htmlspecialchars($row['room_location']) ?>"></div>
                                                <div class="mb-4"><label class="form-label small fw-medium">Status</label><select name="status_kelas" class="form-select"><option value="Ongoing" <?=($row['status']=='Ongoing'?'selected':'')?>>Ongoing</option><option value="Upcoming" <?=($row['status']=='Upcoming'?'selected':'')?>>Upcoming</option><option value="Completed" <?=($row['status']=='Completed'?'selected':'')?>>Completed</option></select></div>
                                                <button type="submit" name="submit_edit" class="btn btn-primary w-100 fw-semibold">Update Kelas</button>
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

<div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0"><h1 class="modal-title fs-5 fw-bold">Tambah Kelas Baru</h1><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body pt-4">
                <form method="POST">
                    <div class="mb-3"><label class="form-label small fw-medium">Nama Kelas</label><input type="text" name="class_name" class="form-control" placeholder="Contoh: TOEFL Intensive Batch 2" required></div>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-medium">Program</label>
                            <select name="course_program" class="form-select" required>
                                <option value="" selected disabled>Pilih...</option>
                                <option value="General English">General English</option>
                                <option value="TOEFL Preparation">TOEFL Preparation</option>
                                <option value="IELTS Preparation">IELTS Preparation</option>
                            </select>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label small fw-medium">Tutor Pengajar</label>
                            <select name="teacher_name" class="form-select border-primary" required>
                                <option value="" selected disabled>Pilih Tutor...</option>
                                <?php 
                                if (empty($data_tutor)) {
                                    echo '<option value="" disabled>Belum ada data tutor aktif!</option>';
                                } else {
                                    foreach($data_tutor as $t) {
                                        echo '<option value="'.$t['full_name'].'">'.$t['full_name'].' ('.$t['specialty'].')</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3"><label class="form-label small fw-medium">Jadwal</label><input type="text" name="schedule_day" class="form-control" placeholder="Senin & Rabu, 19:00 - 21:00"></div>
                    <div class="mb-4"><label class="form-label small fw-medium">Lokasi</label><input type="text" name="room_location" class="form-control" placeholder="Ruang A1 / Zoom Link"></div>
                    <button type="submit" name="submit_tambah" class="btn btn-primary w-100 fw-semibold">Simpan Kelas</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>