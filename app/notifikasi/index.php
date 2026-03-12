<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    exit("Akses ditolak");
}

$id_user = $_SESSION['id_user'];
$role    = $_SESSION['role'];

/* ==========================
   AUTO HAPUS NOTIFIKASI > 3 BULAN
========================== */
mysqli_query($conn, "
    DELETE FROM notifikasi
    WHERE tanggal < DATE_SUB(NOW(), INTERVAL 3 MONTH)
");

$q = mysqli_query($conn, "
    SELECT pesan, link, tanggal
    FROM notifikasi
    WHERE id_user = '$id_user'
    ORDER BY tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Notifikasi Sistem</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:90%;margin:30px auto;background:#fff;padding:20px;border-radius:8px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ccc;padding:8px}
th{background:#eee}
a{text-decoration:none;color:#2563eb}
</style>
</head>
<body>

<div class="container">

<h2>🔔 Notifikasi Sistem</h2>

<table>
<tr>
    <th>No</th>
    <th>Pesan</th>
    <th>Tanggal</th>
</tr>

<?php
$no = 1;

if (mysqli_num_rows($q) === 0) {
    echo "<tr><td colspan='3' align='center'>Tidak ada notifikasi</td></tr>";
}

while ($n = mysqli_fetch_assoc($q)) {
?>
<tr>
    <td><?= $no++ ?></td>
    <td>
        <?php
        // 🔥 ADMIN TIDAK PAKAI LINK
        if ($role === 'pimpinan' && !empty($n['link'])) {
        ?>
            <a href="<?= htmlspecialchars($n['link']) ?>">
                <?= htmlspecialchars($n['pesan']) ?>
            </a>
        <?php
        } else {
            echo htmlspecialchars($n['pesan']);
        }
        ?>
    </td>
    <td><?= date('d/m/y H:i', strtotime($n['tanggal'])) ?></td>
</tr>
<?php } ?>
</table>

<br>
<a href="../dashboard/<?= $_SESSION['role'] ?>.php">⬅ Kembali ke Dashboard</a>

</div>

</body>
</html>