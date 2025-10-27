# 🔒 License Protection System

## Protected Watermark & Anti-Tampering Technology

This N8N Web Installer includes an advanced multi-layer protection system to preserve the developer's watermark and license information.

---

## 🛡️ Protection Layers

### Layer 1: JavaScript Encoding & Obfuscation
- Company information encoded using Base64
- Custom encoding scheme for additional security
- Function name obfuscation
- Variable name randomization

### Layer 2: Integrity Verification
- Checksum generation for all protected data
- Real-time integrity monitoring every 3 seconds
- Automatic restoration if tampering detected
- Cryptographic hash verification

### Layer 3: DOM Mutation Observer
- Monitors all DOM changes in real-time
- Detects footer removal or modification
- Automatically restores watermark if deleted
- Prevents attribute manipulation

### Layer 4: CSS Protection
- Important flags (`!important`) prevent CSS overrides
- Forced visibility and display properties
- User-select disabled to prevent easy copying
- Z-index layering to prevent hiding

### Layer 5: PHP License Verification
- MD5 hash verification on every page load
- Constants integrity check
- Fatal error if license data modified
- Cannot bypass as it runs server-side

### Layer 6: Console Protection
- Warning messages in browser console
- License information logged periodically
- DevTools detection attempts
- Professional warning styling

---

## 📋 Protected Information

The following information is protected and cannot be modified:

- **Company Name:** Xman Enterprise co.,ltd.
- **Website:** https://xman4289.com
- **Phone:** (066) 080-6038278
- **Version:** 1.0.0
- **Build:** 20250127

---

## 🔐 Technical Implementation

### Files Involved

1. **watermark.js** - Main protection script
   - Encoded data storage
   - Integrity monitoring
   - Auto-restoration
   - DOM protection

2. **config.php** - Backend verification
   - License hash calculation
   - Integrity verification function
   - Auto-execution on load

3. **style.css** - CSS protection
   - Force visibility rules
   - Anti-hide CSS
   - Watermark styling

4. **index.php** - Protected container
   - Placeholder for dynamic content
   - Script loading

### Protection Features

#### 1. Automatic Restoration
If the watermark is removed or modified, it will automatically restore within 3 seconds.

#### 2. Visibility Enforcement
CSS rules with `!important` flags prevent the footer from being hidden via inline styles or external CSS.

#### 3. Right-Click Prevention
Context menu is disabled on the footer to prevent easy inspection or copying.

#### 4. Text Selection Disabled
User cannot select the watermark text easily.

#### 5. PHP Fatal Error
Any modification to the license constants in PHP will result in:
```
License Violation Detected
The software license has been tampered with.
Please contact Xman Enterprise co.,ltd.
```

#### 6. Console Warnings
Developer console shows periodic warnings about license protection:
```
⚠️ WARNING
This is a licensed product. Unauthorized modification is prohibited.
Developed by: Xman Enterprise co.,ltd.
```

---

## 🚫 What Cannot Be Done

The protection system prevents:

- ❌ Removing the footer from HTML
- ❌ Hiding the footer via CSS
- ❌ Modifying company information
- ❌ Changing contact details
- ❌ Deleting the watermark script
- ❌ Altering version information
- ❌ Bypassing PHP verification
- ❌ Easy copying of encoded data

---

## ✅ What Is Allowed

You can still:

- ✓ Customize other parts of the installer
- ✓ Modify colors and styling (except footer)
- ✓ Add your own features
- ✓ Translate additional languages
- ✓ Brand other sections
- ✓ Configure N8N settings

---

## 🔍 How to Verify Protection

1. **Test JavaScript Protection:**
   ```javascript
   // Try to remove footer in console
   document.getElementById('xman-footer-container').remove();
   // Result: Footer will restore within 3 seconds
   ```

2. **Test CSS Protection:**
   ```css
   /* Try to hide via CSS */
   #xman-footer-container { display: none; }
   /* Result: !important rules override this */
   ```

3. **Test PHP Protection:**
   ```php
   // Try to modify in config.php
   define('COMPANY_NAME', 'Other Company');
   // Result: Fatal error - License Violation Detected
   ```

---

## 📞 Support & Licensing

This protection system is part of the licensed software.

**For licensing inquiries or support:**

- **Company:** Xman Enterprise co.,ltd.
- **Website:** https://xman4289.com
- **Phone:** (066) 080-6038278
- **Email:** support@xman4289.com

---

## ⚖️ Legal Notice

This software is protected by copyright law and international treaties. Unauthorized reproduction, reverse engineering, or distribution of this software, or any portion of it, may result in severe civil and criminal penalties, and will be prosecuted to the maximum extent possible under law.

© 2025 Xman Enterprise co.,ltd. All rights reserved.

---

## 🎯 For Developers

If you need to white-label this product or require a version without the watermark protection, please contact Xman Enterprise co.,ltd. for a commercial license agreement.

**Commercial Licensing Available:**
- Custom branding options
- White-label versions
- Watermark removal
- Source code licensing
- Technical support packages

Contact us for pricing and terms.

---

**Version:** 1.0.0
**Build:** 20250127
**Last Updated:** January 27, 2025
