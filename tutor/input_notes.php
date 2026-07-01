<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'tutor') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

$nama_tutor = isset($_SESSION['tutor_name']) ? $_SESSION['tutor_name'] : 'Tutor Linggis';

// --- LOGIKA SIMPAN LEARNING NOTES ---
if (isset($_POST['simpan_notes'])) {
    $student_name = mysqli_real_escape_string($koneksi, $_POST['student_name']);
    $session_date = mysqli_real_escape_string($koneksi, $_POST['session_date']);
    $session_number = mysqli_real_escape_string($koneksi, $_POST['session_number']);
    $topic = mysqli_real_escape_string($koneksi, $_POST['topic']);
    $notes = mysqli_real_escape_string($koneksi, $_POST['notes']);
    
    // Asumsi nama tabelnya learning_notes, silakan sesuaikan jika beda
    $query_insert = "INSERT INTO learning_notes (tutor_name, student_name, session_date, session_number, topic, notes) 
                     VALUES ('$nama_tutor', '$student_name', '$session_date', '$session_number', '$topic', '$notes')";
    
    if (mysqli_query($koneksi, $query_insert)) {
        $pesan_sukses = "Learning notes berhasil disimpan!";
    } else {
        $pesan_error = "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}

// Ambil daftar siswa yang diajar oleh tutor ini untuk dropdown
// Sesuaikan query ini dengan relasi tabel kamu jika diperlukan
$q_siswa = mysqli_query($koneksi, "SELECT DISTINCT s.full_name FROM students s 
                                   JOIN classes c ON s.course = c.course_program 
                                   WHERE TRIM(c.teacher_name) = TRIM('$nama_tutor') 
                                   ORDER BY s.full_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Learning Notes - Linggis Teacher</title>
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
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="input_notes.php"><span class="material-symbols-outlined">menu_book</span><span>Learning Notes</span></a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php"><span class="material-symbols-outlined">analytics</span><span>Grades</span></a>
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
                <h2 class="fw-bolder text-dark tracking-tight mb-1">Learning Notes</h2>
                <p class="text-muted">Catat perkembangan, materi, dan hasil evaluasi harian siswa Anda di sini.</p>
            </div>

            <?php if(isset($pesan_sukses)) { ?>
                <div class="alert alert-success border-0 shadow-sm rounded-3 py-3 d-flex align-items-center gap-2 mb-4">
                    <span class="material-symbols-outlined">task_alt</span> <?= $pesan_sukses ?>
                </div>
            <?php } ?>
            <?php if(isset($pesan_error)) { ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-3 py-3 d-flex align-items-center gap-2 mb-4">
                    <span class="material-symbols-outlined">error</span> <?= $pesan_error ?>
                </div>
            <?php } ?>

            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white" style="max-width: 800px;">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-primary">edit_document</span> 
                        Input Catatan Baru
                    </h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <form method="POST" action="">
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Nama Siswa</label>
                            <select name="student_name" class="form-select border-secondary" required>
                                <option value="" selected disabled>-- Pilih Siswa --</option>
                                <?php 
                                if($q_siswa && mysqli_num_rows($q_siswa) > 0) {
                                    while($s = mysqli_fetch_assoc($q_siswa)) {
                                        echo "<option value='".htmlspecialchars($s['full_name'])."'>".htmlspecialchars($s['full_name'])."</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>Belum ada siswa yang terdaftar di kelas Anda</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Tanggal Pertemuan</label>
                                <input type="date" name="session_date" class="form-control border-secondary" required>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <label class="form-label small fw-bold text-secondary">Pertemuan Ke-</label>
                                <input type="number" name="session_number" class="form-control border-secondary" min="1" placeholder="Misal: 5" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Materi / Topik Pembahasan</label>
                            <input type="text" name="topic" class="form-control border-secondary" placeholder="Contoh: Basic Grammar & Vocabulary" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Catatan Evaluasi</label>
                            <textarea name="notes" class="form-control border-secondary" rows="5" placeholder="Tuliskan perkembangan siswa, kesulitan yang dialami, dan saran..." required></textarea>
                        </div>

                        <div class="text-end mt-4 pt-2 border-top">
                            <button type="reset" class="btn btn-light fw-semibold px-4 me-2 rounded-pill">Reset</button>
                            <button type="submit" name="simpan_notes" class="btn btn-primary fw-bold px-5 rounded-pill shadow-sm">
                                <span class="material-symbols-outlined align-middle fs-5 me-1">save</span> Simpan Catatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>