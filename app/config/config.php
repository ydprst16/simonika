<?php

/**
 * ========================================
 * BASE CONFIG
 * ========================================
 */

// Base URL project (WAJIB SESUAIKAN)
define('BASE_URL', '/simonika/');


/**
 * ========================================
 * APP CONFIG
 * ========================================
 */

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Environment (development / production)
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

/**
 * Generate URL
 */
function url($path = '')
{
    return BASE_URL . ltrim($path, '/');
}

/**
 * Asset helper (images, css, js)
 */
function asset($path = '')
{
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

/**
 * Redirect helper
 */
function redirect($path)
{
    header("Location: " . url($path));
    exit();
}