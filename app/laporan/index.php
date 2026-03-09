<?php
session_start();
if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','operator','pimpinan'])) {
    die("Akses ditolak");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Laporan Sistem Arsip</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{
    width:600px;
    margin:40px auto;
    background:#fff;
    padding:25px;
    border-radius:8px
}
a{
    display:block;
    margin:12px 0;
    font-size:16px;
    text-decoration:none;
}
</style>
</head>
<body>

<div class="container">
<h2>📑 Laporan Sistem Arsip</h2>

<a href="arsip.php">📂 Laporan Data Arsip</a>
<a href="retensi.php">⏳ Laporan Retensi Arsip</a>
<a href="pemusnahan.php">🔥 Laporan Pemusnahan Arsip</a>

<?php if (in_array($_SESSION['role'], ['admin','pimpinan'])) { ?>
<a href="log.php">🧾 Laporan Log Aktivitas</a>
<?php } ?>

<br>
<a href="../dashboard/admin.php">⬅ Kembali ke Dashboard</a>
</div>

</body>
</html>
