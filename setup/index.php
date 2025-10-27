<?php
require_once 'config.php';

// Check if exec() is available
if (!function_exists('exec')) {
    // Redirect to manual installation page
    header('Location: manual-install.php');
    exit;
}

// Test if exec() actually works
$exec_test = [];
$exec_works = false;
try {
    @exec('echo "test" 2>&1', $exec_test, $return_var);
    $exec_works = ($return_var === 0);
} catch (Exception $e) {
    $exec_works = false;
}

if (!$exec_works) {
    // Redirect to manual installation page
    header('Location: manual-install.php');
    exit;
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['th', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
    header('Location: index.php');
    exit;
}

// Load language
$lang = $_SESSION['lang'] ?? DEFAULT_LANG;
$t = require "language/{$lang}.php";

function __($key) {
    global $t;
    return $t[$key] ?? $key;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N8N Installer - <?php echo COMPANY_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>N8N Installer</h1>
            <select id="lang" onchange="changeLang(this.value)">
                <option value="th" <?php echo $lang === 'th' ? 'selected' : ''; ?>>ไทย</option>
                <option value="en" <?php echo $lang === 'en' ? 'selected' : ''; ?>>English</option>
            </select>
        </div>

        <!-- Progress -->
        <div class="progress">
            <div class="step active" data-step="1">1</div>
            <div class="step" data-step="2">2</div>
            <div class="step" data-step="3">3</div>
            <div class="step" data-step="4">4</div>
            <div class="step" data-step="5">5</div>
        </div>

        <!-- Steps -->
        <div class="wizard">
            <!-- Step 1: Welcome -->
            <div class="wizard-step active" id="step-1">
                <h2><?php echo __('welcome'); ?></h2>
                <p><?php echo __('welcome_msg'); ?></p>
                <div class="info-box">
                    <p><strong><?php echo COMPANY_NAME; ?></strong></p>
                    <p><?php echo COMPANY_WEBSITE; ?></p>
                    <p><?php echo COMPANY_PHONE; ?></p>
                    <p>Version: <?php echo VERSION; ?></p>
                </div>
                <button onclick="nextStep()" class="btn btn-primary"><?php echo __('next'); ?></button>
            </div>

            <!-- Step 2: Requirements -->
            <div class="wizard-step" id="step-2">
                <h2><?php echo __('requirements'); ?></h2>
                <div id="req-list"></div>
                <button onclick="prevStep()" class="btn"><?php echo __('back'); ?></button>
                <button onclick="checkRequirements()" class="btn btn-primary"><?php echo __('check_req'); ?></button>
                <button onclick="nextStep()" class="btn btn-primary" id="req-next" style="display:none"><?php echo __('next'); ?></button>
            </div>

            <!-- Step 3: Database -->
            <div class="wizard-step" id="step-3">
                <h2><?php echo __('database'); ?></h2>
                <form id="db-form">
                    <label><?php echo __('db_type'); ?></label>
                    <select name="db_type" id="db_type" required>
                        <option value="mysql">MySQL</option>
                        <option value="postgres">PostgreSQL</option>
                        <option value="sqlite">SQLite</option>
                    </select>

                    <div id="db-fields">
                        <label><?php echo __('db_host'); ?></label>
                        <input type="text" name="db_host" value="localhost" required>

                        <label><?php echo __('db_port'); ?></label>
                        <input type="text" name="db_port" value="3306" required>

                        <label><?php echo __('db_name'); ?></label>
                        <input type="text" name="db_name" required>

                        <label><?php echo __('db_user'); ?></label>
                        <input type="text" name="db_user" required>

                        <label><?php echo __('db_pass'); ?></label>
                        <input type="password" name="db_pass">
                    </div>

                    <button type="button" onclick="testDatabase()" class="btn"><?php echo __('test_connection'); ?></button>
                    <div id="db-result"></div>
                </form>
                <button onclick="prevStep()" class="btn"><?php echo __('back'); ?></button>
                <button onclick="nextStep()" class="btn btn-primary"><?php echo __('next'); ?></button>
            </div>

            <!-- Step 4: N8N Config -->
            <div class="wizard-step" id="step-4">
                <h2><?php echo __('n8n_config'); ?></h2>
                <form id="n8n-form">
                    <label><?php echo __('n8n_url'); ?> *</label>
                    <input type="url" name="n8n_url" id="n8n_url"
                           value="https://<?php echo $_SERVER['HTTP_HOST']; ?>/n8n" required>
                    <small style="color: red;"><?php echo __('https_required'); ?></small>

                    <label><?php echo __('admin_email'); ?> *</label>
                    <input type="email" name="admin_email" required>

                    <label><?php echo __('admin_pass'); ?> *</label>
                    <input type="password" name="admin_pass" required>

                    <label><?php echo __('encryption_key'); ?> *</label>
                    <input type="text" name="encryption_key" id="encryption_key" required>
                    <button type="button" onclick="generateKey()" class="btn"><?php echo __('generate_key'); ?></button>
                    <small style="color: red;"><?php echo __('min_32_chars'); ?></small>
                </form>
                <button onclick="prevStep()" class="btn"><?php echo __('back'); ?></button>
                <button onclick="startInstall()" class="btn btn-success"><?php echo __('install'); ?></button>
            </div>

            <!-- Step 5: Installing -->
            <div class="wizard-step" id="step-5">
                <h2><?php echo __('installing'); ?></h2>
                <p><?php echo __('install_progress'); ?></p>
                <div id="install-log"></div>
                <div class="loading"></div>
            </div>

            <!-- Step 6: Complete -->
            <div class="wizard-step" id="step-6">
                <h2><?php echo __('complete'); ?></h2>
                <p><?php echo __('success_msg'); ?></p>
                <div class="success-box">
                    <p>✓ N8N URL: <span id="final-url"></span></p>
                    <p>✓ Admin Email: <span id="final-email"></span></p>
                </div>
                <button onclick="finish()" class="btn btn-success"><?php echo __('finish'); ?></button>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>© <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?> |
               <a href="<?php echo COMPANY_WEBSITE; ?>" target="_blank"><?php echo COMPANY_WEBSITE; ?></a> |
               <?php echo COMPANY_PHONE; ?></p>
            <p style="font-size: 12px; color: #888;">Version <?php echo VERSION; ?> (Build <?php echo BUILD; ?>)</p>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
