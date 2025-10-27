<?php
/**
 * N8N Web Installer Configuration
 *
 * @package    N8N Web Installer
 * @version    1.0.0
 * @author     Xman Enterprise co.,ltd.
 * @website    https://xman4289.com
 * @phone      (066) 080-6038278
 * @license    Proprietary
 */

// Prevent direct access
if (!defined('N8N_INSTALLER')) {
    die('Direct access not permitted');
}

// Installer Version
define('INSTALLER_VERSION', '1.0.0');
define('INSTALLER_BUILD', '20250127');

// Company Information
define('COMPANY_NAME', 'Xman Enterprise co.,ltd.');
define('COMPANY_WEBSITE', 'https://xman4289.com');
define('COMPANY_PHONE', '(066) 080-6038278');

// Installation Settings
define('INSTALL_ROOT', dirname(__DIR__)); // Parent directory of setup folder
define('SETUP_DIR', __DIR__);
define('DEFAULT_LANGUAGE', 'th');

// N8N Settings
define('N8N_VERSION', 'latest');
define('N8N_GITHUB_REPO', 'https://api.github.com/repos/n8n-io/n8n/releases/latest');
define('N8N_REQUIRED_PHP_VERSION', '7.4.0');
define('N8N_REQUIRED_NODE_VERSION', '18.0.0');

// Session Settings
define('SESSION_NAME', 'n8n_installer_session');
define('SESSION_LIFETIME', 3600); // 1 hour

// Security
define('CSRF_TOKEN_NAME', 'n8n_csrf_token');

// Database Types
$db_types = [
    'mysql' => 'MySQL / MariaDB',
    'postgres' => 'PostgreSQL',
    'sqlite' => 'SQLite'
];

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', SETUP_DIR . '/error.log');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Initialize CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Set default language
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = DEFAULT_LANGUAGE;
}
