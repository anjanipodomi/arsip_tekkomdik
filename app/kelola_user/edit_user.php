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
    $password2    = $_POST['password2'] ?? '';
    $role         = $_POST['role'] ?? '';

    if ($id_user === '' || $nama_lengkap === '' || $username === '' || $role === '') {
        $_SESSION['error'] = "Data tidak lengkap";
        header("Location: ../../views/edit_user.php?id=$id_user");        exit;
    }

    $lama = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'")
    );

    if (!$lama) {
        $_SESSION['error'] = "User tidak ditemukan";
        header("Location: ../../views/kelola_user.php");
        exit;
    }

    // Cek username sudah dipakai atau belum
    $cek = mysqli_query($conn, "
        SELECT id_user FROM users
        WHERE username='$username' AND id_user != '$id_user'
    ");

    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = "Username sudah digunakan";
        header("Location: ../../views/edit_user.php?id=$id_user");        exit;
    }

    // Jika password diisi, wajib sama
    if ($password !== '') {
        if ($password !== $password2) {
            $_SESSION['error'] = "Konfirmasi password tidak sama";
        header("Location: ../../views/edit_user.php?id=$id_user");            exit;
        }
    }


    $tidak_berubah =
        trim($lama['nama_lengkap']) === $nama_lengkap &&
        trim($lama['username']) === $username &&
        $lama['role'] === $role &&
        trim($password) === '';

    if ($tidak_berubah) {
        $_SESSION['error'] = "Tidak ada perubahan data";
        header("Location: ../../views/edit_user.php?id=$id_user");        exit;
    }

    if ($password !== '') {
        $password_hash = md5($password); // nanti kita bisa upgrade ke password_hash()
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

    $_SESSION['success'] = "Data user berhasil diperbarui";
    header("Location: ../../views/kelola_user.php");
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

<h3>✏️ Edit User</h3>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background:#fff3cd;color:#856404;padding:10px;margin-bottom:15px;">
        ⚠️ <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST">

    <input type="hidden" name="id_user" value="<?= $data['id_user'] ?>">

    Nama Lengkap<br>
    <input type="text" name="nama_lengkap"
           value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required><br><br>

    Username<br>
    <input type="text" name="username"
           value="<?= htmlspecialchars($data['username']) ?>" required><br><br>

    Password Baru (kosongkan jika tidak diubah)<br>
    <input type="password" name="password" id="password"><br><br>

    Konfirmasi Password<br>
    <input type="password" name="password2" id="password2"><br><br>

    Role<br>
    <select name="role" required>
        <option value="staff" <?= $data['role']=='staff'?'selected':'' ?>>Staff</option>
        <option value="pimpinan" <?= $data['role']=='pimpinan'?'selected':'' ?>>Pimpinan</option>
    </select><br><br>

    <button type="submit">💾 Update</button>
    <a href="index.php">Batal</a>

</form>

<script>
function togglePassword() {
    const pass = document.getElementById("password");
    const btn  = document.querySelector(".toggle-password");

    if (pass.type === "password") {
        pass.type = "text";
        btn.textContent = "🙈";
    } else {
        pass.type = "password";
        btn.textContent = "👁";
    }
}
</script>

</body>
</html>
