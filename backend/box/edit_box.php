<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id = $_GET['id'] ?? '';
if ($id === '') {
    die("ID box tidak valid");
}

$q = mysqli_query($conn, "SELECT * FROM box WHERE id_box='$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Data box tidak ditemukan");
}

/* ==========================
   BLOK EDIT JIKA NONAKTIF
========================== */
if ($data['status'] === 'nonaktif') {
    echo "<script>
        alert('Status NONAKTIF! Tidak bisa edit box.');
        window.location.href = 'index.php';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Box Arsip</title>
</head>
<body>

<h3>✏️ Edit Box Arsip</h3>

<form method="POST" action="simpan_box.php">
    <input type="hidden" name="id_box" value="<?= $data['id_box'] ?>">

    <label>Kode Box</label><br>
    <input type="text" name="kode_box"
           value="<?= htmlspecialchars($data['kode_box']) ?>" required><br><br>

    <label>Lokasi Fisik</label><br>
    <input type="text" name="lokasi_fisik"
           value="<?= htmlspecialchars($data['lokasi_fisik']) ?>" required><br><br>

    <button type="submit">💾 Update</button>
    <a href="index.php">Batal</a>
</form>

</body>
</html>
