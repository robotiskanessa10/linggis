<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['role'] !== 'orang_tua') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");

$nama_ortu = isset($_SESSION['parent_name']) ? $_SESSION['parent_name'] : 'Bapak/Ibu';
$nama_anak = isset($_SESSION['child_name']) ? $_SESSION['child_name'] : ''; 

// 1. Ambil Data Tagihan Terakhir
$status_pembayaran = "Belum Ada Tagihan";
$jumlah_tagihan = "0";
$warna_status = "bg-secondary";
$id_tagihan = "";
$bukti_bayar = null;

$q_pay = mysqli_query($koneksi, "SELECT * FROM payments WHERE student_name = '$nama_anak' ORDER BY id DESC LIMIT 1");
if($q_pay && mysqli_num_rows($q_pay) > 0) {
    $d_pay = mysqli_fetch_assoc($q_pay);
    $id_tagihan = $d_pay['id'];
    $jumlah_tagihan = number_format($d_pay['amount'] ?? 0, 0, ',', '.');
    $bukti_bayar = $d_pay['bukti_bayar']; 
    
    $s = strtolower($d_pay['status']);
    
    if($s == 'paid' || $s == 'lunas') {
        $status_pembayaran = "LUNAS";
        $warna_status = "bg-success";
    } else {
        if ($bukti_bayar != null && $bukti_bayar != "") {
            $status_pembayaran = "MENUNGGU KONFIRMASI";
            $warna_status = "bg-warning text-dark";
        } else {
            $status_pembayaran = "BELUM DIBAYAR";
            $warna_status = "bg-danger";
        }
    }
}

// 2. Ambil Riwayat
$q_history = mysqli_query($koneksi, "SELECT * FROM payments WHERE student_name = '$nama_anak' ORDER BY payment_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tagihan & Pembayaran - Linggis Parent</title>
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
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="payments.php"><span class="material-symbols-outlined">receipt_long</span><span>Tagihan & Pembayaran</span></a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded mb-1" href="profil.php"><span class="material-symbols-outlined">manage_accounts</span><span>Profil Saya</span></a>
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
                <h2 class="fw-bolder text-dark mb-1">Tagihan & Pembayaran</h2>
                <p class="text-muted">Kelola administrasi biaya kursus <strong><?= htmlspecialchars(ucwords($nama_anak)); ?></strong>.</p>
            </div>

            <?php if(isset($_SESSION['pesan'])) { ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm">
                    <strong>Berhasil!</strong> <?= $_SESSION['pesan']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php unset($_SESSION['pesan']); } ?>

            <?php if(isset($_SESSION['error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm">
                    <strong>Gagal!</strong> <?= $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php unset($_SESSION['error']); } ?>

            <div class="row g-4">
                <div class="col-xl-7">
                    <div class="card card-custom shadow-sm bg-white p-4 mb-4 border-0">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h5 class="fw-bold mb-1">Tagihan Saat Ini</h5>
                                <p class="text-muted small"><?= date('F Y') ?></p>
                            </div>
                            <span class="badge <?= $warna_status ?> px-3 py-2 rounded-pill fw-bold"><?= $status_pembayaran ?></span>
                        </div>
                        
                        <div class="bg-light p-4 rounded-4 mb-4 text-start">
                            <p class="text-muted small mb-1 text-uppercase fw-bold">Total Yang Harus Dibayar</p>
                            <h1 class="fw-bolder text-dark">Rp <?= $jumlah_tagihan ?></h1>
                        </div>
                        
                        <?php if($status_pembayaran == "BELUM DIBAYAR") { ?>
                            <div class="alert alert-info border-0 rounded-4 d-flex align-items-center gap-3">
                                <span class="material-symbols-outlined">account_balance</span>
                                <small>Transfer ke: <b>BCA 123456789 a/n LINGGIS ACADEMY</b></small>
                            </div>
                            <button data-bs-toggle="modal" data-bs-target="#uploadModal" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mt-2">
                                <span class="material-symbols-outlined">upload_file</span> Upload Bukti Transfer
                            </button>

                        <?php } else if($status_pembayaran == "MENUNGGU KONFIRMASI") { ?>
                            <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center gap-3">
                                <span class="material-symbols-outlined">hourglass_top</span>
                                <small><b>Bukti Sedang Dicek!</b> Admin kami sedang memverifikasi pembayaran Anda.</small>
                            </div>

                        <?php } else { ?>
                            <div class="alert alert-success border-0 rounded-4 d-flex align-items-center gap-3">
                                <span class="material-symbols-outlined">check_circle</span>
                                <small><b>Pembayaran Lunas!</b> Terima kasih telah menyelesaikan administrasi bulan ini.</small>
                            </div>
                            
                            <a href="generate_kwitansi.php?id=<?= $id_tagihan ?>" target="_blank" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mt-3 text-decoration-none">
                                <span class="material-symbols-outlined">download</span> Unduh Kwitansi Resmi (PDF)
                            </a>
                        <?php } ?>
                    </div>

                    <div class="card card-custom shadow-sm bg-white overflow-hidden border-0">
                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-0">Riwayat Transaksi</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-secondary small">
                                    <tr>
                                        <th class="px-4 py-3">TANGGAL</th>
                                        <th class="px-4 py-3">JUMLAH</th>
                                        <th class="px-4 py-3 text-center">STATUS</th>
                                        <th class="px-4 py-3 text-center">KWITANSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($q_history && mysqli_num_rows($q_history) > 0) {
                                        while($row = mysqli_fetch_assoc($q_history)) { 
                                            $st = strtolower($row['status']);
                                            $is_lunas = ($st == 'lunas' || $st == 'paid');
                                        ?>
                                        <tr>
                                            <td class="px-4 py-3"><?= date('d M Y', strtotime($row['payment_date'] ?? 'now')) ?></td>
                                            <td class="px-4 py-3 fw-bold">Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                                            <td class="px-4 py-3 text-center">
                                                <small class="fw-bold <?= $is_lunas ? 'text-success' : 'text-danger' ?>">
                                                    ● <?= strtoupper($row['status']) ?>
                                                </small>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <?php if($is_lunas) { ?>
                                                    <a href="generate_kwitansi.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 text-decoration-none">
                                                        <span class="material-symbols-outlined fs-6">download</span> PDF
                                                    </a>
                                                <?php } else { echo "-"; } ?>
                                            </td>
                                        </tr>
                                    <?php } } else { ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted small">Belum ada riwayat pembayaran.</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="card card-custom shadow-sm bg-primary text-white p-4 position-relative overflow-hidden border-0">
                        <div class="position-relative z-1">
                            <h5 class="fw-bold mb-3">Informasi Finance</h5>
                            <p class="small opacity-75">Status pembayaran akan otomatis berubah menjadi LUNAS maksimal 1x24 jam setelah Anda mengunggah bukti transfer.</p>
                            <hr class="opacity-25">
                            <p class="small mb-1 opacity-75">Customer Service:</p>
                            <div class="d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined">support_agent</span>
                                <span class="fw-bold fs-5">0812-3456-7890</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined position-absolute opacity-10" style="font-size: 180px; right: -30px; bottom: -30px; z-index: 0;">receipt_long</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header border-bottom-0 p-4">
        <h5 class="modal-title fw-bold" id="uploadModalLabel">Upload Bukti Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 pt-0">
        <p class="text-muted small mb-4">Pastikan nominal transfer dan nomor rekening tujuan sudah sesuai sebelum mengunggah bukti.</p>
        
        <form action="upload_bukti.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_payment" value="<?= $id_tagihan ?>">
            
            <div class="mb-4">
                <label class="form-label fw-semibold small">Pilih File/Foto Struk</label>
                <input class="form-control bg-light" type="file" name="foto_bukti" accept=".jpg, .jpeg, .png, .pdf" required>
                <small class="text-muted" style="font-size: 11px;">Maksimal ukuran: 2MB. Format: JPG, PNG, PDF.</small>
            </div>
            
            <button type="submit" name="upload" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
                Kirim Bukti Pembayaran
            </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>