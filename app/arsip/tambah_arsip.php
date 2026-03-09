<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

function old($key, $default = '') {
    return isset($_SESSION['old'][$key])
        ? htmlspecialchars($_SESSION['old'][$key])
        : $default;
}

include __DIR__ . "/../config/database.php";

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak");
}

$kategori = mysqli_query($conn, "SELECT * FROM kategori WHERE status = 'aktif' ORDER BY nama_kategori");
$box      = mysqli_query($conn, "SELECT * FROM box WHERE status='aktif' ORDER BY kode_box");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tambah Arsip Inaktif</title>
<style>
body { font-family: Arial; background:#f4f6f8; }
.container { width:760px; margin:30px auto; background:#fff; padding:25px; border-radius:8px; }
label { font-weight:bold; margin-top:12px; display:block; }
input,select,textarea { width:100%; padding:8px; margin-top:5px; }
button { margin-top:10px; padding:8px 14px; border:none; border-radius:4px; cursor:pointer; }
.btn-primary { background:#2563eb; color:#fff; }
.btn-camera { background:#16a34a; color:#fff; }
.btn-ocr { background:#7c3aed; color:#fff; }
.btn-plus { padding:6px 10px; font-weight:bold; }
.flex { display:flex; gap:6px; align-items:center; }
#cameraBox { display:none; margin-top:15px; }
video { width:100%; border:1px solid #ccc; }
</style>
</head>

<body>
<div class="container">
<h2>📄 Tambah Arsip Inaktif</h2>

<?php if (isset($_SESSION['error'])) { ?>
<script>
    alert("<?= htmlspecialchars($_SESSION['error']) ?>");
</script>
<?php unset($_SESSION['error']); } ?>



<form method="POST" action="simpan_arsip.php" enctype="multipart/form-data">

<label>Asal Surat</label>
<input type="text" name="asal_surat" value="<?= old('asal_surat') ?>">

<label>Nomor Surat / Undangan</label>
<input type="text" name="nomor_surat" value="<?= old('nomor_surat') ?>">

<label>Tanggal Surat</label>
<input type="date" name="tanggal_surat" value="<?= old('tanggal_surat') ?>">

<label>Isi Ringkas</label>
<textarea name="isi_ringkas"><?= old('isi_ringkas') ?></textarea>

<label>Klasifikasi Keamanan</label>
<select name="klasifikasi_keamanan" required>
    <option value="">-- Pilih --</option>
    <option <?= old('klasifikasi_keamanan')=='Biasa'?'selected':'' ?>>Biasa</option>
    <option>Terbatas</option>
    <option>Rahasia</option>
    <option>Sangat Rahasia</option>
</select>

<label>Jumlah Berkas</label>
<input type="number" name="jumlah_berkas" value="1" min="1" required>

<label>Tingkat Perkembangan</label>
<select name="tingkat_perkembangan" required>
    <option value="">-- Pilih --</option>
    <option>Asli</option>
    <option>Copy</option>
</select>

<label>Kategori Arsip</label>
<div class="flex">
    <select name="id_kategori" id="selectKategori" required style="flex:1;">
        <option value="">-- Pilih --</option>
        <?php while($k=mysqli_fetch_assoc($kategori)){ ?>
            <option value="<?= $k['id_kategori'] ?>"
                <?= old('id_kategori') == $k['id_kategori'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama_kategori']) ?>
            </option>
        <?php } ?>
    </select>
    <button type="button" class="btn-plus" onclick="tambahKategoriCepat()">＋</button>
</div>


<label>Box Arsip</label>
<div class="flex">
    <select name="id_box" id="selectBox" required style="flex:1;">
        <option value="">-- Pilih --</option>
        <?php while($b=mysqli_fetch_assoc($box)){ ?>
            <option value="<?= $b['id_box'] ?>"
                <?= old('id_box') == $b['id_box'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['kode_box']) ?> (<?= htmlspecialchars($b['lokasi_fisik']) ?>)
            </option>
        <?php } ?>
    </select>
    <button type="button" class="btn-plus" onclick="tambahBoxCepat()">＋</button>
</div>

<label>File Arsip</label>
<input type="file" name="file_arsip" id="fileInput" accept=".pdf,.jpg,.jpeg,.png">

<button type="button" class="btn-camera" onclick="openCamera()">📷 Kamera</button>
<button type="button" class="btn-ocr" onclick="scanOCR()">🔍 Scan OCR & Isi Form</button>

<div id="cameraBox">
    <video id="video" autoplay></video>
    <button type="button" onclick="capture()">📸 Ambil Foto</button>
    <canvas id="canvas" style="display:none"></canvas>
</div>

<br>
<button type="submit" class="btn-primary">💾 Simpan Arsip</button>

</form>
</div>

<script>
let video = document.getElementById('video');
let streamRef = null;

function isMobileDevice() {
    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}
/* KAMERA */
function openCamera(){

    // 1️⃣ BLOK JIKA BUKAN HP
    if (!isMobileDevice()) {
        alert("Scan kamera hanya khusus untuk HP");
        return;
    }

    // 2️⃣ TAMPILKAN AREA KAMERA
    document.getElementById('cameraBox').style.display = 'block';

    // 3️⃣ AKSES KAMERA BELAKANG HP
    navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: { exact: "environment" }
        }
    })
    .then(stream => {
        streamRef = stream;
        video.srcObject = stream;
        video.play();
    })
    .catch(err => {
        console.error(err);
        alert("Tidak dapat mengakses kamera belakang HP");
    });
}


function capture(){
    const canvas = document.getElementById('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video,0,0);

    canvas.toBlob(blob=>{
        const file = new File([blob],'kamera.png',{type:'image/png'});
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('fileInput').files = dt.files;
        streamRef.getTracks().forEach(t=>t.stop());
        alert('Foto siap di-OCR');
    });
}

/* OCR */
function scanOCR(){
    const f = document.getElementById('fileInput').files[0];
    if(!f){ alert('Pilih file dulu'); return; }

    const fd = new FormData();
    fd.append('file', f);

    fetch('../ocr/ajax_scan_ocr.php',{
        method:'POST',
        body:fd
    })
    .then(r => r.text()) // ⬅️ PENTING
    .then(txt => {
        console.log('RAW RESPONSE:', txt); // DEBUG
        const res = JSON.parse(txt);       // parse manual

        if(res.status !== 'ok'){
            alert(res.pesan || 'OCR gagal');
            return;
        }

        const h = res.hasil;
        document.querySelector('[name="asal_surat"]').value = h.asal_surat || '';
        document.querySelector('[name="nomor_surat"]').value = h.nomor_surat || '';
        document.querySelector('[name="tanggal_surat"]').value = h.tanggal_surat || '';
        document.querySelector('[name="isi_ringkas"]').value = h.isi_ringkas || '';

        alert('OCR berhasil & form terisi');
    })
    .catch(err=>{
        console.error(err);
        alert('Gagal memproses OCR (cek console)');
    });
}


/* TAMBAH BOX CEPAT */
function tambahBoxCepat(){
    const kode = prompt("Kode Box:");
    if(!kode) return;
    const lokasi = prompt("Lokasi Fisik:");
    if(!lokasi) return;

    const fd = new FormData();
    fd.append('kode_box', kode);
    fd.append('lokasi_fisik', lokasi);

    fetch('../box/ajax_tambah.php',{ method:'POST', body:fd })
    .then(r=>r.json())
    .then(res=>{
        if(res.status!=='ok'){ alert(res.pesan); return; }
        const s=document.getElementById('selectBox');
        const o=document.createElement('option');
        o.value=res.id;
        o.textContent=res.kode+' ('+res.lokasi+')';
        o.selected=true;
        s.appendChild(o);
        alert('Box berhasil ditambahkan');
    });
}

/* =======================
   TAMBAH KATEGORI CEPAT
======================= */
function tambahKategoriCepat(){
    const nama = prompt("Nama kategori:");
    if(!nama) return;

    const fd = new FormData();
    fd.append('nama_kategori', nama);

    fetch('../kategori/ajax_tambah.php',{
        method:'POST',
        body:fd
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.status!=='ok'){
            alert(res.pesan);
            return;
        }

        const select = document.getElementById('selectKategori');
        const opt = document.createElement('option');
        opt.value = res.id;
        opt.textContent = res.nama;
        opt.selected = true;
        select.appendChild(opt);

        alert('Kategori berhasil ditambahkan');
    }) 
    .catch(err=>{
        console.error(err);
        alert('Gagal menambah kategori');
    });
}


/* =======================
   VALIDASI FORM TAMBAH ARSIP
======================= */
document.querySelector('form').addEventListener('submit', function(e){


    const wajib = [
        {name:'asal_surat', label:'Asal Surat'},
        {name:'nomor_surat', label:'Nomor Surat'},
        {name:'tanggal_surat', label:'Tanggal Surat'},
        {name:'isi_ringkas', label:'Isi Ringkas'},
        {name:'klasifikasi_keamanan', label:'Klasifikasi Keamanan'},
        {name:'tingkat_perkembangan', label:'Tingkat Perkembangan'},
        {name:'id_kategori', label:'Kategori Arsip'},
        {name:'id_box', label:'Box Arsip'},
    ];

    for (let f of wajib) {
        const el = document.querySelector(`[name="${f.name}"]`);
        if (!el || el.value.trim() === '') {
            alert(f.label + ' wajib diisi');
            el.focus();
            e.preventDefault(); // ❌ batalkan submit
            return;
        }
    }

    // =======================
    // VALIDASI TANGGAL TIDAK BOLEH MASA DEPAN
    // =======================
    const inputTanggal = document.querySelector('[name="tanggal_surat"]');
    const tglSurat = new Date(inputTanggal.value);
    const hariIni = new Date();

    // set jam ke 00:00 supaya perbandingan adil
    tglSurat.setHours(0,0,0,0);
    hariIni.setHours(0,0,0,0);

    if (tglSurat > hariIni) {
        alert('Tanggal surat tidak boleh melebihi hari ini');
        inputTanggal.focus();
        e.preventDefault();
        return;
    }


    const file = document.getElementById('fileInput').files[0];
    if (!file) {
        alert('File arsip wajib diunggah');
        e.preventDefault();
        return;
    }
});

</script>

<?php unset($_SESSION['old']); ?>

</body>
</html>