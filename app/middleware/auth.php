<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

function checkRole($roles = [])
{
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        header("Location: login.php");
        exit();
    }
}