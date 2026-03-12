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

$query = mysqli_query($conn, "SELECT * FROM box ORDER BY id_box ASC");

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

    <h3 class="fw-bold mb-4">
        <i class="bi bi-archive me-2"></i> Data Box Arsip
    </h3>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <button class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#boxModal">
                <i class="bi bi-plus-lg"></i> Tambah Box
            </button>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">No</th>
                            <th>Kode Box</th>
                            <th>Lokasi Fisik</th>
                            <th style="width:150px;">Status</th>
                            <th style="width:160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no=1; while($row=mysqli_fetch_assoc($query)) : 
                        $status = strtolower($row['status']);
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['kode_box']) ?></td>
                            <td><?= htmlspecialchars($row['lokasi_fisik']) ?></td>

                            <td>
                                <?php if($status == 'aktif'): ?>
                                    <span class="badge bg-success">AKTIF</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">NONAKTIF</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex justify-content-center gap-2">

                                    <?php if($status == 'nonaktif'): ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    <?php else: ?>
                                        <a href="edit_box.php?id=<?= $row['id_box'] ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if($status == 'aktif'): ?>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="toggleStatus(<?= $row['id_box'] ?>, 'nonaktif')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success"
                                                onclick="toggleStatus(<?= $row['id_box'] ?>, 'aktif')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
</main>


<!-- MODAL -->
<div class="modal fade" id="boxModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Box Arsip</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Kode Box</label>
            <input type="text" id="kodeBox" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Lokasi Fisik</label>
            <input type="text" id="lokasiBox" class="form-control">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="simpanBox()">Simpan</button>
      </div>

    </div>
  </div>
</div>

<script>
function simpanBox(){
    const kode = document.getElementById('kodeBox').value.trim();
    const lokasi = document.getElementById('lokasiBox').value.trim();

    if(!kode || !lokasi){
        alert("Semua field wajib diisi");
        return;
    }

    const fd = new FormData();
    fd.append('kode_box', kode);
    fd.append('lokasi_fisik', lokasi);

    fetch("<?= BASE_URL ?>app/box/ajax_tambah.php",{
        method:'POST',
        body:fd
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.status!=='ok'){
            alert(res.pesan);
            return;
        }
        location.reload();
    });
}

function toggleStatus(id, aksi){
    if(!confirm("Yakin ubah status box ini?")) return;

    fetch("<?= BASE_URL ?>app/box/toggle_status.php?id=" + id + "&aksi=" + aksi)
        .then(()=> location.reload());
}
</script>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>