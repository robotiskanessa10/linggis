<?php
session_start();

// Cek apakah yang login benar-benar Orang Tua
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true || $_SESSION['role'] !== 'orang_tua') {
    header("Location: ../index.php");
    exit();
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
if (mysqli_connect_errno()) { echo "Koneksi database gagal: " . mysqli_connect_error(); exit(); }

// ==========================================
// SAMAKAN PERSIS DENGAN DASHBOARD
// ==========================================
$nama_ortu = isset($_SESSION['parent_name']) ? $_SESSION['parent_name'] : 'Bapak/Ibu Farah';
$nama_anak = (isset($_SESSION['child_name']) && $_SESSION['child_name'] != '') ? $_SESSION['child_name'] : 'farah dhiya'; 

// ==========================================
// MENGAMBIL DATA UNTUK STATISTIK & KALENDER
// ==========================================

// 1. Statistik Kehadiran 
$stat_hadir = 0; $stat_izin = 0; $stat_alpha = 0;
$q_stat = mysqli_query($koneksi, "SELECT status_hadir, COUNT(*) as jumlah FROM attendance WHERE student_name LIKE '%$nama_anak%' GROUP BY status_hadir");

if($q_stat) {
    while($row = mysqli_fetch_assoc($q_stat)) {
        $status = strtolower($row['status_hadir']);
        if($status == 'hadir' || $status == 'present') { $stat_hadir = $row['jumlah']; }
        elseif($status == 'izin' || $status == 'sakit') { $stat_izin += $row['jumlah']; }
        elseif($status == 'alpha' || $status == 'absen') { $stat_alpha = $row['jumlah']; }
    }
}

// 2. Ambil data absensi untuk dikirim ke FullCalendar
$events = array();
$q_absen = mysqli_query($koneksi, "SELECT * FROM attendance WHERE student_name LIKE '%$nama_anak%' ORDER BY tanggal DESC");

if($q_absen) {
    while($row = mysqli_fetch_assoc($q_absen)) {
        $status_asli = $row['status_hadir'];
        $status_low = strtolower($status_asli);
        
        $color = '#198754'; // Hijau
        if($status_low == 'izin' || $status_low == 'sakit') { $color = '#ffc107'; } 
        elseif($status_low == 'alpha' || $status_low == 'absen') { $color = '#dc3545'; }

        $events[] = array(
            'title' => $status_asli . ' - ' . $row['class_name'],
            'start' => $row['tanggal'],
            'color' => $color
        );
    }
}
$events_json = json_encode($events);

// Kembalikan pointer query absensi ke awal
if($q_absen) { mysqli_data_seek($q_absen, 0); }

// Fungsi untuk ubah bulan angka jadi teks Bahasa Indonesia
$bulan_indo = [
    '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', 
    '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags', 
    '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kehadiran Anak - Linggis Parent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    
    <style>
        body { font-family: 'Public Sans', sans-serif; background-color: #f8f9fc; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: flex; align-items: center; }
        .sidebar-width { width: 280px; min-width: 280px; }
        .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
        .text-primary-custom { color: #0d6efd; }
        .hover-bg-light:hover { background-color: #f1f5f9; }
        .icon-box { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
        
        .fc-theme-standard td, .fc-theme-standard th { border-color: #e2e8f0; }
        .fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; text-transform: capitalize; }
        .fc .fc-button-primary { background-color: #0d6efd; border-color: #0d6efd; text-transform: capitalize; }
        .fc .fc-daygrid-event { padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer;}
        .fc .fc-day-today { background-color: rgba(13, 110, 253, 0.05) !important; }
        .fc-col-header-cell-cushion { text-transform: uppercase; font-size: 0.85rem; padding-top: 10px; padding-bottom: 10px; }
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
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 bg-primary-soft text-primary-custom rounded fw-semibold" href="attendance.php">
                    <span class="material-symbols-outlined">fact_check</span><span>Kehadiran Anak</span>
                </a>
                
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="grades.php">
                    <span class="material-symbols-outlined">military_tech</span><span>Nilai & Evaluasi</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="catatan_tutor.php">
                    <span class="material-symbols-outlined">menu_book</span><span>Catatan Tutor</span>
                </a>
                <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition" href="payments.php">
                    <span class="material-symbols-outlined">receipt_long</span><span>Tagihan & Pembayaran</span>
                </a>
            </nav>
        </div>
        <div class="p-4 border-top">
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-secondary hover-bg-light rounded transition mb-1" href="#"><span class="material-symbols-outlined">manage_accounts</span><span>Profil Saya</span></a>
            <a class="nav-link d-flex align-items-center gap-3 px-3 py-2 text-danger rounded transition fw-medium" href="../logout.php"><span class="material-symbols-outlined">logout</span><span>Keluar</span></a>
        </div>
    </aside>

    <main class="flex-grow-1 overflow-y-auto bg-light">
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
                <h2 class="fw-bolder text-dark tracking-tight mb-1">Kehadiran Anak</h2>
                <p class="text-muted mb-0">Pantau absensi kelas <strong><?= htmlspecialchars(ucwords($nama_anak)); ?></strong> melalui kalender di bawah ini.</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                            <div class="icon-box" style="background-color: rgba(25, 135, 84, 0.1); color: #198754;"><span class="material-symbols-outlined">how_to_reg</span></div>
                            <div><p class="text-muted small fw-bold mb-0 text-uppercase">Hadir</p><h3 class="fw-bolder mb-0 text-dark"><?= $stat_hadir ?></h3></div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                            <div class="icon-box" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107;"><span class="material-symbols-outlined">sick</span></div>
                            <div><p class="text-muted small fw-bold mb-0 text-uppercase">Izin / Sakit</p><h3 class="fw-bolder mb-0 text-dark"><?= $stat_izin ?></h3></div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                            <div class="icon-box" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545;"><span class="material-symbols-outlined">person_off</span></div>
                            <div><p class="text-muted small fw-bold mb-0 text-uppercase">Alpha</p><h3 class="fw-bolder mb-0 text-dark"><?= $stat_alpha ?></h3></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                        <div class="d-flex gap-3 mb-3 justify-content-end small fw-medium">
                            <span class="d-flex align-items-center gap-1"><span class="badge" style="background-color: #198754; width:12px; height:12px; border-radius:50%;"> </span> Hadir</span>
                            <span class="d-flex align-items-center gap-1"><span class="badge" style="background-color: #ffc107; width:12px; height:12px; border-radius:50%;"> </span> Izin/Sakit</span>
                            <span class="d-flex align-items-center gap-1"><span class="badge" style="background-color: #dc3545; width:12px; height:12px; border-radius:50%;"> </span> Alpha</span>
                        </div>
                        
                        <div id="calendar"></div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                        <div class="card-header bg-white border-bottom p-4">
                            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                <span class="material-symbols-outlined text-primary">history</span> Riwayat Terbaru
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-secondary small text-uppercase sticky-top">
                                        <tr>
                                            <th class="px-4 py-3 border-0">Tanggal</th>
                                            <th class="px-4 py-3 border-0 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($q_absen && mysqli_num_rows($q_absen) > 0) {
                                            while($row = mysqli_fetch_assoc($q_absen)) { 
                                                $bg_color = 'bg-success';
                                                if(strtolower($row['status_hadir']) == 'izin' || strtolower($row['status_hadir']) == 'sakit') $bg_color = 'bg-warning text-dark';
                                                if(strtolower($row['status_hadir']) == 'alpha' || strtolower($row['status_hadir']) == 'absen') $bg_color = 'bg-danger';

                                                // Merubah format YYYY-MM-DD menjadi format Indonesia
                                                $tgl_parts = explode('-', $row['tanggal']);
                                                $tanggal_indo = $tgl_parts[2] . ' ' . $bulan_indo[$tgl_parts[1]] . ' ' . $tgl_parts[0];
                                        ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <span class="d-block fw-bold text-dark"><?= $tanggal_indo ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($row['class_name']) ?></small><br>
                                                    <small class="text-primary" style="font-size:0.7rem;">Input by: <?= htmlspecialchars($row['input_by']) ?></small>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="badge <?= $bg_color ?> px-3 py-1 rounded-pill"><?= htmlspecialchars($row['status_hadir']) ?></span>
                                                </td>
                                            </tr>
                                        <?php } } else { ?>
                                            <tr><td colspan="2" class="text-center py-5 text-muted small"><span class="material-symbols-outlined d-block fs-2 mb-2 opacity-25">event_busy</span>Belum ada riwayat absensi.</td></tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js'></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var eventData = <?= $events_json; ?>;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id', // Seting Bahasa Indonesia aktif
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'today'
            },
            height: 550,
            events: eventData,
            eventClick: function(info) {
                // Formatting tanggal manual untuk alert
                var dateObj = new Date(info.event.start);
                var options = { day: 'numeric', month: 'long', year: 'numeric' };
                var indoDate = dateObj.toLocaleDateString('id-ID', options);

                alert('Topik/Kelas: ' + info.event.title + '\nTanggal: ' + indoDate);
            }
        });

        calendar.render();
    });
</script>

</body>
</html>