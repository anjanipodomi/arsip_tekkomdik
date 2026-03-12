<?php
session_start();
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../library/fpdf.php';

function tglIndo($tanggal)
{
    if(!$tanggal) return '-';
    return date('d/m/Y', strtotime($tanggal));
}

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
class PDF extends FPDF
{
    function Header()
    {
        $this->Image(__DIR__.'/../../assets/kop_balai.png',10,8,277);
        $this->Ln(70);
    }
}
$pdf = new PDF('L','mm','A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true,20);
$pdf->AddPage();

$pdf->SetLeftMargin(10);
$pdf->SetRightMargin(10);


/* ==========================
   JUDUL
========================== */
$status = $_GET['status'] ?? '';

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,8,'LAPORAN '.strtoupper($jenis),0,1,'C');

if(!empty($status)){
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(0,6,'Status Arsip : '.strtoupper($status),0,1,'C');
}

$pdf->Ln(5);

/* ==========================
   FUNGSI ROW MULTILINE + PAGE BREAK
========================== */
function rowMultiCell($pdf, $data, $width, $headerCallback, $lineHeight = 5)
{
    $maxLines = 1;

    foreach ($data as $i => $text) {

        $text = (string)$text;

        $nb = ceil(
            $pdf->GetStringWidth($text) / ($width[$i] - 4)
        );

        if($nb < 1) $nb = 1;

        if($nb > $maxLines){
            $maxLines = $nb;
        }
    }

    $rowHeight = $maxLines * $lineHeight;

    /* PAGE BREAK AMAN */
    if ($pdf->GetY() + $rowHeight > 185) {
        $pdf->AddPage();
        $headerCallback();
    }

    $xStart = $pdf->GetX();
    $yStart = $pdf->GetY();

    foreach ($data as $i => $text) {

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        $pdf->Rect($x, $y, $width[$i], $rowHeight);

        $pdf->MultiCell(
            $width[$i]-2,
            $lineHeight,
            utf8_decode((string)$text),
            0,
            'L'
        );

        $pdf->SetXY($x + $width[$i], $y);
    }

    $pdf->Ln($rowHeight);
}

/* ======================================================
   LAPORAN ARSIP
====================================================== */
if ($jenis === 'arsip') {

    $header = ['No','Nomor Surat','Asal Surat','Tanggal','Kategori','Box','Status'];
    $width  = [15,45,105,30,35,20,27];

    $drawHeader = function() use ($pdf, $header, $width) {

        $pdf->SetFont('Arial','B',10);

        $pdf->SetFillColor(220,220,220);

        foreach ($header as $i => $h) {
            $pdf->Cell($width[$i],8,$h,1,0,'C',true);
        }

        $pdf->Ln();

        $pdf->SetFont('Arial','',10);
    };

    $drawHeader();

    $perPage = 20;
    $rowCount = 0;

    $status = $_GET['status'] ?? '';

    $q = mysqli_query($conn,"
    SELECT 
        arsip.nomor_surat,
        arsip.asal_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        box.kode_box
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    ".($status ? "WHERE arsip.status_arsip='".mysqli_real_escape_string($conn,$status)."'" : "")."
    ORDER BY arsip.tanggal_surat DESC
    LIMIT $offset, $limit
    ");

    $no = $offset + 1;
    $perPage = 20;
    $rowCount = 0;
    while ($r = mysqli_fetch_assoc($q)) {

        if($rowCount == $perPage){
            $pdf->AddPage();
            $drawHeader();
            $rowCount = 0;
        }

        rowMultiCell($pdf, [
            $no++,
            $r['nomor_surat'],
            $r['asal_surat'],
            tglIndo($r['tanggal_surat']),
            $r['nama_kategori'],
            $r['kode_box'],
            $r['status_arsip']
        ], $width, $drawHeader);

        $rowCount++;
    }
}

/* ======================================================
   LAPORAN RETENSI
====================================================== */
if ($jenis === 'retensi') {

    $header = ['No','Nomor Surat','Kategori','Umur (Tahun)','Status Retensi'];
    $width  = [15,78,90,35,59];

    $drawHeader = function() use ($pdf, $header, $width) {
        $pdf->SetFont('Arial','B',9);
        $pdf->SetFillColor(220,220,220);
        foreach ($header as $i => $h) {
            $pdf->Cell($width[$i],8,$h,1,0,'C',true);
        }
        $pdf->Ln();
        $pdf->SetFont('Arial','',8);
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
    $width  = [15,60,105,40,57];

    $drawHeader = function() use ($pdf, $header, $width) {
        $pdf->SetFont('Arial','B',9);
        $pdf->SetFillColor(220,220,220);
        foreach ($header as $i => $h) {
            $pdf->Cell($width[$i],8,$h,1,0,'C',true);
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
            tglIndo($r['tanggal_pemusnahan']),
            $r['nama_lengkap']
        ], $width, $drawHeader);
    }
}

/* ======================================================
   LAPORAN LOG AKTIVITAS
====================================================== */
if ($jenis === 'log') {

    $header = ['No','Nama User','Aktivitas','Modul','Waktu'];
    $width  = [15,45,115,40,62];

    $drawHeader = function() use ($pdf, $header, $width) {

        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(220,220,220);

        foreach ($header as $i => $h) {
            $pdf->Cell($width[$i],6,$h,1,0,'C',true);
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

    $no = $offset + 1;

    while ($r = mysqli_fetch_assoc($q)) {

        rowMultiCell($pdf, [
            $no++,
            $r['nama_lengkap'] ?? '-',
            $r['aktivitas'],
            $r['modul'],
            tglIndo($r['tanggal']).' '.date('H:i', strtotime($r['tanggal']))
        ], $width, $drawHeader);

    }
}

ob_end_clean();

$pdf->Output('D', 'laporan_'.$jenis.'_'.date('Ymd_His').'.pdf');
exit;
