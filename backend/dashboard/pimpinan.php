<?php
/**
 * Dashboard Pimpinan Tekkomdik
 * Fungsi:
 * - Monitoring arsip
 * - Akses notifikasi
 * - Navigasi ke arsip menunggu & history pemusnahan
 */

session_start();
include __DIR__ . "/../config/database.php";
include __DIR__ . "/../notifikasi/helper.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['role'] !== 'pimpinan') {
    exit("Akses ditolak");
}

$id_user = $_SESSION['id_user'];
//Badge berdasarkan arsip siap musnah
$q_notif = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM arsip
    WHERE status_arsip = 'Siap Musnah'
");

$r_notif = mysqli_fetch_assoc($q_notif);
$jumlah_notif = (int)$r_notif['total'];
/* ==========================
   INPUT SEARCH + PAGINATION
========================== */
$keyword = trim($_GET['keyword'] ?? '');

$limit = 50;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$where = [];
$where[] = "arsip.status_arsip != 'Dimusnahkan'";

if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where[] = "(arsip.asal_surat LIKE '%$kw%'
              OR arsip.nomor_surat LIKE '%$kw%'
              OR arsip.isi_ringkas LIKE '%$kw%')";
}

$whereSQL = "WHERE " . implode(" AND ", $where);

/* ==========================
   HITUNG TOTAL DATA
========================== */
$total_result = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    $whereSQL
");

$total_row  = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_page = ceil($total_data / $limit);

/* ==========================
   QUERY DATA PER HALAMAN
========================== */
$query_all = mysqli_query($conn, "
    SELECT
        arsip.id_arsip,
        arsip.asal_surat,
        arsip.nomor_surat,
        arsip.tanggal_surat,
        arsip.status_arsip,
        kategori.nama_kategori,
        box.kode_box
    FROM arsip
    LEFT JOIN kategori ON arsip.id_kategori = kategori.id_kategori
    LEFT JOIN box ON arsip.id_box = box.id_box
    $whereSQL
    ORDER BY arsip.tanggal_input DESC
    LIMIT $offset, $limit
");

$no = $offset + 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Pimpinan Tekkomdik</title>

<style>
body { font-family: Arial; background:#f4f6f8; }
.container { width:95%; margin:30px auto; }
.box { background:#fff; padding:15px; border-radius:6px; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; background:#fff; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#ddd; }

.btn { padding:6px 10px; border-radius:4px; color:#fff; text-decoration:none; font-size:13px; }
.detail { background:#3498db; }
.download { background:#2ecc71; }
.view { background:#f39c12; }

.logout { float:right; color:red; text-decoration:none; font-weight:bold; }

.badge { padding:3px 7px; border-radius:4px; font-size:12px; color:#fff; }
.aktif { background:#2980b9; }
.inaktif { background:#7f8c8d; }
.musnah { background:#c0392b; }

.pagination{
    margin:15px 0 25px 0;
    text-align:center;
}

.pagination a{
    padding:6px 12px;
    margin:0 4px;
    border:1px solid #ccc;
    text-decoration:none;
    background:#fff;
    border-radius:4px;
    display:inline-block;
}

.pagination a.active{
    background:#3498db;
    color:#fff;
    font-weight:bold;
    border-color:#3498db;
}

.action-box a {
    display:inline-block;
    margin-top:8px;
    background:#34495e;
    color:#fff;
    padding:10px 15px;
    border-radius:5px;
    text-decoration:none;
}
.action-box a.danger {
    background:#e74c3c;
}
</style>
</head>

<body>

<div class="container">

<h2>
📊 Dashboard Pimpinan Tekkomdik

<a href="../notifikasi/index.php" style="margin-left:15px;text-decoration:none;">
🔔 Notifikasi
<?php if ($jumlah_notif > 0): ?>
<strong style="color:red;">(<?= $jumlah_notif ?>)</strong>
<?php endif; ?>
</a>

<a href="../auth/logout.php"
   class="logout"
   onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
   Logout
</a>

</h2>

<!-- ================= AKSI UTAMA ================= -->
<div class="box action-box">
    <h3>🗑️ Arsip Menunggu Pemusnahan</h3>
    <p>Arsip yang telah melewati masa retensi dan memerlukan keputusan pimpinan.</p>
    <a href="../dashboard/arsip_menunggu.php" class="danger">
        🔍 Lihat Arsip Menunggu Pemusnahan
    </a>
</div>

<div class="box action-box">
    <h3>📜 Riwayat Pemusnahan Arsip</h3>
    <p>Riwayat arsip yang telah disetujui atau ditolak pemusnahannya.</p>
    <a href="../pemusnahan/history.php">
        📄 Lihat Riwayat Pemusnahan
    </a>
</div>

<!-- ================= SEARCH ================= -->
<div class="box">
<form method="GET">
<strong>Cari Arsip:</strong>
<input type="text" name="keyword"
       placeholder="Asal / Nomor / Isi Ringkas"
       value="<?= htmlspecialchars($keyword) ?>">
<button type="submit">Cari</button>
</form>
</div>

<!-- ================= DAFTAR ARSIP ================= -->
<h3>📁 Daftar Seluruh Arsip</h3>

<div class="pagination">
<?php if ($page > 1): ?>
<a href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page-1 ?>">« Prev</a>
<?php endif; ?>

<?php for ($i=1; $i <= $total_page; $i++): ?>
<a href="?keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"
   class="<?= ($i==$page)?'active':'' ?>">
<?= $i ?>
</a>
<?php endfor; ?>

<?php if ($page < $total_page): ?>
<a href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page+1 ?>">Next »</a>
<?php endif; ?>
</div>

<table>
<tr>
<th>No</th>
<th>Asal Surat</th>
<th>Nomor Surat</th>
<th>Tanggal</th>
<th>Kategori</th>
<th>Box</th>
<th>Status</th>
<th>Aksi</th>
</tr>

<?php
if (mysqli_num_rows($query_all) === 0) {
    echo "<tr><td colspan='8'>Data tidak ditemukan</td></tr>";
} else {
    while ($row = mysqli_fetch_assoc($query_all)) {

        $statusClass = match ($row['status_arsip']) {
            'Aktif'        => 'aktif',
            'Inaktif'      => 'inaktif',
            'Siap Musnah'  => 'musnah',
            default        => 'inaktif'
        };
?>
<tr>
<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['asal_surat']) ?></td>
<td><?= htmlspecialchars($row['nomor_surat']) ?></td>
<td><?= date('d/m/Y', strtotime($row['tanggal_surat'])) ?></td>
<td><?= htmlspecialchars($row['nama_kategori']) ?></td>
<td><?= htmlspecialchars($row['kode_box']) ?></td>
<td>
<span class="badge <?= $statusClass ?>">
<?= $row['status_arsip'] ?>
</span>
</td>
<td>
<a href="../arsip/detail_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn detail">Detail</a>
<a href="../arsip/view_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn view">View</a>
<a href="../arsip/download_arsip.php?id=<?= $row['id_arsip'] ?>" class="btn download">Download</a>
</td>
</tr>
<?php
    }
}
?>
</table>

</div>
</body>
</html>