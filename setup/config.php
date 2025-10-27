<?php
/**
 * N8N Installer Configuration
 * Xman Enterprise co.,ltd.
 */

// Security
define('N8N_INSTALLER', true);

// Version
define('VERSION', '1.0.0');
define('BUILD', '20250127');

// Company Info
define('COMPANY_NAME', 'Xman Enterprise co.,ltd.');
define('COMPANY_WEBSITE', 'https://xman4289.com');
define('COMPANY_PHONE', '(066) 080-6038278');

// Paths
define('SETUP_DIR', __DIR__);
define('INSTALL_ROOT', dirname(__DIR__));

// Language
define('DEFAULT_LANG', 'th');

// Session
session_start();

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

// CSRF Token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
