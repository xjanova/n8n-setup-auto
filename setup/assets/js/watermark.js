/**
 * Protected Watermark System - DO NOT MODIFY
 * Xman Enterprise co.,ltd.
 */

(function() {
    'use strict';

    // Encoded company information (Base64 + Custom encoding)
    const _0x4a2b = [
        'WG1hbiBFbnRlcnByaXNlIGNvLixsdGQu',
        'aHR0cHM6Ly94bWFuNDI4OS5jb20=',
        'KDA2NikgMDgwLTYwMzgyNzg=',
        'MS4wLjA=',
        'MjAyNTAxMjc=',
        'eG1hbi1mb290ZXItY29udGFpbmVy',
        'aW5zdGFsbGVyLWZvb3Rlcg==',
        'cG93ZXJlZF9ieQ==',
        'dmVyc2lvbg==',
        'UG93ZXJlZCBieQ==',
        'VmVyc2lvbg==',
        'QnVpbGQ=',
        'RGV2ZWxvcGVkIGJ5',
        'UG93ZXJlZCBieQ==',
        'xYTg4OQ=='
    ];

    // Decoding function
    function _0xdecode(str) {
        try {
            return atob(str);
        } catch(e) {
            return str;
        }
    }

    // Protected data
    const footerData = {
        company: _0xdecode(_0x4a2b[0]),
        website: _0xdecode(_0x4a2b[1]),
        phone: _0xdecode(_0x4a2b[2]),
        version: _0xdecode(_0x4a2b[3]),
        build: _0xdecode(_0x4a2b[4])
    };

    // Generate checksum for integrity verification
    function generateChecksum(data) {
        let hash = 0;
        const str = JSON.stringify(data);
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString(36);
    }

    // Create footer content
    function createFooter() {
        const container = document.getElementById(_0xdecode(_0x4a2b[5]));

        if (!container) {
            console.error('Footer container not found');
            return;
        }

        const currentYear = new Date().getFullYear();

        // Create footer HTML
        const footerHTML = `
            <div class="xman-watermark" data-integrity="${generateChecksum(footerData)}">
                <p>
                    <strong style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        💼 Developed by:
                    </strong>
                    <a href="${footerData.website}"
                       target="_blank"
                       rel="noopener noreferrer"
                       style="color: #6366f1; text-decoration: none; font-weight: 700; transition: all 0.3s;"
                       onmouseover="this.style.color='#8b5cf6'; this.style.textDecoration='underline';"
                       onmouseout="this.style.color='#6366f1'; this.style.textDecoration='none';">
                        ${footerData.company}
                    </a>
                </p>
                <p style="margin: 8px 0;">
                    <span style="color: #6366f1; font-weight: 600;">📞</span>
                    <a href="tel:${footerData.phone.replace(/[^0-9]/g, '')}"
                       style="color: #374151; text-decoration: none; font-weight: 500;">
                        ${footerData.phone}
                    </a>
                    <span style="margin: 0 8px; color: #d1d5db;">|</span>
                    <span style="color: #6366f1; font-weight: 600;">🌐</span>
                    <a href="${footerData.website}"
                       target="_blank"
                       rel="noopener noreferrer"
                       style="color: #374151; text-decoration: none; font-weight: 500;">
                        ${footerData.website}
                    </a>
                </p>
                <p style="margin: 8px 0; font-size: 0.9rem;">
                    <span style="color: #8b5cf6; font-weight: 600;">⚡</span>
                    <strong>Version:</strong> ${footerData.version}
                    <span style="color: #d1d5db;">|</span>
                    <strong>Build:</strong> ${footerData.build}
                </p>
                <p style="margin-top: 12px; color: #9ca3af; font-size: 0.85rem; border-top: 2px solid rgba(99, 102, 241, 0.1); padding-top: 12px;">
                    © ${currentYear} <strong style="color: #6366f1;">${footerData.company}</strong>. All rights reserved.
                    <br>
                    <span style="font-size: 0.75rem; color: #d1d5db;">
                        Professional N8N Web Installer | Licensed Software
                    </span>
                </p>
                <div style="margin-top: 10px; font-size: 0.7rem; color: #e5e7eb; opacity: 0.6;">
                    🔒 Protected by integrity verification system
                </div>
            </div>
        `;

        container.innerHTML = footerHTML;

        // Store checksum
        container.setAttribute('data-checksum', generateChecksum(footerData));

        // Protect from modifications
        Object.freeze(footerData);

        // Monitor for tampering
        startIntegrityMonitoring(container);
    }

    // Integrity monitoring system
    function startIntegrityMonitoring(container) {
        const originalChecksum = container.getAttribute('data-checksum');

        // Check every 3 seconds
        setInterval(function() {
            const watermark = container.querySelector('.xman-watermark');

            if (!watermark) {
                console.warn('Watermark removed - Restoring...');
                createFooter();
                return;
            }

            const currentChecksum = watermark.getAttribute('data-integrity');

            if (currentChecksum !== originalChecksum) {
                console.warn('Footer integrity compromised - Restoring...');
                createFooter();
            }

            // Check if footer is hidden
            const style = window.getComputedStyle(container);
            if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
                container.style.display = '';
                container.style.visibility = '';
                container.style.opacity = '';
                console.warn('Footer visibility restored');
            }
        }, 3000);

        // Prevent deletion via DevTools
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.removedNodes.length > 0) {
                    // Check if watermark was removed
                    if (!container.querySelector('.xman-watermark')) {
                        console.warn('Watermark removal detected - Restoring...');
                        createFooter();
                    }
                }

                if (mutation.type === 'attributes') {
                    // Restore important attributes
                    if (mutation.attributeName === 'class') {
                        if (!container.classList.contains('installer-footer')) {
                            container.classList.add('installer-footer');
                        }
                    }
                }
            });
        });

        observer.observe(container, {
            attributes: true,
            childList: true,
            subtree: true
        });

        // Prevent right-click on footer
        container.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Prevent text selection
        container.style.userSelect = 'none';
        container.style.webkitUserSelect = 'none';
        container.style.mozUserSelect = 'none';
        container.style.msUserSelect = 'none';
    }

    // Additional protection: Check console manipulation
    function protectConsole() {
        const consoleCheck = setInterval(function() {
            const devtools = /./;
            devtools.toString = function() {
                this.opened = true;
            };

            console.log('%c⚠️ WARNING', 'color: #ef4444; font-size: 20px; font-weight: bold;');
            console.log('%cThis is a licensed product. Unauthorized modification is prohibited.', 'color: #f59e0b; font-size: 14px;');
            console.log('%cDeveloped by: Xman Enterprise co.,ltd.', 'color: #6366f1; font-size: 14px; font-weight: bold;');
            console.log('%cWebsite: https://xman4289.com', 'color: #8b5cf6; font-size: 12px;');
            console.log('%cPhone: (066) 080-6038278', 'color: #10b981; font-size: 12px;');
        }, 5000);
    }

    // Encode the creation function to prevent easy modification
    const init = function() {
        console.log('🛡️ watermark.js: Initializing...');
        console.log('Document readyState:', document.readyState);

        if (document.readyState === 'loading') {
            console.log('🛡️ watermark.js: Waiting for DOMContentLoaded');
            document.addEventListener('DOMContentLoaded', function() {
                console.log('🛡️ watermark.js: DOMContentLoaded fired, creating footer');
                createFooter();
            });
        } else {
            console.log('🛡️ watermark.js: DOM already loaded, creating footer immediately');
            createFooter();
        }

        // Start console protection
        protectConsole();

        // Add watermark to page title periodically
        setInterval(function() {
            const originalTitle = document.title;
            if (!originalTitle.includes('Xman Enterprise')) {
                // Don't change, but log
                console.log('License: Xman Enterprise co.,ltd. | https://xman4289.com');
            }
        }, 10000);
    };

    // Execute initialization
    console.log('🛡️ watermark.js: Loaded, executing init()');
    init();

    // Prevent this script from being modified
    Object.freeze(init);
    Object.freeze(createFooter);
    Object.freeze(startIntegrityMonitoring);

    // Global protection
    window.addEventListener('load', function() {
        // Double check after full load
        setTimeout(function() {
            const container = document.getElementById(_0xdecode(_0x4a2b[5]));
            if (!container || !container.querySelector('.xman-watermark')) {
                createFooter();
            }
        }, 500);
    });

    // Export verification function (hidden)
    window._xman_verify = function() {
        return generateChecksum(footerData);
    };

})();

// Anti-tamper seal
console.log('%c🛡️ Protected by Xman Enterprise Security System', 'background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; padding: 10px 20px; font-size: 14px; font-weight: bold; border-radius: 8px;');
