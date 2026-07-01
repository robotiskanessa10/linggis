<?php
// 1. Wajib mulai session untuk mengambil nama admin yang sedang login
session_start();

// Cek apakah sudah login, kalau belum tendang ke login.php
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: ../login.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// --- LOGIKA TAMBAH AKUN BARU ---
if (isset($_POST['submit_user'])) {
    $name = $_POST['full_name'];
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    $q_insert = "INSERT INTO users (username, password, full_name, role) VALUES ('$user', '$pass', '$name', '$role')";
    if (mysqli_query($koneksi, $q_insert)) {
        header("Location: admin_accounts.php");
        exit();
    }
}

// --- LOGIKA EDIT AKUN ---
if (isset($_POST['submit_edit'])) {
    $id_e = $_POST['id_db'];
    $name_e = $_POST['full_name'];
    $user_e = $_POST['username'];
    $pass_e = $_POST['password'];
    $role_e = $_POST['role'];

    $q_update = "UPDATE users SET full_name='$name_e', username='$user_e', password='$pass_e', role='$role_e' WHERE id='$id_e'";
    if (mysqli_query($koneksi, $q_update)) {
        header("Location: admin_accounts.php");
        exit();
    }
}

// --- LOGIKA HAPUS AKUN ---
if (isset($_GET['hapus_id'])) {
    $id = $_GET['hapus_id'];
    mysqli_query($koneksi, "DELETE FROM users WHERE id = '$id'");
    header("Location: admin_accounts.php");
    exit();
}

// --- LOGIKA PENCARIAN & FILTER ROLE ---
$kw = isset($_GET['search']) ? $_GET['search'] : "";
$f_role = isset($_GET['role']) ? $_GET['role'] : "";

$query = "SELECT * FROM users WHERE 1=1";
if ($kw != "") { 
    $query .= " AND (full_name LIKE '%$kw%' OR username LIKE '%$kw%')"; 
}
if ($f_role != "") { 
    $query .= " AND role = '$f_role'"; 
}
$query .= " ORDER BY role ASC, full_name ASC";
$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Accounts - Linggis Admin</title>
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
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="classes.php">
                    <span class="material-symbols-outlined">calendar_month</span><span>Classes & Schedule</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php">
                    <span class="material-symbols-outlined">how_to_reg</span><span>Attendance Record</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php">
                    <span class="material-symbols-outlined">payments</span><span>Payments & Reports</span>
                </a>
                        <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php">
                     <span class="material-symbols-outlined">assignment_turned_in</span>
                    <span>Grades & Progress</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="admin_accounts.php">
                    <span class="material-symbols-outlined">admin_panel_settings</span><span>Admin Accounts</span>
                </a>
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
                <form action="" method="GET">
                    <span class="material-symbols-outlined" style="position: absolute; z-index: 10; padding-top: 8px;">search</span>
                    <input class="form-control bg-light border-0" name="search" placeholder="Cari nama atau email..." type="text" value="<?= htmlspecialchars($kw); ?>" />
                    <?php if($f_role != '') { ?>
                        <input type="hidden" name="role" value="<?= htmlspecialchars($f_role); ?>">
                    <?php } ?>
                </form>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin'; ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Super Administrator</p>
                </div>
                <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px; object-fit: cover;" 
                     src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin'); ?>&background=0D8ABC&color=fff"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bolder text-dark mb-1">Admin Accounts</h2>
                    <p class="text-muted mb-0">Kelola akses masuk untuk Admin, Tutor, dan Orang Tua.</p>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <span class="material-symbols-outlined fs-6">person_add</span> Tambah Akun
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between">
                    
                    <form method="GET" action="" class="d-flex gap-2 align-items-center">
                        <?php if($kw != '') { ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($kw); ?>">
                        <?php } ?>
                        
                        <select name="role" class="form-select form-select-sm fw-medium" style="width: auto;" onchange="this.form.submit()">
                            <option value="">Semua Peran (Role)</option>
                            <option value="admin" <?= ($f_role == 'admin' ? 'selected' : '') ?>>Admin</option>
                            <option value="tutor" <?= ($f_role == 'tutor' ? 'selected' : '') ?>>Tutor / Guru</option>
                            <option value="orang_tua" <?= ($f_role == 'orang_tua' ? 'selected' : '') ?>>Orang Tua</option>
                        </select>
                    </form>

                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3">NAMA LENGKAP</th>
                                <th class="px-4 py-3">USERNAME / EMAIL</th>
                                <th class="px-4 py-3">PASSWORD</th>
                                <th class="px-4 py-3">ROLE (PERAN)</th>
                                <th class="px-4 py-3 text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($result) == 0) {
                                echo '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data akun.</td></tr>';
                            }
                            
                            while($row = mysqli_fetch_assoc($result)) { 
                                if ($row['role'] == 'admin') {
                                    $role_bg = 'bg-primary-subtle text-primary';
                                    $role_label = 'ADMIN';
                                } elseif ($row['role'] == 'tutor') {
                                    $role_bg = 'bg-success-subtle text-success';
                                    $role_label = 'TUTOR / GURU';
                                } else {
                                    $role_bg = 'bg-warning-subtle text-warning';
                                    $role_label = 'ORANG TUA';
                                }
                            ?>
                            <tr>
                                <td class="px-4 py-3 fw-bold text-dark"><?= $row['full_name'] ?></td>
                                <td class="px-4 py-3 text-secondary"><?= $row['username'] ?></td>
                                <td class="px-4 py-3 text-secondary" style="font-family: monospace; letter-spacing: 1px;"><?= $row['password'] ?></td>
                                <td class="px-4 py-3">
                                    <span class="badge rounded-pill <?= $role_bg ?> text-uppercase" style="font-size: 0.65rem; padding: 0.35em 0.65em;">
                                        <?= $role_label ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <button class="btn btn-sm btn-light text-primary me-1" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $row['id'] ?>">
                                        <span class="material-symbols-outlined" style="font-size: 1.1rem;">edit</span>
                                    </button>
                                    <a href="?hapus_id=<?= $row['id'] ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('Yakin ingin menghapus akun <?= $row['full_name'] ?>?')">
                                        <span class="material-symbols-outlined" style="font-size: 1.1rem;">delete</span>
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="editUserModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                  <div class="modal-header border-bottom-0 pb-0">
                                    <h1 class="modal-title fs-5 fw-bold">Edit Akun</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                  </div>
                                  <div class="modal-body pt-4">
                                    <form method="POST">
                                      <input type="hidden" name="id_db" value="<?= $row['id'] ?>">
                                      
                                      <div class="mb-3">
                                        <label class="form-label small fw-medium text-dark">Nama Lengkap</label>
                                        <input type="text" name="full_name" class="form-control" value="<?= $row['full_name'] ?>" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label small fw-medium text-dark">Username / Email</label>
                                        <input type="text" name="username" class="form-control" value="<?= $row['username'] ?>" required>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label small fw-medium text-dark">Password</label>
                                        <input type="text" name="password" class="form-control" value="<?= $row['password'] ?>" required>
                                      </div>
                                      <div class="mb-4">
                                        <label class="form-label small fw-medium text-dark">Role (Peran Akses)</label>
                                        <select class="form-select" name="role" required>
                                            <option value="admin" <?= ($row['role'] == 'admin' ? 'selected' : '') ?>>Admin</option>
                                            <option value="tutor" <?= ($row['role'] == 'tutor' ? 'selected' : '') ?>>Tutor / Guru</option>
                                            <option value="orang_tua" <?= ($row['role'] == 'orang_tua' ? 'selected' : '') ?>>Orang Tua</option>
                                        </select>
                                      </div>
                                      <button type="submit" name="submit_edit" class="btn btn-primary w-100 fw-semibold py-2">Update Akun</button>
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

<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h1 class="modal-title fs-5 fw-bold">Tambah Akun Baru</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-4">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label small fw-medium text-dark">Nama Lengkap</label>
            <input type="text" name="full_name" class="form-control" placeholder="Contoh: Rasya Akbar" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-medium text-dark">Username / Email</label>
            <input type="text" name="username" class="form-control" placeholder="Contoh: rasya@linggis.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-medium text-dark">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Buat password..." required>
          </div>
          <div class="mb-4">
            <label class="form-label small fw-medium text-dark">Role (Peran Akses)</label>
            <select class="form-select" name="role" required>
                <option value="" selected disabled>Pilih peran...</option>
                <option value="admin">Admin</option>
                <option value="tutor">Tutor / Guru</option>
                <option value="orang_tua">Orang Tua</option>
            </select>
          </div>
          <button type="submit" name="submit_user" class="btn btn-primary w-100 fw-semibold py-2">Simpan Akun</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>