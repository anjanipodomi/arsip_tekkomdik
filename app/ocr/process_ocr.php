<?php
/**
 * PROSES OCR ARSIP (FINAL)
 */

session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

// ================= CEK LOGIN =================
if (!isset($_SESSION['id_user'])) die("Akses ditolak");

$id_user  = $_SESSION['id_user'];
$id_arsip = $_GET['id'] ?? '';

if ($id_arsip === '') die("ID arsip tidak valid");

// ================= PATH TOOL =================
$tesseract = 'C:\Tesseract-OCR\tesseract.exe';
$pdftoppm  = 'C:\poppler\Library\bin\pdftoppm.exe';

// ================= DATA ARSIP =================
$q = mysqli_query($conn,"SELECT file_path FROM arsip WHERE id_arsip='$id_arsip'");
$a = mysqli_fetch_assoc($q);
if (!$a) die("Arsip tidak ditemukan");

$file = "../".$a['file_path'];
if (!file_exists($file)) die("File arsip tidak ditemukan");

// ================= TMP =================
$tmpDir = "../assets/ocr/tmp";
if (!is_dir($tmpDir)) mkdir($tmpDir,0777,true);

$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
$text = '';

// ================= OCR =================
if ($ext === 'pdf') {

    $imgBase = $tmpDir.'/pdf_'.uniqid();
    exec("\"$pdftoppm\" -png \"$file\" \"$imgBase\"");

    foreach (glob($imgBase.'-*.png') as $img) {
        $out = $tmpDir.'/out_'.uniqid();
        exec("\"$tesseract\" \"$img\" \"$out\" -l ind --oem 1 --psm 6");
        if (file_exists("$out.txt")) {
            $text .= file_get_contents("$out.txt")."\n";
        }
    }

} else {

    $out = $tmpDir.'/out_'.uniqid();
    exec("\"$tesseract\" \"$file\" \"$out\" -l ind --oem 1 --psm 6");
    if (file_exists("$out.txt")) {
        $text = file_get_contents("$out.txt");
    }
}

// ================= SIMPAN =================
if (trim($text) !== '') {

    $safe = mysqli_real_escape_string($conn,$text);

    mysqli_query($conn,"
        INSERT INTO hasil_ocr (id_arsip, teks_ocr, tanggal_ocr)
        VALUES ('$id_arsip','$safe',NOW())
    ");

    mysqli_query($conn,"UPDATE arsip SET status_ocr='Sukses' WHERE id_arsip='$id_arsip'");
    simpan_log($conn,$id_user,"OCR sukses arsip $id_arsip");

} else {

    mysqli_query($conn,"UPDATE arsip SET status_ocr='Gagal' WHERE id_arsip='$id_arsip'");
    simpan_log($conn,$id_user,"OCR gagal arsip $id_arsip");
}

header("Location: ../arsip/detail_arsip.php?id=$id_arsip");
exit;
