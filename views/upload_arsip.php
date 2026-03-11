<?php
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

/* ==========================
   CEK LOGIN
========================== */
if (!isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

/* KATEGORI AKTIF */
$qKategori = mysqli_query($conn, "
    SELECT id_kategori, nama_kategori
    FROM kategori
    WHERE status = 'AKTIF'
    ORDER BY nama_kategori ASC
");

/* BOX AKTIF */
$qBox = mysqli_query($conn, "
    SELECT id_box, kode_box
    FROM box
    WHERE status = 'aktif'
    ORDER BY kode_box ASC
");

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

<h3 class="fw-bold mb-4">
    <i class="bi bi-plus-circle me-2"></i> Tambah Arsip Inaktif
</h3>

<div class="card shadow-sm border-0">
<div class="card-body">

<form action="<?= BASE_URL ?>app/arsip/simpan_arsip.php"
      method="POST"
      enctype="multipart/form-data">

<div class="row g-3">

    <div class="col-md-6">
        <label class="form-label">Asal Surat</label>
        <input type="text" name="asal_surat" class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Nomor Surat</label>
        <input type="text" name="nomor_surat" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Tanggal Surat</label>
        <input type="date" name="tanggal_surat" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Jumlah Berkas</label>
        <input type="number" name="jumlah_berkas" class="form-control" value="1" min="1">
    </div>

    <div class="col-md-4">
        <label class="form-label">Tingkat Perkembangan</label>
        <select name="tingkat_perkembangan" class="form-select">
            <option value="">-- Pilih --</option>
            <option value="Asli">Asli</option>
            <option value="Copy">Copy</option>
        </select>
    </div>

    <div class="col-md-12">
        <label class="form-label">Isi Ringkas</label>
        <textarea name="isi_ringkas" rows="3" class="form-control"></textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Klasifikasi Keamanan</label>
        <select name="klasifikasi_keamanan" class="form-select" required>
            <option value="">-- Pilih --</option>
            <option value="Biasa">Biasa</option>
            <option value="Terbatas">Terbatas</option>
            <option value="Rahasia">Rahasia</option>
            <option value="Sangat Rahasia">Sangat Rahasia</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Kategori Arsip</label>
        <select name="id_kategori" class="form-select" required>
            <option value="">-- Pilih --</option>
            <?php while ($k = mysqli_fetch_assoc($qKategori)): ?>
                <option value="<?= $k['id_kategori'] ?>">
                    <?= htmlspecialchars($k['nama_kategori']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Box Arsip</label>
        <select name="id_box"  class="form-select" required>
            <option value="">-- Pilih --</option>
            <?php while ($b = mysqli_fetch_assoc($qBox)): ?>
                <option value="<?= $b['id_box'] ?>">
                    <?= htmlspecialchars($b['kode_box']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">File Arsip</label>
        <input type="file"
               name="file_arsip"
               id="fileInput"
               class="form-control"
               accept="image/*,application/pdf">
    </div>

</div>

<div id="ocrLoading" class="mt-3 text-primary" style="display:none;">
    🔍 Sedang memproses OCR...
</div>

<div class="mt-4 d-flex gap-2">

    <button type="button" class="btn btn-success" onclick="openCamera()">
        <i class="bi bi-camera"></i> Kamera
    </button>

    <button type="button" class="btn btn-warning" onclick="scanOCR()">
        <i class="bi bi-search"></i> Scan OCR
    </button>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-save"></i> Simpan Arsip
    </button>

</div>

</form>
</div>
</div>

</div>
</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>

<!-- ==========================
     SCRIPT OCR & KAMERA
========================== -->
<script>
function openCamera() {
    const isMobile = /Android|iPhone|iPad/i.test(navigator.userAgent);

    if (!isMobile) {
        alert("Fitur kamera hanya bisa digunakan melalui HP.");
        return;
    }

    const input = document.getElementById('fileInput');
    input.setAttribute('accept', 'image/*');
    input.setAttribute('capture', 'environment');
    input.click();
}

function scanOCR() {
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];

    if (!file) {
        alert("Silakan pilih file atau foto terlebih dahulu.");
        return;
    }

    const loading = document.getElementById('ocrLoading');
    loading.style.display = 'block';

    const formData = new FormData();
    formData.append('file', file);

    fetch('<?= BASE_URL ?>app/ocr/ajax_scan_ocr.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        loading.style.display = 'none';

        if (res.status !== 'ok') {
            alert(res.pesan || 'OCR gagal diproses');
            return;
        }

        const hasil = res.hasil;

        if (hasil.asal_surat)
            document.querySelector('[name="asal_surat"]').value = hasil.asal_surat;

        if (hasil.nomor_surat)
            document.querySelector('[name="nomor_surat"]').value = hasil.nomor_surat;

        if (hasil.tanggal_surat)
            document.querySelector('[name="tanggal_surat"]').value = hasil.tanggal_surat;

        if (hasil.isi_ringkas)
            document.querySelector('[name="isi_ringkas"]').value = hasil.isi_ringkas;

        alert("✅ OCR berhasil & form terisi otomatis");
    })
    .catch(err => {
        loading.style.display = 'none';
        console.error(err);
        alert("Terjadi kesalahan saat memproses OCR");
    });
}
</script>