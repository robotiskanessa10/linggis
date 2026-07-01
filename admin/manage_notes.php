<?php
session_start();

// Cek apakah yang login benar-benar Admin
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

$nama_admin = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Administrator';

// --- LOGIKA HAPUS CATATAN ---
if (isset($_GET['hapus_id'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus_id']);
    $query_hapus = mysqli_query($koneksi, "DELETE FROM learning_notes WHERE id = '$id_hapus'");
    
    if ($query_hapus) {
        echo "<script>alert('Catatan berhasil dihapus!'); window.location='manage_notes.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus catatan!'); window.location='manage_notes.php';</script>";
    }
}

// --- LOGIKA EDIT CATATAN ---
if (isset($_POST['edit_note'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_POST['id_note']);
    $student_name = mysqli_real_escape_string($koneksi, $_POST['student_name']);
    $tutor_name = mysqli_real_escape_string($koneksi, $_POST['tutor_name']);
    $topic = mysqli_real_escape_string($koneksi, $_POST['topic']);
    $notes = mysqli_real_escape_string($koneksi, $_POST['notes']);
    
    $query_edit = mysqli_query($koneksi, "UPDATE learning_notes SET student_name='$student_name', tutor_name='$tutor_name', topic='$topic', notes='$notes' WHERE id='$id_edit'");
    
    if ($query_edit) {
        echo "<script>alert('Catatan berhasil diperbarui!'); window.location='manage_notes.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui catatan!'); window.location='manage_notes.php';</script>";
    }
}

// --- LOGIKA PENCARIAN (SEARCH) ---
$search = "";
$kondisi_search = "";
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
    $kondisi_search = "WHERE student_name LIKE '%$search%' OR tutor_name LIKE '%$search%' OR topic LIKE '%$search%' OR notes LIKE '%$search%'";
}

// Ambil semua data notes berdasarkan pencarian (jika ada), urutkan dari yang terbaru
$q_notes = mysqli_query($koneksi, "SELECT * FROM learning_notes $kondisi_search ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Learning Notes - Admin Linggis</title>
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
        .table-custom th { font-size: 0.8rem; letter-spacing: 0.5px; text-transform: uppercase; }
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
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="dashboard_admin.php"><span class="material-symbols-outlined">dashboard</span><span>Overview</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="students.php"><span class="material-symbols-outlined">group</span><span>Students</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="teachers.php"><span class="material-symbols-outlined">person_pin_circle</span><span>Teachers</span></a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="manage_notes.php"><span class="material-symbols-outlined">menu_book</span><span>Learning Notes</span></a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="classes.php"><span class="material-symbols-outlined">calendar_month</span><span>Classes & Schedule</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php"><span class="material-symbols-outlined">how_to_reg</span><span>Attendance Record</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php"><span class="material-symbols-outlined">payments</span><span>Payments & Reports</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php"><span class="material-symbols-outlined">assignment_turned_in</span><span>Grades & Progress</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="admin_accounts.php"><span class="material-symbols-outlined">admin_panel_settings</span><span>Admin Accounts</span></a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="#"><span class="material-symbols-outlined">settings</span><span>Settings</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Sign Out</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-md-none me-2"><span class="material-symbols-outlined">menu</span></button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= htmlspecialchars($nama_admin); ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Administrator</p>
                </div>
                <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px;" src="https://ui-avatars.com/api/?name=<?= urlencode($nama_admin); ?>&background=0D8ABC&color=fff"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="fw-bolder text-dark tracking-tight mb-1">Learning Notes</h2>
                    <p class="text-muted mb-0">Pantau, cari, dan kelola catatan perkembangan belajar siswa dari para Tutor.</p>
                </div>
                
                <form method="GET" action="manage_notes.php" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><span class="material-symbols-outlined fs-5">search</span></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari tutor, siswa, materi..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary fw-semibold px-4 rounded">Cari</button>
                    <?php if($search != "") { ?>
                        <a href="manage_notes.php" class="btn btn-light border fw-semibold rounded">Reset</a>
                    <?php } ?>
                </form>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-custom align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="px-4 py-3 border-0">TANGGAL</th>
                                    <th class="px-4 py-3 border-0">TUTOR</th>
                                    <th class="px-4 py-3 border-0">SISWA</th>
                                    <th class="px-4 py-3 border-0">MATERI (SESI)</th>
                                    <th class="px-4 py-3 border-0 text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($q_notes) > 0) { 
                                    while($row = mysqli_fetch_assoc($q_notes)) { ?>
                                    <tr>
                                        <td class="px-4 py-3 text-muted fw-medium"><?= date('d M Y', strtotime($row['session_date'])) ?></td>
                                        <td class="px-4 py-3 fw-bold text-dark">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="material-symbols-outlined text-primary fs-5">person_play</span>
                                                <?= htmlspecialchars($row['tutor_name']) ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 fw-bold text-dark"><?= htmlspecialchars($row['student_name']) ?></td>
                                        <td class="px-4 py-3">
                                            <span class="badge bg-primary-soft text-primary px-2 py-1 rounded mb-1">Pertemuan <?= htmlspecialchars($row['session_number']) ?></span><br>
                                            <small class="text-muted fw-medium"><?= htmlspecialchars($row['topic']) ?></small>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id'] ?>" title="Baca Detail">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id'] ?>" title="Edit Data">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                                </button>
                                                <a href="manage_notes.php?hapus_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1" onclick="return confirm('Apakah Anda yakin ingin menghapus catatan ini?');" title="Hapus Data">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="modalDetail<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4">
                                                <div class="modal-header border-bottom-0 pb-0 mt-2 mx-2">
                                                    <h5 class="modal-title fw-bolder d-flex align-items-center gap-2 text-dark">
                                                        <span class="material-symbols-outlined text-primary">menu_book</span> Detail Catatan
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row mb-3 g-2">
                                                        <div class="col-6">
                                                            <label class="small text-muted d-block fw-semibold">Siswa:</label>
                                                            <span class="fw-bold text-dark"><?= htmlspecialchars($row['student_name']) ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="small text-muted d-block fw-semibold">Tutor:</label>
                                                            <span class="fw-bold text-dark"><?= htmlspecialchars($row['tutor_name']) ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="small text-muted d-block fw-semibold">Materi / Topik:</label>
                                                        <span class="badge bg-light text-dark border px-2 py-1 mt-1"><?= htmlspecialchars($row['topic']) ?></span>
                                                    </div>
                                                    <div>
                                                        <label class="small text-muted d-block fw-semibold mb-2">Evaluasi Belajar:</label>
                                                        <div class="p-3 bg-light border rounded-3 text-dark" style="font-size: 0.95rem; line-height: 1.6;">
                                                            <?= nl2br(htmlspecialchars($row['notes'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 pt-0 mx-2 mb-2">
                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="modalEdit<?= $row['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow rounded-4">
                                                <div class="modal-header border-bottom-0 pb-0 mt-2 mx-2">
                                                    <h5 class="modal-title fw-bolder d-flex align-items-center gap-2 text-dark">
                                                        <span class="material-symbols-outlined text-success">edit_square</span> Edit Catatan
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body p-4 text-start">
                                                        <input type="hidden" name="id_note" value="<?= $row['id'] ?>">
                                                        
                                                        <div class="row mb-3 g-3">
                                                            <div class="col-md-6">
                                                                <label class="small text-muted fw-semibold mb-1">Nama Siswa</label>
                                                                <input type="text" name="student_name" class="form-control" value="<?= htmlspecialchars($row['student_name']) ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="small text-muted fw-semibold mb-1">Nama Tutor</label>
                                                                <input type="text" name="tutor_name" class="form-control" value="<?= htmlspecialchars($row['tutor_name']) ?>" required>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="small text-muted fw-semibold mb-1">Materi / Topik</label>
                                                            <input type="text" name="topic" class="form-control" value="<?= htmlspecialchars($row['topic']) ?>" required>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="small text-muted fw-semibold mb-1">Evaluasi Belajar</label>
                                                            <textarea name="notes" class="form-control" rows="5" required><?= htmlspecialchars($row['notes']) ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top-0 pt-0 mx-2 mb-2">
                                                        <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="edit_note" class="btn btn-success rounded-pill px-4 fw-semibold">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                <?php } } else { ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted fw-medium">
                                            <span class="material-symbols-outlined d-block fs-1 mb-2 opacity-50">search_off</span>
                                            Tidak ada catatan yang ditemukan.
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>