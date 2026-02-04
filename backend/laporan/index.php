<?php
session_start();
include __DIR__ . "/../config/database.php";

// admin & pimpinan
if (!isset($_SESSION['id_user']) || !in_array($_SESSION['role'], ['admin','pimpinan'])) {
    die("Akses ditolak");
}
?>

<h2>📑 Laporan Arsip & Pemusnahan</h2>

<ul>
    <li>
        <a href="export_excel.php">
            📊 Export Laporan Pemusnahan (Excel)
        </a>
    </li>
    <li>
        <a href="export_pdf.php" target="_blank">
            📄 Export Laporan Pemusnahan (PDF)
        </a>
    </li>
</ul>
