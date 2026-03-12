<?php
session_start();
include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user'])) {
    die("Akses ditolak");
}

// ==========================
// AMBIL ID
// ==========================
$id = $_GET['id'] ?? $_POST['id_arsip'] ?? '';
if ($id === '') {
    die("ID arsip tidak valid");
}

$pesan = '';
$tipe_pesan = '';

// ==========================
// AMBIL DATA LAMA
// ==========================
$q = mysqli_query($conn, "SELECT * FROM arsip WHERE id_arsip='$id'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Data arsip tidak ditemukan");
}

// ==========================
// PROSES UPDATE
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $asal  = trim($_POST['asal_surat']);
    $nomor = trim($_POST['nomor_surat']);
    $tgl   = $_POST['tanggal_surat'];
    $isi   = trim($_POST['isi_ringkas']);

    // ==========================
    // VALIDASI WAJIB #2
    // FIELD TIDAK BOLEH KOSONG
    // ==========================
    if ($asal === '' || $nomor === '' || $tgl === '' || $isi === '') {

        $pesan = "❌ Semua field wajib diisi, data tidak boleh kosong";
        $tipe_pesan = "warning";

    }
    
    // ==========================
    // VALIDASI TANGGAL SURAT
    // ==========================
    $hari_ini = date('Y-m-d');
    if ($tgl > $hari_ini) {
        $pesan = "❌ Tanggal surat tidak boleh melebihi tanggal hari ini";
        $tipe_pesan = "warning";
    }

    // ==========================
    // CEK PERUBAHAN DATA
    // ==========================
    else if (
        $asal  === $data['asal_surat'] &&
        $nomor === $data['nomor_surat'] &&
        $tgl   === $data['tanggal_surat'] &&
        $isi   === $data['isi_ringkas']
    ) {

        $pesan = "⚠️ Tidak ada perubahan data";
        $tipe_pesan = "warning";

    } else {

        $asal_db  = mysqli_real_escape_string($conn, $asal);
        $nomor_db = mysqli_real_escape_string($conn, $nomor);
        $isi_db   = mysqli_real_escape_string($conn, $isi);

        mysqli_query($conn, "
            UPDATE arsip SET
                asal_surat='$asal_db',
                nomor_surat='$nomor_db',
                tanggal_surat='$tgl',
                isi_ringkas='$isi_db'
            WHERE id_arsip='$id'
        ");

        // 🔥 REDIRECT KE DETAIL ARSIP
        header("Location: detail_arsip.php?id=$id&updated=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Arsip</title>

<style>
body {
    font-family: Arial, sans-serif;
    background:#f4f6f8;
}
.container {
    width: 70%;
    margin: 30px auto;
    background:#fff;
    padding: 25px;
    border-radius: 6px;
}
.back {
    text-decoration:none;
    font-size:20px;
}
.warning {
    color:#e67e22;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="container">

<!-- ⬅️ BACK ICON -->
<a href="detail_arsip.php?id=<?= $data['id_arsip'] ?>" class="back">⬅️</a>

<h2>Edit Arsip</h2>

<?php if ($pesan): ?>
    <p class="<?= $tipe_pesan ?>"><?= $pesan ?></p>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="id_arsip" value="<?= $data['id_arsip'] ?>">

    Asal Surat<br>
    <input type="text" name="asal_surat"
           value="<?= htmlspecialchars($data['asal_surat']) ?>" required><br><br>

    Nomor Surat<br>
    <input type="text" name="nomor_surat"
           value="<?= htmlspecialchars($data['nomor_surat']) ?>" required><br><br>

    Tanggal Surat<br>
    <input type="date" name="tanggal_surat"
           value="<?= $data['tanggal_surat'] ?>" required><br><br>

    Isi Ringkas<br>
    <textarea name="isi_ringkas" rows="4" required><?= htmlspecialchars($data['isi_ringkas']) ?></textarea><br><br>

    <button type="submit">💾 Update</button>
</form>

</div>
</body>
</html>
