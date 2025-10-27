<?php
/**
 * Check if exec() function is available
 */

header('Content-Type: application/json');

$result = [
    'exec_available' => false,
    'disabled_functions' => [],
    'message' => '',
    'php_ini_path' => php_ini_loaded_file(),
];

// Get disabled functions
$disabled = ini_get('disable_functions');
if ($disabled) {
    $result['disabled_functions'] = array_map('trim', explode(',', $disabled));
}

// Test if exec() works
if (function_exists('exec')) {
    try {
        $output = [];
        $return_var = 0;
        @exec('echo "test" 2>&1', $output, $return_var);

        if ($return_var === 0 && !empty($output)) {
            $result['exec_available'] = true;
            $result['message'] = 'exec() is available and working';
        } else {
            $result['message'] = 'exec() exists but may not be working properly';
        }
    } catch (Exception $e) {
        $result['message'] = 'exec() exists but threw error: ' . $e->getMessage();
    }
} else {
    $result['message'] = 'exec() function is disabled in PHP configuration';
}

// Additional info
$result['alternatives'] = [
    'shell_exec' => function_exists('shell_exec'),
    'system' => function_exists('system'),
    'passthru' => function_exists('passthru'),
    'proc_open' => function_exists('proc_open'),
];

echo json_encode($result, JSON_PRETTY_PRINT);
