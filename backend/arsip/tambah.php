<?php
/**
 * Form Tambah Arsip Inaktif
 * Role : Admin / Operator Arsip
 * Modul: Digitalisasi Arsip Lama
 * Sistem: Pengelolaan Arsip Inaktif Digital - Balai Tekkomdik DIY
 */

session_start();
include __DIR__ . "/../config/database.php";

// ==========================
// CEK LOGIN & ROLE
// ==========================
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

// ==========================
// AMBIL DATA MASTER
// ==========================
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori");
$box      = mysqli_query($conn, "SELECT * FROM box ORDER BY kode_box");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Arsip Inaktif</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f6f8;
}
.container {
    width: 680px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
}
label {
    font-weight: bold;
    margin-top: 12px;
    display: block;
}
input, select, textarea {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}
button {
    margin-top: 15px;
    padding: 8px 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-primary {
    background: #2c7be5;
    color: #fff;
}
.btn-danger {
    background: #dc3545;
    color: #fff;
}
.modal-bg {
    display:none;
    position:fixed;
    top:0;left:0;right:0;bottom:0;
    background:rgba(0,0,0,0.4);
}
.modal {
    background:#fff;
    width:380px;
    margin:120px auto;
    padding:20px;
    border-radius:6px;
}
.modal h3 {
    margin-top:0;
}
</style>
</head>

<body>

<div class="container">
<h2>📄 Tambah Arsip Inaktif</h2>

<form method="POST" action="simpan.php" enctype="multipart/form-data">

<!-- ================= METADATA ================= -->
<label>Asal Surat</label>
<input type="text" name="asal_surat" required>

<label>Nomor Surat</label>
<input type="text" name="nomor_surat" required>

<label>Tanggal Surat</label>
<input type="date" name="tanggal_surat" required>

<label>Isi Ringkas</label>
<textarea name="isi_ringkas" rows="4"></textarea>

<!-- ================= KLASIFIKASI ================= -->
<label>Klasifikasi Keamanan</label>
<select name="klasifikasi_keamanan" required>
    <option value="">-- Pilih --</option>
    <option value="Biasa">Biasa</option>
    <option value="Terbatas">Terbatas</option>
    <option value="Rahasia">Rahasia</option>
    <option value="Sangat Rahasia">Sangat Rahasia</option>
</select>

<label>Jumlah Berkas</label>
<input type="number" name="jumlah_berkas" value="1" min="1" required>

<label>Tingkat Perkembangan</label>
<select name="tingkat_perkembangan" required>
    <option value="">-- Pilih --</option>
    <option value="Asli">Asli</option>
    <option value="Copy">Copy</option>
</select>

<!-- ================= KATEGORI ================= -->
<label>Kategori Arsip</label>
<select name="id_kategori" id="kategoriSelect" required>
    <option value="">-- Pilih Kategori --</option>
    <?php while ($k = mysqli_fetch_assoc($kategori)) { ?>
        <option value="<?= $k['id_kategori'] ?>">
            <?= htmlspecialchars($k['nama_kategori']) ?>
        </option>
    <?php } ?>
    <option value="__tambah__">+ Tambah Kategori</option>
</select>

<!-- ================= BOX ================= -->
<label>Lokasi Simpan (Box Arsip)</label>
<select name="id_box" id="boxSelect" required>
    <option value="">-- Pilih Box --</option>
    <?php while ($b = mysqli_fetch_assoc($box)) { ?>
        <option value="<?= $b['id_box'] ?>">
            <?= htmlspecialchars($b['kode_box']) ?> (<?= htmlspecialchars($b['lokasi_fisik']) ?>)
        </option>
    <?php } ?>
    <option value="__tambah__">+ Tambah Box</option>
</select>

<!-- ================= FILE ================= -->
<label>File Arsip</label>
<input type="file" name="file_arsip" accept=".pdf,.jpg,.jpeg,.png" required>

<button type="submit" class="btn-primary">💾 Simpan Arsip</button>
</form>
</div>

<!-- ================= MODAL KATEGORI ================= -->
<div id="modalKategori" class="modal-bg">
<div class="modal">
<h3>Tambah Kategori</h3>
<input type="text" id="namaKategori" placeholder="Nama Kategori">
<br>
<button class="btn-danger" onclick="tutupModal('modalKategori')">Batal</button>
<button class="btn-primary" onclick="simpanKategori()">Tambah</button>
</div>
</div>

<!-- ================= MODAL BOX ================= -->
<div id="modalBox" class="modal-bg">
<div class="modal">
<h3>Tambah Box Arsip</h3>
<label>Kode Box</label>
<input type="text" id="kodeBox">
<label>Lokasi Fisik</label>
<input type="text" id="lokasiBox">
<label>Keterangan</label>
<textarea id="ketBox"></textarea>
<button class="btn-danger" onclick="tutupModal('modalBox')">Batal</button>
<button class="btn-primary" onclick="simpanBox()">Tambah</button>
</div>
</div>

<script>
// ====== KATEGORI ======
document.getElementById('kategoriSelect').addEventListener('change', function(){
    if (this.value === '__tambah__') {
        this.value = '';
        document.getElementById('modalKategori').style.display = 'block';
    }
});

function simpanKategori(){
    const nama = document.getElementById('namaKategori').value.trim();
    if(!nama){ alert('Nama kategori wajib diisi'); return; }

    fetch('../kategori/ajax_tambah.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'nama_kategori='+encodeURIComponent(nama)
    })
    .then(res=>res.json())
    .then(res=>{
        if(res.status==='ok'){
            const opt = document.createElement('option');
            opt.value = res.id;
            opt.text = res.nama;
            opt.selected = true;
            kategoriSelect.insertBefore(opt, kategoriSelect.lastElementChild);
            tutupModal('modalKategori');
        } else alert(res.pesan);
    });
}

// ====== BOX ======
document.getElementById('boxSelect').addEventListener('change', function(){
    if (this.value === '__tambah__') {
        this.value = '';
        document.getElementById('modalBox').style.display = 'block';
    }
});

function simpanBox(){
    const kode = kodeBox.value.trim();
    const lokasi = lokasiBox.value.trim();
    if(!kode || !lokasi){ alert('Data box wajib diisi'); return; }

    fetch('../box/ajax_tambah.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`kode_box=${encodeURIComponent(kode)}&lokasi_fisik=${encodeURIComponent(lokasi)}&keterangan=${encodeURIComponent(ketBox.value)}`
    })
    .then(res=>res.json())
    .then(res=>{
        if(res.status==='ok'){
            const opt=document.createElement('option');
            opt.value=res.id;
            opt.text=res.kode+' ('+res.lokasi+')';
            opt.selected=true;
            boxSelect.insertBefore(opt, boxSelect.lastElementChild);
            tutupModal('modalBox');
        } else alert(res.pesan);
    });
}

function tutupModal(id){
    document.getElementById(id).style.display='none';
}
</script>

</body>
</html>
