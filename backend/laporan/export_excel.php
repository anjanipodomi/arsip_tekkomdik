<?php
session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

require "../library/fpdf.php";

// admin & pimpinan
if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    die("Akses ditolak");
}

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'LAPORAN PEMUSNAHAN ARSIP',0,1,'C');

$pdf->Ln(5);
$pdf->SetFont('Arial','B',10);

// header tabel
$header = [
    'Nomor Surat', 'Asal Surat', 'Tanggal Surat',
    'Tanggal Pemusnahan', 'Disetujui Oleh'
];

$width = [40, 60, 35, 40, 50];

foreach ($header as $i => $col) {
    $pdf->Cell($width[$i],8,$col,1,0,'C');
}
$pdf->Ln();

$pdf->SetFont('Arial','',10);

$query = mysqli_query($conn, "
    SELECT arsip.nomor_surat, arsip.asal_surat, arsip.tanggal_surat,
           pemusnahan.tanggal_pemusnahan, users.username
    FROM pemusnahan
    JOIN arsip ON pemusnahan.id_arsip = arsip.id_arsip
    JOIN users ON pemusnahan.disetujui_oleh = users.id_user
    ORDER BY pemusnahan.tanggal_pemusnahan DESC
");

while ($row = mysqli_fetch_assoc($query)) {
    $pdf->Cell($width[0],8,$row['nomor_surat'],1);
    $pdf->Cell($width[1],8,$row['asal_surat'],1);
    $pdf->Cell($width[2],8,$row['tanggal_surat'],1);
    $pdf->Cell($width[3],8,$row['tanggal_pemusnahan'],1);
    $pdf->Cell($width[4],8,$row['username'],1);
    $pdf->Ln();
}

$pdf->Output("I","laporan_pemusnahan.pdf");
exit;
