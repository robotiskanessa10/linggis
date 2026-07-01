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

// --- KAMUS HARGA KURSUS ---
$harga_kursus = [
    'General English' => 500000,
    'TOEFL Preparation' => 750000,
    'IELTS Preparation' => 1000000
];

// --- LOGIKA BATALKAN (HAPUS) PEMBAYARAN ---
if (isset($_GET['hapus_id'])) {
    $id_hapus = $_GET['hapus_id'];
    mysqli_query($koneksi, "DELETE FROM payments WHERE id = '$id_hapus'");
    header("Location: payments.php");
    exit();
}

// --- LOGIKA PROSES BAYAR (DARI TOMBOL DI TABEL) ---
if (isset($_POST['submit_bayar'])) {
    $payment_id = $_POST['payment_id']; 
    $method = $_POST['method'];
    $date = date('Y-m-d');
    
    $q_update = "UPDATE payments SET status = 'Paid', method = '$method', payment_date = '$date' WHERE id = '$payment_id'";
    if (mysqli_query($koneksi, $q_update)) {
        header("Location: payments.php");
        exit();
    }
}

// =========================================================================
// --- FITUR BARU: LOGIKA GENERATE TAGIHAN BULANAN MASSAL (ANTI-DOBEL) ---
// =========================================================================
if (isset($_POST['generate_tagihan'])) {
    $bulan_ini = date('m');
    $tahun_ini = date('Y');
    $tanggal_sekarang = date('Y-m-d');
    
    $berhasil = 0;
    $dilewati = 0;

    // Ambil ID Tagihan (Invoice) terakhir untuk penomoran urut
    $q_last_inv = mysqli_query($koneksi, "SELECT id FROM payments ORDER BY id DESC LIMIT 1");
    $urutan_inv = 1;
    if (mysqli_num_rows($q_last_inv) > 0) {
        $data_inv = mysqli_fetch_assoc($q_last_inv);
        $urutan_inv = $data_inv['id'] + 1;
    }

    // Ambil data SEMUA SISWA dari tabel students
    $q_siswa = mysqli_query($koneksi, "SELECT * FROM students");
    
    while ($siswa = mysqli_fetch_assoc($q_siswa)) {
        $nama_siswa = $siswa['full_name'];
        $kursus = $siswa['course'];
        
        // CEK ANTI-DOBEL: Apakah siswa ini sudah punya tagihan di bulan ini?
        $q_cek = mysqli_query($koneksi, "SELECT id FROM payments WHERE student_name = '$nama_siswa' AND MONTH(payment_date) = '$bulan_ini' AND YEAR(payment_date) = '$tahun_ini'");
        
        if (mysqli_num_rows($q_cek) == 0) {
            // Jika belum ada, BUATKAN TAGIHAN BARU!
            $harga = isset($harga_kursus[$kursus]) ? $harga_kursus[$kursus] : 0;
            $invoice_id = 'INV-' . $tahun_ini . '-' . str_pad($urutan_inv, 3, '0', STR_PAD_LEFT);
            
            // Masukkan ke database dengan status Unpaid
            $q_insert = "INSERT INTO payments (invoice_id, student_name, course_program, amount, payment_date, method, status) 
                         VALUES ('$invoice_id', '$nama_siswa', '$kursus', '$harga', '$tanggal_sekarang', 'Transfer Bank', 'Unpaid')";
            
            if (mysqli_query($koneksi, $q_insert)) {
                $berhasil++;
                $urutan_inv++; 
            }
        } else {
            // Jika sudah ada tagihan di bulan ini, lewati (jangan ditagih 2x)
            $dilewati++; 
        }
    }
    
    // Simpan pesan notifikasi ke Session
    $_SESSION['pesan_generate'] = "Generate selesai! <b>$berhasil</b> tagihan baru berhasil dibuat. (<b>$dilewati</b> siswa sudah ditagih bulan ini).";
    header("Location: payments.php");
    exit();
}
// =========================================================================

// --- LOGIKA SEARCH & FILTER ---
$keyword = isset($_GET['search']) ? $_GET['search'] : "";
$filter_status = isset($_GET['status']) ? $_GET['status'] : "";

$query = "SELECT * FROM (
            SELECT 
                s.full_name AS student_name, 
                s.course AS course_program, 
                p.id AS payment_id,
                p.invoice_id, 
                p.payment_date, 
                p.amount AS paid_amount,
                p.bukti_bayar,
                COALESCE(p.status, 'Unpaid') AS status
            FROM students s
            LEFT JOIN payments p ON s.full_name = p.student_name
          ) AS data_tagihan WHERE 1=1";

if ($keyword != "") {
    $query .= " AND (invoice_id LIKE '%$keyword%' OR student_name LIKE '%$keyword%')";
}
if ($filter_status != "") {
    $query .= " AND status = '$filter_status'";
}
$query .= " ORDER BY status DESC, payment_date DESC, student_name ASC"; 
$result = mysqli_query($koneksi, $query);

// --- PERHITUNGAN TOTAL ---
$data_tabel = [];
$total_paid = 0;
$total_unpaid = 0;

while($row = mysqli_fetch_assoc($result)) {
    $course = $row['course_program'];
    $tagihan = isset($harga_kursus[$course]) ? $harga_kursus[$course] : 0;
    
    if ($row['status'] == 'Paid') {
        $total_paid += $row['paid_amount'];
        $row['display_amount'] = $row['paid_amount'];
    } else {
        $total_unpaid += $tagihan;
        $row['display_amount'] = $tagihan; 
    }
    $data_tabel[] = $row; 
}

// --- FITUR EXPORT CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    if (ob_get_length()) ob_clean(); 
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Laporan_Pembayaran_Linggis.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); 
    fputcsv($out, array('NO. INVOICE', 'NAMA SISWA', 'PROGRAM KURSUS', 'TAGIHAN / JUMLAH (Rp)', 'STATUS'), ';');
    foreach($data_tabel as $row) {
        $invoice = $row['invoice_id'] ? $row['invoice_id'] : 'Belum Ada';
        fputcsv($out, array($invoice, $row['student_name'], $row['course_program'], $row['display_amount'], $row['status']), ';');
    }
    fclose($out); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payments & Reports - Linggis Admin</title>
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
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="dashboard_admin.php"><span class="material-symbols-outlined">dashboard</span><span>Overview</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="students.php"><span class="material-symbols-outlined">group</span><span>Students</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="teachers.php"><span class="material-symbols-outlined">person_pin_circle</span><span>Teachers</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="manage_notes.php"><span class="material-symbols-outlined">menu_book</span><span>Learning Notes</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="classes.php"><span class="material-symbols-outlined">calendar_month</span><span>Classes & Schedule</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="attendance.php"><span class="material-symbols-outlined">how_to_reg</span><span>Attendance Record</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="payments.php"><span class="material-symbols-outlined">payments</span><span>Payments & Reports</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php"><span class="material-symbols-outlined">assignment_turned_in</span><span>Grades & Progress</span></a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="admin_accounts.php"><span class="material-symbols-outlined">admin_panel_settings</span><span>Admin Accounts</span></a>
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
                <form action="" method="GET">
                    <span class="material-symbols-outlined" style="position: absolute; z-index: 10; padding-top: 8px;">search</span>
                    <input class="form-control bg-light border-0" name="search" placeholder="Cari invoice atau nama..." type="text" value="<?= htmlspecialchars($keyword); ?>" />
                </form>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <p class="mb-0 fs-6 fw-semibold text-dark"><?= isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin User'; ?></p>
                    <p class="mb-0 text-muted" style="font-size: 0.75rem;">Super Administrator</p>
                </div>
                <img class="rounded-circle border" style="width: 40px; height: 40px;" src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['admin_name'] ?? 'Admin'); ?>&background=0D8ABC&color=fff"/>
            </div>
        </header>

        <div class="p-4 p-md-5">
            <?php if(isset($_SESSION['pesan_generate'])) { ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-2 mb-4">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span><?= $_SESSION['pesan_generate'] ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php unset($_SESSION['pesan_generate']); } ?>

            <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
                <div>
                    <h2 class="fw-bolder text-dark mb-1">Tagihan & Pembayaran</h2>
                    <p class="text-muted mb-0">Kelola uang masuk dan tagih SPP bulanan siswa di sini.</p>
                </div>
                
                <div class="d-flex gap-2">
                    <form method="POST" onsubmit="return confirm('Anda yakin ingin men-generate tagihan bulan ini untuk SEMUA SISWA?');">
                        <button type="submit" name="generate_tagihan" class="btn btn-warning fw-bold d-flex align-items-center gap-2 shadow-sm text-dark px-4 border-0">
                            <span class="material-symbols-outlined fs-5">receipt_long</span> Buat Tagihan Bulan Ini
                        </button>
                    </form>

                    <a href="payments.php" class="btn btn-outline-primary d-flex align-items-center gap-2 bg-white px-3">
                        <span class="material-symbols-outlined fs-6">refresh</span>
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                        <div class="card-body p-4"><p class="mb-1 opacity-75">Uang Masuk (Lunas)</p><h2 class="fw-bold mb-0">Rp <?= number_format($total_paid, 0, ',', '.'); ?></h2></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 bg-white border-start border-danger border-4 h-100">
                        <div class="card-body p-4"><p class="text-muted mb-1">Estimasi Piutang (Belum Dibayar)</p><h2 class="fw-bold mb-0 text-danger">Rp <?= number_format($total_unpaid, 0, ',', '.'); ?></h2></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between">
                    <form method="GET" class="d-flex gap-2">
                        <?php if($keyword != "") { ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($keyword); ?>">
                        <?php } ?>
                        <select name="status" class="form-select form-select-sm fw-medium border-0 bg-light px-3" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="Paid" <?= ($filter_status=='Paid'?'selected':'') ?>>Paid (Lunas)</option>
                            <option value="Unpaid" <?= ($filter_status=='Unpaid'?'selected':'') ?>>Unpaid (Belum Lunas)</option>
                        </select>
                    </form>
                    
                    <a href="?export=csv&search=<?= urlencode($keyword); ?>&status=<?= urlencode($filter_status); ?>" class="btn btn-outline-secondary btn-sm fw-semibold d-flex align-items-center gap-1 text-dark border-0 bg-light px-3">
                        <span class="material-symbols-outlined fs-6">download</span> Export
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted" style="font-size: 0.75rem;">
                            <tr>
                                <th class="px-4 py-3">NO. INVOICE</th>
                                <th class="px-4 py-3">NAMA SISWA</th>
                                <th class="px-4 py-3">PROGRAM KURSUS</th>
                                <th class="px-4 py-3">TAGIHAN / JUMLAH</th>
                                <th class="px-4 py-3 text-center">STATUS</th>
                                <th class="px-4 py-3 text-end" style="min-width: 200px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(count($data_tabel) == 0) {
                                echo '<tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data tagihan.</td></tr>';
                            }

                            foreach($data_tabel as $row) { 
                                $is_paid = ($row['status'] == 'Paid');
                                $has_bukti = (!empty($row['bukti_bayar']));
                                
                                if ($is_paid) {
                                    $st_bg = 'bg-success-subtle text-success';
                                    $status_label = 'LUNAS';
                                } else if ($has_bukti) {
                                    $st_bg = 'bg-warning-subtle text-warning-emphasis'; 
                                    $status_label = 'MENUNGGU ACC';
                                } else {
                                    $st_bg = 'bg-danger-subtle text-danger';
                                    $status_label = 'UNPAID';
                                }
                                
                                $inv_text = $row['invoice_id'] ? $row['invoice_id'] : '<span class="text-muted"><i>Belum Ada</i></span>';
                                $modal_id = "modal" . preg_replace('/[^A-Za-z0-9]/', '', $row['student_name']) . $row['payment_id'];
                            ?>
                            <tr>
                                <td class="px-4 py-3 text-secondary small fw-medium"><?= $inv_text ?></td>
                                <td class="px-4 py-3 fw-bold text-dark"><?= $row['student_name'] ?></td>
                                <td class="px-4 py-3 text-secondary small"><?= $row['course_program'] ?></td>
                                <td class="px-4 py-3 fw-bold">Rp <?= number_format($row['display_amount'], 0, ',', '.') ?></td>
                                <td class="px-4 py-3 text-center"><span class="badge rounded-pill <?= $st_bg ?> text-uppercase px-3 py-2" style="font-size: 0.65rem;"><?= $status_label ?></span></td>
                                <td class="px-4 py-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php if($is_paid) { ?>
                                            <a href="?hapus_id=<?= $row['payment_id'] ?>" class="btn btn-sm btn-outline-danger fw-medium px-3" onclick="return confirm('Batalkan pembayaran untuk <?= $row['student_name'] ?>? Status akan kembali menjadi Unpaid.')">Batalkan</a>
                                        <?php } else { ?>
                                            <?php if($has_bukti) { ?>
                                                <a href="../orangtua/bukti_transfer/<?= $row['bukti_bayar'] ?>" target="_blank" class="btn btn-sm btn-info text-white fw-semibold shadow-sm px-3 d-flex align-items-center gap-1">
                                                    Lihat Struk
                                                </a>
                                            <?php } ?>
                                            <button class="btn btn-sm btn-success fw-semibold shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#<?= $modal_id ?>">
                                                <?= $has_bukti ? 'ACC Lunas' : 'Bayar Lunas' ?>
                                            </button>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>

                            <?php if(!$is_paid) { ?>
                            <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0">
                                  <div class="modal-header border-bottom-0"><h1 class="modal-title fs-5 fw-bold">Konfirmasi Pembayaran</h1><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                  <div class="modal-body pt-0 text-start">
                                    <form method="POST">
                                      <input type="hidden" name="payment_id" value="<?= $row['payment_id'] ?>">
                                      
                                      <div class="mb-3">
                                          <label class="form-label small text-muted">Nama Siswa</label>
                                          <input type="text" class="form-control fw-bold" value="<?= $row['student_name'] ?>" readonly style="background-color: #f8f9fa;">
                                      </div>
                                      <div class="mb-3">
                                          <label class="form-label small text-muted">Program & Tagihan</label>
                                          <input type="text" class="form-control text-primary fw-bold" value="<?= $row['course_program'] ?> - Rp <?= number_format($row['display_amount'], 0, ',', '.') ?>" readonly style="background-color: #e9ecef;">
                                      </div>
                                      <div class="mb-4">
                                          <label class="form-label small fw-medium">Metode Pembayaran</label>
                                          <select class="form-select border-primary" name="method" required>
                                              <option value="Transfer Bank">Transfer Bank</option>
                                              <option value="Tunai / Cash">Tunai / Cash</option>
                                              <option value="E-Wallet">E-Wallet (OVO/Dana/GoPay)</option>
                                          </select>
                                      </div>
                                      <button type="submit" name="submit_bayar" class="btn btn-primary w-100 fw-semibold py-2">Konfirmasi Lunas Sekarang</button>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>