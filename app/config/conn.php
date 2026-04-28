<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// fallback localhost
if (!$host) {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "mgr-edit";
    $port = 3306;
}

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>