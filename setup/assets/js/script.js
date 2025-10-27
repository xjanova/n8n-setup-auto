// Global variables
let currentStep = 1;
const totalSteps = 5;

// Change language
function changeLang(lang) {
    window.location.href = '?lang=' + lang;
}

// Update progress steps
function updateProgress() {
    document.querySelectorAll('.step').forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('active', 'done');

        if (stepNum < currentStep) {
            step.classList.add('done');
        } else if (stepNum === currentStep) {
            step.classList.add('active');
        }
    });
}

// Show/hide steps
function showStep(step) {
    document.querySelectorAll('.wizard-step').forEach(el => {
        el.classList.remove('active');
    });
    document.getElementById('step-' + step).classList.add('active');
    currentStep = step;
    updateProgress();
    window.scrollTo(0, 0);
}

// Next step
function nextStep() {
    if (currentStep < 6) {
        showStep(currentStep + 1);
    }
}

// Previous step
function prevStep() {
    if (currentStep > 1) {
        showStep(currentStep - 1);
    }
}

// Check requirements
async function checkRequirements() {
    const reqList = document.getElementById('req-list');
    reqList.innerHTML = '<div class="loading"></div>';

    try {
        const response = await fetch('install.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=check_requirements'
        });

        // Check if response is ok
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }

        // Get response text first
        const text = await response.text();

        // Try to parse as JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON response:', text);
            throw new Error('Server returned invalid response. Please check server logs.');
        }

        if (result.success) {
            let html = '';
            let allPassed = true;

            for (const [key, req] of Object.entries(result.requirements)) {
                const status = req.status === 'pass' ? 'pass' : 'fail';
                if (status === 'fail') allPassed = false;

                html += `
                    <div class="req-item ${status}">
                        <div>
                            <div class="name">${req.name}</div>
                            <small>${req.message}</small>
                        </div>
                        <div class="status">${status === 'pass' ? '✓' : '✗'}</div>
                    </div>
                `;
            }

            reqList.innerHTML = html;

            // Show next button if all passed
            if (allPassed) {
                document.getElementById('req-next').style.display = 'inline-block';
            }
        } else {
            reqList.innerHTML = '<div class="alert alert-error">' + result.message + '</div>';
        }
    } catch (error) {
        reqList.innerHTML = '<div class="alert alert-error">Error: ' + error.message + '</div>';
    }
}

// Test database connection
async function testDatabase() {
    const form = document.getElementById('db-form');
    const formData = new FormData(form);
    formData.append('action', 'test_database');

    const resultDiv = document.getElementById('db-result');
    resultDiv.innerHTML = '<div class="loading"></div>';

    try {
        const response = await fetch('install.php', {
            method: 'POST',
            body: formData
        });

        // Check if response is ok
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }

        // Get response text first
        const text = await response.text();

        // Try to parse as JSON
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON response:', text);
            throw new Error('Server returned invalid response. Please check server logs.');
        }

        if (result.success) {
            resultDiv.innerHTML = '<div class="alert alert-success">✓ ' + result.message + '</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-error">✗ ' + result.message + '</div>';
        }
    } catch (error) {
        resultDiv.innerHTML = '<div class="alert alert-error">Error: ' + error.message + '</div>';
    }
}

// Generate encryption key
function generateKey() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let key = '';
    for (let i = 0; i < 64; i++) {
        key += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('encryption_key').value = key;
}

// Validate N8N URL is HTTPS
function validateHttps(url) {
    try {
        const urlObj = new URL(url);
        return urlObj.protocol === 'https:';
    } catch (e) {
        return false;
    }
}

// Start installation with real-time streaming
async function startInstall() {
    // Validate N8N URL
    const n8nUrl = document.getElementById('n8n_url').value;
    if (!validateHttps(n8nUrl)) {
        alert('N8N URL must use HTTPS protocol!');
        return;
    }

    // Validate encryption key
    const encKey = document.getElementById('encryption_key').value;
    if (encKey.length < 32) {
        alert('Encryption key must be at least 32 characters!');
        return;
    }

    // Collect all data
    const dbForm = new FormData(document.getElementById('db-form'));
    const n8nForm = new FormData(document.getElementById('n8n-form'));

    const formData = new FormData();

    // Add database data
    for (const [key, value] of dbForm.entries()) {
        formData.append(key, value);
    }

    // Add N8N data
    for (const [key, value] of n8nForm.entries()) {
        formData.append(key, value);
    }

    // Go to installation step
    showStep(5);

    const logDiv = document.getElementById('install-log');
    logDiv.innerHTML = '';

    // Hide loading spinner, we'll show real output
    const loadingDiv = document.querySelector('.loading');
    if (loadingDiv) {
        loadingDiv.style.display = 'none';
    }

    function addLog(msg, type = 'info') {
        const div = document.createElement('div');
        div.className = 'log-line log-' + type;
        div.textContent = msg;
        logDiv.appendChild(div);
        logDiv.scrollTop = logDiv.scrollHeight;
    }

    // Use install-direct.php which generates .sh script and runs with proc_open/shell_exec
    try {
        const response = await fetch('install-direct.php', {
            method: 'POST',
            body: formData
        });

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const {done, value} = await reader.read();

            if (done) {
                break;
            }

            // Decode the chunk and add to buffer
            buffer += decoder.decode(value, {stream: true});

            // Process complete lines
            const lines = buffer.split('\n');
            buffer = lines.pop(); // Keep incomplete line in buffer

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const jsonStr = line.substring(6).trim();
                        if (jsonStr) {
                            const data = JSON.parse(jsonStr);

                            // Check if installation is complete
                            if (data.complete) {
                                if (data.success) {
                                    addLog('', 'success');
                                    addLog('🎉 Ready to use N8N!', 'success');

                                    // Show completion
                                    document.getElementById('final-url').textContent = data.url;
                                    document.getElementById('final-email').textContent = data.email;

                                    setTimeout(() => {
                                        showStep(6);
                                    }, 2000);
                                } else {
                                    addLog('', 'error');
                                    addLog('Installation failed. Please check the errors above.', 'error');
                                }
                                return; // Exit the function
                            } else if (data.message) {
                                // Regular log message
                                const prefix = data.time ? '[' + data.time + '] ' : '';
                                addLog(prefix + data.message, data.type);
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e, 'Line:', line);
                    }
                }
            }
        }
    } catch (error) {
        console.error('Fetch error:', error);
        addLog('', 'error');
        addLog('ERROR: ' + error.message, 'error');
    }
}

// Finish installation
function finish() {
    const url = document.getElementById('final-url').textContent;
    if (confirm('Open N8N now?')) {
        window.location.href = url;
    }
}

// Handle database type change
document.addEventListener('DOMContentLoaded', function() {
    const dbType = document.getElementById('db_type');
    if (dbType) {
        dbType.addEventListener('change', function() {
            const dbFields = document.getElementById('db-fields');
            if (this.value === 'sqlite') {
                dbFields.style.display = 'none';
            } else {
                dbFields.style.display = 'block';

                // Update default port
                const portInput = document.querySelector('input[name="db_port"]');
                if (this.value === 'postgres') {
                    portInput.value = '5432';
                } else {
                    portInput.value = '3306';
                }
            }
        });
    }
});
