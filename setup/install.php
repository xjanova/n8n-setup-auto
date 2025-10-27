<?php
/**
 * N8N Installation Processor
 * Handles all AJAX requests and installation logic
 */

define('N8N_INSTALLER', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/requirements.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/n8n-installer.php';

// Load language
$lang = load_language();

// Handle POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

// Get action
$action = sanitize_input($_POST['action'] ?? '');

if (empty($action)) {
    json_response(false, 'No action specified');
}

// Route to appropriate handler
switch ($action) {
    case 'change_language':
        handle_change_language();
        break;

    case 'check_requirements':
        handle_check_requirements();
        break;

    case 'test_database':
        handle_test_database();
        break;

    case 'install':
        handle_installation();
        break;

    case 'cleanup':
        handle_cleanup();
        break;

    default:
        json_response(false, 'Invalid action');
}

/**
 * Handle language change
 */
function handle_change_language() {
    $language = sanitize_input($_POST['language'] ?? DEFAULT_LANGUAGE);

    if (!in_array($language, ['th', 'en'])) {
        $language = DEFAULT_LANGUAGE;
    }

    $_SESSION['language'] = $language;

    // Redirect back to installer
    header('Location: index.php');
    exit;
}

/**
 * Handle requirements check
 */
function handle_check_requirements() {
    $checker = new RequirementsChecker();
    $requirements = $checker->get_requirements();

    json_response(true, 'Requirements checked', [
        'requirements' => $requirements,
        'all_passed' => $checker->all_passed()
    ]);
}

/**
 * Handle database connection test
 */
function handle_test_database() {
    global $lang;

    $config = [
        'type' => sanitize_input($_POST['db_type'] ?? 'mysql'),
        'host' => sanitize_input($_POST['db_host'] ?? 'localhost'),
        'port' => (int) ($_POST['db_port'] ?? 3306),
        'database' => sanitize_input($_POST['db_name'] ?? ''),
        'username' => sanitize_input($_POST['db_user'] ?? ''),
        'password' => $_POST['db_password'] ?? '', // Don't sanitize password
        'prefix' => sanitize_input($_POST['db_prefix'] ?? 'n8n_')
    ];

    // Validate required fields
    if (empty($config['host']) || empty($config['database']) || empty($config['username'])) {
        json_response(false, $lang['error_database'] ?? 'Missing required fields');
    }

    $db = new DatabaseHandler($config);
    $result = $db->test_connection();

    if ($result['success']) {
        // Store config in session for later use
        $_SESSION['db_config'] = $config;
        json_response(true, $lang['database_success'] ?? 'Connection successful');
    } else {
        json_response(false, $result['message']);
    }
}

/**
 * Handle installation
 */
function handle_installation() {
    global $lang;

    // Collect all configuration
    $config = [
        // Database settings
        'db_type' => sanitize_input($_POST['db_type'] ?? 'mysql'),
        'db_host' => sanitize_input($_POST['db_host'] ?? 'localhost'),
        'db_port' => (int) ($_POST['db_port'] ?? 3306),
        'db_name' => sanitize_input($_POST['db_name'] ?? ''),
        'db_user' => sanitize_input($_POST['db_user'] ?? ''),
        'db_password' => $_POST['db_password'] ?? '',
        'db_prefix' => sanitize_input($_POST['db_prefix'] ?? 'n8n_'),

        // N8N settings
        'n8n_url' => sanitize_input($_POST['n8n_url'] ?? ''),
        'n8n_port' => (int) ($_POST['n8n_port'] ?? 5678),
        'admin_email' => sanitize_input($_POST['admin_email'] ?? ''),
        'admin_password' => $_POST['admin_password'] ?? '',
        'timezone' => sanitize_input($_POST['timezone'] ?? 'Asia/Bangkok'),
        'encryption_key' => sanitize_input($_POST['encryption_key'] ?? ''),
        'install_location' => sanitize_input($_POST['install_location'] ?? INSTALL_ROOT)
    ];

    // Validate configuration
    $validation_errors = validate_config($config);
    if (!empty($validation_errors)) {
        json_response(false, implode(', ', $validation_errors));
    }

    // Create installer instance
    $installer = new N8NInstaller($config['install_location']);
    $installer->set_config($config);

    // Perform installation
    $result = $installer->install();

    if ($result['success']) {
        // Store installation info in session
        $_SESSION['install_info'] = $installer->get_install_info();
        $_SESSION['install_complete'] = true;

        json_response(true, $lang['success_installation'] ?? 'Installation completed successfully', [
            'install_info' => $installer->get_install_info()
        ]);
    } else {
        json_response(false, $result['message']);
    }
}

/**
 * Validate configuration
 */
function validate_config($config) {
    $errors = [];

    // Database validation
    if (empty($config['db_host'])) {
        $errors[] = 'Database host is required';
    }
    if (empty($config['db_name'])) {
        $errors[] = 'Database name is required';
    }
    if (empty($config['db_user'])) {
        $errors[] = 'Database user is required';
    }

    // N8N validation
    if (empty($config['n8n_url']) || !validate_url($config['n8n_url'])) {
        $errors[] = 'Valid N8N URL is required';
    }
    if (empty($config['admin_email']) || !validate_email($config['admin_email'])) {
        $errors[] = 'Valid admin email is required';
    }
    if (empty($config['admin_password']) || strlen($config['admin_password']) < 8) {
        $errors[] = 'Admin password must be at least 8 characters';
    }
    if (empty($config['encryption_key']) || strlen($config['encryption_key']) < 32) {
        $errors[] = 'Encryption key must be at least 32 characters';
    }

    return $errors;
}

/**
 * Handle cleanup
 */
function handle_cleanup() {
    global $lang;

    $result = N8NInstaller::cleanup();

    if ($result['success']) {
        // Clear session
        session_destroy();

        json_response(true, $lang['success_cleanup'] ?? 'Installation files removed successfully');
    } else {
        json_response(false, $result['message']);
    }
}
