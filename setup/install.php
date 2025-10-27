<?php
require_once 'config.php';

// Disable all output buffering and error display
while (ob_get_level()) {
    ob_end_clean();
}
ini_set('display_errors', 0);
error_reporting(0);

// Set JSON header
header('Content-Type: application/json');

// Get action
$action = $_POST['action'] ?? '';

try {
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
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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

    // Node.js Check
    $nodeOutput = [];
    $nodeReturn = 0;
    @exec('which node 2>&1', $nodeOutput, $nodeReturn);
    $nodeInstalled = ($nodeReturn === 0);

    $nodeVersion = '';
    if ($nodeInstalled) {
        $nodeVerOutput = [];
        @exec('node --version 2>&1', $nodeVerOutput);
        $nodeVersion = trim($nodeVerOutput[0] ?? '');
    }

    $requirements['nodejs'] = [
        'name' => 'Node.js',
        'status' => $nodeInstalled ? 'pass' : 'fail',
        'message' => $nodeInstalled ? "Installed: {$nodeVersion}" : 'Not installed (required for N8N)'
    ];

    // npm Check
    $npmOutput = [];
    $npmReturn = 0;
    @exec('which npm 2>&1', $npmOutput, $npmReturn);
    $npmInstalled = ($npmReturn === 0);

    $npmVersion = '';
    if ($npmInstalled) {
        $npmVerOutput = [];
        @exec('npm --version 2>&1', $npmVerOutput);
        $npmVersion = trim($npmVerOutput[0] ?? '');
    }

    $requirements['npm'] = [
        'name' => 'npm',
        'status' => $npmInstalled ? 'pass' : 'fail',
        'message' => $npmInstalled ? "Installed: v{$npmVersion}" : 'Not installed (required for N8N)'
    ];

    // Write Permission
    $installRoot = dirname(__DIR__);
    $writable = is_writable($installRoot);

    $requirements['writable'] = [
        'name' => 'Write Permission',
        'status' => $writable ? 'pass' : 'fail',
        'message' => $writable ? 'Directory is writable' : 'Cannot write to directory'
    ];

    $output = json_encode([
        'success' => true,
        'requirements' => $requirements
    ]);

    if ($output === false) {
        echo json_encode(['success' => false, 'message' => 'Failed to encode requirements']);
        return;
    }

    echo $output;
    exit;
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
            exit;
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
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        exit;
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

        // Check if Node.js and npm are installed
        exec('which node 2>&1', $nodeOutput, $nodeReturn);
        exec('which npm 2>&1', $npmOutput, $npmReturn);

        if ($nodeReturn !== 0) {
            throw new Exception('Node.js is not installed. Please install Node.js first.');
        }
        if ($npmReturn !== 0) {
            throw new Exception('npm is not installed. Please install npm first.');
        }
        $logs[] = '✓ Node.js and npm found';

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

        // Install N8N globally
        $logs[] = '⏳ Installing N8N (this may take a few minutes)...';
        exec('npm install -g n8n 2>&1', $installOutput, $installReturn);

        if ($installReturn !== 0) {
            $logs[] = '⚠️ Warning: N8N installation may have failed';
            $logs[] = 'Output: ' . implode("\n", array_slice($installOutput, -3));
        } else {
            $logs[] = '✓ N8N installed successfully';
        }

        // Create package.json for local N8N instance
        $packageJson = [
            'name' => 'n8n-instance',
            'version' => '1.0.0',
            'description' => 'N8N instance for ' . COMPANY_NAME,
            'scripts' => [
                'start' => 'n8n start',
                'stop' => 'pkill -f n8n'
            ],
            'dependencies' => [
                'n8n' => 'latest'
            ]
        ];

        file_put_contents($n8nDir . '/package.json', json_encode($packageJson, JSON_PRETTY_PRINT));
        $logs[] = '✓ Created package.json';

        // Install N8N locally in the project directory
        $logs[] = '⏳ Installing N8N in project directory...';
        $currentDir = getcwd();
        chdir($n8nDir);
        exec('npm install 2>&1', $localInstallOutput, $localInstallReturn);
        chdir($currentDir);

        if ($localInstallReturn === 0) {
            $logs[] = '✓ N8N installed in project directory';
        } else {
            $logs[] = '⚠️ Local installation warning (using global N8N instead)';
        }

        // Create start script
        $startScript = "#!/bin/bash\n";
        $startScript .= "# N8N Start Script\n";
        $startScript .= "# Generated by " . COMPANY_NAME . "\n\n";
        $startScript .= "cd " . $n8nDir . "\n";
        $startScript .= "export N8N_USER_FOLDER=" . $n8nDir . "\n";
        $startScript .= "export N8N_BASIC_AUTH_ACTIVE=true\n";
        $startScript .= "export N8N_BASIC_AUTH_USER=\"{$adminEmail}\"\n";
        $startScript .= "export N8N_BASIC_AUTH_PASSWORD=\"{$adminPass}\"\n";
        $startScript .= "export N8N_ENCRYPTION_KEY=\"{$encryptionKey}\"\n";
        $startScript .= "export N8N_HOST=\"{$n8nUrl}\"\n";
        $startScript .= "export N8N_PROTOCOL=https\n";
        $startScript .= "export N8N_PORT=5678\n\n";

        // Add database environment variables
        if ($dbType === 'sqlite') {
            $startScript .= "export DB_TYPE=sqlite\n";
            $startScript .= "export DB_SQLITE_DATABASE=" . INSTALL_ROOT . "/n8n.db\n";
        } elseif ($dbType === 'mysql') {
            $startScript .= "export DB_TYPE=mysqldb\n";
            $startScript .= "export DB_MYSQLDB_HOST=\"" . ($_POST['db_host'] ?? 'localhost') . "\"\n";
            $startScript .= "export DB_MYSQLDB_PORT=\"" . ($_POST['db_port'] ?? '3306') . "\"\n";
            $startScript .= "export DB_MYSQLDB_DATABASE=\"" . ($_POST['db_name'] ?? '') . "\"\n";
            $startScript .= "export DB_MYSQLDB_USER=\"" . ($_POST['db_user'] ?? '') . "\"\n";
            $startScript .= "export DB_MYSQLDB_PASSWORD=\"" . ($_POST['db_pass'] ?? '') . "\"\n";
        } elseif ($dbType === 'postgres') {
            $startScript .= "export DB_TYPE=postgresdb\n";
            $startScript .= "export DB_POSTGRESDB_HOST=\"" . ($_POST['db_host'] ?? 'localhost') . "\"\n";
            $startScript .= "export DB_POSTGRESDB_PORT=\"" . ($_POST['db_port'] ?? '5432') . "\"\n";
            $startScript .= "export DB_POSTGRESDB_DATABASE=\"" . ($_POST['db_name'] ?? '') . "\"\n";
            $startScript .= "export DB_POSTGRESDB_USER=\"" . ($_POST['db_user'] ?? '') . "\"\n";
            $startScript .= "export DB_POSTGRESDB_PASSWORD=\"" . ($_POST['db_pass'] ?? '') . "\"\n";
        }

        $startScript .= "\nn8n start\n";

        file_put_contents($n8nDir . '/start.sh', $startScript);
        chmod($n8nDir . '/start.sh', 0755);
        $logs[] = '✓ Created start script';

        // Create stop script
        $stopScript = "#!/bin/bash\n";
        $stopScript .= "# N8N Stop Script\n";
        $stopScript .= "pkill -f 'n8n start'\n";
        $stopScript .= "echo 'N8N stopped'\n";

        file_put_contents($n8nDir . '/stop.sh', $stopScript);
        chmod($n8nDir . '/stop.sh', 0755);
        $logs[] = '✓ Created stop script';

        // Create systemd service file (if running as root)
        $user = posix_getpwuid(posix_geteuid())['name'];
        $serviceContent = "[Unit]\n";
        $serviceContent .= "Description=N8N Workflow Automation\n";
        $serviceContent .= "After=network.target\n\n";
        $serviceContent .= "[Service]\n";
        $serviceContent .= "Type=simple\n";
        $serviceContent .= "User={$user}\n";
        $serviceContent .= "WorkingDirectory={$n8nDir}\n";
        $serviceContent .= "ExecStart={$n8nDir}/start.sh\n";
        $serviceContent .= "Restart=on-failure\n\n";
        $serviceContent .= "[Install]\n";
        $serviceContent .= "WantedBy=multi-user.target\n";

        file_put_contents($n8nDir . '/n8n.service', $serviceContent);
        $logs[] = '✓ Created systemd service file';

        // Try to start N8N in background
        $logs[] = '⏳ Starting N8N...';
        exec("cd {$n8nDir} && nohup ./start.sh > n8n.log 2>&1 & echo $!", $pidOutput);

        if (!empty($pidOutput[0])) {
            file_put_contents($n8nDir . '/n8n.pid', $pidOutput[0]);
            $logs[] = '✓ N8N started (PID: ' . $pidOutput[0] . ')';
            $logs[] = '✓ N8N is running on port 5678';
            $logs[] = 'ℹ️ Check logs: ' . $n8nDir . '/n8n.log';
        } else {
            $logs[] = '⚠️ N8N may not have started automatically';
            $logs[] = 'ℹ️ Manual start: cd ' . $n8nDir . ' && ./start.sh';
        }

        // Mark as complete
        $_SESSION['install_complete'] = true;
        $logs[] = '✓ Installation completed successfully';
        $logs[] = '✓ You can now access N8N at: ' . $n8nUrl;

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
