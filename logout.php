<?php
session_start();
// Hapus semua data session
session_destroy();
// Tendang balik ke halaman login
header("Location: index.php");
exit();
?>