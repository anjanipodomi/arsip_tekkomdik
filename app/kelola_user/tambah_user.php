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
<<<<<<< HEAD

<script>
function togglePass(id){
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
}
</script>

=======
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de
<body>

<div class="container">
<h3>➕ Tambah User</h3>

<form method="POST" action="simpan_user.php">
    <label>Nama Lengkap</label>
    <input type="text" name="nama_lengkap" required>

    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
<<<<<<< HEAD
    <div style="position:relative">
    <input type="password" name="password" id="password" required>
    <span onclick="togglePass('password')" style="position:absolute;right:10px;top:8px;cursor:pointer">👁</span>
    </div>
=======
    <input type="password" name="password" required>
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de

    <label>Role</label>
    <select name="role" required>
        <option value="">-- Pilih --</option>
        <option value="staff">Staff</option>
        <option value="pimpinan">Pimpinan</option>
    </select>

    <button type="submit">Simpan</button>
</form>
</div>
<<<<<<< HEAD
</body>

=======

</body>
>>>>>>> 52e3a4bcc0afc093f685ce77eddfbd5cc03f96de
</html>
