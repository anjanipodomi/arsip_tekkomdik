<?php
require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../app/config/database.php";

/* ==========================
   CEK LOGIN & ROLE
========================== */
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "views/login.php");
    exit;
}

$data = mysqli_query($conn, "
    SELECT * FROM kategori 
    ORDER BY id_kategori ASC
");

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php";
require_once __DIR__ . "/../layout/header.php";
require_once __DIR__ . "/../layout/sidebar.php";
?>

<main class="content">
<div class="container-fluid">

    <h3 class="fw-bold mb-4">📂 Kategori Arsip</h3>

    <div class="card shadow-sm border-0">

        <!-- CARD HEADER -->
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <button class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#kategoriModal">
                <i class="bi bi-plus-lg"></i> Tambah Kategori
            </button>
        </div>

        <!-- CARD BODY -->
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">No</th>
                            <th style="width:120px;">Klasifikasi</th>
                            <th>Nama Kategori</th>
                            <th style="width:150px;">Status</th>
                            <th style="width:160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($data)): 
                        $status = strtoupper($row['status']);
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <?= htmlspecialchars($row['klasifikasi_kategori']) ?>
                            </td>
                            <td class="text-start">
                                <?= htmlspecialchars($row['nama_kategori']) ?>
                            </td>

                            <td>
                                <?php if($status == 'AKTIF'): ?>
                                    <span class="badge bg-success">AKTIF</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">NONAKTIF</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="d-flex justify-content-center gap-2">

                                    <!-- Edit -->
                                    <a href="<?= BASE_URL ?>views/edit_kategori.php?id=<?= $row['id_kategori'] ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip"
                                       data-bs-placement="top"
                                       title="Edit Kategori">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <?php if($status == 'AKTIF'): ?>
                                        <!-- Nonaktifkan -->
                                        <button class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Nonaktifkan Kategori"
                                                onclick="toggleStatus(<?= $row['id_kategori'] ?>, 'nonaktif')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <!-- Aktifkan -->
                                        <button class="btn btn-sm btn-outline-success"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="top"
                                                title="Aktifkan Kategori"
                                                onclick="toggleStatus(<?= $row['id_kategori'] ?>, 'aktif')">
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


<!-- MODAL TAMBAH KATEGORI -->
<div class="modal fade" id="kategoriModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Kode Klasifikasi</label>
            <input type="text" id="klasifikasiKategori" class="form-control" placeholder="Contoh: 001">
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Kategori</label>
            <input type="text" id="namaKategori" class="form-control" placeholder="Masukkan nama kategori">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="simpanKategori()">Simpan</button>
      </div>

    </div>
  </div>
</div>


<script>
function simpanKategori(){
    const klasifikasi = document.getElementById('klasifikasiKategori').value.trim();
    const nama = document.getElementById('namaKategori').value.trim();

    if(!klasifikasi || !nama){
        alert("Klasifikasi dan nama kategori tidak boleh kosong");
        return;
    }

    fetch('<?= BASE_URL ?>app/kategori/ajax_tambah.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'klasifikasi_kategori=' + encodeURIComponent(klasifikasi)
            + '&nama_kategori=' + encodeURIComponent(nama)
    })
    .then(() => location.reload());
}

function toggleStatus(id, aksi) {

    let pesan = aksi === 'aktif'
        ? "Aktifkan kembali kategori ini?"
        : "Nonaktifkan kategori ini?";

    if (!confirm(pesan)) return;

    fetch('<?= BASE_URL ?>app/kategori/toggle_status.php?id=' + id + '&aksi=' + aksi)
        .then(() => location.reload());
}

/* AKTIFKAN TOOLTIP */
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>