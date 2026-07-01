<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: ../login.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// --- LOGIKA HAPUS DATA (DENGAN EFEK CASCADE MANUAL) ---
if (isset($_GET['hapus_id'])) {
    $id_h = $_GET['hapus_id'];

    // 1. Cari tahu dulu siapa nama siswa yang mau dihapus berdasarkan ID-nya
    $q_nama = mysqli_query($koneksi, "SELECT full_name FROM students WHERE id = '$id_h'");
    $data_siswa = mysqli_fetch_assoc($q_nama);
    
    if ($data_siswa) {
        $nama_siswa = $data_siswa['full_name'];

        // 2. CASCADE 1: Hapus semua "Uang Hantu" di tabel payments
        mysqli_query($koneksi, "DELETE FROM payments WHERE student_name = '$nama_siswa'");

        // 3. CASCADE 2: Hapus semua "Nilai/Rapor" di tabel grades
        mysqli_query($koneksi, "DELETE FROM grades WHERE student_name = '$nama_siswa'");

        // 4. AKHIR: Baru hapus fisik data siswanya dari tabel students
        mysqli_query($koneksi, "DELETE FROM students WHERE id = '$id_h'");
    }

    header("Location: students.php");
    exit();
}

// --- LOGIKA EDIT DATA ---
if (isset($_POST['submit_edit'])) {
    $id_e = $_POST['id_db'];
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $parent_email = $_POST['parent_email']; // Ambil data ortu
    $course = $_POST['course'];
    $status = $_POST['status_siswa'];

    $q_update = "UPDATE students SET full_name='$name', email='$email', parent_email='$parent_email', course='$course', status='$status' WHERE id='$id_e'";
    mysqli_query($koneksi, $q_update);
    header("Location: students.php");
    exit();
}

// --- LOGIKA TAMBAH DATA ---
if (isset($_POST['submit_tambah'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $parent_email = $_POST['parent_email']; // Ambil data ortu
    $course = $_POST['course'];
    $status = $_POST['status_siswa'];
    $date = date('Y-m-d');

    // Bikin ID otomatis (LNG-001)
    $query_last = "SELECT student_id FROM students ORDER BY id DESC LIMIT 1";
    $res_last = mysqli_query($koneksi, $query_last);
    if (mysqli_num_rows($res_last) > 0) {
        $row_last = mysqli_fetch_assoc($res_last);
        $last_num = (int) substr($row_last['student_id'], 4);
        $new_num = $last_num + 1;
    } else { $new_num = 1; }
    
    $id_siswa = 'LNG-' . str_pad($new_num, 3, '0', STR_PAD_LEFT);

    $q_insert = "INSERT INTO students (student_id, full_name, email, parent_email, course, enroll_date, status) 
                 VALUES ('$id_siswa', '$name', '$email', '$parent_email', '$course', '$date', '$status')";
    mysqli_query($koneksi, $q_insert);
    header("Location: students.php");
    exit();
}

// --- FITUR PENCARIAN & FILTER ---
$kw = isset($_GET['search']) ? $_GET['search'] : "";
$f_course = isset($_GET['course']) ? $_GET['course'] : "";
$f_stat = isset($_GET['status']) ? $_GET['status'] : "";

$query = "SELECT * FROM students WHERE 1=1";
// Pencarian sekarang bisa mencari nama anak, ID, atau email ortu
if ($kw != "") { $query .= " AND (full_name LIKE '%$kw%' OR student_id LIKE '%$kw%' OR parent_email LIKE '%$kw%')"; }
if ($f_course != "") { $query .= " AND course = '$f_course'"; }
if ($f_stat != "") { $query .= " AND status = '$f_stat'"; }
$query .= " ORDER BY id DESC";

$result = mysqli_query($koneksi, $query);

// --- EXPORT CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    if (ob_get_length()) ob_clean(); 
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Data_Siswa_Linggis.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); 
    fputcsv($out, array('ID SISWA', 'NAMA LENGKAP', 'EMAIL SISWA', 'EMAIL ORTU', 'PROGRAM', 'TANGGAL DAFTAR', 'STATUS'), ';');
    
    $res_exp = mysqli_query($koneksi, $query);
    while($r = mysqli_fetch_assoc($res_exp)) {
        fputcsv($out, array($r['student_id'], $r['full_name'], $r['email'], $r['parent_email'], $r['course'], $r['enroll_date'], $r['status']), ';');
    }
    fclose($out); exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Students - Linggis Admin</title>
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
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="students.php">
                    <span class="material-symbols-outlined">group</span><span>Students</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="teachers.php">
                    <span class="material-symbols-outlined">person_pin_circle</span><span>Teachers</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="manage_notes.php">
                  <span class="material-symbols-outlined">menu_book</span>
                  <span>Learning Notes</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="classes.php">
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
                    <input class="form-control bg-light border-0" name="search" placeholder="Cari nama, ID, atau Email Ortu..." type="text" value="<?= htmlspecialchars($kw); ?>" />
                </form>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin User'; ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Super Administrator</p>
                </div>
                <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px; object-fit: cover;" src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin'); ?>&background=0D8ABC&color=fff"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bolder text-dark mb-1">Data Siswa</h2>
                    <p class="text-muted mb-0">Kelola informasi, pendaftaran, dan status peserta kursus.</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <span class="material-symbols-outlined fs-6">add</span> Tambah Siswa
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <form method="GET" class="d-flex gap-2">
                        <?php if($kw != "") { ?><input type="hidden" name="search" value="<?= htmlspecialchars($kw); ?>"><?php } ?>
                        <select name="course" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Kursus</option>
                            <option value="General English" <?= ($f_course=='General English'?'selected':'') ?>>General English</option>
                            <option value="TOEFL Preparation" <?= ($f_course=='TOEFL Preparation'?'selected':'') ?>>TOEFL Preparation</option>
                            <option value="IELTS Preparation" <?= ($f_course=='IELTS Preparation'?'selected':'') ?>>IELTS Preparation</option>
                        </select>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Active" <?= ($f_stat=='Active'?'selected':'') ?>>Active</option>
                            <option value="Pending" <?= ($f_stat=='Pending'?'selected':'') ?>>Pending</option>
                            <option value="Graduated" <?= ($f_stat=='Graduated'?'selected':'') ?>>Graduated</option>
                        </select>
                    </form>
                    <a href="?export=csv&search=<?= urlencode($kw) ?>&course=<?= urlencode($f_course) ?>&status=<?= urlencode($f_stat) ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 1.1rem;">download</span> Export
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3">ID SISWA</th>
                                <th class="px-4 py-3">NAMA LENGKAP</th>
                                <th class="px-4 py-3">PROGRAM KURSUS</th>
                                <th class="px-4 py-3">TANGGAL DAFTAR</th>
                                <th class="px-4 py-3">STATUS</th>
                                <th class="px-4 py-3 text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($result) == 0) {
                                echo '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data siswa.</td></tr>';
                            }
                            while($row = mysqli_fetch_assoc($result)) { 
                                $inisial = strtoupper(substr($row['full_name'], 0, 2));
                                $st_color = ($row['status'] == 'Active' ? 'bg-success-subtle text-success' : ($row['status'] == 'Pending' ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary'));
                            ?>
                            <tr>
                                <td class="px-4 py-3 fw-medium text-secondary"><?= $row['student_id'] ?></td>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary-soft text-primary-custom d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                            <?= $inisial ?>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= $row['full_name'] ?></span>
                                            <?php if(!empty($row['email'])) { ?>
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Siswa: <?= $row['email'] ?></small>
                                            <?php } ?>
                                            <?php if(!empty($row['parent_email'])) { ?>
                                                <small class="text-primary d-block" style="font-size: 0.7rem;">Ortu: <?= $row['parent_email'] ?></small>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-secondary" style="font-size: 0.875rem;"><?= $row['course'] ?></td>
                                <td class="px-4 py-3 text-secondary" style="font-size: 0.875rem;"><?= date('d M Y', strtotime($row['enroll_date'])) ?></td>
                                <td class="px-4 py-3"><span class="badge rounded-pill <?= $st_color ?> text-uppercase" style="font-size: 0.65rem;"><?= $row['status'] ?></span></td>
                                <td class="px-4 py-3 text-end">
                                    <button class="btn btn-sm btn-light text-primary" data-bs-toggle="modal" data-bs-target="#editStudent<?= $row['id'] ?>"><span class="material-symbols-outlined" style="font-size: 1.1rem;">edit</span></button>
                                    <a href="?hapus_id=<?= $row['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Hapus data siswa ini?')"><span class="material-symbols-outlined" style="font-size: 1.1rem;">delete</span></a>
                                </td>
                            </tr>

                            <div class="modal fade" id="editStudent<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-bottom-0 pb-0"><h1 class="modal-title fs-5 fw-bold">Edit Siswa</h1><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body pt-4">
                                            <form method="POST">
                                                <input type="hidden" name="id_db" value="<?= $row['id'] ?>">
                                                <div class="mb-3"><label class="form-label small fw-medium">Nama Lengkap</label><input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($row['full_name']) ?>" required></div>
                                                <div class="row mb-3">
                                                    <div class="col-6"><label class="form-label small fw-medium">Email Siswa</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email'] ?? '') ?>"></div>
                                                    <div class="col-6"><label class="form-label small fw-medium text-primary">Email Ortu (Login)</label><input type="email" name="parent_email" class="form-control border-primary" value="<?= htmlspecialchars($row['parent_email'] ?? '') ?>"></div>
                                                </div>
                                                <div class="mb-3"><label class="form-label small fw-medium">Program Kursus</label><select name="course" class="form-select"><option value="General English" <?=($row['course']=='General English'?'selected':'')?>>General English</option><option value="TOEFL Preparation" <?=($row['course']=='TOEFL Preparation'?'selected':'')?>>TOEFL Preparation</option><option value="IELTS Preparation" <?=($row['course']=='IELTS Preparation'?'selected':'')?>>IELTS Preparation</option></select></div>
                                                <div class="mb-4"><label class="form-label small fw-medium">Status</label><select name="status_siswa" class="form-select"><option value="Active" <?=($row['status']=='Active'?'selected':'')?>>Active</option><option value="Pending" <?=($row['status']=='Pending'?'selected':'')?>>Pending</option><option value="Graduated" <?=($row['status']=='Graduated'?'selected':'')?>>Graduated</option></select></div>
                                                <button type="submit" name="submit_edit" class="btn btn-primary w-100 fw-semibold">Update Siswa</button>
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

<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0"><h1 class="modal-title fs-5 fw-bold">Tambah Siswa Baru</h1><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body pt-4">
                <form method="POST">
                    <div class="mb-3"><label class="form-label small fw-medium">Nama Lengkap Siswa</label><input type="text" name="full_name" class="form-control" required></div>
                    <div class="row mb-3">
                        <div class="col-6"><label class="form-label small fw-medium">Email Siswa</label><input type="email" name="email" class="form-control" placeholder="Opsional"></div>
                        <div class="col-6"><label class="form-label small fw-medium text-primary">Email Orang Tua</label><input type="email" name="parent_email" class="form-control border-primary" placeholder="ortu@email.com" required></div>
                    </div>
                    <div class="mb-3"><label class="form-label small fw-medium">Program Kursus</label><select name="course" class="form-select" required><option value="" selected disabled>Pilih...</option><option value="General English">General English</option><option value="TOEFL Preparation">TOEFL Preparation</option><option value="IELTS Preparation">IELTS Preparation</option></select></div>
                    <div class="mb-4"><label class="form-label small fw-medium">Status</label><select name="status_siswa" class="form-select"><option value="Active">Active</option><option value="Pending" selected>Pending</option></select></div>
                    <button type="submit" name="submit_tambah" class="btn btn-primary w-100 fw-semibold">Simpan Siswa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>