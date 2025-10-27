<?php
/**
 * System Requirements Checker
 */

if (!defined('N8N_INSTALLER')) {
    die('Direct access not permitted');
}

class RequirementsChecker {
    private $requirements = [];

    public function __construct() {
        $this->check_all();
    }

    /**
     * Check all requirements
     */
    public function check_all() {
        $this->check_php_version();
        $this->check_php_extensions();
        $this->check_node_version();
        $this->check_npm_version();
        $this->check_file_permissions();
        $this->check_disk_space();
        $this->check_memory_limit();
        $this->check_execution_time();
    }

    /**
     * Check PHP version
     */
    private function check_php_version() {
        $current = PHP_VERSION;
        $required = N8N_REQUIRED_PHP_VERSION;

        $this->requirements['php_version'] = [
            'name' => 'PHP Version',
            'required' => $required,
            'current' => $current,
            'status' => version_compare($current, $required, '>=') ? 'passed' : 'failed',
            'message' => "PHP $current (Required: $required or higher)"
        ];
    }

    /**
     * Check required PHP extensions
     */
    private function check_php_extensions() {
        $required_extensions = [
            'curl',
            'json',
            'mbstring',
            'zip',
            'pdo',
            'openssl'
        ];

        $missing = [];
        $loaded = [];

        foreach ($required_extensions as $ext) {
            if (extension_loaded($ext)) {
                $loaded[] = $ext;
            } else {
                $missing[] = $ext;
            }
        }

        $status = empty($missing) ? 'passed' : 'failed';
        $message = empty($missing)
            ? 'All required extensions are loaded'
            : 'Missing: ' . implode(', ', $missing);

        $this->requirements['php_extensions'] = [
            'name' => 'PHP Extensions',
            'required' => implode(', ', $required_extensions),
            'current' => implode(', ', $loaded),
            'status' => $status,
            'message' => $message
        ];
    }

    /**
     * Check Node.js version
     */
    private function check_node_version() {
        $version = get_command_version('node', '--version');
        $required = N8N_REQUIRED_NODE_VERSION;

        if ($version === false) {
            $this->requirements['node_version'] = [
                'name' => 'Node.js',
                'required' => $required,
                'current' => 'Not installed',
                'status' => 'failed',
                'message' => 'Node.js not found. Please install Node.js ' . $required . ' or higher'
            ];
        } else {
            // Remove 'v' prefix from version
            $version = ltrim($version, 'v');

            $this->requirements['node_version'] = [
                'name' => 'Node.js',
                'required' => $required,
                'current' => $version,
                'status' => version_compare($version, $required, '>=') ? 'passed' : 'warning',
                'message' => "Node.js v$version" . (version_compare($version, $required, '<') ? " (Recommended: v$required or higher)" : '')
            ];
        }
    }

    /**
     * Check NPM version
     */
    private function check_npm_version() {
        $version = get_command_version('npm', '--version');

        if ($version === false) {
            $this->requirements['npm_version'] = [
                'name' => 'NPM',
                'required' => '8.0.0',
                'current' => 'Not installed',
                'status' => 'failed',
                'message' => 'NPM not found. Please install NPM'
            ];
        } else {
            $this->requirements['npm_version'] = [
                'name' => 'NPM',
                'required' => '8.0.0',
                'current' => $version,
                'status' => version_compare($version, '8.0.0', '>=') ? 'passed' : 'warning',
                'message' => "NPM v$version"
            ];
        }
    }

    /**
     * Check file permissions
     */
    private function check_file_permissions() {
        $install_root = INSTALL_ROOT;
        $writable = is_directory_writable($install_root);

        $this->requirements['file_permissions'] = [
            'name' => 'File Permissions',
            'required' => 'Writable',
            'current' => $writable ? 'Writable' : 'Not writable',
            'status' => $writable ? 'passed' : 'failed',
            'message' => $writable
                ? 'Installation directory is writable'
                : 'Installation directory is not writable. Please set proper permissions'
        ];
    }

    /**
     * Check disk space
     */
    private function check_disk_space() {
        $free_space = disk_free_space(INSTALL_ROOT);
        $required_space = 500 * 1024 * 1024; // 500 MB

        $this->requirements['disk_space'] = [
            'name' => 'Disk Space',
            'required' => format_bytes($required_space),
            'current' => format_bytes($free_space),
            'status' => $free_space >= $required_space ? 'passed' : 'warning',
            'message' => format_bytes($free_space) . ' free' . ($free_space < $required_space ? ' (Low disk space)' : '')
        ];
    }

    /**
     * Check memory limit
     */
    private function check_memory_limit() {
        $memory_limit = ini_get('memory_limit');
        $memory_bytes = $this->convert_to_bytes($memory_limit);
        $required_bytes = 256 * 1024 * 1024; // 256 MB

        $status = 'passed';
        $message = $memory_limit;

        if ($memory_limit === '-1') {
            $message = 'Unlimited (Good)';
        } elseif ($memory_bytes < $required_bytes) {
            $status = 'warning';
            $message = "$memory_limit (Recommended: 256M or higher)";
        }

        $this->requirements['memory_limit'] = [
            'name' => 'Memory Limit',
            'required' => '256M',
            'current' => $memory_limit,
            'status' => $status,
            'message' => $message
        ];
    }

    /**
     * Check max execution time
     */
    private function check_execution_time() {
        $max_execution_time = ini_get('max_execution_time');
        $required = 300; // 5 minutes

        $status = 'passed';
        $message = $max_execution_time . ' seconds';

        if ($max_execution_time == 0) {
            $message = 'Unlimited (Good)';
        } elseif ($max_execution_time < $required) {
            $status = 'warning';
            $message = "$max_execution_time seconds (Recommended: $required or higher)";
        }

        $this->requirements['max_execution_time'] = [
            'name' => 'Max Execution Time',
            'required' => $required . 's',
            'current' => $max_execution_time == 0 ? 'Unlimited' : $max_execution_time . 's',
            'status' => $status,
            'message' => $message
        ];
    }

    /**
     * Convert PHP memory limit to bytes
     */
    private function convert_to_bytes($value) {
        $value = trim($value);

        if ($value === '-1') {
            return PHP_INT_MAX;
        }

        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Get all requirements
     */
    public function get_requirements() {
        return $this->requirements;
    }

    /**
     * Check if all requirements passed
     */
    public function all_passed() {
        foreach ($this->requirements as $req) {
            if ($req['status'] === 'failed') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get failed requirements
     */
    public function get_failed() {
        return array_filter($this->requirements, function($req) {
            return $req['status'] === 'failed';
        });
    }
}
