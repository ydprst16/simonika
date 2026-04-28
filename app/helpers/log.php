<?php

function log_activity($conn, $username, $aktivitas)
{

    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("
        INSERT INTO user_log (username, aktivitas, ip_address)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("sss", $username, $aktivitas, $ip);
    $stmt->execute();
}