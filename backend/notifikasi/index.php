<?php
/**
 * Daftar Notifikasi
 * Role : Admin & Pimpinan
 */

session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    exit("Akses ditolak");
}

$id_user = $_SESSION['id_user'];

// Tandai notif baru sebagai dibaca
mysqli_query($conn, "
    UPDATE notifikasi
    SET status = 'dibaca'
    WHERE id_user = '$id_user'
      AND status = 'baru'
");


$q = mysqli_query($conn, "
    SELECT pesan, link, status, tanggal
    FROM notifikasi
    WHERE id_user = '$id_user'
      AND status IN ('baru','dibaca')
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
.baru{font-weight:bold}
a{text-decoration:none;color:#2563eb}

.selesai {
    color: #6b7280;       /* abu-abu */
    font-style: italic;
}

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
    <th>Status</th>
</tr>

<?php
$no = 1;

if (mysqli_num_rows($q) === 0) {
    echo "<tr><td colspan='4' align='center'>Tidak ada notifikasi</td></tr>";
}

while ($n = mysqli_fetch_assoc($q)) {
    $class = match ($n['status']) {
        'baru'    => 'baru',
        'selesai' => 'selesai',
        default   => ''
    };

?>
<tr class="<?= $class ?>">
    <td><?= $no++ ?></td>
    <td>
        <?php if (!empty($n['link'])) { ?>
            <a href="<?= htmlspecialchars($n['link']) ?>" target="_self">
                <?= htmlspecialchars($n['pesan']) ?>
            </a>
        <?php } else { ?>
            <?= htmlspecialchars($n['pesan']) ?>
        <?php } ?>
    </td>
    <td><?= date('d-m-Y H:i', strtotime($n['tanggal'])) ?></td>
    <td><?= ucfirst($n['status']) ?></td>
</tr>
<?php } ?>
</table>

<br>
<a href="../dashboard/<?= $_SESSION['role'] ?>.php">⬅ Kembali ke Dashboard</a>

</div>

</body>
</html>

<?php
mysqli_query($conn, "
    UPDATE notifikasi
    SET status = 'dibaca'
    WHERE id_user = '$id_user'
      AND status = 'baru'
");
?>
