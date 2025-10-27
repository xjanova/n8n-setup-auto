#!/bin/bash

##############################################################################
# N8N Installation Script (SSH Version)
# Xman Enterprise co.,ltd.
# https://xman4289.com
# (066) 080-6038278
#
# This script installs N8N when PHP exec() is disabled
# Usage: bash install-n8n.sh
##############################################################################

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

echo "================================================="
echo "  N8N Installation Script"
echo "  Xman Enterprise co.,ltd."
echo "================================================="
echo ""

# Detect installation directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
INSTALL_DIR="${SCRIPT_DIR}/n8n"

print_step "Installation directory: $INSTALL_DIR"
echo ""

# Check Node.js
print_step "Checking Node.js..."
if ! command -v node &> /dev/null; then
    print_error "Node.js is not installed"
    echo ""
    echo "Please install Node.js first:"
    echo "  curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -"
    echo "  sudo apt-get install -y nodejs"
    exit 1
fi

NODE_VERSION=$(node --version)
print_success "Node.js $NODE_VERSION"

# Check npm
print_step "Checking npm..."
if ! command -v npm &> /dev/null; then
    print_error "npm is not installed"
    exit 1
fi

NPM_VERSION=$(npm --version)
print_success "npm v$NPM_VERSION"
echo ""

# Get user input
echo "Please provide the following information:"
echo ""

read -p "N8N URL (https://...): " N8N_URL
read -p "Admin Email: " ADMIN_EMAIL
read -sp "Admin Password: " ADMIN_PASSWORD
echo ""
read -p "Encryption Key (min 32 chars, leave empty to generate): " ENCRYPTION_KEY

if [ -z "$ENCRYPTION_KEY" ]; then
    ENCRYPTION_KEY=$(openssl rand -base64 48)
    print_success "Generated encryption key"
fi

echo ""
read -p "Database Type (mysql/postgres/sqlite) [sqlite]: " DB_TYPE
DB_TYPE=${DB_TYPE:-sqlite}

if [ "$DB_TYPE" != "sqlite" ]; then
    read -p "Database Host [localhost]: " DB_HOST
    DB_HOST=${DB_HOST:-localhost}

    read -p "Database Port [3306]: " DB_PORT
    DB_PORT=${DB_PORT:-3306}

    read -p "Database Name: " DB_NAME
    read -p "Database User: " DB_USER
    read -sp "Database Password: " DB_PASS
    echo ""
fi

echo ""
print_step "Creating N8N directory..."
mkdir -p "$INSTALL_DIR"
cd "$INSTALL_DIR"
print_success "Directory created"

# Create package.json
print_step "Creating package.json..."
cat > package.json <<EOF
{
  "name": "n8n-instance",
  "version": "1.0.0",
  "description": "N8N instance for Xman Enterprise",
  "scripts": {
    "start": "n8n start",
    "stop": "pkill -f n8n"
  },
  "dependencies": {
    "n8n": "latest"
  }
}
EOF
print_success "package.json created"

# Create .env file
print_step "Creating .env file..."
cat > .env <<EOF
# N8N Configuration
# Generated: $(date)
# Xman Enterprise co.,ltd.

N8N_BASIC_AUTH_ACTIVE=true
N8N_BASIC_AUTH_USER=$ADMIN_EMAIL
N8N_BASIC_AUTH_PASSWORD=$ADMIN_PASSWORD
N8N_ENCRYPTION_KEY=$ENCRYPTION_KEY
N8N_HOST=$N8N_URL
N8N_PROTOCOL=https
N8N_PORT=5678
N8N_USER_FOLDER=$INSTALL_DIR

# Database Configuration
EOF

if [ "$DB_TYPE" = "sqlite" ]; then
    echo "DB_TYPE=sqlite" >> .env
    echo "DB_SQLITE_DATABASE=$INSTALL_DIR/n8n.db" >> .env
elif [ "$DB_TYPE" = "mysql" ]; then
    cat >> .env <<EOF
DB_TYPE=mysqldb
DB_MYSQLDB_HOST=$DB_HOST
DB_MYSQLDB_PORT=$DB_PORT
DB_MYSQLDB_DATABASE=$DB_NAME
DB_MYSQLDB_USER=$DB_USER
DB_MYSQLDB_PASSWORD=$DB_PASS
EOF
elif [ "$DB_TYPE" = "postgres" ]; then
    cat >> .env <<EOF
DB_TYPE=postgresdb
DB_POSTGRESDB_HOST=$DB_HOST
DB_POSTGRESDB_PORT=$DB_PORT
DB_POSTGRESDB_DATABASE=$DB_NAME
DB_POSTGRESDB_USER=$DB_USER
DB_POSTGRESDB_PASSWORD=$DB_PASS
EOF
fi

print_success ".env file created"

# Install N8N
echo ""
print_step "Installing N8N (this may take 2-5 minutes)..."
npm install

if [ $? -eq 0 ]; then
    print_success "N8N installed successfully"
else
    print_error "N8N installation failed"
    exit 1
fi

# Create start script
print_step "Creating start script..."
cat > start.sh <<'EOF'
#!/bin/bash
cd "$(dirname "$0")"
source .env
exec npx n8n start
EOF
chmod +x start.sh
print_success "start.sh created"

# Create stop script
print_step "Creating stop script..."
cat > stop.sh <<'EOF'
#!/bin/bash
pkill -f "n8n start"
echo "N8N stopped"
EOF
chmod +x stop.sh
print_success "stop.sh created"

# Create systemd service
print_step "Creating systemd service file..."
CURRENT_USER=$(whoami)
cat > n8n.service <<EOF
[Unit]
Description=N8N Workflow Automation
After=network.target

[Service]
Type=simple
User=$CURRENT_USER
WorkingDirectory=$INSTALL_DIR
ExecStart=$INSTALL_DIR/start.sh
Restart=on-failure

[Install]
WantedBy=multi-user.target
EOF
print_success "n8n.service created"

# Save installation info
cat > install-info.json <<EOF
{
  "installed_at": "$(date -u +"%Y-%m-%d %H:%M:%S")",
  "version": "1.0.0",
  "build": "20250127",
  "url": "$N8N_URL",
  "admin_email": "$ADMIN_EMAIL",
  "database": "$DB_TYPE",
  "company": "Xman Enterprise co.,ltd."
}
EOF

echo ""
echo "================================================="
echo "  Installation Completed Successfully!"
echo "================================================="
echo ""
print_success "N8N URL: $N8N_URL"
print_success "Admin Email: $ADMIN_EMAIL"
print_success "Database: $DB_TYPE"
print_success "Installation directory: $INSTALL_DIR"
echo ""
echo "Next steps:"
echo ""
echo "1. Start N8N manually:"
echo "   cd $INSTALL_DIR && ./start.sh"
echo ""
echo "2. Or install as systemd service:"
echo "   sudo cp n8n.service /etc/systemd/system/"
echo "   sudo systemctl daemon-reload"
echo "   sudo systemctl enable n8n"
echo "   sudo systemctl start n8n"
echo ""
echo "3. Access N8N:"
echo "   $N8N_URL"
echo ""
echo "4. Login with:"
echo "   Email: $ADMIN_EMAIL"
echo "   Password: (your password)"
echo ""
echo "================================================="
echo "  Developed by Xman Enterprise co.,ltd."
echo "  https://xman4289.com | (066) 080-6038278"
echo "================================================="
