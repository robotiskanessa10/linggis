<?php
session_start();

// Cek apakah yang login benar-benar Orang Tua
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'orang_tua') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

// Sesuaikan nama variabel session dengan sistem kamu
$nama_ortu = isset($_SESSION['parent_name']) ? $_SESSION['parent_name'] : 'Bapak/Ibu';
$nama_anak = isset($_SESSION['child_name']) ? $_SESSION['child_name'] : 'Andi Saputra';

// Ambil HANYA data notes milik anak dari orang tua yang login
$q_notes = mysqli_query($koneksi, "SELECT * FROM learning_notes WHERE student_name = '$nama_anak' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Catatan Tutor - Parent Linggis</title>
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
        .note-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .note-card:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; border-color: #0d6efd !important; }
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
                <div>
                    <h1 class="fs-5 fw-bold mb-0">Linggis Parent</h1>
                    <p class="text-muted" style="font-size: 0.75rem; font-weight: 500; margin-bottom: 0;">Student Progress</p>
                </div>
            </div>

            <nav class="nav flex-column gap-1">
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="dashboard_ortu.php">
                    <span class="material-symbols-outlined">home</span><span>Beranda</span>
                </a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php">
                    <span class="material-symbols-outlined">fact_check</span><span>Kehadiran Anak</span>
                </a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php">
                    <span class="material-symbols-outlined">military_tech</span><span>Nilai & Evaluasi</span>
                </a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="catatan_tutor.php">
                    <span class="material-symbols-outlined">menu_book</span><span>Catatan Tutor</span>
                </a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php">
                    <span class="material-symbols-outlined">receipt_long</span><span>Tagihan & Pembayaran</span>
                </a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="profil.php"><span class="material-symbols-outlined">manage_accounts</span><span>Profil Saya</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Keluar</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto">
        <header class="bg-white border-bottom sticky-top px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-md-none me-2"><span class="material-symbols-outlined">menu</span></button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= htmlspecialchars($nama_ortu); ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Orang Tua / Wali</p>
                </div>
                <img class="rounded-circle border border-primary-subtle" style="width: 40px; height: 40px;" src="https://ui-avatars.com/api/?name=<?= urlencode($nama_ortu); ?>&background=EBF5FB&color=0D8ABC"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <div class="mb-4">
                <h2 class="fw-bolder text-dark tracking-tight mb-1">Catatan Perkembangan Belajar</h2>
                <p class="text-muted mb-0">Pantau evaluasi dan pesan dari tutor untuk <strong><?= htmlspecialchars($nama_anak); ?></strong>.</p>
            </div>

            <?php if(mysqli_num_rows($q_notes) > 0) { ?>
                <div class="row g-4">
                    <?php while($row = mysqli_fetch_assoc($q_notes)) { ?>
                        <div class="col-12 col-xl-6">
                            <div class="card note-card border border-light-subtle shadow-sm rounded-4 h-100 bg-white">
                                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill mb-2 fw-semibold">
                                            Pertemuan <?= htmlspecialchars($row['session_number']) ?>
                                        </span>
                                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($row['topic']) ?></h5>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted fw-medium d-block mb-1"><?= date('d F Y', strtotime($row['session_date'])) ?></small>
                                    </div>
                                </div>
                                <div class="card-body px-4 py-3">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="material-symbols-outlined text-muted fs-5">record_voice_over</span>
                                        <span class="text-secondary small fw-medium">Tutor: <strong class="text-dark"><?= htmlspecialchars($row['tutor_name']) ?></strong></span>
                                    </div>
                                    <div class="p-3 bg-light rounded-3 text-dark mb-0" style="font-size: 0.95rem; line-height: 1.6; border-left: 4px solid #0d6efd;">
                                        <?= nl2br(htmlspecialchars($row['notes'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="card border-0 shadow-sm rounded-4 bg-white text-center py-5">
                    <div class="card-body py-5">
                        <span class="material-symbols-outlined text-muted opacity-25" style="font-size: 80px;">speaker_notes_off</span>
                        <h4 class="fw-bold mt-3 text-dark">Belum Ada Catatan</h4>
                        <p class="text-muted mb-0">Tutor belum memasukkan catatan evaluasi untuk <?= htmlspecialchars($nama_anak); ?>.</p>
                    </div>
                </div>
            <?php } ?>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>