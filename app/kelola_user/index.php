<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$users = mysqli_query($conn, "
    SELECT id_user, nama_lengkap, username, role, created_at
    FROM users
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Kelola User</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:90%;margin:30px auto;background:#fff;padding:20px}
table{width:100%;border-collapse:collapse}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:#ddd}
.btn{padding:6px 10px;text-decoration:none;border-radius:4px;color:#fff}
.tambah{background:#9b59b6}
.edit{background:#3498db}
.hapus{background:#e74c3c}
</style>
</head>
<body>

<div class="container">
<h2>👥 Kelola User</h2>

<a href="tambah_user.php" class="btn tambah">➕ Tambah User</a><br><br>

<table>
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>Username</th>
    <th>Role</th>
    <th>Tanggal Dibuat</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($u=mysqli_fetch_assoc($users)){ ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
    <td><?= htmlspecialchars($u['username']) ?></td>
    <td><?= ucfirst($u['role']) ?></td>
    <td><?= $u['created_at'] ?></td>
    <td>
        <a href="edit_user.php?id=<?= $u['id_user'] ?>" class="btn edit">Edit</a>
        <a href="hapus_user.php?id=<?= $u['id_user'] ?>" class="btn hapus"
           onclick="return confirm('Hapus user ini?')">Hapus</a>
    </td>
</tr>
<?php } ?>
</table>

</div>
</body>
</html>
