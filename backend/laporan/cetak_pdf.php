<?php
session_start();
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../library/fpdf.php';

/* ==========================
   CEK LOGIN
========================== */
if (!isset($_SESSION['id_user']) || 
    !in_array($_SESSION['role'], ['admin','staff','pimpinan'])) {
    exit("Akses ditolak");
}

$jenis = $_GET['jenis'] ?? '';

/* ==========================
   PAGINATION (50 DATA)
========================== */
$limit = 50;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;
if (!in_array($jenis, ['arsip','retensi','pemusnahan','log'])) {
    exit("Jenis laporan tidak valid");
}

/* ==========================
   INIT PDF
========================== */
$pdf = new FPDF('L','mm','A4');
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

/* ==========================
   JUDUL
========================== */
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'LAPORAN '.strtoupper($jenis),0,1,'C');

$pdf->SetFont('Arial','',10);
$pdf->Cell(0,8,'Balai Teknologi Komunikasi Pendidikan (TEKKOMDIK) DIY',0,1,'C');
$pdf->Ln(5);

/* ==========================
   FUNGSI ROW MULTILINE + PAGE BREAK
========================== */
function rowMultiCell($pdf, $data, $width, $headerCallback, $lineHeight = 7)
{
    $maxLines = 1;
    foreach ($data as $i => $text) {
        $text = (string)$text;
        $lines = ceil($pdf->GetStringWidth($text) / ($width[$i] - 2));
        $maxLines = max($maxLines, $lines);
    }

    $rowHeight = $maxLines * $lineHeight;

    // PAGE BREAK AMAN
    if ($pdf->GetY() + $rowHeight > 185) {
        $pdf->AddPage();
        $headerCallback(); // header ulang
    }

    foreach ($data as $i => $text) {
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->Rect($x, $y, $width[$i], $rowHeight);
        $pdf->MultiCell($width[$i], $lineHeight, (string)$text, 0);
        $pdf->SetXY($x + $width[$i], $y);
    }
    $pdf->Ln($rowHeight);
}

/* ======================================================
   LAPORAN ARSIP
====================================================== */
if ($jenis === 'arsip') {

    $header = ['No','Nomor Surat','Asal Surat','Tanggal','Kategori','Box','Status'];
    $width  = [10,45,95,30,45,25,30];

    $drawHeader = function() use ($pdf, $header, $width) {
        $pdf->SetFont('Arial','B',9);
        foreach ($header as $i => $h) {
            $pdf->Cell($width[$i],8,$h,1,0,'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
    };

    $drawHeader();

    $q = mysqli_query($conn,"
        SELECT arsip.nomor_surat, arsip.asal_surat, arsip.tanggal_surat,
            arsip.status_arsip, kategori.nama_kategori, box.kode_box
        FROM arsip
        LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
        LEFT JOIN box ON arsip.id_box = box.id_box
        ORDER BY arsip.tanggal_surat DESC
        LIMIT $offset, $limit
    ");

    $no = $offset + 1;
    while ($r = mysqli_fetch_assoc($q)) {
        rowMultiCell($pdf, [
            $no++,
            $r['nomor_surat'],
            $r['asal_surat'],
            $r['tanggal_surat'],
            $r['nama_kategori'],
            $r['kode_box'],
            $r['status_arsip']
        ], $width, $drawHeader);
    }
}

/* ======================================================
   LAPORAN RETENSI
====================================================== */
if ($jenis === 'retensi') {

    $header = ['No','Nomor Surat','Kategori','Umur (Tahun)','Status Retensi'];
    $width  = [10,70,80,40,60];

    $drawHeader = function() use ($pdf, $header, $width) {
        $pdf->SetFont('Arial','B',9);
        foreach ($header as $i => $h) {
            $pdf->Cell($width[$i],8,$h,1,0,'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
    };

    $drawHeader();

    $q = mysqli_query($conn,"
        SELECT arsip.nomor_surat,
               kategori.nama_kategori,
               arsip.status_arsip,
               TIMESTAMPDIFF(YEAR, arsip.tanggal_surat, CURDATE()) AS umur
        FROM arsip
        LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
        ORDER BY umur DESC
        LIMIT $offset, $limit
    ");

    $no = $offset + 1; while ($r = mysqli_fetch_assoc($q)) {        rowMultiCell($pdf, [
            $no++,
            $r['nomor_surat'],
            $r['nama_kategori'],
            $r['umur'],
            $r['status_arsip']
        ], $width, $drawHeader);
    }
}

/* ======================================================
   LAPORAN PEMUSNAHAN
====================================================== */
if ($jenis === 'pemusnahan') {

    $header = ['No','Nomor Surat','Asal Surat','Tanggal Musnah','Disetujui Oleh'];
    $width  = [10,50,95,40,60];

    $drawHeader = function() use ($pdf, $header, $width) {
        $pdf->SetFont('Arial','B',9);
        foreach ($header as $i => $h) {
            $pdf->Cell($width[$i],8,$h,1,0,'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
    };

    $drawHeader();

    $q = mysqli_query($conn,"
        SELECT arsip.nomor_surat, arsip.asal_surat,
               pemusnahan.tanggal_pemusnahan,
               users.nama_lengkap
        FROM pemusnahan
        JOIN arsip ON pemusnahan.id_arsip = arsip.id_arsip
        JOIN users ON pemusnahan.disetujui_oleh = users.id_user
        ORDER BY pemusnahan.tanggal_pemusnahan DESC
        LIMIT $offset, $limit
    ");

    $no = $offset + 1;    while ($r = mysqli_fetch_assoc($q)) {
        rowMultiCell($pdf, [
            $no++,
            $r['nomor_surat'],
            $r['asal_surat'],
            $r['tanggal_pemusnahan'],
            $r['nama_lengkap']
        ], $width, $drawHeader);
    }
}

/* ======================================================
   LAPORAN LOG AKTIVITAS
====================================================== */
if ($jenis === 'log') {

    $header = ['No','Nama User','Aktivitas','Modul','Waktu'];
    $width  = [10,45,120,40,55];

    $drawHeader = function() use ($pdf, $header, $width) {
        $pdf->SetFont('Arial','B',9);
        foreach ($header as $i => $h) {
            $pdf->Cell($width[$i],8,$h,1,0,'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
    };

    $drawHeader();

    $q = mysqli_query($conn,"
        SELECT users.nama_lengkap, log_aktivitas.aktivitas,
               log_aktivitas.modul, log_aktivitas.tanggal
        FROM log_aktivitas
        LEFT JOIN users ON log_aktivitas.id_user = users.id_user
        ORDER BY log_aktivitas.tanggal DESC
        LIMIT $offset, $limit
    ");

    $no = $offset + 1;    while ($r = mysqli_fetch_assoc($q)) {
        rowMultiCell($pdf, [
            $no++,
            $r['nama_lengkap'] ?? '-',
            $r['aktivitas'],
            $r['modul'],
            date('d-m-Y H:i', strtotime($r['tanggal']))
        ], $width, $drawHeader);
    }
}

ob_end_clean();
$pdf->Output('D', 'laporan_'.$jenis.'_'.date('Ymd_His').'.pdf');
exit;
