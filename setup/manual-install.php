<?php
require_once 'config.php';

$lang = $_SESSION['lang'] ?? DEFAULT_LANG;
$t = require "language/{$lang}.php";
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Installation - N8N Installer</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .manual-box {
            background: #f9f9f9;
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 20px 0;
        }
        .code-box {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 10px 0;
        }
        .step-box {
            background: white;
            border: 2px solid #2196F3;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
        }
        .step-number {
            background: #2196F3;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Manual Installation Required</h1>
        </div>

        <div class="manual-box">
            <h2>🔒 PHP exec() Function is Disabled</h2>
            <p>Your server has disabled the PHP <code>exec()</code> function, which is required for automatic installation.</p>
            <p><strong>You have 2 options:</strong></p>
        </div>

        <div class="step-box">
            <h3><span class="step-number">1</span> Enable exec() Function (Recommended)</h3>
            <p>If you have server admin access, enable <code>exec()</code> in PHP configuration:</p>

            <h4>Edit php.ini:</h4>
            <div class="code-box">
                <div># Find this line:</div>
                <div>disable_functions = exec,passthru,shell_exec,system,proc_open,popen</div>
                <div></div>
                <div># Remove 'exec' from the list:</div>
                <div>disable_functions = passthru,shell_exec,system,proc_open,popen</div>
            </div>

            <h4>Restart Apache:</h4>
            <div class="code-box">
                <div>sudo systemctl restart apache2</div>
                <div># or</div>
                <div>sudo service apache2 restart</div>
            </div>

            <p>Then refresh this installer and try again.</p>
        </div>

        <div class="step-box">
            <h3><span class="step-number">2</span> Install via SSH (Alternative)</h3>
            <p>Install N8N directly via command line using our automated script:</p>

            <h4>Step 1: Connect to your server via SSH</h4>
            <div class="code-box">
                <div>ssh user@<?php echo $_SERVER['HTTP_HOST']; ?></div>
            </div>

            <h4>Step 2: Navigate to installation directory</h4>
            <div class="code-box">
                <div>cd <?php echo INSTALL_ROOT; ?></div>
            </div>

            <h4>Step 3: Download and run installation script</h4>
            <div class="code-box">
                <div># Make script executable</div>
                <div>chmod +x install-n8n.sh</div>
                <div></div>
                <div># Run the installer</div>
                <div>bash install-n8n.sh</div>
            </div>

            <p>The script will ask you for:</p>
            <ul>
                <li>N8N URL (https://yourdomain.com/n8n)</li>
                <li>Admin Email</li>
                <li>Admin Password</li>
                <li>Encryption Key (or generate automatically)</li>
                <li>Database Settings</li>
            </ul>

            <p>Installation takes 2-5 minutes depending on your connection speed.</p>
        </div>

        <div class="info-box">
            <h3>📋 Script Location</h3>
            <p>The installation script is located at:</p>
            <div class="code-box">
                <div><?php echo INSTALL_ROOT; ?>/install-n8n.sh</div>
            </div>
        </div>

        <div class="info-box">
            <h3>🔍 Debug Information</h3>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
            <p><strong>Disabled Functions:</strong></p>
            <div class="code-box">
                <div><?php echo ini_get('disable_functions') ?: 'None'; ?></div>
            </div>
        </div>

        <div class="alert alert-warning">
            <span>ℹ️</span>
            <span>
                <strong>Need help?</strong><br>
                Contact Xman Enterprise co.,ltd.<br>
                Website: <a href="<?php echo COMPANY_WEBSITE; ?>" target="_blank"><?php echo COMPANY_WEBSITE; ?></a><br>
                Phone: <?php echo COMPANY_PHONE; ?>
            </span>
        </div>

        <div class="btn-group">
            <button onclick="window.location.href='check-exec.php'" class="btn">
                🔍 Check exec() Status
            </button>
            <button onclick="window.location.reload()" class="btn btn-primary">
                🔄 Retry After Fixing
            </button>
        </div>

        <div class="footer">
            <p>© <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?> |
               <a href="<?php echo COMPANY_WEBSITE; ?>" target="_blank"><?php echo COMPANY_WEBSITE; ?></a> |
               <?php echo COMPANY_PHONE; ?></p>
        </div>
    </div>
</body>
</html>
