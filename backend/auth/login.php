<?php
/**
 * Login Sistem Arsip Inaktif
 * - GET  : tampilkan form login
 * - POST : proses login
 */

session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../log/log_helper.php";

/* ==========================
   GUARD: SUDAH LOGIN?
   redirect sesuai role
========================== */
if (isset($_SESSION['id_user'], $_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: ../dashboard/admin.php");
            break;
        case 'staff':
            header("Location: ../dashboard/staff.php");
            break;
        case 'pimpinan':
            header("Location: ../dashboard/pimpinan.php");
            break;
        default:
            // role tidak valid → reset
            session_unset();
            session_destroy();
            header("Location: login.php");
    }
    exit;
}

$error = '';

/* ==========================
   PROSES LOGIN (POST)
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi";
    } else {

        // sementara pakai md5 (sesuai sistem kamu)
        $password_hash = md5($password);

        $username_db = mysqli_real_escape_string($conn, $username);

        $q = mysqli_query($conn, "
            SELECT id_user, username, role
            FROM users
            WHERE username='$username_db'
              AND password='$password_hash'
            LIMIT 1
        ");

        $user = mysqli_fetch_assoc($q);

        if ($user) {

            // SET SESSION
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            simpan_log($conn, $user['id_user'], "Login ke sistem");

            // redirect sesuai role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../dashboard/admin.php");
                    break;
                case 'staff':
                    header("Location: ../dashboard/staff.php");
                    break;
                case 'pimpinan':
                    header("Location: ../dashboard/pimpinan.php");
                    break;
                default:
                    // role tidak dikenal
                    session_unset();
                    session_destroy();
                    $error = "Role pengguna tidak valid";
            }
            exit;

        } else {
            $error = "Username atau password salah";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Sistem Arsip</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }
        .login-box {
            width: 350px;
            margin: 100px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
        }
        label { font-weight: bold; }
        input {
            width: 100%;
            padding: 8px;
            margin: 6px 0 12px;
        }
        button {
            width: 100%;
            padding: 8px;
            background: #2c7be5;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .error {
            background: #f8d7da;
            color: #842029;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
    </style>
</head>

<body>

<div class="login-box">
    <h3>🔐 Login Sistem Arsip Inaktif</h3>

    <?php if ($error !== '') { ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php } ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
