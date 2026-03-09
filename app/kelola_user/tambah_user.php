<?php
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah User</title>
<style>
body{font-family:Arial;background:#f4f6f8}
.container{width:400px;margin:40px auto;background:#fff;padding:20px}
input,select{width:100%;padding:8px;margin-top:6px}
button{margin-top:10px;padding:8px;width:100%}
</style>
</head>
<body>

<div class="container">
<h3>➕ Tambah User</h3>

<form method="POST" action="simpan_user.php">
    <label>Nama Lengkap</label>
    <input type="text" name="nama_lengkap" required>

    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Role</label>
    <select name="role" required>
        <option value="">-- Pilih --</option>
        <option value="staff">Staff</option>
        <option value="pimpinan">Pimpinan</option>
    </select>

    <button type="submit">Simpan</button>
</form>
</div>

</body>
</html>
