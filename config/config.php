<?php
// Basic site and database configuration
// Update DB_* values to match your local MySQL setup.

// Site
define('SITE_NAME', 'Logistip');
// Admin credentials (change these in production)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'admin123');
// Base URL of this app (include leading and trailing slash). For WAMP folder 'APLX', use '/APLX/'.
if (!defined('BASE_URL')) {
    define('BASE_URL', '/APLX/');
}

// Database (WAMP default: user root, password empty)
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'aplx');
define('DB_USER', 'root');
define('DB_PASS', '1234');
define('DB_CHARSET', 'utf8mb4');

// Error reporting (development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
