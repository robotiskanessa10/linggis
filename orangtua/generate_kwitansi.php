<?php
session_start();
require('libs/fpdf.php'); // Pastikan path ke file fpdf.php benar

if (!isset($_GET['id']) || !isset($_SESSION['status_login'])) {
    die("Akses dilarang!");
}

$koneksi = mysqli_connect("localhost", "root", "", "linggis_db");
$id_pembayaran = $_GET['id'];
$nama_anak = $_SESSION['child_name'];

// Ambil data pembayaran spesifik
$query = mysqli_query($koneksi, "SELECT * FROM payments WHERE id = '$id_pembayaran' AND student_name = '$nama_anak'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data pembayaran tidak ditemukan!");
}

// MULAI BUAT PDF
$pdf = new FPDF('P','mm','A5'); // Ukuran A5 biar kayak kwitansi asli
$pdf->AddPage();

// Header / Nama Lembaga
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'LINGGIS ACADEMY',0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,5,'Jl. Pendidikan No. 123, Lamongan, Jawa Timur',0,1,'C');
$pdf->Cell(0,5,'WhatsApp: 0812-3456-7890',0,1,'C');
$pdf->Line(10, 35, 138, 35); // Garis pemisah
$pdf->Ln(10);

// Judul Kwitansi
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,'KWITANSI PEMBAYARAN',0,1,'C');
$pdf->Ln(5);

// Isi Kwitansi
$pdf->SetFont('Arial','',10);
$pdf->Cell(40,8,'No. Transaksi',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(0,8,'INV-' . $data['id'] . '/' . date('Ymd'),0,1);

$pdf->Cell(40,8,'Telah terima dari',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(0,8, strtoupper($_SESSION['parent_name']),0,1);

$pdf->Cell(40,8,'Nama Siswa',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(0,8, strtoupper($data['student_name']),0,1);

$pdf->Cell(40,8,'Untuk Pembayaran',0,0);
$pdf->Cell(5,8,':',0,0);
$pdf->Cell(0,8,'Kursus Bahasa Inggris Periode ' . date('F Y'),0,1);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(40,12,'TERBILANG: ',1,0,'C',true);
$pdf->Cell(88,12,'Rp ' . number_format($data['amount'], 0, ',', '.'),1,1,'C',true);

// Tanda Tangan
$pdf->Ln(10);
$pdf->SetFont('Arial','',10);
$pdf->Cell(85);
$pdf->Cell(0,5,'Lamongan, ' . date('d F Y'),0,1,'C');
$pdf->Ln(15);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(85);
$pdf->Cell(0,5,'( Admin Linggis )',0,1,'C');

$pdf->Output('I', 'Kwitansi_' . $data['student_name'] . '.pdf');
?>