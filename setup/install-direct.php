<?php
require_once 'config.php';

// Disable output buffering for real-time streaming
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

// Function to send log to client
function sendLog($message, $type = 'info') {
    $data = json_encode([
        'message' => $message,
        'type' => $type,
        'time' => date('H:i:s')
    ]);
    echo "data: {$data}\n\n";
    if (ob_get_level()) ob_flush();
    flush();
    usleep(50000); // 0.05 second delay
}

try {
    sendLog('=================================================', 'header');
    sendLog('  N8N Installation via Generated Script', 'header');
    sendLog('  ' . COMPANY_NAME, 'header');
    sendLog('=================================================', 'header');
    sendLog('');

    // Get form data
    $dbType = $_POST['db_type'] ?? 'sqlite';
    $n8nUrl = $_POST['n8n_url'] ?? '';
    $adminEmail = $_POST['admin_email'] ?? '';
    $adminPass = $_POST['admin_pass'] ?? '';
    $encryptionKey = $_POST['encryption_key'] ?? '';
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';

    sendLog('[1/8] Validating configuration...', 'step');

    // Validate
    if (empty($n8nUrl) || empty($adminEmail) || empty($adminPass) || empty($encryptionKey)) {
        sendLog('ERROR: Missing required fields', 'error');
        echo "data: " . json_encode(['complete' => true, 'success' => false]) . "\n\n";
        flush();
        exit;
    }

    // Validate HTTPS
    $parsed = parse_url($n8nUrl);
    if (!isset($parsed['scheme']) || strtolower($parsed['scheme']) !== 'https') {
        sendLog('ERROR: N8N URL must use HTTPS protocol', 'error');
        echo "data: " . json_encode(['complete' => true, 'success' => false]) . "\n\n";
        flush();
        exit;
    }

    sendLog('✓ Configuration validated', 'success');
    sendLog('');

    // Check available functions
    sendLog('[2/8] Checking available shell functions...', 'step');
    $shellFunction = null;

    if (function_exists('proc_open')) {
        $shellFunction = 'proc_open';
        sendLog('✓ Using proc_open()', 'success');
    } elseif (function_exists('shell_exec')) {
        $shellFunction = 'shell_exec';
        sendLog('✓ Using shell_exec()', 'success');
    } elseif (function_exists('passthru')) {
        $shellFunction = 'passthru';
        sendLog('✓ Using passthru()', 'success');
    } else {
        sendLog('ERROR: No shell execution functions available', 'error');
        sendLog('Please enable at least one: proc_open, shell_exec, or passthru', 'error');
        echo "data: " . json_encode(['complete' => true, 'success' => false]) . "\n\n";
        flush();
        exit;
    }
    sendLog('');

    // Create installation directory
    sendLog('[3/8] Creating installation directory...', 'step');
    $installDir = INSTALL_ROOT . '/n8n';
    if (!is_dir($installDir)) {
        mkdir($installDir, 0755, true);
    }
    sendLog('✓ Directory: ' . $installDir, 'success');
    sendLog('');

    // Generate installation script
    sendLog('[4/8] Generating installation script...', 'step');

    $scriptContent = "#!/bin/bash\n";
    $scriptContent .= "# N8N Installation Script\n";
    $scriptContent .= "# Generated: " . date('Y-m-d H:i:s') . "\n";
    $scriptContent .= "# Xman Enterprise co.,ltd.\n\n";
    $scriptContent .= "set -e\n\n";

    $scriptContent .= "cd " . escapeshellarg($installDir) . "\n\n";

    // Create package.json
    $scriptContent .= "cat > package.json <<'PACKAGE_EOF'\n";
    $scriptContent .= json_encode([
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
    ], JSON_PRETTY_PRINT) . "\n";
    $scriptContent .= "PACKAGE_EOF\n\n";

    $scriptContent .= "echo '✓ Created package.json'\n\n";

    // Create .env file
    $scriptContent .= "cat > .env <<'ENV_EOF'\n";
    $scriptContent .= "N8N_BASIC_AUTH_ACTIVE=true\n";
    $scriptContent .= "N8N_BASIC_AUTH_USER=" . $adminEmail . "\n";
    $scriptContent .= "N8N_BASIC_AUTH_PASSWORD=" . $adminPass . "\n";
    $scriptContent .= "N8N_ENCRYPTION_KEY=" . $encryptionKey . "\n";
    $scriptContent .= "N8N_HOST=" . $n8nUrl . "\n";
    $scriptContent .= "N8N_PROTOCOL=https\n";
    $scriptContent .= "N8N_PORT=5678\n";
    $scriptContent .= "N8N_USER_FOLDER=" . $installDir . "\n\n";

    if ($dbType === 'sqlite') {
        $scriptContent .= "DB_TYPE=sqlite\n";
        $scriptContent .= "DB_SQLITE_DATABASE=" . $installDir . "/n8n.db\n";
    } elseif ($dbType === 'mysql') {
        $scriptContent .= "DB_TYPE=mysqldb\n";
        $scriptContent .= "DB_MYSQLDB_HOST=" . $dbHost . "\n";
        $scriptContent .= "DB_MYSQLDB_PORT=" . $dbPort . "\n";
        $scriptContent .= "DB_MYSQLDB_DATABASE=" . $dbName . "\n";
        $scriptContent .= "DB_MYSQLDB_USER=" . $dbUser . "\n";
        $scriptContent .= "DB_MYSQLDB_PASSWORD=" . $dbPass . "\n";
    } elseif ($dbType === 'postgres') {
        $scriptContent .= "DB_TYPE=postgresdb\n";
        $scriptContent .= "DB_POSTGRESDB_HOST=" . $dbHost . "\n";
        $scriptContent .= "DB_POSTGRESDB_PORT=" . $dbPort . "\n";
        $scriptContent .= "DB_POSTGRESDB_DATABASE=" . $dbName . "\n";
        $scriptContent .= "DB_POSTGRESDB_USER=" . $dbUser . "\n";
        $scriptContent .= "DB_POSTGRESDB_PASSWORD=" . $dbPass . "\n";
    }

    $scriptContent .= "ENV_EOF\n\n";
    $scriptContent .= "echo '✓ Created .env file'\n\n";

    // Install N8N
    $scriptContent .= "echo '⏳ Installing N8N...'\n";
    $scriptContent .= "npm install 2>&1\n\n";

    // Create start.sh
    $scriptContent .= "cat > start.sh <<'START_EOF'\n";
    $scriptContent .= "#!/bin/bash\n";
    $scriptContent .= "cd " . escapeshellarg($installDir) . "\n";
    $scriptContent .= "source .env\n";
    $scriptContent .= "exec npx n8n start\n";
    $scriptContent .= "START_EOF\n\n";
    $scriptContent .= "chmod +x start.sh\n";
    $scriptContent .= "echo '✓ Created start.sh'\n\n";

    // Create stop.sh
    $scriptContent .= "cat > stop.sh <<'STOP_EOF'\n";
    $scriptContent .= "#!/bin/bash\n";
    $scriptContent .= "pkill -f 'n8n start'\n";
    $scriptContent .= "echo 'N8N stopped'\n";
    $scriptContent .= "STOP_EOF\n\n";
    $scriptContent .= "chmod +x stop.sh\n";
    $scriptContent .= "echo '✓ Created stop.sh'\n\n";

    // Start N8N
    $scriptContent .= "nohup ./start.sh > n8n.log 2>&1 & echo $! > n8n.pid\n";
    $scriptContent .= "echo '✓ N8N started'\n";
    $scriptContent .= "echo '✓ PID: '$(cat n8n.pid)\n";

    // Save script
    $scriptPath = $installDir . '/install-generated.sh';
    file_put_contents($scriptPath, $scriptContent);
    chmod($scriptPath, 0755);

    sendLog('✓ Script generated: install-generated.sh', 'success');
    sendLog('');

    // Execute script
    sendLog('[5/8] Executing installation script...', 'step');
    sendLog('This may take 2-5 minutes', 'info');
    sendLog('');

    if ($shellFunction === 'proc_open') {
        // Use proc_open for real-time output
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open("bash " . escapeshellarg($scriptPath) . " 2>&1", $descriptorspec, $pipes);

        if (is_resource($process)) {
            fclose($pipes[0]);

            stream_set_blocking($pipes[1], false);

            $output = '';
            $lineCount = 0;

            while (!feof($pipes[1])) {
                $line = fgets($pipes[1]);
                if ($line !== false && trim($line) !== '') {
                    $lineCount++;
                    // Show every line
                    sendLog('  ' . trim($line), 'info');
                }
                usleep(100000); // 0.1 second
            }

            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnValue = proc_close($process);

            if ($returnValue === 0) {
                sendLog('', 'success');
                sendLog('✓ Installation script completed successfully', 'success');
            } else {
                sendLog('', 'warning');
                sendLog('⚠ Script exited with code: ' . $returnValue, 'warning');
            }
        } else {
            sendLog('ERROR: Failed to execute script', 'error');
        }
    } else {
        // Fallback to shell_exec or passthru
        if ($shellFunction === 'shell_exec') {
            $output = shell_exec("bash " . escapeshellarg($scriptPath) . " 2>&1");
        } else {
            ob_start();
            passthru("bash " . escapeshellarg($scriptPath) . " 2>&1");
            $output = ob_get_clean();
        }

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                sendLog('  ' . trim($line), 'info');
            }
        }
        sendLog('✓ Installation completed', 'success');
    }

    sendLog('');
    sendLog('[6/8] Verifying installation...', 'step');

    // Check if files were created
    $checks = [
        'package.json' => file_exists($installDir . '/package.json'),
        '.env' => file_exists($installDir . '/.env'),
        'node_modules' => is_dir($installDir . '/node_modules'),
        'start.sh' => file_exists($installDir . '/start.sh'),
        'stop.sh' => file_exists($installDir . '/stop.sh'),
    ];

    foreach ($checks as $file => $exists) {
        if ($exists) {
            sendLog('✓ ' . $file, 'success');
        } else {
            sendLog('✗ ' . $file . ' not found', 'error');
        }
    }

    sendLog('');
    sendLog('[7/8] Saving installation info...', 'step');

    $info = [
        'installed_at' => date('Y-m-d H:i:s'),
        'version' => VERSION,
        'build' => BUILD,
        'url' => $n8nUrl,
        'admin_email' => $adminEmail,
        'database' => $dbType,
        'company' => COMPANY_NAME
    ];

    file_put_contents($installDir . '/install-info.json', json_encode($info, JSON_PRETTY_PRINT));
    sendLog('✓ Installation info saved', 'success');

    sendLog('');
    sendLog('[8/8] Checking N8N status...', 'step');

    if (file_exists($installDir . '/n8n.pid')) {
        $pid = trim(file_get_contents($installDir . '/n8n.pid'));
        sendLog('✓ N8N PID: ' . $pid, 'success');
    } else {
        sendLog('⚠ PID file not found', 'warning');
    }

    $_SESSION['install_complete'] = true;

    sendLog('');
    sendLog('=================================================', 'header');
    sendLog('  Installation Completed Successfully!', 'header');
    sendLog('=================================================', 'header');
    sendLog('');
    sendLog('✓ N8N URL: ' . $n8nUrl, 'success');
    sendLog('✓ Admin Email: ' . $adminEmail, 'success');
    sendLog('✓ Database: ' . strtoupper($dbType), 'success');
    sendLog('✓ Installation directory: ' . $installDir, 'success');
    sendLog('');
    sendLog('Next steps:', 'info');
    sendLog('1. Access N8N: ' . $n8nUrl, 'info');
    sendLog('2. Login with your admin credentials', 'info');
    sendLog('3. Check logs: tail -f ' . $installDir . '/n8n.log', 'info');
    sendLog('');
    sendLog('Management commands:', 'info');
    sendLog('- Start: cd ' . $installDir . ' && ./start.sh', 'info');
    sendLog('- Stop: cd ' . $installDir . ' && ./stop.sh', 'info');
    sendLog('- Logs: tail -f ' . $installDir . '/n8n.log', 'info');
    sendLog('');

    // Send completion signal
    echo "data: " . json_encode([
        'complete' => true,
        'success' => true,
        'url' => $n8nUrl,
        'email' => $adminEmail
    ]) . "\n\n";
    flush();

} catch (Exception $e) {
    sendLog('', 'error');
    sendLog('ERROR: ' . $e->getMessage(), 'error');
    sendLog('Installation failed!', 'error');
    echo "data: " . json_encode(['complete' => true, 'success' => false]) . "\n\n";
    flush();
}
