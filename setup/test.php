<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

echo "PHP Test Page\n\n";

// Test 1: Basic PHP
echo "✓ PHP is working\n";
echo "✓ PHP Version: " . phpversion() . "\n\n";

// Test 2: Check files exist
echo "Checking files:\n";
$files = ['config.php', 'install.php', 'index.php'];
foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file NOT FOUND\n";
    }
}
echo "\n";

// Test 3: Try to include config
echo "Testing config.php:\n";
try {
    define('N8N_INSTALLER', true);
    require_once __DIR__ . '/config.php';
    echo "✓ config.php loaded successfully\n";
    echo "✓ VERSION: " . (defined('VERSION') ? VERSION : 'NOT DEFINED') . "\n";
    echo "✓ COMPANY_NAME: " . (defined('COMPANY_NAME') ? COMPANY_NAME : 'NOT DEFINED') . "\n";
} catch (Exception $e) {
    echo "✗ Error loading config.php: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Test JSON
echo "Testing JSON:\n";
$data = ['success' => true, 'message' => 'Test'];
$json = json_encode($data);
echo "✓ JSON encode: " . $json . "\n";
echo "\n";

// Test 5: Test exec
echo "Testing exec:\n";
$output = [];
$return = 0;
@exec('which php 2>&1', $output, $return);
echo "✓ exec() works\n";
echo "  Return code: $return\n";
echo "  Output: " . implode(', ', $output) . "\n";
echo "\n";

// Test 6: Check permissions
echo "Checking permissions:\n";
$installRoot = dirname(__DIR__);
echo "  Install root: $installRoot\n";
echo "  Writable: " . (is_writable($installRoot) ? 'YES' : 'NO') . "\n";
echo "\n";

echo "All tests completed!\n";
