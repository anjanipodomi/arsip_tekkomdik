<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$data = mysqli_query($conn, "
    SELECT * FROM box
    ORDER BY kode_box
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Box Arsip</title>
<style>
table { border-collapse: collapse; width:75%; }
th,td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#eee; }
.status-aktif { color:green; font-weight:bold; }
.status-nonaktif { color:red; font-weight:bold; }
button { padding:6px 10px; cursor:pointer; }
a { text-decoration:none; margin:0 4px; }
</style>
</head>
<body>

<h3>📦 Box Arsip</h3>

<!-- TOMBOL AJAX TAMBAH BOX -->
<button onclick="tambahBox()">➕ Tambah Box</button>

<br><br>

<table>
<tr>
    <th>No</th>
    <th>Kode Box</th>
    <th>Lokasi Fisik</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;
while($r = mysqli_fetch_assoc($data)){
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($r['kode_box']) ?></td>
    <td><?= htmlspecialchars($r['lokasi_fisik']) ?></td>

    <td>
        <?php if ($r['status'] === 'aktif') { ?>
            <span class="status-aktif">AKTIF</span>
        <?php } else { ?>
            <span class="status-nonaktif">NONAKTIF</span>
        <?php } ?>
    </td>

    <td>
        <a href="edit_box.php?id=<?= $r['id_box'] ?>">Edit</a> |

        <?php if ($r['status'] === 'aktif') { ?>
            <a href="toggle_status.php?id=<?= $r['id_box'] ?>&aksi=nonaktif"
               onclick="return confirm('Nonaktifkan box ini?')">
               🔴 Nonaktifkan
            </a>
        <?php } else { ?>
            <a href="toggle_status.php?id=<?= $r['id_box'] ?>&aksi=aktif"
               onclick="return confirm('Aktifkan kembali box ini?')">
               🟢 Aktifkan
            </a>
        <?php } ?>
    </td>
</tr>
<?php } ?>

</table>

<script>
/* ==========================
   AJAX TAMBAH BOX (FINAL)
========================== */
function tambahBox(){
    const kode = prompt("Masukkan Kode Box:");
    if(!kode) return;

    const lokasi = prompt("Masukkan Lokasi Fisik Box:");
    if(!lokasi) return;

    const fd = new FormData();
    fd.append('kode_box', kode);
    fd.append('lokasi_fisik', lokasi);

    fetch('ajax_tambah.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        if(res.status !== 'ok'){
            alert(res.pesan);
            return;
        }
        alert('✅ Box berhasil ditambahkan');
        location.reload(); // refresh tabel
    })
    .catch(err => {
        console.error(err);
        alert('❌ Gagal menambahkan box');
    });
}
</script>

</body>
</html>
