<?php
/**
 * N8N Installer Configuration
 * Xman Enterprise co.,ltd.
 */

// Security
if (!defined('N8N_INSTALLER')) {
    define('N8N_INSTALLER', true);
}

// Version
if (!defined('VERSION')) {
    define('VERSION', '1.0.0');
}
if (!defined('BUILD')) {
    define('BUILD', '20250127');
}

// Company Info
if (!defined('COMPANY_NAME')) {
    define('COMPANY_NAME', 'Xman Enterprise co.,ltd.');
}
if (!defined('COMPANY_WEBSITE')) {
    define('COMPANY_WEBSITE', 'https://xman4289.com');
}
if (!defined('COMPANY_PHONE')) {
    define('COMPANY_PHONE', '(066) 080-6038278');
}

// Paths
if (!defined('SETUP_DIR')) {
    define('SETUP_DIR', __DIR__);
}
if (!defined('INSTALL_ROOT')) {
    define('INSTALL_ROOT', dirname(__DIR__));
}

// Language
if (!defined('DEFAULT_LANG')) {
    define('DEFAULT_LANG', 'th');
}

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
