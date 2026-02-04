<?php
/**
 * Daftar Arsip Siap Dimusnahkan
 * Role: Pimpinan
 */

session_start();
include __DIR__ . "/../config/database.php";

// ==========================
// CEK LOGIN & ROLE
// ==========================
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pimpinan') {
    die("Akses ditolak");
}

$data = mysqli_query($conn, "
    SELECT id_arsip, nomor_surat, asal_surat, tanggal_surat
    FROM arsip
    WHERE status_arsip='Siap Musnah'
    ORDER BY tanggal_surat ASC
");
?>

<h2>🗑️ Persetujuan Pemusnahan Arsip</h2>

<table border="1" cellpadding="8" cellspacing="0">
<tr>
    <th>No</th>
    <th>Nomor Surat</th>
    <th>Asal Surat</th>
    <th>Tanggal Surat</th>
    <th>Aksi</th>
</tr>

<?php $no = 1; while ($row = mysqli_fetch_assoc($data)) { ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['nomor_surat']) ?></td>
    <td><?= htmlspecialchars($row['asal_surat']) ?></td>
    <td><?= htmlspecialchars($row['tanggal_surat']) ?></td>
    <td>
        <a href="approve.php?id=<?= $row['id_arsip'] ?>&aksi=setuju"
           onclick="return confirm('Setujui pemusnahan arsip ini?')">
           Setujui
        </a>
        |
        <a href="approve.php?id=<?= $row['id_arsip'] ?>&aksi=tolak"
           onclick="return confirm('Tolak pemusnahan arsip ini?')">
           Tolak
        </a>
    </td>
</tr>
<?php } ?>

<?php if (mysqli_num_rows($data) === 0) { ?>
<tr>
    <td colspan="5" align="center">Tidak ada arsip yang menunggu persetujuan</td>
</tr>
<?php } ?>
</table>
