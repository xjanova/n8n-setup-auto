<?php
/**
 * N8N Installation Handler
 */

if (!defined('N8N_INSTALLER')) {
    die('Direct access not permitted');
}

class N8NInstaller {
    private $install_path;
    private $config = [];
    private $db_handler;

    /**
     * Constructor
     */
    public function __construct($install_path = null) {
        $this->install_path = $install_path ?? INSTALL_ROOT;
        $this->db_handler = new DatabaseHandler();
    }

    /**
     * Set configuration
     */
    public function set_config($config) {
        $this->config = $config;

        // Configure database handler
        $this->db_handler->configure([
            'type' => $config['db_type'] ?? 'mysql',
            'host' => $config['db_host'] ?? 'localhost',
            'port' => $config['db_port'] ?? 3306,
            'database' => $config['db_name'] ?? '',
            'username' => $config['db_user'] ?? '',
            'password' => $config['db_password'] ?? '',
            'prefix' => $config['db_prefix'] ?? 'n8n_'
        ]);
    }

    /**
     * Perform installation
     */
    public function install() {
        try {
            log_message('Starting N8N installation');

            // Step 1: Download N8N
            $this->download_n8n();

            // Step 2: Setup directory structure
            $this->setup_directories();

            // Step 3: Create database tables
            $result = $this->db_handler->create_tables();
            if (!$result['success']) {
                throw new Exception('Database setup failed: ' . $result['message']);
            }

            // Step 4: Create configuration files
            $this->create_configuration();

            // Step 5: Install dependencies
            $this->install_dependencies();

            // Step 6: Insert initial data
            $result = $this->db_handler->insert_initial_data(
                $this->config['admin_email'],
                $this->config['admin_password']
            );

            if (!$result['success']) {
                throw new Exception('Initial data setup failed: ' . $result['message']);
            }

            // Step 7: Set permissions
            $this->set_permissions();

            log_message('N8N installation completed successfully');

            return ['success' => true, 'message' => 'Installation completed successfully'];
        } catch (Exception $e) {
            log_message('Installation failed: ' . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Download N8N
     */
    private function download_n8n() {
        log_message('Downloading N8N...');

        // For demonstration, we'll use npm to install n8n
        // In production, you might want to download a specific version or package

        $n8n_dir = $this->install_path . '/n8n';

        if (!is_dir($n8n_dir)) {
            mkdir($n8n_dir, 0755, true);
        }

        // Create package.json
        $package_json = [
            'name' => 'n8n-installation',
            'version' => '1.0.0',
            'description' => 'N8N Workflow Automation',
            'dependencies' => [
                'n8n' => 'latest'
            ]
        ];

        file_put_contents(
            $n8n_dir . '/package.json',
            json_encode($package_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        log_message('N8N download prepared');
    }

    /**
     * Setup directory structure
     */
    private function setup_directories() {
        log_message('Setting up directory structure...');

        $directories = [
            $this->install_path . '/n8n',
            $this->install_path . '/n8n/.n8n',
            $this->install_path . '/n8n/.n8n/workflows',
            $this->install_path . '/n8n/.n8n/credentials',
            $this->install_path . '/logs',
            $this->install_path . '/backups'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                log_message("Created directory: $dir");
            }
        }
    }

    /**
     * Create configuration files
     */
    private function create_configuration() {
        log_message('Creating configuration files...');

        $n8n_dir = $this->install_path . '/n8n';

        // Create .env file for N8N
        $env_config = [
            'n8n_basic_auth_active' => 'true',
            'n8n_basic_auth_user' => $this->config['admin_email'],
            'n8n_basic_auth_password' => $this->config['admin_password'],
            'n8n_host' => parse_url($this->config['n8n_url'], PHP_URL_HOST) ?? 'localhost',
            'n8n_port' => $this->config['n8n_port'] ?? 5678,
            'n8n_protocol' => parse_url($this->config['n8n_url'], PHP_URL_SCHEME) ?? 'http',
            'webhook_url' => $this->config['n8n_url'],
            'generic_timezone' => $this->config['timezone'] ?? 'Asia/Bangkok',
            'n8n_encryption_key' => $this->config['encryption_key'],
            'db_type' => $this->config['db_type'],
            'db_' . $this->config['db_type'] . '_host' => $this->config['db_host'],
            'db_' . $this->config['db_type'] . '_port' => $this->config['db_port'],
            'db_' . $this->config['db_type'] . '_database' => $this->config['db_name'],
            'db_' . $this->config['db_type'] . '_user' => $this->config['db_user'],
            'db_' . $this->config['db_type'] . '_password' => $this->config['db_password'],
            'db_table_prefix' => $this->config['db_prefix'],
            'n8n_log_level' => 'info',
            'n8n_log_output' => 'console,file',
            'n8n_log_file_location' => $this->install_path . '/logs/',
        ];

        create_env_file($n8n_dir . '/.env', $env_config);
        log_message('Created .env file');

        // Create start script
        $start_script = "#!/bin/bash\n";
        $start_script .= "cd " . escapeshellarg($n8n_dir) . "\n";
        $start_script .= "export N8N_USER_FOLDER=\".n8n\"\n";
        $start_script .= "npx n8n start\n";

        file_put_contents($this->install_path . '/start-n8n.sh', $start_script);
        chmod($this->install_path . '/start-n8n.sh', 0755);
        log_message('Created start script');

        // Create systemd service file (optional)
        $this->create_systemd_service();
    }

    /**
     * Create systemd service file
     */
    private function create_systemd_service() {
        $service_content = "[Unit]\n";
        $service_content .= "Description=N8N Workflow Automation\n";
        $service_content .= "After=network.target\n\n";
        $service_content .= "[Service]\n";
        $service_content .= "Type=simple\n";
        $service_content .= "User=www-data\n";
        $service_content .= "WorkingDirectory=" . $this->install_path . "/n8n\n";
        $service_content .= "ExecStart=/usr/bin/npx n8n start\n";
        $service_content .= "Restart=on-failure\n\n";
        $service_content .= "[Install]\n";
        $service_content .= "WantedBy=multi-user.target\n";

        file_put_contents($this->install_path . '/n8n.service', $service_content);
        log_message('Created systemd service file');
    }

    /**
     * Install dependencies
     */
    private function install_dependencies() {
        log_message('Installing N8N dependencies...');

        $n8n_dir = $this->install_path . '/n8n';

        // Install N8N via npm
        $command = "cd " . escapeshellarg($n8n_dir) . " && npm install 2>&1";

        $output = [];
        $return_var = 0;

        exec($command, $output, $return_var);

        if ($return_var === 0) {
            log_message('N8N dependencies installed successfully');
        } else {
            log_message('NPM install output: ' . implode("\n", $output), 'WARNING');
            // Don't fail, as npm might not be available during web installation
        }
    }

    /**
     * Set proper permissions
     */
    private function set_permissions() {
        log_message('Setting file permissions...');

        $directories = [
            $this->install_path . '/n8n',
            $this->install_path . '/logs',
            $this->install_path . '/backups'
        ];

        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                chmod($dir, 0755);
                log_message("Set permissions for: $dir");
            }
        }
    }

    /**
     * Get installation info
     */
    public function get_install_info() {
        return [
            'install_path' => $this->install_path,
            'n8n_url' => $this->config['n8n_url'] ?? '',
            'admin_email' => $this->config['admin_email'] ?? '',
            'database' => $this->db_handler->get_connection_info()
        ];
    }

    /**
     * Cleanup installation files
     */
    public static function cleanup() {
        log_message('Cleaning up installation files...');

        $setup_dir = SETUP_DIR;

        if (delete_directory($setup_dir)) {
            log_message('Setup directory removed successfully');
            return ['success' => true, 'message' => 'Installation files removed'];
        } else {
            log_message('Failed to remove setup directory', 'WARNING');
            return ['success' => false, 'message' => 'Failed to remove installation files'];
        }
    }
}
