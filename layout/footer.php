<footer class="footer">
© <?=date('Y')?> Balai Teknologi Komunikasi Pendidikan DIY  
<br>Aplikasi Dokumen Inaktif Siap Akses
</footer>

<?php if (in_array($_SESSION['role'], ['admin','pimpinan'])): ?>
<script src="<?= BASE_URL ?>assets/js/notifikasi.js"></script>
<?php endif; ?>

</body>
</html>