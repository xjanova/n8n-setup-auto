# N8N Web Installer 🚀

## Professional Web-Based Installer for N8N Workflow Automation

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://xman4289.com)
[![License](https://img.shields.io/badge/license-Proprietary-red.svg)](https://xman4289.com)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net)
[![Node.js](https://img.shields.io/badge/Node.js-18.0%2B-339933.svg)](https://nodejs.org)

---

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Screenshots](#screenshots)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)
- [Security](#security)
- [License](#license)
- [Support](#support)

---

## 🌟 About

The **N8N Web Installer** is a professional, enterprise-grade web-based installation wizard for N8N (workflow automation tool). It provides an intuitive, step-by-step interface to install and configure N8N on your server with ease.

### Developed By

**Xman Enterprise co.,ltd.**
Website: [https://xman4289.com](https://xman4289.com)
Phone: (066) 080-6038278

---

## ✨ Features

### 🎨 Beautiful Modern UI
- **3D Design** - Professional gradient purple-cyan color scheme
- **Responsive Layout** - Works perfectly on all devices
- **Smooth Animations** - Delightful user experience with CSS animations
- **Dark/Light Mode** - Automatic theme adaptation

### 🌐 Multi-Language Support
- Thai (ไทย) - Full translation
- English - Complete localization
- Easy to add more languages

### 🔍 Smart System Checks
- **PHP Version** - Automatic detection and validation
- **PHP Extensions** - Check for required extensions (curl, json, mbstring, zip, pdo, openssl)
- **Node.js & NPM** - Version compatibility check
- **File Permissions** - Write permission validation
- **Disk Space** - Available space verification
- **Memory & Execution Time** - PHP configuration checks

### 🗄️ Database Support
- **MySQL/MariaDB** - Full support
- **PostgreSQL** - Complete integration
- **SQLite** - Lightweight option
- **Connection Testing** - Pre-installation validation

### ⚙️ Advanced Configuration
- **Custom Installation Path** - Flexible directory selection
- **Encryption Key** - Auto-generation capability
- **Timezone Settings** - Multiple timezone support
- **Port Configuration** - Customizable N8N port
- **Admin Account** - Secure password setup

### 📊 Installation Progress
- **Real-time Progress Bar** - Visual feedback
- **Step-by-step Process** - Clear installation stages
- **Error Handling** - Comprehensive error messages
- **Logging** - Complete installation logs

### 💡 Professional Features
- **Random Tips** - Helpful hints throughout installation
- **CSRF Protection** - Security token validation
- **Auto-cleanup** - Removes installation files after completion
- **Session Management** - Secure session handling

---

## 📦 Requirements

### Server Requirements

#### PHP
- **Version**: 7.4.0 or higher
- **Extensions**:
  - `curl` - For HTTP requests
  - `json` - For JSON handling
  - `mbstring` - For multi-byte string support
  - `zip` - For archive extraction
  - `pdo` - For database connectivity
  - `openssl` - For encryption

#### Node.js
- **Version**: 18.0.0 or higher (LTS recommended)
- **NPM**: 8.0.0 or higher

#### Database
Choose one of the following:
- **MySQL**: 5.7+ or **MariaDB**: 10.2+
- **PostgreSQL**: 10+
- **SQLite**: 3.x

#### Server Configuration
- **Memory Limit**: 256M or higher
- **Max Execution Time**: 300 seconds (5 minutes) or higher
- **Disk Space**: Minimum 500 MB free
- **Write Permissions**: On installation directory

### Supported Operating Systems
- Linux (Ubuntu, Debian, CentOS, RHEL)
- macOS
- Windows (with proper PHP/Node.js setup)

---

## 🚀 Installation

### Quick Start (3 Steps)

#### 1. Upload Files
```bash
# Upload the setup folder to your web server
# Example structure:
# /var/www/html/
# └── setup/
#     ├── index.php
#     ├── install.php
#     ├── config.php
#     └── ...
```

#### 2. Set Permissions
```bash
# Set proper permissions
chmod -R 755 setup/
chmod -R 777 ../  # Installation root directory
```

#### 3. Access Installer
```
Open your browser and navigate to:
http://your-domain.com/setup/

Follow the on-screen wizard!
```

### Detailed Installation Steps

#### Step 1: Pre-Installation Checklist

Before starting, ensure you have:

- [ ] PHP 7.4+ installed with required extensions
- [ ] Node.js 18.0+ and NPM installed
- [ ] Database server (MySQL/PostgreSQL/SQLite) ready
- [ ] Database credentials (username, password, database name)
- [ ] Write permissions on server directory
- [ ] Internet connection for downloading N8N

#### Step 2: Upload Installer

**Via FTP/SFTP:**
1. Download the installer package
2. Extract the ZIP file
3. Upload the `setup` folder to your web root
4. Ensure the folder is accessible via browser

**Via Command Line:**
```bash
# Clone or download the installer
cd /var/www/html
# Upload setup folder here

# Verify structure
ls -la setup/
```

#### Step 3: Run the Installer

1. **Access**: Navigate to `http://your-domain.com/setup/`
2. **Language**: Select your preferred language (Thai/English)
3. **Welcome**: Review the requirements checklist
4. **System Check**: Wait for automatic system validation
5. **Database**: Enter your database credentials and test connection
6. **Configuration**: Set up N8N URL, admin credentials, and encryption key
7. **Installation**: Click "Install" and wait for completion
8. **Complete**: Installation files will be automatically removed

---

## 📖 Usage

### Wizard Steps Explained

#### Step 1: Welcome Screen
- Introduction to the installer
- Requirements checklist
- Language selection
- Helpful tips

#### Step 2: System Requirements
- Automatic system checks
- PHP version and extensions
- Node.js and NPM detection
- Disk space and permissions
- View detailed results

#### Step 3: Database Configuration
- Select database type (MySQL/PostgreSQL/SQLite)
- Enter connection details:
  - Host (default: localhost)
  - Port (default: 3306 for MySQL)
  - Database name
  - Username and password
  - Table prefix (optional)
- Test connection before proceeding

#### Step 4: N8N Configuration
- **N8N URL**: Full URL where N8N will be accessible
- **Port**: N8N service port (default: 5678)
- **Admin Email**: Administrator login email
- **Admin Password**: Secure password (min 8 characters)
- **Timezone**: Server timezone
- **Encryption Key**: Auto-generated or custom (32+ characters)
- **Install Location**: Custom path (optional)

#### Step 5: Installation Process
Real-time progress tracking:
1. Downloading N8N
2. Extracting files
3. Creating database tables
4. Generating configuration files
5. Installing dependencies
6. Finalizing setup

#### Step 6: Installation Complete
- Installation summary
- Access credentials
- Quick start guide
- Automatic cleanup of installer files

---

## ⚙️ Configuration

### Environment Variables

After installation, N8N will be configured with the following environment variables in `.env` file:

```bash
# Authentication
N8N_BASIC_AUTH_ACTIVE=true
N8N_BASIC_AUTH_USER=your-email@domain.com
N8N_BASIC_AUTH_PASSWORD=your-password

# Server
N8N_HOST=localhost
N8N_PORT=5678
N8N_PROTOCOL=http
WEBHOOK_URL=http://your-domain.com

# Timezone
GENERIC_TIMEZONE=Asia/Bangkok

# Encryption
N8N_ENCRYPTION_KEY=your-generated-key

# Database
DB_TYPE=mysql
DB_MYSQL_HOST=localhost
DB_MYSQL_PORT=3306
DB_MYSQL_DATABASE=n8n
DB_MYSQL_USER=username
DB_MYSQL_PASSWORD=password
DB_TABLE_PREFIX=n8n_

# Logging
N8N_LOG_LEVEL=info
N8N_LOG_OUTPUT=console,file
```

### Starting N8N

After installation, you can start N8N using:

```bash
# Using the generated start script
cd /path/to/installation
./start-n8n.sh

# Or manually
cd /path/to/installation/n8n
npx n8n start

# Or using systemd service (if configured)
sudo systemctl start n8n
sudo systemctl enable n8n  # Auto-start on boot
```

### Accessing N8N

Open your browser and navigate to:
```
http://your-domain.com:5678
```

Login with the credentials you set during installation.

---

## 🔧 Troubleshooting

### Common Issues

#### 1. "PHP version too old"
**Solution**: Upgrade PHP to 7.4 or higher
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php7.4

# CentOS/RHEL
sudo yum install php74
```

#### 2. "Missing PHP extensions"
**Solution**: Install required extensions
```bash
# Ubuntu/Debian
sudo apt install php7.4-curl php7.4-json php7.4-mbstring php7.4-zip php7.4-pdo php7.4-mysql

# CentOS/RHEL
sudo yum install php74-curl php74-json php74-mbstring php74-zip php74-pdo php74-mysqlnd
```

#### 3. "Node.js not found"
**Solution**: Install Node.js 18+
```bash
# Using NodeSource
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Verify
node --version
npm --version
```

#### 4. "Permission denied"
**Solution**: Set correct permissions
```bash
# Make installation directory writable
sudo chmod -R 755 /path/to/installation
sudo chown -R www-data:www-data /path/to/installation
```

#### 5. "Database connection failed"
**Solution**: Verify database credentials and ensure database exists
```bash
# Create database
mysql -u root -p
CREATE DATABASE n8n;
GRANT ALL PRIVILEGES ON n8n.* TO 'username'@'localhost' IDENTIFIED BY 'password';
FLUSH PRIVILEGES;
EXIT;
```

#### 6. "Installation stalled"
**Solution**: Check PHP settings
```bash
# Edit php.ini
max_execution_time = 300
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
```

### Logs

Check installation logs for detailed error messages:
```bash
# Installer log
cat /path/to/setup/installer.log

# N8N logs
cat /path/to/installation/logs/n8n.log

# PHP error log
tail -f /var/log/php/error.log
```

---

## 🔒 Security

### Best Practices

1. **Remove Installer**: The installer automatically removes itself after completion. If not, manually delete the `setup` folder:
   ```bash
   rm -rf /path/to/setup
   ```

2. **Strong Passwords**: Use strong, unique passwords for:
   - Database user
   - N8N admin account
   - Encryption key

3. **HTTPS**: Use SSL/TLS for production:
   ```bash
   # Update N8N configuration
   N8N_PROTOCOL=https
   ```

4. **Firewall**: Configure firewall to allow only necessary ports:
   ```bash
   sudo ufw allow 5678/tcp
   sudo ufw enable
   ```

5. **Database Security**: Use dedicated database user with limited privileges

6. **Backup**: Regular backups of:
   - N8N workflows
   - Database
   - Configuration files
   - Encryption key

7. **Updates**: Keep N8N updated:
   ```bash
   cd /path/to/installation/n8n
   npm update n8n
   ```

---

## 📄 License

**Proprietary License**

© 2025 Xman Enterprise co.,ltd. All rights reserved.

This software is proprietary and confidential. Unauthorized copying, transfer, or reproduction of this software, via any medium, is strictly prohibited.

For licensing inquiries, contact:
- Website: https://xman4289.com
- Phone: (066) 080-6038278

---

## 🆘 Support

### Get Help

- **Website**: [https://xman4289.com](https://xman4289.com)
- **Phone**: (066) 080-6038278
- **Email**: support@xman4289.com

### Documentation

- [N8N Official Documentation](https://docs.n8n.io)
- [N8N Community Forum](https://community.n8n.io)

### Professional Services

We offer:
- Custom installation services
- N8N configuration and optimization
- Workflow development
- Technical support
- Training and consulting

Contact us for a quote!

---

## 🙏 Acknowledgments

- N8N Team - For creating an amazing workflow automation tool
- PHP Community - For the powerful language
- Node.js Team - For the runtime environment

---

## 📊 Version History

### Version 1.0.0 (2025-01-27)
- ✨ Initial release
- 🎨 Beautiful modern UI with 3D design
- 🌐 Thai and English language support
- 🔍 Comprehensive system requirements checking
- 🗄️ MySQL, PostgreSQL, and SQLite support
- ⚙️ Advanced configuration options
- 📊 Real-time installation progress
- 💡 Professional tips and guidance
- 🔒 Security features and CSRF protection
- 🧹 Automatic cleanup after installation

---

**Made with ❤️ by Xman Enterprise co.,ltd.**

🌐 [https://xman4289.com](https://xman4289.com)
📞 (066) 080-6038278

---

_For the best experience, use the latest versions of PHP and Node.js, and ensure your server meets all requirements._
