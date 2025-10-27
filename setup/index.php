<?php
/**
 * N8N Web Installer - Main Interface
 *
 * @package    N8N Web Installer
 * @version    1.0.0
 * @author     Xman Enterprise co.,ltd.
 * @website    https://xman4289.com
 * @phone      (066) 080-6038278
 */

define('N8N_INSTALLER', true);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/requirements.php';

// Load language
$lang = load_language();

// Check if already installed
if (isset($_SESSION['install_complete']) && file_exists(INSTALL_ROOT . '/n8n/.env')) {
    // Redirect to complete step
}

// Get current language
$current_lang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo __('app_name'); ?> - <?php echo COMPANY_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="installer-container">
        <!-- Language Selector -->
        <div class="language-selector">
            <select id="language" name="language">
                <option value="th" <?php echo $current_lang === 'th' ? 'selected' : ''; ?>>ไทย (TH)</option>
                <option value="en" <?php echo $current_lang === 'en' ? 'selected' : ''; ?>>English (EN)</option>
            </select>
        </div>

        <!-- Header -->
        <div class="installer-header">
            <div class="installer-logo">🚀</div>
            <h1 class="installer-title"><?php echo __('app_name'); ?></h1>
            <p class="installer-subtitle"><?php echo __('welcome_subtitle'); ?></p>
        </div>

        <!-- Main Card -->
        <div class="installer-card">
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="progress-line" style="width: 0%"></div>
                <div class="step active" data-step="1">
                    <div class="step-circle"></div>
                    <div class="step-label"><?php echo __('step_welcome'); ?></div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-circle"></div>
                    <div class="step-label"><?php echo __('step_requirements'); ?></div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-circle"></div>
                    <div class="step-label"><?php echo __('step_database'); ?></div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-circle"></div>
                    <div class="step-label"><?php echo __('step_configuration'); ?></div>
                </div>
                <div class="step" data-step="5">
                    <div class="step-circle"></div>
                    <div class="step-label"><?php echo __('step_installation'); ?></div>
                </div>
                <div class="step" data-step="6">
                    <div class="step-circle"></div>
                    <div class="step-label"><?php echo __('step_complete'); ?></div>
                </div>
            </div>

            <!-- Step 1: Welcome -->
            <div id="step-1" class="step-content active">
                <h2><?php echo __('welcome_title'); ?></h2>
                <p><?php echo __('welcome_description'); ?></p>

                <div class="requirement-list" style="margin-top: 30px;">
                    <?php foreach ($lang['welcome_requirements'] as $req): ?>
                    <div class="requirement-item passed">
                        <div class="requirement-icon">📋</div>
                        <div class="requirement-info">
                            <div class="requirement-name"><?php echo $req; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tip Box -->
                <div class="tip-box" data-tips='<?php echo json_encode($lang['tips']); ?>'>
                    <div class="tip-box-title"><?php echo __('tip_prefix'); ?>:</div>
                    <div class="tip-box-content"><?php echo $lang['tips'][0]; ?></div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-next"><?php echo __('welcome_start'); ?> →</button>
                </div>
            </div>

            <!-- Step 2: Requirements Check -->
            <div id="step-2" class="step-content">
                <h2><?php echo __('requirements_title'); ?></h2>
                <p><?php echo __('requirements_description'); ?></p>

                <div id="requirements-check" class="requirement-list">
                    <?php
                    $checker = new RequirementsChecker();
                    $requirements = $checker->get_requirements();

                    foreach ($requirements as $key => $req):
                    ?>
                    <div id="req-<?php echo $key; ?>" class="requirement-item <?php echo $req['status']; ?>">
                        <div class="requirement-icon">
                            <?php
                            if ($req['status'] === 'passed') echo '✓';
                            elseif ($req['status'] === 'failed') echo '✕';
                            else echo '⚠';
                            ?>
                        </div>
                        <div class="requirement-info">
                            <div class="requirement-name"><?php echo $req['name']; ?></div>
                            <div class="requirement-value"><?php echo $req['message']; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tip Box -->
                <div class="tip-box" data-tips='<?php echo json_encode($lang['tips']); ?>'>
                    <div class="tip-box-title"><?php echo __('tip_prefix'); ?>:</div>
                    <div class="tip-box-content"></div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary btn-back">← <?php echo __('back'); ?></button>
                    <button type="button" class="btn btn-primary btn-next"><?php echo __('next'); ?> →</button>
                </div>
            </div>

            <!-- Step 3: Database Configuration -->
            <div id="step-3" class="step-content">
                <h2><?php echo __('database_title'); ?></h2>
                <p><?php echo __('database_description'); ?></p>

                <form id="database-form">
                    <div class="form-group">
                        <label for="db_type"><?php echo __('database_type'); ?> <span class="required">*</span></label>
                        <select id="db_type" name="db_type" required>
                            <option value="mysql">MySQL / MariaDB</option>
                            <option value="postgres">PostgreSQL</option>
                            <option value="sqlite">SQLite</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="db_host"><?php echo __('database_host'); ?> <span class="required">*</span></label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>

                    <div class="form-group">
                        <label for="db_port"><?php echo __('database_port'); ?> <span class="required">*</span></label>
                        <input type="number" id="db_port" name="db_port" value="3306" required>
                    </div>

                    <div class="form-group">
                        <label for="db_name"><?php echo __('database_name'); ?> <span class="required">*</span></label>
                        <input type="text" id="db_name" name="db_name" placeholder="n8n" required>
                    </div>

                    <div class="form-group">
                        <label for="db_user"><?php echo __('database_username'); ?> <span class="required">*</span></label>
                        <input type="text" id="db_user" name="db_user" required>
                    </div>

                    <div class="form-group">
                        <label for="db_password"><?php echo __('database_password'); ?></label>
                        <input type="password" id="db_password" name="db_password">
                    </div>

                    <div class="form-group">
                        <label for="db_prefix"><?php echo __('database_prefix'); ?></label>
                        <input type="text" id="db_prefix" name="db_prefix" value="n8n_">
                        <span class="hint"><?php echo __('optional'); ?></span>
                    </div>

                    <div class="form-group">
                        <button type="button" id="test-db" class="btn btn-secondary">
                            🔍 <?php echo __('database_test'); ?>
                        </button>
                    </div>
                </form>

                <!-- Tip Box -->
                <div class="tip-box" data-tips='<?php echo json_encode($lang['tips']); ?>'>
                    <div class="tip-box-title"><?php echo __('tip_prefix'); ?>:</div>
                    <div class="tip-box-content"></div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary btn-back">← <?php echo __('back'); ?></button>
                    <button type="button" class="btn btn-primary btn-next"><?php echo __('next'); ?> →</button>
                </div>
            </div>

            <!-- Step 4: N8N Configuration -->
            <div id="step-4" class="step-content">
                <h2><?php echo __('n8n_title'); ?></h2>
                <p><?php echo __('n8n_description'); ?></p>

                <form id="n8n-config-form">
                    <div class="form-group">
                        <label for="n8n_url"><?php echo __('n8n_url'); ?> <span class="required">*</span></label>
                        <input type="text" id="n8n_url" name="n8n_url"
                               value="<?php echo get_base_url() . '/../n8n'; ?>" required>
                        <span class="hint"><?php echo __('n8n_url'); ?></span>
                    </div>

                    <div class="form-group">
                        <label for="n8n_port"><?php echo __('n8n_port'); ?> <span class="required">*</span></label>
                        <input type="number" id="n8n_port" name="n8n_port" value="5678" required>
                    </div>

                    <div class="form-group">
                        <label for="admin_email"><?php echo __('n8n_admin_email'); ?> <span class="required">*</span></label>
                        <input type="email" id="admin_email" name="admin_email" required>
                    </div>

                    <div class="form-group">
                        <label for="admin_password"><?php echo __('n8n_admin_password'); ?> <span class="required">*</span></label>
                        <input type="password" id="admin_password" name="admin_password" required>
                        <span class="hint">Minimum 8 characters</span>
                    </div>

                    <div class="form-group">
                        <label for="admin_password_confirm"><?php echo __('n8n_admin_password_confirm'); ?> <span class="required">*</span></label>
                        <input type="password" id="admin_password_confirm" name="admin_password_confirm" required>
                    </div>

                    <div class="form-group">
                        <label for="timezone"><?php echo __('n8n_timezone'); ?></label>
                        <select id="timezone" name="timezone">
                            <option value="Asia/Bangkok" selected>Asia/Bangkok (UTC+7)</option>
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">America/New_York (EST)</option>
                            <option value="Europe/London">Europe/London (GMT)</option>
                            <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="encryption_key"><?php echo __('n8n_encryption_key'); ?> <span class="required">*</span></label>
                        <input type="text" id="encryption_key" name="encryption_key" required>
                        <button type="button" id="generate-key" class="btn btn-secondary" style="margin-top: 10px;">
                            🔑 <?php echo __('n8n_generate_key'); ?>
                        </button>
                    </div>

                    <div class="form-group">
                        <label for="install_location"><?php echo __('install_location'); ?></label>
                        <input type="text" id="install_location" name="install_location"
                               value="<?php echo INSTALL_ROOT; ?>">
                        <span class="hint"><?php echo __('install_location_hint'); ?></span>
                    </div>
                </form>

                <!-- Tip Box -->
                <div class="tip-box" data-tips='<?php echo json_encode($lang['tips']); ?>'>
                    <div class="tip-box-title"><?php echo __('tip_prefix'); ?>:</div>
                    <div class="tip-box-content"></div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary btn-back">← <?php echo __('back'); ?></button>
                    <button type="button" id="install-btn" class="btn btn-success">
                        ⚡ <?php echo __('install'); ?>
                    </button>
                </div>
            </div>

            <!-- Step 5: Installation Progress -->
            <div id="step-5" class="step-content">
                <h2><?php echo __('installation_title'); ?></h2>
                <p><?php echo __('installation_description'); ?></p>

                <div class="progress-container">
                    <div class="progress-bar-wrapper">
                        <div id="install-progress-bar" class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div id="install-progress-text" class="progress-text">0%</div>
                </div>

                <div class="installation-steps">
                    <div id="install-step-download" class="installation-step">
                        <div class="installation-step-icon">⏳</div>
                        <div class="installation-step-text"><?php echo __('installation_step_download'); ?></div>
                    </div>

                    <div id="install-step-extract" class="installation-step">
                        <div class="installation-step-icon">⏳</div>
                        <div class="installation-step-text"><?php echo __('installation_step_extract'); ?></div>
                    </div>

                    <div id="install-step-database" class="installation-step">
                        <div class="installation-step-icon">⏳</div>
                        <div class="installation-step-text"><?php echo __('installation_step_database'); ?></div>
                    </div>

                    <div id="install-step-config" class="installation-step">
                        <div class="installation-step-icon">⏳</div>
                        <div class="installation-step-text"><?php echo __('installation_step_config'); ?></div>
                    </div>

                    <div id="install-step-dependencies" class="installation-step">
                        <div class="installation-step-icon">⏳</div>
                        <div class="installation-step-text"><?php echo __('installation_step_dependencies'); ?></div>
                    </div>

                    <div id="install-step-finalize" class="installation-step">
                        <div class="installation-step-icon">⏳</div>
                        <div class="installation-step-text"><?php echo __('installation_step_finalize'); ?></div>
                    </div>
                </div>

                <!-- Tip Box -->
                <div class="tip-box" data-tips='<?php echo json_encode($lang['tips']); ?>'>
                    <div class="tip-box-title"><?php echo __('tip_prefix'); ?>:</div>
                    <div class="tip-box-content"></div>
                </div>
            </div>

            <!-- Step 6: Complete -->
            <div id="step-6" class="step-content">
                <h2>🎉 <?php echo __('complete_title'); ?></h2>
                <p><?php echo __('complete_description'); ?></p>

                <div class="alert alert-success">
                    <span>✓</span>
                    <span><?php echo __('success_installation'); ?></span>
                </div>

                <div class="requirement-list">
                    <div class="requirement-item passed">
                        <div class="requirement-icon">🌐</div>
                        <div class="requirement-info">
                            <div class="requirement-name"><?php echo __('complete_url'); ?></div>
                            <div class="requirement-value" id="complete-url">-</div>
                        </div>
                    </div>

                    <div class="requirement-item passed">
                        <div class="requirement-icon">📧</div>
                        <div class="requirement-info">
                            <div class="requirement-name"><?php echo __('complete_admin_email'); ?></div>
                            <div class="requirement-value" id="complete-email">-</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <span>⚠</span>
                    <span><?php echo __('complete_cleanup_warning'); ?></span>
                </div>

                <div class="btn-group">
                    <button type="button" id="finish-btn" class="btn btn-success">
                        🎯 <?php echo __('finish'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="installer-footer">
            <p>
                <strong><?php echo __('powered_by'); ?>:</strong>
                <a href="<?php echo COMPANY_WEBSITE; ?>" target="_blank"><?php echo COMPANY_NAME; ?></a>
            </p>
            <p>
                📞 <?php echo COMPANY_PHONE; ?> |
                <?php echo __('version'); ?>: <?php echo INSTALLER_VERSION; ?> (Build <?php echo INSTALLER_BUILD; ?>)
            </p>
            <p style="margin-top: 10px; color: #9ca3af; font-size: 0.85rem;">
                © <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.
            </p>
        </div>
    </div>

    <script src="assets/js/installer.js"></script>
</body>
</html>
