<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get action
$action = $_POST['action'] ?? '';

// Handle actions
switch ($action) {
    case 'check_requirements':
        checkRequirements();
        break;

    case 'test_database':
        testDatabase();
        break;

    case 'install':
        performInstall();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// Check system requirements
function checkRequirements() {
    $requirements = [];

    // HTTPS Check
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    $requirements['https'] = [
        'name' => 'HTTPS Protocol',
        'status' => $isHttps ? 'pass' : 'fail',
        'message' => $isHttps ? 'Secure HTTPS connection' : 'HTTPS required for N8N'
    ];

    // PHP Version
    $phpVersion = phpversion();
    $phpOk = version_compare($phpVersion, '7.4.0', '>=');

    $requirements['php'] = [
        'name' => 'PHP Version',
        'status' => $phpOk ? 'pass' : 'fail',
        'message' => "Current: {$phpVersion} (Required: 7.4+)"
    ];

    // Required Extensions
    $extensions = ['curl', 'mbstring', 'pdo', 'zip', 'json'];
    foreach ($extensions as $ext) {
        $loaded = extension_loaded($ext);
        $requirements["ext_{$ext}"] = [
            'name' => "PHP Extension: {$ext}",
            'status' => $loaded ? 'pass' : 'fail',
            'message' => $loaded ? 'Installed' : 'Not installed'
        ];
    }

    // Write Permission
    $installRoot = dirname(__DIR__);
    $writable = is_writable($installRoot);

    $requirements['writable'] = [
        'name' => 'Write Permission',
        'status' => $writable ? 'pass' : 'fail',
        'message' => $writable ? 'Directory is writable' : 'Cannot write to directory'
    ];

    echo json_encode([
        'success' => true,
        'requirements' => $requirements
    ]);
}

// Test database connection
function testDatabase() {
    $dbType = $_POST['db_type'] ?? 'mysql';
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';

    try {
        if ($dbType === 'sqlite') {
            $pdo = new PDO("sqlite:" . INSTALL_ROOT . "/n8n.db");
            echo json_encode(['success' => true, 'message' => 'SQLite connection successful']);
            return;
        }

        $dsn = '';
        if ($dbType === 'mysql') {
            $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}";
        } elseif ($dbType === 'postgres') {
            $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}";
        }

        $pdo = new PDO($dsn, $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo json_encode(['success' => true, 'message' => 'Database connection successful']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
    }
}

// Perform installation
function performInstall() {
    $logs = [];

    try {
        // Get form data
        $dbType = $_POST['db_type'] ?? 'mysql';
        $n8nUrl = $_POST['n8n_url'] ?? '';
        $adminEmail = $_POST['admin_email'] ?? '';
        $adminPass = $_POST['admin_pass'] ?? '';
        $encryptionKey = $_POST['encryption_key'] ?? '';

        // Validate
        if (empty($n8nUrl) || empty($adminEmail) || empty($adminPass) || empty($encryptionKey)) {
            throw new Exception('Missing required fields');
        }

        // Validate HTTPS
        $parsed = parse_url($n8nUrl);
        if (!isset($parsed['scheme']) || strtolower($parsed['scheme']) !== 'https') {
            throw new Exception('N8N URL must use HTTPS protocol');
        }

        // Validate encryption key
        if (strlen($encryptionKey) < 32) {
            throw new Exception('Encryption key must be at least 32 characters');
        }

        $logs[] = '✓ Configuration validated';

        // Create .env file
        $envContent = "N8N_BASIC_AUTH_ACTIVE=true\n";
        $envContent .= "N8N_BASIC_AUTH_USER=" . $adminEmail . "\n";
        $envContent .= "N8N_BASIC_AUTH_PASSWORD=" . $adminPass . "\n";
        $envContent .= "N8N_ENCRYPTION_KEY=" . $encryptionKey . "\n";
        $envContent .= "N8N_HOST=" . $n8nUrl . "\n";
        $envContent .= "N8N_PROTOCOL=https\n";
        $envContent .= "N8N_PORT=5678\n";

        // Database config
        if ($dbType === 'sqlite') {
            $envContent .= "DB_TYPE=sqlite\n";
            $envContent .= "DB_SQLITE_DATABASE=" . INSTALL_ROOT . "/n8n.db\n";
        } elseif ($dbType === 'mysql') {
            $envContent .= "DB_TYPE=mysqldb\n";
            $envContent .= "DB_MYSQLDB_HOST=" . ($_POST['db_host'] ?? 'localhost') . "\n";
            $envContent .= "DB_MYSQLDB_PORT=" . ($_POST['db_port'] ?? '3306') . "\n";
            $envContent .= "DB_MYSQLDB_DATABASE=" . ($_POST['db_name'] ?? '') . "\n";
            $envContent .= "DB_MYSQLDB_USER=" . ($_POST['db_user'] ?? '') . "\n";
            $envContent .= "DB_MYSQLDB_PASSWORD=" . ($_POST['db_pass'] ?? '') . "\n";
        } elseif ($dbType === 'postgres') {
            $envContent .= "DB_TYPE=postgresdb\n";
            $envContent .= "DB_POSTGRESDB_HOST=" . ($_POST['db_host'] ?? 'localhost') . "\n";
            $envContent .= "DB_POSTGRESDB_PORT=" . ($_POST['db_port'] ?? '5432') . "\n";
            $envContent .= "DB_POSTGRESDB_DATABASE=" . ($_POST['db_name'] ?? '') . "\n";
            $envContent .= "DB_POSTGRESDB_USER=" . ($_POST['db_user'] ?? '') . "\n";
            $envContent .= "DB_POSTGRESDB_PASSWORD=" . ($_POST['db_pass'] ?? '') . "\n";
        }

        // Create n8n directory
        $n8nDir = INSTALL_ROOT . '/n8n';
        if (!is_dir($n8nDir)) {
            mkdir($n8nDir, 0755, true);
            $logs[] = '✓ Created N8N directory';
        }

        // Write .env file
        $envFile = $n8nDir . '/.env';
        if (file_put_contents($envFile, $envContent)) {
            $logs[] = '✓ Created .env configuration file';
        } else {
            throw new Exception('Failed to create .env file');
        }

        // Create installation info
        $info = [
            'installed_at' => date('Y-m-d H:i:s'),
            'version' => VERSION,
            'build' => BUILD,
            'url' => $n8nUrl,
            'admin_email' => $adminEmail,
            'company' => COMPANY_NAME
        ];

        file_put_contents($n8nDir . '/install-info.json', json_encode($info, JSON_PRETTY_PRINT));
        $logs[] = '✓ Created installation info';

        // Mark as complete
        $_SESSION['install_complete'] = true;
        $logs[] = '✓ Installation completed successfully';

        echo json_encode([
            'success' => true,
            'message' => 'Installation completed',
            'logs' => $logs
        ]);

    } catch (Exception $e) {
        $logs[] = '✗ Error: ' . $e->getMessage();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'logs' => $logs
        ]);
    }
}
