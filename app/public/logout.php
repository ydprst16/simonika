<?php
session_start();

require_once __DIR__ . '/../config/config.php';

// hapus session
$_SESSION = [];
session_unset();
session_destroy();

// redirect ke base URL
header("Location: " . BASE_URL);
exit();