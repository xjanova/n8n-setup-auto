<?php
/**
 * English Language File
 */

return [
    // General
    'app_name' => 'N8N Installer',
    'welcome' => 'Welcome',
    'next' => 'Next',
    'back' => 'Back',
    'install' => 'Install',
    'finish' => 'Finish',
    'cancel' => 'Cancel',
    'yes' => 'Yes',
    'no' => 'No',
    'required' => 'Required',
    'optional' => 'Optional',
    'recommended' => 'Recommended',

    // Company Info
    'company_name' => 'Xman Enterprise co.,ltd.',
    'company_website' => 'https://xman4289.com',
    'company_phone' => '(066) 080-6038278',
    'powered_by' => 'Powered by',
    'version' => 'Version',

    // Steps
    'step_welcome' => 'Welcome',
    'step_requirements' => 'Requirements',
    'step_database' => 'Database',
    'step_configuration' => 'Configuration',
    'step_installation' => 'Installation',
    'step_complete' => 'Complete',

    // Welcome Page
    'welcome_title' => 'Welcome to N8N Installer',
    'welcome_subtitle' => 'Professional Workflow Automation System',
    'welcome_description' => 'This installer will help you easily install and configure N8N on your server. Please make sure you have the following information ready:',
    'welcome_requirements' => [
        'PHP Server version 7.4 or higher',
        'Node.js version 18.0 or higher',
        'MySQL, PostgreSQL, or SQLite database',
        'Write permissions on the server',
        'Internet connection for downloading N8N'
    ],
    'welcome_start' => 'Start Installation',
    'select_language' => 'Select Language',

    // Requirements Check
    'requirements_title' => 'System Requirements Check',
    'requirements_description' => 'Please ensure your system meets all requirements',
    'requirements_checking' => 'Checking requirements...',
    'requirements_passed' => 'Passed',
    'requirements_failed' => 'Failed',
    'requirements_warning' => 'Warning',
    'php_version' => 'PHP Version',
    'php_extensions' => 'PHP Extensions',
    'file_permissions' => 'File Permissions',
    'node_version' => 'Node.js Version',
    'npm_version' => 'NPM Version',
    'disk_space' => 'Free Disk Space',
    'memory_limit' => 'Memory Limit',
    'max_execution_time' => 'Max Execution Time',

    // Database Configuration
    'database_title' => 'Database Configuration',
    'database_description' => 'Please enter your database connection details',
    'database_type' => 'Database Type',
    'database_host' => 'Host',
    'database_port' => 'Port',
    'database_name' => 'Database Name',
    'database_username' => 'Username',
    'database_password' => 'Password',
    'database_prefix' => 'Table Prefix',
    'database_test' => 'Test Connection',
    'database_testing' => 'Testing connection...',
    'database_success' => 'Connection successful!',
    'database_error' => 'Connection failed',

    // N8N Configuration
    'n8n_title' => 'N8N Configuration',
    'n8n_description' => 'Configure basic settings for your N8N',
    'n8n_url' => 'N8N URL',
    'n8n_port' => 'Port',
    'n8n_admin_email' => 'Admin Email',
    'n8n_admin_password' => 'Admin Password',
    'n8n_admin_password_confirm' => 'Confirm Password',
    'n8n_timezone' => 'Timezone',
    'n8n_encryption_key' => 'Encryption Key',
    'n8n_generate_key' => 'Auto Generate Key',
    'install_location' => 'Installation Location',
    'install_location_hint' => 'Specify installation path (leave empty for default)',

    // Installation Process
    'installation_title' => 'Installing N8N',
    'installation_description' => 'Please wait... This process may take a few minutes...',
    'installation_step_download' => 'Downloading N8N',
    'installation_step_extract' => 'Extracting Files',
    'installation_step_database' => 'Creating Database',
    'installation_step_config' => 'Creating Configuration',
    'installation_step_dependencies' => 'Installing Dependencies',
    'installation_step_finalize' => 'Finalizing Setup',
    'installation_progress' => 'Progress',

    // Installation Complete
    'complete_title' => 'Installation Complete!',
    'complete_subtitle' => 'Congratulations! Your N8N is ready to use',
    'complete_description' => 'Installation completed successfully. You can now access N8N',
    'complete_info' => 'Installation Information',
    'complete_url' => 'Login URL',
    'complete_admin_email' => 'Admin Email',
    'complete_next_steps' => 'Next Steps',
    'complete_login' => 'Login to N8N',
    'complete_documentation' => 'Read Documentation',
    'complete_cleanup' => 'Remove Installation Files Now',
    'complete_cleanup_warning' => 'For security, the setup folder will be deleted after you click "Finish"',

    // Tips
    'tip_prefix' => 'Tip',
    'tips' => [
        'Make sure your database is backed up before installation',
        'Use a strong password for the admin account',
        'Keep your encryption key safe, you\'ll need it for data recovery',
        'Node.js LTS version is recommended for maximum stability',
        'Update N8N regularly to get the latest features and security patches',
        'Configure firewall to allow the port N8N uses',
        'Use HTTPS for accessing N8N in production environment',
        'Set up correct Webhook URL for automation workflows'
    ],

    // Errors
    'error_title' => 'Error Occurred',
    'error_general' => 'An error occurred. Please try again',
    'error_permission' => 'No file write permission',
    'error_database' => 'Cannot connect to database',
    'error_download' => 'Cannot download N8N',
    'error_extraction' => 'Cannot extract files',
    'error_configuration' => 'Cannot create configuration file',
    'error_node_not_found' => 'Node.js not found. Please install Node.js first',
    'error_npm_not_found' => 'NPM not found. Please install NPM first',
    'error_csrf' => 'Invalid CSRF token',

    // Success Messages
    'success_general' => 'Success!',
    'success_database' => 'Database created successfully',
    'success_configuration' => 'Configuration successful',
    'success_installation' => 'Installation successful',
    'success_cleanup' => 'Installation folder removed successfully',
];
