/**
 * N8N Web Installer JavaScript
 * @author Xman Enterprise co.,ltd.
 * @website https://xman4289.com
 */

class N8NInstaller {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 6;
        this.tips = [];
        this.init();
    }

    init() {
        this.setupLanguageSelector();
        this.setupEventListeners();
        this.showRandomTip();
        this.updateProgressLine();
    }

    setupLanguageSelector() {
        const langSelector = document.getElementById('language');
        if (langSelector) {
            langSelector.addEventListener('change', (e) => {
                this.changeLanguage(e.target.value);
            });
        }
    }

    setupEventListeners() {
        // Next button
        document.querySelectorAll('.btn-next').forEach(btn => {
            btn.addEventListener('click', () => this.nextStep());
        });

        // Back button
        document.querySelectorAll('.btn-back').forEach(btn => {
            btn.addEventListener('click', () => this.prevStep());
        });

        // Test database connection
        const testDbBtn = document.getElementById('test-db');
        if (testDbBtn) {
            testDbBtn.addEventListener('click', () => this.testDatabaseConnection());
        }

        // Generate encryption key
        const genKeyBtn = document.getElementById('generate-key');
        if (genKeyBtn) {
            genKeyBtn.addEventListener('click', () => this.generateEncryptionKey());
        }

        // Install button
        const installBtn = document.getElementById('install-btn');
        if (installBtn) {
            installBtn.addEventListener('click', () => this.startInstallation());
        }

        // Finish button
        const finishBtn = document.getElementById('finish-btn');
        if (finishBtn) {
            finishBtn.addEventListener('click', () => this.finishInstallation());
        }
    }

    async changeLanguage(lang) {
        // Show loading indicator
        const langSelector = document.getElementById('language');
        if (langSelector) {
            langSelector.disabled = true;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'change_language');
            formData.append('language', lang);

            const response = await fetch('install.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                // Reload page to apply language change
                window.location.reload();
            } else {
                throw new Error('Failed to change language');
            }
        } catch (error) {
            console.error('Language change error:', error);
            if (langSelector) {
                langSelector.disabled = false;
            }
            // Fallback: Try simple reload with query parameter
            window.location.href = 'index.php?lang=' + lang;
        }
    }

    nextStep() {
        // Validate current step before proceeding
        if (!this.validateCurrentStep()) {
            return;
        }

        if (this.currentStep < this.totalSteps) {
            this.hideStep(this.currentStep);
            this.currentStep++;
            this.showStep(this.currentStep);
            this.updateProgressLine();
            this.showRandomTip();
            this.scrollToTop();
        }
    }

    prevStep() {
        if (this.currentStep > 1) {
            this.hideStep(this.currentStep);
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateProgressLine();
            this.showRandomTip();
            this.scrollToTop();
        }
    }

    showStep(step) {
        // Show content
        const content = document.getElementById(`step-${step}`);
        if (content) {
            content.classList.add('active');
        }

        // Update progress
        const stepElement = document.querySelector(`.step[data-step="${step}"]`);
        if (stepElement) {
            stepElement.classList.add('active');
        }

        // Mark previous steps as completed
        for (let i = 1; i < step; i++) {
            const prevStep = document.querySelector(`.step[data-step="${i}"]`);
            if (prevStep) {
                prevStep.classList.add('completed');
                prevStep.classList.remove('active');
            }
        }
    }

    hideStep(step) {
        const content = document.getElementById(`step-${step}`);
        if (content) {
            content.classList.remove('active');
        }

        const stepElement = document.querySelector(`.step[data-step="${step}"]`);
        if (stepElement) {
            stepElement.classList.remove('active');
        }
    }

    updateProgressLine() {
        const progressLine = document.querySelector('.progress-line');
        if (progressLine) {
            const percentage = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
            progressLine.style.width = `${percentage}%`;
        }
    }

    validateCurrentStep() {
        switch (this.currentStep) {
            case 1: // Welcome
                return true;
            case 2: // Requirements
                return this.validateRequirements();
            case 3: // Database
                return this.validateDatabase();
            case 4: // Configuration
                return this.validateConfiguration();
            default:
                return true;
        }
    }

    validateRequirements() {
        const failedChecks = document.querySelectorAll('.requirement-item.failed');
        if (failedChecks.length > 0) {
            this.showAlert('error', 'Please fix all failed requirements before continuing.');
            return false;
        }
        return true;
    }

    validateDatabase() {
        const dbType = document.getElementById('db_type')?.value;
        const dbHost = document.getElementById('db_host')?.value;
        const dbName = document.getElementById('db_name')?.value;
        const dbUser = document.getElementById('db_user')?.value;

        if (!dbType || !dbHost || !dbName || !dbUser) {
            this.showAlert('error', 'Please fill in all required database fields.');
            return false;
        }

        // Check if connection was tested
        if (!sessionStorage.getItem('db_tested')) {
            this.showAlert('warning', 'Please test the database connection first.');
            return false;
        }

        return true;
    }

    validateConfiguration() {
        const n8nUrl = document.getElementById('n8n_url')?.value;
        const adminEmail = document.getElementById('admin_email')?.value;
        const adminPassword = document.getElementById('admin_password')?.value;
        const confirmPassword = document.getElementById('admin_password_confirm')?.value;
        const encryptionKey = document.getElementById('encryption_key')?.value;

        if (!n8nUrl || !adminEmail || !adminPassword || !confirmPassword) {
            this.showAlert('error', 'Please fill in all required fields.');
            return false;
        }

        // Validate HTTPS protocol
        try {
            const url = new URL(n8nUrl);
            if (url.protocol !== 'https:') {
                this.showAlert('error', '🔒 HTTPS is required! N8N must be accessed via HTTPS for security. Please use https:// instead of http://');
                return false;
            }
        } catch (e) {
            this.showAlert('error', 'Invalid URL format. Please enter a valid URL starting with https://');
            return false;
        }

        if (adminPassword !== confirmPassword) {
            this.showAlert('error', 'Passwords do not match.');
            return false;
        }

        if (adminPassword.length < 8) {
            this.showAlert('error', 'Password must be at least 8 characters long.');
            return false;
        }

        if (!encryptionKey || encryptionKey.length < 32) {
            this.showAlert('error', 'Please generate an encryption key (minimum 32 characters).');
            return false;
        }

        return true;
    }

    async testDatabaseConnection() {
        const testBtn = document.getElementById('test-db');
        const originalText = testBtn.innerHTML;
        testBtn.disabled = true;
        testBtn.innerHTML = '<span class="spinner"></span> Testing...';

        const formData = new FormData();
        formData.append('action', 'test_database');
        formData.append('db_type', document.getElementById('db_type').value);
        formData.append('db_host', document.getElementById('db_host').value);
        formData.append('db_port', document.getElementById('db_port').value);
        formData.append('db_name', document.getElementById('db_name').value);
        formData.append('db_user', document.getElementById('db_user').value);
        formData.append('db_password', document.getElementById('db_password').value);

        try {
            const response = await fetch('install.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('success', result.message || 'Database connection successful!');
                sessionStorage.setItem('db_tested', 'true');
            } else {
                this.showAlert('error', result.message || 'Database connection failed.');
                sessionStorage.removeItem('db_tested');
            }
        } catch (error) {
            this.showAlert('error', 'Error testing database connection: ' + error.message);
            sessionStorage.removeItem('db_tested');
        } finally {
            testBtn.disabled = false;
            testBtn.innerHTML = originalText;
        }
    }

    generateEncryptionKey() {
        const key = this.randomString(32);
        const keyInput = document.getElementById('encryption_key');
        if (keyInput) {
            keyInput.value = key;
        }
    }

    randomString(length) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    async startInstallation() {
        console.log('Starting installation...');

        // Validate configuration first
        if (!this.validateConfiguration()) {
            console.log('Configuration validation failed');
            return;
        }

        const installBtn = document.getElementById('install-btn');
        if (!installBtn) {
            console.error('Install button not found');
            return;
        }

        installBtn.disabled = true;
        console.log('Install button disabled');

        // Manually move to installation step (step 5)
        this.hideStep(this.currentStep);
        this.currentStep = 5;
        this.showStep(this.currentStep);
        this.updateProgressLine();
        this.scrollToTop();

        console.log('Moved to installation step');

        // Collect all form data
        const formData = new FormData();
        formData.append('action', 'install');

        // Database settings
        formData.append('db_type', document.getElementById('db_type').value);
        formData.append('db_host', document.getElementById('db_host').value);
        formData.append('db_port', document.getElementById('db_port').value);
        formData.append('db_name', document.getElementById('db_name').value);
        formData.append('db_user', document.getElementById('db_user').value);
        formData.append('db_password', document.getElementById('db_password').value);
        formData.append('db_prefix', document.getElementById('db_prefix').value);

        // N8N settings
        formData.append('n8n_url', document.getElementById('n8n_url').value);
        formData.append('n8n_port', document.getElementById('n8n_port').value);
        formData.append('admin_email', document.getElementById('admin_email').value);
        formData.append('admin_password', document.getElementById('admin_password').value);
        formData.append('timezone', document.getElementById('timezone').value);
        formData.append('encryption_key', document.getElementById('encryption_key').value);
        formData.append('install_location', document.getElementById('install_location').value);

        console.log('Form data collected, starting installation process');

        // Start installation process
        await this.performInstallation(formData);
    }

    async performInstallation(formData) {
        console.log('performInstallation started');

        const steps = [
            { id: 'download', duration: 3000 },
            { id: 'extract', duration: 2000 },
            { id: 'database', duration: 2000 },
            { id: 'config', duration: 2000 },
            { id: 'dependencies', duration: 5000 },
            { id: 'finalize', duration: 2000 }
        ];

        let progress = 0;
        const progressBar = document.getElementById('install-progress-bar');
        const progressText = document.getElementById('install-progress-text');

        if (!progressBar || !progressText) {
            console.error('Progress elements not found');
            this.showAlert('error', 'Installation interface elements not found');
            return;
        }

        console.log('Starting installation steps simulation');

        for (let i = 0; i < steps.length; i++) {
            const step = steps[i];
            const stepElement = document.getElementById(`install-step-${step.id}`);

            if (!stepElement) {
                console.error(`Step element not found: install-step-${step.id}`);
                continue;
            }

            console.log(`Starting step: ${step.id}`);

            // Mark as active
            stepElement.classList.add('active');
            const iconElement = stepElement.querySelector('.installation-step-icon');
            if (iconElement) {
                iconElement.innerHTML = '<div class="spinner"></div>';
            }

            // Simulate progress
            const increment = 100 / steps.length;
            await this.sleep(step.duration);

            // Mark as completed
            stepElement.classList.remove('active');
            stepElement.classList.add('completed');
            if (iconElement) {
                iconElement.innerHTML = '<div class="check">✓</div>';
            }

            progress += increment;
            progressBar.style.width = `${progress}%`;
            progressText.textContent = `${Math.round(progress)}%`;

            console.log(`Completed step: ${step.id}, progress: ${Math.round(progress)}%`);
        }

        // Installation complete - call backend
        console.log('Calling backend installation API');
        try {
            const response = await fetch('install.php', {
                method: 'POST',
                body: formData
            });

            console.log('Backend response received:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('Installation result:', result);

            if (result.success) {
                // Save installation info
                sessionStorage.setItem('install_complete', 'true');
                sessionStorage.setItem('n8n_url', document.getElementById('n8n_url').value);
                sessionStorage.setItem('admin_email', document.getElementById('admin_email').value);

                console.log('Installation successful, moving to complete step');

                // Move to complete step manually
                setTimeout(() => {
                    this.hideStep(this.currentStep);
                    this.currentStep = 6;
                    this.showStep(this.currentStep);
                    this.updateProgressLine();

                    // Update complete page info
                    const completeUrl = document.getElementById('complete-url');
                    const completeEmail = document.getElementById('complete-email');
                    if (completeUrl) {
                        completeUrl.textContent = sessionStorage.getItem('n8n_url');
                    }
                    if (completeEmail) {
                        completeEmail.textContent = sessionStorage.getItem('admin_email');
                    }
                }, 1000);
            } else {
                console.error('Installation failed:', result.message);
                this.showAlert('error', result.message || 'Installation failed.');
            }
        } catch (error) {
            console.error('Installation error:', error);
            this.showAlert('error', 'Installation error: ' + error.message);
        }
    }

    async finishInstallation() {
        const finishBtn = document.getElementById('finish-btn');
        finishBtn.disabled = true;
        finishBtn.innerHTML = '<span class="spinner"></span> Cleaning up...';

        try {
            const formData = new FormData();
            formData.append('action', 'cleanup');

            const response = await fetch('install.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Redirect to N8N
                const n8nUrl = sessionStorage.getItem('n8n_url') || '/';
                window.location.href = n8nUrl;
            } else {
                this.showAlert('warning', 'Installation complete but cleanup failed. Please manually delete the setup folder.');
                setTimeout(() => {
                    window.location.href = '/';
                }, 3000);
            }
        } catch (error) {
            this.showAlert('warning', 'Installation complete. Please manually delete the setup folder.');
            setTimeout(() => {
                window.location.href = '/';
            }, 3000);
        }
    }

    showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
            <span class="alert-icon">${this.getAlertIcon(type)}</span>
            <span class="alert-message">${message}</span>
        `;

        const container = document.querySelector('.installer-card');
        container.insertBefore(alertDiv, container.firstChild);

        // Auto remove after 5 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);

        this.scrollToTop();
    }

    getAlertIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    showRandomTip() {
        const tipBox = document.querySelector('.tip-box');
        if (!tipBox) return;

        const tips = JSON.parse(tipBox.dataset.tips || '[]');
        if (tips.length > 0) {
            const randomTip = tips[Math.floor(Math.random() * tips.length)];
            const tipContent = tipBox.querySelector('.tip-box-content');
            if (tipContent) {
                tipContent.textContent = randomTip;
            }
        }
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Initialize installer when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.installer = new N8NInstaller();
});

// Auto-check requirements on page load
window.addEventListener('load', () => {
    const requirementsCheck = document.getElementById('requirements-check');
    if (requirementsCheck) {
        checkRequirements();
    }
});

async function checkRequirements() {
    const formData = new FormData();
    formData.append('action', 'check_requirements');

    try {
        const response = await fetch('install.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success && result.requirements) {
            updateRequirementsUI(result.requirements);
        }
    } catch (error) {
        console.error('Error checking requirements:', error);
    }
}

function updateRequirementsUI(requirements) {
    Object.keys(requirements).forEach(key => {
        const item = document.getElementById(`req-${key}`);
        if (item) {
            const req = requirements[key];
            item.className = `requirement-item ${req.status}`;

            const valueElement = item.querySelector('.requirement-value');
            if (valueElement) {
                valueElement.textContent = req.message;
            }
        }
    });
}
