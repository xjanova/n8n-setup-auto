# N8N Auto Installation Script for Ubuntu

[![Ubuntu](https://img.shields.io/badge/Ubuntu-20.04%20%7C%2022.04%20%7C%2024.04-orange?logo=ubuntu)](https://ubuntu.com/)
[![Node.js](https://img.shields.io/badge/Node.js-20%20LTS-green?logo=node.js)](https://nodejs.org/)
[![N8N](https://img.shields.io/badge/N8N-Latest-blue?logo=n8n)](https://n8n.io/)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

🇹🇭 **[คู่มือภาษาไทย](./คู่มือติดตั้ง.md)** | 🇬🇧 **English Guide Below**

---

## Overview

Complete automated installation script for **N8N** (workflow automation tool) on Ubuntu servers. This script handles everything from system updates to service configuration, making N8N deployment effortless.

## Features

- ✅ **Fully Automated** - One command installation
- ✅ **Node.js 20 LTS** - Latest stable version
- ✅ **Systemd Service** - Auto-start on boot
- ✅ **User Management** - Dedicated system user
- ✅ **Environment Config** - Pre-configured settings
- ✅ **Firewall Setup** - UFW configuration
- ✅ **Nginx Support** - Optional reverse proxy
- ✅ **Multi-version** - Ubuntu 20.04, 22.04, 24.04

## Quick Start

### One-Line Installation

```bash
wget https://raw.githubusercontent.com/[YOUR-REPO]/sh-install/install-n8n.sh && chmod +x install-n8n.sh && sudo ./install-n8n.sh
```

### Manual Installation

```bash
# Download the script
wget https://raw.githubusercontent.com/[YOUR-REPO]/sh-install/install-n8n.sh

# Make it executable
chmod +x install-n8n.sh

# Run the installation
sudo ./install-n8n.sh
```

### From Git Repository

```bash
# Clone the repository
git clone https://github.com/[YOUR-REPO]/n8n-setup-auto.git

# Navigate to the directory
cd n8n-setup-auto/sh-install

# Make script executable
chmod +x install-n8n.sh

# Run the installation
sudo ./install-n8n.sh
```

## System Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **OS** | Ubuntu 20.04+ | Ubuntu 22.04+ |
| **CPU** | 1 Core | 2+ Cores |
| **RAM** | 1 GB | 2+ GB |
| **Disk** | 5 GB | 10+ GB |
| **Network** | Internet | Broadband |

## What Gets Installed

The script automatically installs and configures:

1. **System Updates** - Latest security patches
2. **Node.js 20** - Latest LTS version
3. **N8N** - Latest stable version
4. **System User** - Dedicated `n8n` user
5. **Systemd Service** - Auto-start configuration
6. **Environment Variables** - Pre-configured settings
7. **Firewall Rules** - UFW configuration (optional)
8. **Nginx Proxy** - Reverse proxy setup (optional)

## Installation Process

```
📋 Pre-Installation Checks
├── ✓ Root privileges verification
├── ✓ Ubuntu version detection
└── ✓ System requirements check

📦 System Preparation
├── ✓ Package list update
├── ✓ System upgrade
└── ✓ Essential tools installation

🟢 Node.js Installation
├── ✓ NodeSource repository setup
├── ✓ Node.js 20 LTS installation
└── ✓ npm verification

👤 User Setup
├── ✓ Create n8n system user
├── ✓ Setup home directory
└── ✓ Create data directory

🚀 N8N Installation
├── ✓ Global npm installation
└── ✓ Version verification

⚙️ Configuration
├── ✓ Environment variables
├── ✓ Systemd service file
└── ✓ Directory permissions

🔥 Optional Components
├── ⚠️ Firewall configuration (prompt)
└── ⚠️ Nginx reverse proxy (prompt)

✨ Service Activation
├── ✓ Enable auto-start
├── ✓ Start N8N service
└── ✓ Status verification
```

## Post-Installation

### Access N8N

```
http://YOUR_SERVER_IP:5678
```

### Service Management

```bash
# Start service
sudo systemctl start n8n

# Stop service
sudo systemctl stop n8n

# Restart service
sudo systemctl restart n8n

# Check status
sudo systemctl status n8n

# View logs
sudo journalctl -u n8n -f
```

### Configuration File

Location: `/home/n8n/.n8n-env`

```bash
# Edit configuration
sudo nano /home/n8n/.n8n-env

# After editing, restart service
sudo systemctl restart n8n
```

## Key Configuration Options

### Change Port

```bash
N8N_PORT=8080  # Change from default 5678
```

### Enable Authentication

```bash
N8N_BASIC_AUTH_ACTIVE=true
N8N_BASIC_AUTH_USER=admin
N8N_BASIC_AUTH_PASSWORD=your_secure_password
```

### Set Webhook URL

```bash
WEBHOOK_URL=http://your-domain.com/
```

### Change Timezone

```bash
GENERIC_TIMEZONE=Asia/Bangkok
```

## Backup & Restore

### Create Backup

```bash
# Backup entire N8N data
sudo tar -czf n8n-backup-$(date +%Y%m%d).tar.gz -C /home/n8n .n8n

# Backup database only
sudo cp /home/n8n/.n8n/database.sqlite /backup/n8n-db-$(date +%Y%m%d).sqlite
```

### Restore Backup

```bash
# Stop service
sudo systemctl stop n8n

# Restore data
sudo tar -xzf n8n-backup-20240101.tar.gz -C /home/n8n/

# Fix permissions
sudo chown -R n8n:n8n /home/n8n/.n8n

# Start service
sudo systemctl start n8n
```

## Update N8N

```bash
# Stop service
sudo systemctl stop n8n

# Create backup
sudo tar -czf /backup/n8n-backup-before-update.tar.gz -C /home/n8n .n8n

# Update N8N
sudo npm update -g n8n

# Start service
sudo systemctl start n8n

# Verify
sudo systemctl status n8n
```

## Troubleshooting

### N8N Won't Start

```bash
# Check status
sudo systemctl status n8n

# View logs
sudo journalctl -u n8n -n 100 --no-pager

# Check permissions
sudo chown -R n8n:n8n /home/n8n/.n8n
```

### Can't Access Web Interface

```bash
# Check if N8N is running
sudo systemctl status n8n

# Check port
sudo netstat -tlnp | grep 5678

# Check firewall
sudo ufw status
sudo ufw allow 5678/tcp
```

### High CPU/Memory Usage

```bash
# Check resource usage
top
htop

# Set limits in systemd
sudo nano /etc/systemd/system/n8n.service
```

Add under `[Service]`:
```ini
MemoryLimit=1G
CPUQuota=50%
```

Then reload:
```bash
sudo systemctl daemon-reload
sudo systemctl restart n8n
```

## Security Recommendations

1. **Enable Authentication**
   ```bash
   N8N_BASIC_AUTH_ACTIVE=true
   ```

2. **Setup SSL/HTTPS**
   ```bash
   sudo certbot --nginx -d your-domain.com
   ```

3. **Configure Firewall**
   ```bash
   sudo ufw enable
   sudo ufw allow ssh
   sudo ufw allow 5678/tcp
   ```

4. **Regular Backups**
   ```bash
   # Setup daily backups via cron
   0 2 * * * /usr/local/bin/backup-n8n.sh
   ```

5. **Keep Updated**
   ```bash
   sudo npm update -g n8n
   ```

## Uninstall

```bash
# Stop and disable service
sudo systemctl stop n8n
sudo systemctl disable n8n

# Backup data (optional)
sudo tar -czf /backup/n8n-final-backup.tar.gz -C /home/n8n .n8n

# Remove N8N
sudo npm uninstall -g n8n

# Remove service file
sudo rm /etc/systemd/system/n8n.service
sudo systemctl daemon-reload

# Remove user and data
sudo userdel -r n8n
```

## Files and Directories

```
/home/n8n/                    # N8N user home directory
├── .n8n/                     # N8N data directory
│   ├── database.sqlite       # SQLite database
│   ├── logs/                 # Log files
│   └── nodes/                # Custom nodes
└── .n8n-env                  # Environment configuration

/etc/systemd/system/
└── n8n.service              # Systemd service file

/usr/local/bin/
└── n8n                      # N8N binary (from npm)
```

## Environment Variables Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `N8N_HOST` | `0.0.0.0` | Bind address |
| `N8N_PORT` | `5678` | Port number |
| `N8N_PROTOCOL` | `http` | Protocol (http/https) |
| `N8N_USER_FOLDER` | `/home/n8n/.n8n` | Data directory |
| `WEBHOOK_URL` | `http://IP:5678/` | Webhook URL |
| `GENERIC_TIMEZONE` | `Asia/Bangkok` | Timezone |
| `N8N_BASIC_AUTH_ACTIVE` | `false` | Enable auth |
| `DB_TYPE` | `sqlite` | Database type |

## FAQ

**Q: Is N8N free?**
A: Yes, N8N is open-source and free to self-host.

**Q: How is it different from Zapier?**
A: N8N is self-hosted, giving you full control and no monthly fees.

**Q: Can I use PostgreSQL instead of SQLite?**
A: Yes, configure it in `/home/n8n/.n8n-env`.

**Q: How do I setup SSL?**
A: Use Let's Encrypt with Nginx. See [Thai Manual](./คู่มือติดตั้ง.md) for details.

**Q: Is it production-ready?**
A: Yes, with proper security configurations (SSL, auth, backups).

## Resources

- **Official Documentation:** https://docs.n8n.io/
- **Community Forum:** https://community.n8n.io/
- **GitHub Repository:** https://github.com/n8n-io/n8n
- **YouTube Channel:** https://www.youtube.com/c/n8n-io

## Documentation

- 🇹🇭 **[Complete Thai Manual](./คู่มือติดตั้ง.md)** - Detailed guide in Thai
- 🇬🇧 **[This README](./README.md)** - Quick start in English

## Support

If you encounter issues:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review logs: `sudo journalctl -u n8n -f`
3. Visit [N8N Community Forum](https://community.n8n.io/)
4. Open an issue on GitHub

## License

- This script: MIT License
- N8N: Fair-code license (Apache 2.0 with Commons Clause)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## Quick Reference Card

```bash
# Installation
sudo ./install-n8n.sh

# Service Control
sudo systemctl {start|stop|restart|status} n8n

# Logs
sudo journalctl -u n8n -f

# Configuration
sudo nano /home/n8n/.n8n-env
sudo systemctl restart n8n

# Backup
sudo tar -czf backup.tar.gz -C /home/n8n .n8n

# Update
sudo systemctl stop n8n
sudo npm update -g n8n
sudo systemctl start n8n

# Access
http://YOUR_IP:5678
```

---

**Made with ❤️ for the N8N Community**

**Version:** 2.0.0
**Last Updated:** 2024
**Maintained by:** N8N Auto Install Script Team
