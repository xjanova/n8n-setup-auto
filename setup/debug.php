<?php
/**
 * Debug endpoint - helps diagnose 500 errors
 */

header('Content-Type: application/json');

$debug = [
    'php_version' => phpversion(),
    'time' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'checks' => []
];

// Check 1: File existence
$debug['checks']['files'] = [
    'config.php' => file_exists(__DIR__ . '/config.php'),
    'install.php' => file_exists(__DIR__ . '/install.php'),
    'index.php' => file_exists(__DIR__ . '/index.php'),
];

// Check 2: Try to load config
try {
    define('N8N_INSTALLER', true);
    require_once __DIR__ . '/config.php';
    $debug['checks']['config_load'] = 'SUCCESS';
    $debug['checks']['constants'] = [
        'VERSION' => defined('VERSION') ? VERSION : 'NOT DEFINED',
        'COMPANY_NAME' => defined('COMPANY_NAME') ? COMPANY_NAME : 'NOT DEFINED',
        'INSTALL_ROOT' => defined('INSTALL_ROOT') ? INSTALL_ROOT : 'NOT DEFINED',
    ];
} catch (Exception $e) {
    $debug['checks']['config_load'] = 'FAILED: ' . $e->getMessage();
}

// Check 3: Session
$debug['checks']['session'] = [
    'status' => session_status(),
    'id' => session_id() ?: 'NO SESSION',
];

// Check 4: Permissions
$debug['checks']['permissions'] = [
    'setup_dir_writable' => is_writable(__DIR__),
    'parent_dir_writable' => is_writable(dirname(__DIR__)),
];

// Check 5: Check for error log
$errorLogFile = __DIR__ . '/error.log';
if (file_exists($errorLogFile)) {
    $debug['error_log'] = [
        'exists' => true,
        'size' => filesize($errorLogFile),
        'last_lines' => array_slice(file($errorLogFile), -10),
    ];
} else {
    $debug['error_log'] = [
        'exists' => false
    ];
}

// Check 6: POST test
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug['post_data'] = $_POST;
    $debug['checks']['post_method'] = 'YES';
} else {
    $debug['checks']['post_method'] = 'NO (use GET to view this debug info)';
}

echo json_encode($debug, JSON_PRETTY_PRINT);
