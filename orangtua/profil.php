<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['role'] !== 'orang_tua') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

// Ambil username (email) dari session saat login
// Jika nama variabel session-mu beda, sesuaikan ya (misal $_SESSION['email'])
$email_ortu = isset($_SESSION['username']) ? $_SESSION['username'] : ''; 

$pesan = "";
$error = "";

// --- LOGIKA GANTI PASSWORD ---
if (isset($_POST['update_password'])) {
    $pass_lama = $_POST['pass_lama'];
    $pass_baru = $_POST['pass_baru'];
    $pass_konfirm = $_POST['pass_konfirm'];

    // Cek apakah password lama cocok dengan database
    $q_cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$email_ortu' AND password = '$pass_lama'");
    
    if (mysqli_num_rows($q_cek) > 0) {
        if ($pass_baru === $pass_konfirm) {
            // Update ke password baru
            mysqli_query($koneksi, "UPDATE users SET password = '$pass_baru' WHERE username = '$email_ortu'");
            $pesan = "Password berhasil diperbarui! Silakan gunakan password baru saat login berikutnya.";
        } else {
            $error = "Password baru dan konfirmasi tidak cocok!";
        }
    } else {
        $error = "Password lama yang Anda masukkan salah!";
    }
}

// --- AMBIL DATA ORTU ---
$q_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$email_ortu'");
$data_ortu = mysqli_fetch_assoc($q_user);
$nama_ortu = $data_ortu ? $data_ortu['full_name'] : (isset($_SESSION['parent_name']) ? $_SESSION['parent_name'] : 'Bapak/Ibu');

// --- AMBIL DATA ANAK ---
$q_anak = mysqli_query($koneksi, "SELECT * FROM students WHERE parent_email = '$email_ortu'");
$data_anak = mysqli_fetch_assoc($q_anak);
$nama_anak = $data_anak ? $data_anak['full_name'] : 'Belum Terdaftar';
$id_anak = $data_anak ? $data_anak['student_id'] : '-';
$kursus_anak = $data_anak ? $data_anak['course'] : '-';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profil Saya - Linggis Parent</title>
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
        .card-custom { border-radius: 20px; border: none; }
    </style>
</head>
<body>

<div class="d-flex vh-100 overflow-hidden">
    <aside class="sidebar-width bg-white border-end d-flex flex-column justify-content-between h-100">
        <div class="p-4 overflow-y-auto">
            <div class="d-flex align-items-center gap-3 mb-5">
                <div class="bg-primary p-2 rounded text-white d-flex align-items-center justify-content-center">
                    <span class="material-symbols-outlined fs-4">family_restroom</span>
                </div>
                <div><h1 class="fs-5 fw-bold mb-0">Linggis Parent</h1></div>
            </div>
            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded" href="dashboard_ortu.php"><span class="material-symbols-outlined">home</span><span>Beranda</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded" href="attendance.php"><span class="material-symbols-outlined">fact_check</span><span>Kehadiran Anak</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded" href="grades.php"><span class="material-symbols-outlined">military_tech</span><span>Nilai & Evaluasi</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded" href="catatan_tutor.php"><span class="material-symbols-outlined">menu_book</span><span>Catatan Tutor</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded" href="payments.php"><span class="material-symbols-outlined">receipt_long</span><span>Tagihan & Pembayaran</span></a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold mb-1" href="profil.php"><span class="material-symbols-outlined">manage_accounts</span><span>Profil Saya</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Keluar</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto bg-light">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-end">
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= htmlspecialchars($nama_ortu); ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Orang Tua / Wali</p>
                </div>
                <img class="rounded-circle border" style="width: 40px; height: 40px;" src="https://ui-avatars.com/api/?name=<?= urlencode($nama_ortu); ?>&background=EBF5FB&color=0D8ABC"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="mb-4 text-start">
                <h2 class="fw-bolder text-dark mb-1">Pengaturan Profil</h2>
                <p class="text-muted">Kelola informasi akun dan keamanan Anda di sini.</p>
            </div>

            <?php if($pesan != "") { ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span> <?= $pesan; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <?php if($error != "") { ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined">error</span> <?= $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="card card-custom shadow-sm bg-white p-4 mb-4 border-0">
                        <h5 class="fw-bold mb-4 border-bottom pb-3">Informasi Akun</h5>
                        
                        <div class="mb-3">
                            <label class="text-muted small fw-medium">Nama Lengkap Wali</label>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="material-symbols-outlined text-primary">person</span>
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($nama_ortu) ?></h6>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="text-muted small fw-medium">Email Login (Username)</label>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="material-symbols-outlined text-primary">mail</span>
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($email_ortu) ?></h6>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-4 border-bottom pb-3 mt-5">Data Siswa Terhubung</h5>
                        <div class="bg-light p-3 rounded-4 border border-light-subtle">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Nama Anak:</span>
                                <span class="fw-bold"><?= htmlspecialchars(ucwords($nama_anak)) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">ID Siswa:</span>
                                <span class="fw-bold text-primary"><?= htmlspecialchars($id_anak) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Program Kursus:</span>
                                <span class="badge bg-success-subtle text-success px-2 py-1"><?= htmlspecialchars($kursus_anak) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card card-custom shadow-sm bg-white p-4 border-0">
                        <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-3">
                            <span class="material-symbols-outlined text-warning fs-4">lock</span>
                            <h5 class="fw-bold mb-0">Ubah Password</h5>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label small fw-medium text-muted">Password Lama</label>
                                <input type="password" name="pass_lama" class="form-control bg-light border-0" placeholder="Masukkan password saat ini" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium text-muted">Password Baru</label>
                                <input type="password" name="pass_baru" class="form-control bg-light border-0" placeholder="Minimal 6 karakter" required minlength="6">
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-medium text-muted">Konfirmasi Password Baru</label>
                                <input type="password" name="pass_konfirm" class="form-control bg-light border-0" placeholder="Ulangi password baru" required minlength="6">
                            </div>
                            
                            <button type="submit" name="update_password" class="btn btn-primary w-100 fw-bold py-2 rounded-pill shadow-sm">
                                Simpan Password Baru
                            </button>
                        </form>
                        
                        <div class="alert alert-warning mt-4 border-0 rounded-4 d-flex align-items-start gap-2 mb-0" style="font-size: 0.8rem;">
                            <span class="material-symbols-outlined fs-6">info</span>
                            <span>Jika Anda lupa password lama, silakan hubungi <b>Admin Linggis</b> untuk melakukan reset password secara manual.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>