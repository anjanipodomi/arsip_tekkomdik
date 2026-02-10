<?php
session_start();
include __DIR__ . "/../config/database.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

/* ==========================
   PROSES UPDATE (POST)
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_user      = $_POST['id_user'] ?? '';
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $password     = $_POST['password'] ?? '';
    $role         = $_POST['role'] ?? '';

    if ($id_user === '' || $nama_lengkap === '' || $username === '' || $role === '') {
        die("Data tidak lengkap");
    }

    // cek username duplikat (kecuali dirinya sendiri)
    $cek = mysqli_query($conn, "
        SELECT id_user FROM users
        WHERE username='$username' AND id_user != '$id_user'
    ");
    if (mysqli_num_rows($cek) > 0) {
        die("Username sudah digunakan");
    }

    if ($password !== '') {
        $password_hash = md5($password);
        mysqli_query($conn, "
            UPDATE users SET
                nama_lengkap='$nama_lengkap',
                username='$username',
                password='$password_hash',
                role='$role'
            WHERE id_user='$id_user'
        ");
    } else {
        mysqli_query($conn, "
            UPDATE users SET
                nama_lengkap='$nama_lengkap',
                username='$username',
                role='$role'
            WHERE id_user='$id_user'
        ");
    }

    header("Location: index.php");
    exit;
}

/* ==========================
   TAMPILKAN FORM (GET)
========================== */
$id = $_GET['id'] ?? '';
if ($id === '') {
    die("ID user tidak valid");
}

$data = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id'")
);

if (!$data) {
    die("User tidak ditemukan");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit User</title>
</head>
<body>

<h3>Edit User</h3>

<form method="POST">

    <input type="hidden" name="id_user" value="<?= $data['id_user'] ?>">

    Nama Lengkap<br>
    <input type="text" name="nama_lengkap"
           value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required><br><br>

    Username<br>
    <input type="text" name="username"
           value="<?= htmlspecialchars($data['username']) ?>" required><br><br>

    Password (kosongkan jika tidak diubah)<br>
    <input type="password" name="password"><br><br>

    Role<br>
    <select name="role" required>
        <option value="staff" <?= $data['role']=='staff'?'selected':'' ?>>Staff</option>
        <option value="pimpinan" <?= $data['role']=='pimpinan'?'selected':'' ?>>Pimpinan</option>
    </select><br><br>

    <button type="submit">Update</button>
</form>

</body>
</html>
