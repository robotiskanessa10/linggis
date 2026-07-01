<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'tutor') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

$nama_tutor = isset($_SESSION['tutor_name']) ? $_SESSION['tutor_name'] : 'Tutor Linggis';

// --- LOGIKA SIMPAN ABSENSI ---
if (isset($_POST['simpan_absensi'])) {
    $class_name = $_POST['class_name'];
    $tanggal = $_POST['tanggal'];
    
    // Looping data siswa yang di-submit
    if (isset($_POST['status_hadir'])) {
        foreach ($_POST['status_hadir'] as $student_name => $status) {
            // Cek apakah hari ini anak tersebut sudah diabsen di kelas ini
            $cek = mysqli_query($koneksi, "SELECT id FROM attendance WHERE class_name='$class_name' AND student_name='$student_name' AND tanggal='$tanggal'");
            
            if (mysqli_num_rows($cek) > 0) {
                // Kalau sudah ada, update statusnya (jaga-jaga kalau tutor salah klik lalu ralat)
                mysqli_query($koneksi, "UPDATE attendance SET status_hadir='$status' WHERE class_name='$class_name' AND student_name='$student_name' AND tanggal='$tanggal'");
            } else {
                // Kalau belum ada, insert baru
                mysqli_query($koneksi, "INSERT INTO attendance (class_name, student_name, tanggal, status_hadir, input_by) 
                                        VALUES ('$class_name', '$student_name', '$tanggal', '$status', '$nama_tutor')");
            }
        }
        $pesan_sukses = "Absensi kelas $class_name tanggal " . date('d M Y', strtotime($tanggal)) . " berhasil disimpan!";
    }
}

// Ambil daftar kelas yang diajar oleh Tutor ini
$q_kelas = mysqli_query($koneksi, "SELECT * FROM classes WHERE teacher_name = '$nama_tutor' ORDER BY class_name ASC");

// Jika tutor memilih kelas dari dropdown
$kelas_terpilih = isset($_GET['pilih_kelas']) ? $_GET['pilih_kelas'] : "";
$tanggal_hari_ini = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

$data_siswa = [];
if ($kelas_terpilih != "") {
    // Cari program kursus dari kelas tersebut
    $q_prog = mysqli_query($koneksi, "SELECT course_program FROM classes WHERE class_name = '$kelas_terpilih' LIMIT 1");
    if(mysqli_num_rows($q_prog) > 0) {
        $program = mysqli_fetch_assoc($q_prog)['course_program'];
        // Ambil semua siswa yang mendaftar di program tersebut dan statusnya Active
        $q_siswa = mysqli_query($koneksi, "SELECT full_name FROM students WHERE course = '$program' AND status = 'Active' ORDER BY full_name ASC");
        while ($s = mysqli_fetch_assoc($q_siswa)) {
            $data_siswa[] = $s['full_name'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance - Linggis Teacher</title>
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
        .search-wrapper .material-symbols-outlined { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d; }
        .search-wrapper input { padding-left: 40px; border-radius: 0.75rem; }
        /* Custom Radio Button Styling */
        .radio-hadir:checked + label { background-color: #198754; color: white; border-color: #198754; }
        .radio-izin:checked + label { background-color: #ffc107; color: black; border-color: #ffc107; }
        .radio-sakit:checked + label { background-color: #0dcaf0; color: black; border-color: #0dcaf0; }
        .radio-alfa:checked + label { background-color: #dc3545; color: white; border-color: #dc3545; }
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
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="dashboard_tutor.php">
                    <span class="material-symbols-outlined">dashboard</span><span>Dashboard</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="attendance.php">
                    <span class="material-symbols-outlined">how_to_reg</span><span>Attendance</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="input_notes.php">
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
                <input class="form-control bg-light border-0" placeholder="Search students..." type="text" />
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= htmlspecialchars($nama_tutor); ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">English Tutor</p>
                </div>
                <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px;" src="https://ui-avatars.com/api/?name=<?= urlencode($nama_tutor); ?>&background=0D8ABC&color=fff"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="mb-4">
                <h2 class="fw-bolder text-dark tracking-tight mb-1">Class Attendance</h2>
                <p class="text-muted">Pilih kelas dan catat kehadiran siswa hari ini.</p>
            </div>

            <?php if(isset($pesan_sukses)) { ?>
                <div class="alert alert-success border-0 shadow-sm rounded-3 py-3 d-flex align-items-center gap-2 mb-4">
                    <span class="material-symbols-outlined">check_circle</span> <?= $pesan_sukses ?>
                </div>
            <?php } ?>

            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary">Pilih Kelas Anda</label>
                            <select name="pilih_kelas" class="form-select border-primary" required>
                                <option value="" selected disabled>-- Pilih Kelas --</option>
                                <?php 
                                mysqli_data_seek($q_kelas, 0);
                                while($k = mysqli_fetch_assoc($q_kelas)) { 
                                    $sel = ($kelas_terpilih == $k['class_name']) ? 'selected' : '';
                                    echo "<option value='".$k['class_name']."' $sel>".$k['class_name']." (".$k['course_program'].")</option>";
                                } 
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Tanggal Pertemuan</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= $tanggal_hari_ini ?>" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-semibold">Tampilkan Siswa</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($kelas_terpilih != "") { ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-primary">checklist</span> 
                            Daftar Hadir: <?= htmlspecialchars($kelas_terpilih) ?>
                        </h5>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="class_name" value="<?= htmlspecialchars($kelas_terpilih) ?>">
                        <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal_hari_ini) ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="px-4 py-3 border-0 w-25">NAMA SISWA</th>
                                        <th class="px-4 py-3 border-0 text-center">TANDAI KEHADIRAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if(count($data_siswa) == 0) {
                                        echo '<tr><td colspan="2" class="text-center py-4 text-muted">Belum ada siswa berstatus Active di kelas/program ini.</td></tr>';
                                    }
                                    foreach($data_siswa as $nama) { 
                                        // Cek apakah siswa ini sudah diabsen sebelumnya (untuk ngasih status otomatis kalau di-refresh)
                                        $q_cek = mysqli_query($koneksi, "SELECT status_hadir FROM attendance WHERE class_name='$kelas_terpilih' AND student_name='$nama' AND tanggal='$tanggal_hari_ini'");
                                        $status_saat_ini = (mysqli_num_rows($q_cek) > 0) ? mysqli_fetch_assoc($q_cek)['status_hadir'] : 'Hadir'; // Default 'Hadir'
                                        
                                        // Bikin ID unik untuk setiap radio button
                                        $id_h = "h_" . md5($nama);
                                        $id_i = "i_" . md5($nama);
                                        $id_s = "s_" . md5($nama);
                                        $id_a = "a_" . md5($nama);
                                    ?>
                                    <tr>
                                        <td class="px-4 py-3 fw-bold text-dark"><?= htmlspecialchars($nama) ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="btn-group shadow-sm" role="group">
                                                <input type="radio" class="btn-check radio-hadir" name="status_hadir[<?= htmlspecialchars($nama) ?>]" id="<?= $id_h ?>" value="Hadir" <?= ($status_saat_ini=='Hadir')?'checked':'' ?>>
                                                <label class="btn btn-outline-secondary btn-sm fw-medium px-3" for="<?= $id_h ?>">Hadir</label>

                                                <input type="radio" class="btn-check radio-izin" name="status_hadir[<?= htmlspecialchars($nama) ?>]" id="<?= $id_i ?>" value="Izin" <?= ($status_saat_ini=='Izin')?'checked':'' ?>>
                                                <label class="btn btn-outline-secondary btn-sm fw-medium px-3" for="<?= $id_i ?>">Izin</label>

                                                <input type="radio" class="btn-check radio-sakit" name="status_hadir[<?= htmlspecialchars($nama) ?>]" id="<?= $id_s ?>" value="Sakit" <?= ($status_saat_ini=='Sakit')?'checked':'' ?>>
                                                <label class="btn btn-outline-secondary btn-sm fw-medium px-3" for="<?= $id_s ?>">Sakit</label>

                                                <input type="radio" class="btn-check radio-alfa" name="status_hadir[<?= htmlspecialchars($nama) ?>]" id="<?= $id_a ?>" value="Alfa" <?= ($status_saat_ini=='Alfa')?'checked':'' ?>>
                                                <label class="btn btn-outline-secondary btn-sm fw-medium px-3" for="<?= $id_a ?>">Alfa</label>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if(count($data_siswa) > 0) { ?>
                        <div class="card-footer bg-white border-top p-4 text-end">
                            <button type="submit" name="simpan_absensi" class="btn btn-success fw-bold px-5 rounded-pill shadow-sm">
                                <span class="material-symbols-outlined align-middle fs-5 me-1">save</span> Simpan Absensi
                            </button>
                        </div>
                        <?php } ?>
                    </form>
                </div>
            <?php } ?>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>