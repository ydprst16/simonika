<?php

/**
 * ========================================
 * BASE CONFIG
 * ========================================
 */

// AUTO DETECT BASE URL
$host = $_SERVER['HTTP_HOST'] ?? '';

if (strpos($host, 'localhost') !== false) {
    define('BASE_URL', '/simonika/');
} else {
    define('BASE_URL', '/');
}


/**
 * ========================================
 * APP CONFIG
 * ========================================
 */

date_default_timezone_set('Asia/Jakarta');

define('APP_ENV', 'development');


/**
 * ========================================
 * ERROR REPORTING
 * ========================================
 */

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}


/**
 * ========================================
 * HELPER FUNCTIONS
 * ========================================
 */

function url($path = '')
{
    return BASE_URL . ltrim($path, '/');
}

function asset($path = '')
{
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

function redirect($path)
{
    header("Location: " . url($path));
    exit();
}