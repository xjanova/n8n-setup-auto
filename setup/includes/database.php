<?php
/**
 * Database Handler
 */

if (!defined('N8N_INSTALLER')) {
    die('Direct access not permitted');
}

class DatabaseHandler {
    private $connection = null;
    private $type = 'mysql';
    private $host = 'localhost';
    private $port = 3306;
    private $database = '';
    private $username = '';
    private $password = '';
    private $prefix = 'n8n_';

    /**
     * Constructor
     */
    public function __construct($config = []) {
        if (!empty($config)) {
            $this->configure($config);
        }
    }

    /**
     * Configure database connection
     */
    public function configure($config) {
        $this->type = $config['type'] ?? 'mysql';
        $this->host = $config['host'] ?? 'localhost';
        $this->port = $config['port'] ?? $this->get_default_port();
        $this->database = $config['database'] ?? '';
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->prefix = $config['prefix'] ?? 'n8n_';
    }

    /**
     * Get default port for database type
     */
    private function get_default_port() {
        $ports = [
            'mysql' => 3306,
            'postgres' => 5432,
            'sqlite' => null
        ];

        return $ports[$this->type] ?? 3306;
    }

    /**
     * Test database connection
     */
    public function test_connection() {
        try {
            $this->connect();
            $this->disconnect();
            return ['success' => true, 'message' => 'Connection successful'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Connect to database
     */
    public function connect() {
        if ($this->connection !== null) {
            return $this->connection;
        }

        try {
            $dsn = $this->build_dsn();
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            return $this->connection;
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Build DSN string
     */
    private function build_dsn() {
        switch ($this->type) {
            case 'mysql':
                return sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                    $this->host,
                    $this->port,
                    $this->database
                );

            case 'postgres':
                return sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s',
                    $this->host,
                    $this->port,
                    $this->database
                );

            case 'sqlite':
                return 'sqlite:' . $this->database;

            default:
                throw new Exception('Unsupported database type: ' . $this->type);
        }
    }

    /**
     * Disconnect from database
     */
    public function disconnect() {
        $this->connection = null;
    }

    /**
     * Create database tables
     */
    public function create_tables() {
        try {
            $this->connect();

            $tables = $this->get_table_schemas();

            foreach ($tables as $table => $schema) {
                $sql = $this->build_create_table_sql($table, $schema);
                $this->connection->exec($sql);
                log_message("Created table: {$this->prefix}{$table}");
            }

            return ['success' => true, 'message' => 'Database tables created successfully'];
        } catch (Exception $e) {
            log_message('Database table creation failed: ' . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get table schemas
     */
    private function get_table_schemas() {
        return [
            'workflows' => [
                'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
                'name' => 'VARCHAR(255) NOT NULL',
                'active' => 'BOOLEAN DEFAULT FALSE',
                'nodes' => 'TEXT',
                'connections' => 'TEXT',
                'settings' => 'TEXT',
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
            ],
            'executions' => [
                'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
                'workflow_id' => 'INT NOT NULL',
                'finished' => 'BOOLEAN DEFAULT FALSE',
                'mode' => 'VARCHAR(50)',
                'started_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'stopped_at' => 'TIMESTAMP NULL',
                'data' => 'TEXT'
            ],
            'credentials' => [
                'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
                'name' => 'VARCHAR(255) NOT NULL',
                'type' => 'VARCHAR(100) NOT NULL',
                'data' => 'TEXT',
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
            ],
            'settings' => [
                'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
                'key' => 'VARCHAR(255) UNIQUE NOT NULL',
                'value' => 'TEXT',
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
            ]
        ];
    }

    /**
     * Build CREATE TABLE SQL
     */
    private function build_create_table_sql($table, $schema) {
        $table_name = $this->prefix . $table;
        $columns = [];

        foreach ($schema as $column => $definition) {
            // Adjust for PostgreSQL
            if ($this->type === 'postgres') {
                $definition = str_replace('AUTO_INCREMENT', 'SERIAL', $definition);
                $definition = str_replace('BOOLEAN', 'BOOLEAN', $definition);
            }

            $columns[] = "$column $definition";
        }

        $columns_sql = implode(', ', $columns);

        return "CREATE TABLE IF NOT EXISTS $table_name ($columns_sql)";
    }

    /**
     * Insert initial data
     */
    public function insert_initial_data($admin_email, $admin_password) {
        try {
            $this->connect();

            // Insert admin credentials
            $sql = "INSERT INTO {$this->prefix}settings (`key`, `value`) VALUES (?, ?)";
            $stmt = $this->connection->prepare($sql);

            $settings = [
                ['admin_email', $admin_email],
                ['admin_password', password_hash($admin_password, PASSWORD_DEFAULT)],
                ['installed_at', date('Y-m-d H:i:s')],
                ['version', INSTALLER_VERSION]
            ];

            foreach ($settings as $setting) {
                $stmt->execute($setting);
            }

            log_message('Initial data inserted successfully');

            return ['success' => true, 'message' => 'Initial data inserted'];
        } catch (Exception $e) {
            log_message('Insert initial data failed: ' . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Execute SQL file
     */
    public function execute_sql_file($filepath) {
        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'SQL file not found'];
        }

        try {
            $this->connect();

            $sql = file_get_contents($filepath);
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $this->connection->exec($statement);
                }
            }

            return ['success' => true, 'message' => 'SQL file executed successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get connection info
     */
    public function get_connection_info() {
        return [
            'type' => $this->type,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'prefix' => $this->prefix
        ];
    }
}
