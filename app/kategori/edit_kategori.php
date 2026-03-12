<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id = $_GET['id'] ?? '';
$q  = mysqli_query($conn,"SELECT * FROM kategori WHERE id_kategori='$id'");
$d  = mysqli_fetch_assoc($q);

if (!$d) die("Kategori tidak ditemukan");

if ($d['status']==='nonaktif'){
    echo "<script>
        alert('Status NONAKTIF! Kategori tidak bisa diedit.');
        window.location='index.php';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Kategori</title></head>
<body>

<h3>✏️ Edit Kategori</h3>

<?php if (isset($_SESSION['error'])): ?>
    <div style="
        background:#fff3cd;
        color:#856404;
        padding:12px;
        border-left:5px solid #f0ad4e;
        margin:15px 0;
        border-radius:4px;
    ">
        ⚠️ <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST" action="simpan_kategori.php">
    <input type="hidden" name="id_kategori" value="<?= $d['id_kategori'] ?>">
    Klasifikasi Kategori
    <input type="text" name="klasifikasi_kategori"
    value="<?= htmlspecialchars($d['klasifikasi_kategori']) ?>"
    required><br><br>

    Nama Kategori<br>
    <input type="text" name="nama_kategori"
           value="<?= htmlspecialchars($d['nama_kategori']) ?>" required><br><br>

    <button type="submit">💾 Update</button>
    <a href="index.php">Batal</a>
</form>

</body>
</html>
