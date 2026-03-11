<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$data = mysqli_query($conn, "
    SELECT * FROM kategori
    ORDER BY nama_kategori
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Kategori Arsip</title>
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

<h3>📁 Kategori Arsip</h3>

<button onclick="tambahKategori()">➕ Tambah Kategori</button>
<br><br>

<table>
<tr>
    <th>No</th>
    <th>Klasifikasi</th>
    <th>Nama Kategori</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($r=mysqli_fetch_assoc($data)){ ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($r['klasifikasi_kategori']) ?></td>
    <td><?= htmlspecialchars($r['nama_kategori']) ?></td>

    <td>
        <?php if ($r['status']==='aktif'){ ?>
            <span class="status-aktif">AKTIF</span>
        <?php } else { ?>
            <span class="status-nonaktif">NONAKTIF</span>
        <?php } ?>
    </td>

    <td>
        <a href="edit_kategori.php?id=<?= $r['id_kategori'] ?>">Edit</a> |
        <?php if ($r['status']==='aktif'){ ?>
            <a href="toggle_status.php?id=<?= $r['id_kategori'] ?>&aksi=nonaktif"
               onclick="return confirm('Nonaktifkan kategori ini?')">🔴 Nonaktifkan</a>
        <?php } else { ?>
            <a href="toggle_status.php?id=<?= $r['id_kategori'] ?>&aksi=aktif"
               onclick="return confirm('Aktifkan kembali kategori ini?')">🟢 Aktifkan</a>
        <?php } ?>
    </td>
</tr>
<?php } ?>

</table>

<script>
function tambahKategori(){
    const klasifikasi = prompt("Kode klasifikasi (contoh: 001)");
    if(!klasifikasi) return;

    const nama = prompt("Nama kategori:");
    if(!nama) return;

    const fd = new FormData();
    fd.append('klasifikasi_kategori', klasifikasi);
    fd.append('nama_kategori', nama);

    fetch('ajax_tambah.php',{ method:'POST', body:fd })
    .then(r=>r.json())
    .then(res=>{
        if(res.status!=='ok'){ alert(res.pesan); return; }
        alert('Kategori berhasil ditambahkan');
        location.reload();
    });
}
</script>

</body>
</html>
