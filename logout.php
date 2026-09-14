<?php
session_start();
session_unset();
session_destroy();

// Tendang admin kembali ke halaman login depan
header("Location: login_admin.php");
exit;
?>