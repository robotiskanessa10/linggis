<?php
// Wajib ada untuk mengambil nama dari login
session_start();

// 1. Membuka koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// --- LOGIKA TAMBAH DATA TUTOR ---
if (isset($_POST['submit_tambah'])) {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email_tutor'];
    $specialty = $_POST['spesialisasi'];
    
    // Bikin ID Tutor otomatis (TUT-001, dst)
    $query_last_id = "SELECT teacher_id FROM teachers ORDER BY id DESC LIMIT 1";
    $result_last = mysqli_query($koneksi, $query_last_id);
    
    if (mysqli_num_rows($result_last) > 0) {
        $row_last = mysqli_fetch_assoc($result_last);
        $last_num = (int) substr($row_last['teacher_id'], 4);
        $new_num = $last_num + 1;
    } else {
        $new_num = 1;
    }
    
    $id_tutor = 'TUT-' . str_pad($new_num, 3, '0', STR_PAD_LEFT);
    $tgl_gabung = date('Y-m-d');
    $status = 'Active';

    $query_insert = "INSERT INTO teachers (teacher_id, full_name, email, specialty, join_date, status) 
                     VALUES ('$id_tutor', '$nama', '$email', '$specialty', '$tgl_gabung', '$status')";
    
    if (mysqli_query($koneksi, $query_insert)) {
        header("Location: teachers.php");
        exit();
    } else {
        echo "Gagal menambah data: " . mysqli_error($koneksi);
    }
}

// --- LOGIKA HAPUS DATA TUTOR ---
if (isset($_GET['hapus_id'])) {
    $id_hapus = $_GET['hapus_id'];
    $query_hapus = "DELETE FROM teachers WHERE id = '$id_hapus'";
    
    if (mysqli_query($koneksi, $query_hapus)) {
        header("Location: teachers.php");
        exit();
    } else {
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }
}

// --- LOGIKA EDIT DATA TUTOR ---
if (isset($_POST['submit_edit'])) {
    $id_edit = $_POST['id_tutor'];
    $nama_edit = $_POST['nama_lengkap'];
    $email_edit = $_POST['email_tutor'];
    $specialty_edit = $_POST['spesialisasi'];
    $status_edit = $_POST['status_tutor']; 

    $query_update = "UPDATE teachers SET 
                        full_name = '$nama_edit', 
                        email = '$email_edit', 
                        specialty = '$specialty_edit', 
                        status = '$status_edit' 
                     WHERE id = '$id_edit'";
    
    if (mysqli_query($koneksi, $query_update)) {
        header("Location: teachers.php");
        exit();
    } else {
        echo "Gagal mengupdate data: " . mysqli_error($koneksi);
    }
}

// --- FITUR PENCARIAN & FILTER DATA ---
$keyword = isset($_GET['search']) ? $_GET['search'] : "";
$filter_specialty = isset($_GET['specialty']) ? $_GET['specialty'] : "";
$filter_status = isset($_GET['status']) ? $_GET['status'] : "";

$query = "SELECT * FROM teachers WHERE 1=1";

if ($keyword != "") {
    $query .= " AND (full_name LIKE '%$keyword%' OR teacher_id LIKE '%$keyword%')";
}
if ($filter_specialty != "") {
    $query .= " AND specialty = '$filter_specialty'";
}
if ($filter_status != "") {
    $query .= " AND status = '$filter_status'";
}

$query .= " ORDER BY id DESC";

// --- FITUR EXPORT CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Data_Tutor_Linggis.csv"');
    
    $output = fopen('php://output', 'w');
    // Sudah pakai pemisah titik koma (;) agar rapi di Excel
    fputcsv($output, array('ID Tutor', 'Nama Pengajar', 'Email', 'Spesialisasi', 'Tanggal Bergabung', 'Status'), ';');

    $result_export = mysqli_query($koneksi, $query);
    while($row = mysqli_fetch_assoc($result_export)) {
        fputcsv($output, array($row['teacher_id'], $row['full_name'], $row['email'], $row['specialty'], $row['join_date'], $row['status']), ';');
    }
    
    fclose($output);
    exit(); 
}

// Ambil data untuk ditampilkan di tabel HTML
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Teachers - Linggis Admin</title>
    
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
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Overview</span>
                </a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="students.php">
                    <span class="material-symbols-outlined">group</span>
                    <span>Students</span>
                </a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="teachers.php">
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
                <form action="" method="GET">
                    <?php if($filter_specialty != "") { ?><input type="hidden" name="specialty" value="<?= $filter_specialty; ?>"><?php } ?>
                    <?php if($filter_status != "") { ?><input type="hidden" name="status" value="<?= $filter_status; ?>"><?php } ?>
                    
                    <span class="material-symbols-outlined" style="position: absolute; z-index: 10; padding-top: 8px;">search</span>
                    <input class="form-control bg-light border-0" name="search" placeholder="Cari nama tutor atau ID..." type="text" value="<?= htmlspecialchars($keyword); ?>" />
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
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bolder text-dark tracking-tight mb-1">Data Pengajar</h2>
                    <p class="text-muted mb-0">Kelola informasi tutor dan spesialisasi mengajar.</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                    <span class="material-symbols-outlined fs-6">add</span> Tambah Tutor
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    
                    <form method="GET" action="" class="d-flex gap-2">
                        <?php if($keyword != "") { ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($keyword); ?>">
                        <?php } ?>

                        <select name="specialty" class="form-select form-select-sm fw-medium" style="width: auto;" onchange="this.form.submit()">
                            <option value="">Semua Spesialisasi</option>
                            <option value="General English" <?= ($filter_specialty == 'General English') ? 'selected' : ''; ?>>General English</option>
                            <option value="TOEFL Preparation" <?= ($filter_specialty == 'TOEFL Preparation') ? 'selected' : ''; ?>>TOEFL Preparation</option>
                            <option value="IELTS Preparation" <?= ($filter_specialty == 'IELTS Preparation') ? 'selected' : ''; ?>>IELTS Preparation</option>
                        </select>

                        <select name="status" class="form-select form-select-sm fw-medium" style="width: auto;" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Active" <?= ($filter_status == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?= ($filter_status == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </form>

                    <a href="?export=csv&search=<?= urlencode($keyword); ?>&specialty=<?= urlencode($filter_specialty); ?>&status=<?= urlencode($filter_status); ?>" class="btn btn-outline-secondary btn-sm fw-semibold d-flex align-items-center gap-1 text-decoration-none text-dark">
                        <span class="material-symbols-outlined" style="font-size: 1.1rem;">download</span> Export
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">ID Tutor</th>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">Nama Pengajar</th>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">Spesialisasi</th>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">Tgl Bergabung</th>
                                <th class="px-4 py-3 text-uppercase fw-bold border-0">Status</th>
                                <th class="px-4 py-3 text-uppercase fw-bold text-end border-0">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            
                            <?php 
                            if(mysqli_num_rows($result) == 0) {
                                echo '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data tutor yang ditemukan.</td></tr>';
                            }
                            
                            while($row = mysqli_fetch_assoc($result)) { 
                                $status_bg = ($row['status'] == 'Active') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
                                $inisial = strtoupper(substr($row['full_name'], 0, 2));
                            ?>
                            <tr>
                                <td class="px-4 py-3 text-secondary fw-medium" style="font-size: 0.875rem;">
                                    <?= $row['teacher_id']; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary-soft text-primary-custom d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            <?= $inisial; ?>
                                        </div>
                                        <div>
                                            <span class="fw-medium text-dark d-block"><?= $row['full_name']; ?></span>
                                            <span class="text-muted" style="font-size: 0.75rem;"><?= $row['email']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-secondary" style="font-size: 0.875rem;">
                                    <?= $row['specialty']; ?>
                                </td>
                                <td class="px-4 py-3 text-secondary" style="font-size: 0.875rem;">
                                    <?= date('d M Y', strtotime($row['join_date'])); ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge rounded-pill <?= $status_bg; ?> text-uppercase" style="font-size: 0.65rem;">
                                        <?= $row['status']; ?>
                                    </span>
                                </td>
                               <td class="px-4 py-3 text-end">
                                    <button type="button" class="btn btn-sm btn-light text-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>">
                                        <span class="material-symbols-outlined" style="font-size: 1.1rem;">edit</span>
                                    </button>
                                    <a href="?hapus_id=<?= $row['id']; ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Apakah kamu yakin ingin menghapus data <?= $row['full_name']; ?>?');">
                                        <span class="material-symbols-outlined" style="font-size: 1.1rem;">delete</span>
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                  <div class="modal-header border-bottom-0 pb-0">
                                    <h1 class="modal-title fs-5 fw-bold">Edit Data Tutor</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body pt-4">
                                    <form method="POST" action="">
                                      <input type="hidden" name="id_tutor" value="<?= $row['id']; ?>">
                                      
                                      <div class="mb-3">
                                        <label class="form-label fw-medium text-dark" style="font-size: 0.875rem;">Nama Lengkap</label>
                                        <input type="text" name="nama_lengkap" class="form-control" value="<?= $row['full_name']; ?>" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label fw-medium text-dark" style="font-size: 0.875rem;">Alamat Email</label>
                                        <input type="email" name="email_tutor" class="form-control" value="<?= $row['email']; ?>" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label fw-medium text-dark" style="font-size: 0.875rem;">Spesialisasi Utama</label>
                                        <select name="spesialisasi" class="form-select" required>
                                          <option value="General English" <?= ($row['specialty'] == 'General English') ? 'selected' : ''; ?>>General English</option>
                                          <option value="TOEFL Preparation" <?= ($row['specialty'] == 'TOEFL Preparation') ? 'selected' : ''; ?>>TOEFL Preparation</option>
                                          <option value="IELTS Preparation" <?= ($row['specialty'] == 'IELTS Preparation') ? 'selected' : ''; ?>>IELTS Preparation</option>
                                        </select>
                                      </div>
                                      <div class="mb-4">
                                        <label class="form-label fw-medium text-dark" style="font-size: 0.875rem;">Status</label>
                                        <select name="status_tutor" class="form-select" required>
                                          <option value="Active" <?= ($row['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                                          <option value="Inactive" <?= ($row['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                      </div>
                                      <div class="d-grid gap-2">
                                          <button type="submit" name="submit_edit" class="btn btn-primary fw-semibold py-2">Update Data Tutor</button>
                                      </div>
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

<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h1 class="modal-title fs-5 fw-bold" id="addTeacherModalLabel">Tambah Tutor Baru</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-4">
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label fw-medium text-dark" style="font-size: 0.875rem;">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama tutor" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium text-dark" style="font-size: 0.875rem;">Alamat Email</label>
            <input type="email" name="email_tutor" class="form-control" placeholder="tutor@linggis.com" required>
          </div>
          <div class="mb-4">
            <label class="form-label fw-medium text-dark" style="font-size: 0.875rem;">Spesialisasi Utama</label>
            <select name="spesialisasi" class="form-select" required>
              <option value="" selected disabled>Pilih spesialisasi...</option>
              <option value="General English">General English</option>
              <option value="TOEFL Preparation">TOEFL Preparation</option>
              <option value="IELTS Preparation">IELTS Preparation</option>
            </select>
          </div>
          <div class="d-grid gap-2">
              <button type="submit" name="submit_tambah" class="btn btn-primary fw-semibold py-2">Simpan Data Tutor</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>