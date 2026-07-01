<?php
session_start();

// Cek login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: ../login.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

$success = "";
$error = "";

// --- LOGIKA UPDATE PROFIL ---
if (isset($_POST['update_profile'])) {
    $new_name = $_POST['full_name'];
    $username = $_SESSION['admin_email'];

    $q_update = "UPDATE users SET full_name = '$new_name' WHERE username = '$username'";
    if (mysqli_query($koneksi, $q_update)) {
        $_SESSION['admin_name'] = $new_name; // Update nama di session juga
        $success = "Profil berhasil diperbarui!";
    } else {
        $error = "Gagal memperbarui profil.";
    }
}

// --- LOGIKA GANTI PASSWORD ---
if (isset($_POST['update_password'])) {
    $username = $_SESSION['admin_email'];
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Cek password lama
    $q_check = mysqli_query($koneksi, "SELECT password FROM users WHERE username = '$username'");
    $data = mysqli_fetch_assoc($q_check);

    if ($old_pass !== $data['password']) {
        $error = "Password lama salah!";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Konfirmasi password baru tidak cocok!";
    } else {
        $q_pass = "UPDATE users SET password = '$new_pass' WHERE username = '$username'";
        if (mysqli_query($koneksi, $q_pass)) {
            $success = "Password berhasil diubah!";
        } else {
            $error = "Gagal mengubah password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings - Linggis Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Public Sans', sans-serif; background-color: #f8f9fc; }
        .material-symbols-outlined { display: flex; align-items: center; }
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
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Linggis Admin</h1>
                    <p class="text-muted" style="font-size: 0.75rem; margin-bottom: 0;">LMS Management</p>
                </div>
            </div>
            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="dashboard_admin.php"><span class="material-symbols-outlined">dashboard</span><span>Overview</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="students.php"><span class="material-symbols-outlined">group</span><span>Students</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="teachers.php"><span class="material-symbols-outlined">person_pin_circle</span><span>Teachers</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="classes.php"><span class="material-symbols-outlined">calendar_month</span><span>Classes & Schedule</span></a>
                 <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php">
                    <span class="material-symbols-outlined">how_to_reg</span><span>Attendance Record</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php"><span class="material-symbols-outlined">payments</span><span>Payments & Reports</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php">
                    <span class="material-symbols-outlined">assignment_turned_in</span><span>Grades & Progress</span>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="admin_accounts.php"><span class="material-symbols-outlined">admin_panel_settings</span><span>Admin Accounts</span></a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold mb-1" href="settings.php"><span class="material-symbols-outlined">settings</span><span>Settings</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Sign Out</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0">System Settings</h5>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= $_SESSION['admin_name']; ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Super Administrator</p>
                </div>
                <img class="rounded-circle border" style="width: 40px; height: 40px;" src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name']); ?>&background=0D8ABC&color=fff"/>
            </div>
        </header>

        <div class="p-4 p-md-5" style="max-width: 900px;">
            <div class="mb-4">
                <h2 class="fw-bolder text-dark mb-1">Pengaturan Akun</h2>
                <p class="text-muted">Kelola informasi profil dan keamanan akses Anda.</p>
            </div>

            <?php if($success != ""): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4"><?= $success; ?></div>
            <?php endif; ?>
            <?php if($error != ""): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?= $error; ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-primary">person</span> Profil Saya
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" value="<?= $_SESSION['admin_name']; ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-medium">Email / Username</label>
                                <input type="text" value="<?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '' ?>" class="form-control" readonly>
                                <small class="text-muted">Username tidak dapat diubah.</small>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary w-100 fw-semibold">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h5 class="fw-bold mb-4 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined text-danger">lock</span> Ganti Password
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Password Lama</label>
                                <input type="password" name="old_password" class="form-control" placeholder="Masukkan password saat ini" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Password Baru</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Buat password baru" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-medium">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
                            </div>
                            <button type="submit" name="update_password" class="btn btn-outline-danger w-100 fw-semibold">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>