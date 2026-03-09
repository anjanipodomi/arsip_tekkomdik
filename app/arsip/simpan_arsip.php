<?php
session_start();

/* ===============================
   HELPER REDIRECT ERROR (WAJIB)
================================ */
include __DIR__ . "/../config/config.php";
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

function redirect_error($pesan) {
    $_SESSION['error'] = $pesan;
    $_SESSION['old']   = $_POST;
    header("Location: " . BASE_URL . "views/upload_arsip.php");
    exit;
}

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

/* ===============================
   VALIDASI FIELD WAJIB
================================ */
$wajib = [
    'asal_surat'           => 'Asal Surat',
    'nomor_surat'          => 'Nomor Surat',
    'tanggal_surat'        => 'Tanggal Surat',
    'isi_ringkas'          => 'Isi Ringkas',
    'klasifikasi_keamanan' => 'Klasifikasi Keamanan',
    'tingkat_perkembangan' => 'Tingkat Perkembangan',
    'id_kategori'          => 'Kategori Arsip',
    'id_box'               => 'Box Arsip'
];

foreach ($wajib as $field => $label) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        redirect_error("$label wajib diisi");
    }
}

/* ===============================
   AMBIL DATA FORM
================================ */
$asal    = $_POST['asal_surat'];
$nomor   = $_POST['nomor_surat'];
$tgl     = $_POST['tanggal_surat'];
$isi     = $_POST['isi_ringkas'];
$klas    = $_POST['klasifikasi_keamanan'];
$jumlah  = $_POST['jumlah_berkas'];
$tingkat = $_POST['tingkat_perkembangan'];
$id_kat  = $_POST['id_kategori'];
$id_box  = $_POST['id_box'];

/* ===============================
   VALIDASI TANGGAL (BACKEND)
================================ */
if (strtotime($tgl) > strtotime(date('Y-m-d'))) {
    redirect_error("Tanggal surat tidak boleh melebihi hari ini");
}

/* ===============================
   VALIDASI NOMOR SURAT DUPLIKAT
================================ */
$cek = mysqli_query($conn,"
    SELECT id_arsip FROM arsip
    WHERE nomor_surat = '$nomor'
");

if (mysqli_num_rows($cek) > 0) {
    redirect_error("Nomor surat sudah terdaftar");
}

/* ===============================
   VALIDASI FILE WAJIB
================================ */
if (!isset($_FILES['file_arsip']) || $_FILES['file_arsip']['error'] !== UPLOAD_ERR_OK) {
    redirect_error("File arsip wajib diunggah");
}

/* ===============================
   VALIDASI TIPE FILE
================================ */
$allowed_ext  = ['pdf','jpg','jpeg','png'];
$allowed_mime = ['application/pdf','image/jpeg','image/png'];

$ext  = strtolower(pathinfo($_FILES['file_arsip']['name'], PATHINFO_EXTENSION));
$mime = mime_content_type($_FILES['file_arsip']['tmp_name']);

if (!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mime)) {
    redirect_error("Format file harus PDF / JPG / PNG");
}

/* ===============================
   UPLOAD FILE
================================ */
$file_path = NULL;

if (!empty($_FILES['file_arsip']['name'])) {

    $dir = "../assets/uploads/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $nama   = time() . '_' . basename($_FILES['file_arsip']['name']);
    $target = $dir . $nama;

    if (!move_uploaded_file($_FILES['file_arsip']['tmp_name'], $target)) {
        redirect_error("Gagal mengunggah file arsip");
    }

    $file_path = "assets/uploads/" . $nama;
}

/* ===============================
   INSERT DATABASE
================================ */
$stmt = mysqli_prepare($conn, "
INSERT INTO arsip (
    asal_surat, nomor_surat, tanggal_surat, isi_ringkas,
    klasifikasi_keamanan, jumlah_berkas, tingkat_perkembangan,
    id_kategori, id_box, status_arsip, tanggal_input, file_path
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Inaktif', CURDATE(), ?)
");

mysqli_stmt_bind_param(
    $stmt,
    "sssssisiss",
    $asal,
    $nomor,
    $tgl,
    $isi,
    $klas,
    $jumlah,
    $tingkat,
    $id_kat,
    $id_box,
    $file_path
);

if (!mysqli_stmt_execute($stmt)) {
    redirect_error("Gagal menyimpan arsip: " . mysqli_error($conn));
}

$id = mysqli_insert_id($conn);

/* ===============================
   LOG AKTIVITAS
================================ */
simpan_log($conn, $_SESSION['id_user'], "Tambah arsip ID $id", "Arsip");

header("Location: " . BASE_URL . "views/arsip.php?success=1");
exit;