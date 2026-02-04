<?php
/**
 * Daftar Notifikasi Admin
 */

session_start();
include __DIR__ . "/../config/database.php";

// ==========================
// CEK LOGIN & ROLE
// ==========================
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$id_user = $_SESSION['id_user'];

// ==========================
// AMBIL NOTIFIKASI
// ==========================
$data = mysqli_query($conn, "
    SELECT pesan, tanggal, status
    FROM notifikasi
    WHERE id_user='$id_user'
    ORDER BY tanggal DESC
");
?>

<h2>🔔 Notifikasi Sistem</h2>

<table border="1" cellpadding="8" cellspacing="0">
<tr>
    <th>No</th>
    <th>Pesan</th>
    <th>Tanggal</th>
    <th>Status</th>
</tr>

<?php
$no = 1;
while ($n = mysqli_fetch_assoc($data)) {
    $style = ($n['status'] === 'baru') ? "style='font-weight:bold;'" : "";
?>
<tr <?= $style ?>>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($n['pesan']) ?></td>
    <td><?= $n['tanggal'] ?></td>
    <td><?= ucfirst($n['status']) ?></td>
</tr>
<?php } ?>

<?php if (mysqli_num_rows($data) === 0) { ?>
<tr>
    <td colspan="4" align="center">Tidak ada notifikasi</td>
</tr>
<?php } ?>
</table>

<?php
// ==========================
// TANDAI SEBAGAI DIBACA
// ==========================
mysqli_query($conn, "
    UPDATE notifikasi 
    SET status='dibaca'
    WHERE id_user='$id_user' AND status='baru'
");
?>
