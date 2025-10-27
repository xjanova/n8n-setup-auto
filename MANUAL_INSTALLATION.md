# N8N Manual Installation Guide

This guide is for servers where PHP `exec()` function is disabled.

## Problem

If you see this message when accessing the installer:
```
⚠️ Manual Installation Required
PHP exec() Function is Disabled
```

## Solution 1: Enable exec() (Recommended)

### For Apache/Linux Servers:

1. **Find your php.ini file:**
```bash
php --ini
```

2. **Edit php.ini:**
```bash
sudo nano /etc/php/8.3/apache2/php.ini
```

3. **Find this line:**
```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
```

4. **Remove `exec` from the list:**
```ini
disable_functions = passthru,shell_exec,system,proc_open,popen
```

5. **Save and restart Apache:**
```bash
sudo systemctl restart apache2
```

6. **Verify:**
```bash
php -r "exec('echo test', \$output); echo implode('', \$output);"
```

If it prints "test", exec() is working!

---

## Solution 2: Install via SSH

If you cannot enable exec(), use this SSH installation method:

### Step 1: Connect to Server
```bash
ssh user@yourdomain.com
```

### Step 2: Navigate to Directory
```bash
cd /path/to/your/website
# Example: cd /home/admin/domains/n8n.xman4289.com/public_html
```

### Step 3: Run Installation Script
```bash
chmod +x install-n8n.sh
bash install-n8n.sh
```

### Step 4: Follow Prompts

The script will ask for:

1. **N8N URL**
   - Example: `https://n8n.yourdomain.com`
   - Must use HTTPS!

2. **Admin Email**
   - Example: `admin@yourdomain.com`

3. **Admin Password**
   - Choose a strong password
   - This will be used to login to N8N

4. **Encryption Key**
   - Press Enter to auto-generate
   - Or provide your own (min 32 chars)

5. **Database Type**
   - Options: `mysql`, `postgres`, or `sqlite`
   - Default: `sqlite` (recommended for simple setup)

6. **Database Credentials** (if not using SQLite)
   - Host: Usually `localhost`
   - Port: `3306` for MySQL, `5432` for PostgreSQL
   - Database Name
   - Username
   - Password

### Step 5: Wait for Installation

Installation typically takes 2-5 minutes depending on your connection speed.

You'll see:
```
✓ Node.js v18.19.0
✓ npm v10.2.3
✓ Directory created
✓ package.json created
✓ .env file created
⏳ Installing N8N (this may take 2-5 minutes)...
✓ N8N installed successfully
✓ start.sh created
✓ stop.sh created
```

### Step 6: Start N8N

**Option A: Manual Start**
```bash
cd n8n
./start.sh
```

**Option B: Systemd Service (Auto-start on boot)**
```bash
sudo cp n8n/n8n.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable n8n
sudo systemctl start n8n
```

### Step 7: Verify Installation

Check if N8N is running:
```bash
ps aux | grep n8n
```

Check logs:
```bash
tail -f n8n/n8n.log
```

Access N8N:
```
https://n8n.yourdomain.com
```

---

## Common Issues

### Issue: "Node.js is not installed"

**Solution:**
```bash
# For Ubuntu/Debian:
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# For CentOS/RHEL:
curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -
sudo yum install -y nodejs
```

### Issue: "Permission denied"

**Solution:**
```bash
chmod +x install-n8n.sh
# Or run with bash explicitly:
bash install-n8n.sh
```

### Issue: "Port 5678 already in use"

**Solution:**
```bash
# Find process using port 5678
sudo lsof -i :5678
# Kill it
sudo kill -9 <PID>
```

### Issue: "Cannot connect to database"

**Solution:**
- Verify database is running
- Check credentials
- Ensure database exists
- Check firewall rules

**Test MySQL connection:**
```bash
mysql -h localhost -u username -p database_name
```

---

## Files Created

After installation, you'll find these in the `n8n/` directory:

```
n8n/
├── .env                    # Environment configuration
├── package.json            # NPM configuration
├── package-lock.json       # NPM lock file
├── node_modules/           # N8N dependencies
├── start.sh               # Start script
├── stop.sh                # Stop script
├── n8n.service            # Systemd service file
├── n8n.log                # Runtime logs
├── install-info.json      # Installation metadata
└── .n8n/                  # N8N user data (auto-created)
```

---

## Management Commands

### Start N8N
```bash
cd n8n && ./start.sh
# Or with systemd:
sudo systemctl start n8n
```

### Stop N8N
```bash
cd n8n && ./stop.sh
# Or with systemd:
sudo systemctl stop n8n
```

### Restart N8N
```bash
sudo systemctl restart n8n
```

### Check Status
```bash
sudo systemctl status n8n
```

### View Logs
```bash
# Real-time logs
tail -f n8n/n8n.log

# Or with systemd:
sudo journalctl -u n8n -f
```

---

## Support

**Xman Enterprise co.,ltd.**
- Website: https://xman4289.com
- Phone: (066) 080-6038278
- Version: 1.0.0
- Build: 20250127

---

## License

© 2025 Xman Enterprise co.,ltd. All rights reserved.
