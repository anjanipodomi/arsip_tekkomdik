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

require_once __DIR__ . "/../layout/init.php";
require_once __DIR__ . "/../layout/head.php"; 
require_once __DIR__ . "/../layout/header.php"; 
require_once __DIR__ . "/../layout/sidebar.php"; 
?>

<main class="content content-center">

<h2>🔍 Scan OCR Arsip</h2>

<form action="<?= BASE_URL ?>app/ocr/process.php" 
      method="POST" 
      enctype="multipart/form-data"
      class="form-card">

    <div class="form-group">
        <label>Upload File (PDF / JPG / PNG)</label>
        <input type="file" name="file_ocr" accept=".pdf,.jpg,.jpeg,.png" required>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            🔍 Proses OCR
        </button>
    </div>

</form>

</main>

<?php require_once __DIR__ . "/../layout/footer.php"; ?>