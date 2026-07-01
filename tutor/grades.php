<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'tutor') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

$nama_tutor = isset($_SESSION['tutor_name']) ? $_SESSION['tutor_name'] : 'Tutor Linggis';

// --- LOGIKA SIMPAN NILAI (SUPPORT AUTO-UPDATE / EDIT) ---
if (isset($_POST['simpan_nilai'])) {
    $class_name = $_POST['class_name'];
    
    if (isset($_POST['student_name'])) {
        foreach ($_POST['student_name'] as $nama_siswa) {
            $g = $_POST['grammar'][$nama_siswa] != '' ? $_POST['grammar'][$nama_siswa] : 0;
            $s = $_POST['speaking'][$nama_siswa] != '' ? $_POST['speaking'][$nama_siswa] : 0;
            $w = $_POST['writing'][$nama_siswa] != '' ? $_POST['writing'][$nama_siswa] : 0;
            $r = $_POST['reading'][$nama_siswa] != '' ? $_POST['reading'][$nama_siswa] : 0;
            
            // Cek apakah data sudah ada
            $cek = mysqli_query($koneksi, "SELECT id FROM grades WHERE class_name='$class_name' AND student_name='$nama_siswa'");
            
            if (mysqli_num_rows($cek) > 0) {
                // JIKA ADA -> UPDATE (Ini fungsi EDIT-nya)
                mysqli_query($koneksi, "UPDATE grades SET grammar='$g', speaking='$s', writing='$w', reading='$r', input_by='$nama_tutor' 
                                        WHERE class_name='$class_name' AND student_name='$nama_siswa'");
            } else {
                // JIKA TIDAK ADA -> INSERT BARU
                mysqli_query($koneksi, "INSERT INTO grades (class_name, student_name, grammar, speaking, writing, reading, input_by) 
                                        VALUES ('$class_name', '$nama_siswa', '$g', '$s', '$w', '$r', '$nama_tutor')");
            }
        }
        $pesan_sukses = "Data nilai berhasil disimpan dan diperbarui!";
    }
}

// Ambil kelas milik Tutor ini (Pakai TRIM biar kebal Spasi Gaib)
$q_kelas = mysqli_query($koneksi, "SELECT * FROM classes WHERE TRIM(teacher_name) = TRIM('$nama_tutor') ORDER BY class_name ASC");

$kelas_terpilih = isset($_GET['pilih_kelas']) ? $_GET['pilih_kelas'] : "";
$data_siswa = [];

// Jika kelas dipilih, cari siswa yang sesuai
if ($kelas_terpilih != "") {
    $q_prog = mysqli_query($koneksi, "SELECT course_program FROM classes WHERE class_name = '$kelas_terpilih' LIMIT 1");
    if(mysqli_num_rows($q_prog) > 0) {
        $program = mysqli_fetch_assoc($q_prog)['course_program'];
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
    <title>Grades - Linggis Teacher</title>
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
        .input-nilai { width: 80px; text-align: center; font-weight: bold; border-radius: 0.5rem; }
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
                <div><h1 class="fs-5 fw-bold mb-0">Linggis</h1><p class="text-muted" style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0;">Teacher Portal</p></div>
            </div>

            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="dashboard_tutor.php"><span class="material-symbols-outlined">dashboard</span><span>Dashboard</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php"><span class="material-symbols-outlined">how_to_reg</span><span>Attendance</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="input_notes.php"><span class="material-symbols-outlined">menu_book</span><span>Learning Notes</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="grades.php"><span class="material-symbols-outlined">analytics</span><span>Grades</span></a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="#"><span class="material-symbols-outlined">settings</span><span>Settings</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Sign Out</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-end">
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
                <h2 class="fw-bolder text-dark tracking-tight mb-1">Student Grades</h2>
                <p class="text-muted">Masukkan atau perbarui nilai evaluasi siswa Anda di sini.</p>
            </div>

            <?php if(isset($pesan_sukses)) { ?>
                <div class="alert alert-success border-0 shadow-sm rounded-3 py-3 d-flex align-items-center gap-2 mb-4">
                    <span class="material-symbols-outlined">task_alt</span> <?= $pesan_sukses ?>
                </div>
            <?php } ?>

            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label small fw-bold text-secondary">Pilih Kelas yang Ingin Dinilai</label>
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
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-semibold">Buka Lembar Nilai</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($kelas_terpilih != "") { ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4">
                        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-primary">edit_document</span> 
                            Input Nilai: <?= htmlspecialchars($kelas_terpilih) ?>
                        </h5>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="class_name" value="<?= htmlspecialchars($kelas_terpilih) ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="px-4 py-3 border-0">NAMA SISWA</th>
                                        <th class="px-4 py-3 border-0 text-center">GRAMMAR</th>
                                        <th class="px-4 py-3 border-0 text-center">SPEAKING</th>
                                        <th class="px-4 py-3 border-0 text-center">WRITING</th>
                                        <th class="px-4 py-3 border-0 text-center">READING</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if(count($data_siswa) == 0) {
                                        echo '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada siswa di kelas ini.</td></tr>';
                                    }
                                    foreach($data_siswa as $nama) { 
                                        // Ambil nilai saat ini dari database jika sudah pernah diinput
                                        $q_nilai = mysqli_query($koneksi, "SELECT * FROM grades WHERE class_name='$kelas_terpilih' AND student_name='$nama'");
                                        $n = mysqli_fetch_assoc($q_nilai);
                                        
                                        $v_gram = $n['grammar'] ?? '';
                                        $v_speak = $n['speaking'] ?? '';
                                        $v_write = $n['writing'] ?? '';
                                        $v_read = $n['reading'] ?? '';
                                    ?>
                                    <tr>
                                        <td class="px-4 py-3 fw-bold text-dark">
                                            <?= htmlspecialchars($nama) ?>
                                            <input type="hidden" name="student_name[]" value="<?= htmlspecialchars($nama) ?>">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" name="grammar[<?= htmlspecialchars($nama) ?>]" class="form-control form-control-sm mx-auto input-nilai border-secondary" value="<?= $v_gram ?>" min="0" max="100" placeholder="0-100">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" name="speaking[<?= htmlspecialchars($nama) ?>]" class="form-control form-control-sm mx-auto input-nilai border-secondary" value="<?= $v_speak ?>" min="0" max="100" placeholder="0-100">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" name="writing[<?= htmlspecialchars($nama) ?>]" class="form-control form-control-sm mx-auto input-nilai border-secondary" value="<?= $v_write ?>" min="0" max="100" placeholder="0-100">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <input type="number" name="reading[<?= htmlspecialchars($nama) ?>]" class="form-control form-control-sm mx-auto input-nilai border-secondary" value="<?= $v_read ?>" min="0" max="100" placeholder="0-100">
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if(count($data_siswa) > 0) { ?>
                        <div class="card-footer bg-white border-top p-4 text-end">
                            <button type="submit" name="simpan_nilai" class="btn btn-primary fw-bold px-5 rounded-pill shadow-sm">
                                <span class="material-symbols-outlined align-middle fs-5 me-1">save</span> Simpan Nilai
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