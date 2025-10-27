#!/bin/bash

################################################################################
# N8N Auto Installation Script for Ubuntu
# Version: 2.0.0
# Description: Complete automated installation of N8N workflow automation tool
# Supported: Ubuntu 20.04, 22.04, 24.04
# Author: Automated Installation Script
################################################################################

set -e  # Exit on error

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration variables
N8N_VERSION="latest"
NODE_VERSION="20"
N8N_PORT="5678"
N8N_USER="n8n"
N8N_HOME="/home/$N8N_USER"
N8N_DATA_DIR="$N8N_HOME/.n8n"
INSTALL_DIR="/opt/n8n"

################################################################################
# Utility Functions
################################################################################

print_header() {
    echo -e "\n${CYAN}============================================================${NC}"
    echo -e "${CYAN}$1${NC}"
    echo -e "${CYAN}============================================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

################################################################################
# Check Functions
################################################################################

check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "This script must be run as root or with sudo"
        exit 1
    fi
    print_success "Running with root privileges"
}

check_ubuntu() {
    if [[ ! -f /etc/lsb-release ]]; then
        print_error "This script is designed for Ubuntu only"
        exit 1
    fi

    source /etc/lsb-release
    print_success "Detected Ubuntu $DISTRIB_RELEASE ($DISTRIB_CODENAME)"

    # Check if supported version
    case $DISTRIB_RELEASE in
        20.04|22.04|24.04)
            print_success "Ubuntu version is supported"
            ;;
        *)
            print_warning "Ubuntu $DISTRIB_RELEASE may not be fully tested"
            ;;
    esac
}

check_system_requirements() {
    print_header "Checking System Requirements"

    # Check CPU
    CPU_CORES=$(nproc)
    print_info "CPU Cores: $CPU_CORES"

    # Check RAM
    TOTAL_RAM=$(free -m | awk '/^Mem:/{print $2}')
    print_info "Total RAM: ${TOTAL_RAM}MB"

    if [ $TOTAL_RAM -lt 1024 ]; then
        print_warning "Recommended RAM is at least 2GB. Current: ${TOTAL_RAM}MB"
    else
        print_success "RAM is sufficient"
    fi

    # Check disk space
    DISK_SPACE=$(df -BG / | awk 'NR==2 {print $4}' | sed 's/G//')
    print_info "Available disk space: ${DISK_SPACE}GB"

    if [ $DISK_SPACE -lt 5 ]; then
        print_warning "Recommended disk space is at least 10GB. Current: ${DISK_SPACE}GB"
    else
        print_success "Disk space is sufficient"
    fi
}

################################################################################
# Installation Functions
################################################################################

update_system() {
    print_header "Updating System Packages"

    export DEBIAN_FRONTEND=noninteractive

    print_info "Updating package lists..."
    apt-get update -qq

    print_info "Upgrading installed packages..."
    apt-get upgrade -y -qq

    print_info "Installing essential packages..."
    apt-get install -y -qq \
        curl \
        wget \
        git \
        build-essential \
        software-properties-common \
        apt-transport-https \
        ca-certificates \
        gnupg \
        lsb-release \
        ufw \
        nano \
        unzip

    print_success "System updated successfully"
}

install_nodejs() {
    print_header "Installing Node.js $NODE_VERSION"

    # Check if Node.js is already installed
    if command -v node &> /dev/null; then
        CURRENT_NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
        if [ "$CURRENT_NODE_VERSION" -ge "$NODE_VERSION" ]; then
            print_success "Node.js $CURRENT_NODE_VERSION is already installed"
            return
        else
            print_info "Updating Node.js from version $CURRENT_NODE_VERSION to $NODE_VERSION"
        fi
    fi

    # Install NodeSource repository
    print_info "Adding NodeSource repository..."
    curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -

    # Install Node.js
    print_info "Installing Node.js..."
    apt-get install -y -qq nodejs

    # Verify installation
    NODE_INSTALLED_VERSION=$(node -v)
    NPM_INSTALLED_VERSION=$(npm -v)

    print_success "Node.js $NODE_INSTALLED_VERSION installed"
    print_success "npm $NPM_INSTALLED_VERSION installed"
}

create_n8n_user() {
    print_header "Creating N8N System User"

    # Check if user already exists
    if id "$N8N_USER" &>/dev/null; then
        print_success "User $N8N_USER already exists"
    else
        print_info "Creating user $N8N_USER..."
        useradd -m -s /bin/bash $N8N_USER
        print_success "User $N8N_USER created"
    fi

    # Create data directory
    if [ ! -d "$N8N_DATA_DIR" ]; then
        mkdir -p "$N8N_DATA_DIR"
        chown -R $N8N_USER:$N8N_USER "$N8N_DATA_DIR"
        print_success "Data directory created: $N8N_DATA_DIR"
    fi
}

install_n8n() {
    print_header "Installing N8N"

    # Check if N8N is already installed
    if command -v n8n &> /dev/null; then
        print_info "N8N is already installed. Upgrading to latest version..."
        npm update -g n8n
    else
        print_info "Installing N8N globally..."
        npm install -g n8n
    fi

    # Verify installation
    if command -v n8n &> /dev/null; then
        N8N_VERSION_INSTALLED=$(n8n --version)
        print_success "N8N $N8N_VERSION_INSTALLED installed successfully"
    else
        print_error "N8N installation failed"
        exit 1
    fi
}

setup_environment() {
    print_header "Setting Up Environment Variables"

    # Create environment file
    ENV_FILE="$N8N_HOME/.n8n-env"

    cat > "$ENV_FILE" << EOF
# N8N Environment Configuration
N8N_HOST=0.0.0.0
N8N_PORT=$N8N_PORT
N8N_PROTOCOL=http
WEBHOOK_URL=http://$(hostname -I | awk '{print $1}'):$N8N_PORT/

# Data and paths
N8N_USER_FOLDER=$N8N_DATA_DIR
N8N_LOG_LEVEL=info
N8N_LOG_OUTPUT=console,file
N8N_LOG_FILE_LOCATION=$N8N_DATA_DIR/logs/

# Execution settings
EXECUTIONS_DATA_SAVE_ON_ERROR=all
EXECUTIONS_DATA_SAVE_ON_SUCCESS=all
EXECUTIONS_DATA_SAVE_MANUAL_EXECUTIONS=true
EXECUTIONS_PROCESS=main

# Performance
N8N_PAYLOAD_SIZE_MAX=16
N8N_METRICS=false

# Timezone
GENERIC_TIMEZONE=Asia/Bangkok

# Security (change these in production!)
N8N_BASIC_AUTH_ACTIVE=false
# N8N_BASIC_AUTH_USER=admin
# N8N_BASIC_AUTH_PASSWORD=change_me_in_production

# Database (default: SQLite)
DB_TYPE=sqlite
DB_SQLITE_VACUUM_ON_STARTUP=true
EOF

    chown $N8N_USER:$N8N_USER "$ENV_FILE"
    chmod 600 "$ENV_FILE"

    print_success "Environment file created: $ENV_FILE"
}

setup_systemd_service() {
    print_header "Setting Up Systemd Service"

    SERVICE_FILE="/etc/systemd/system/n8n.service"

    cat > "$SERVICE_FILE" << EOF
[Unit]
Description=N8N - Workflow Automation Tool
Documentation=https://docs.n8n.io
After=network.target

[Service]
Type=simple
User=$N8N_USER
EnvironmentFile=$N8N_HOME/.n8n-env
ExecStart=$(which n8n) start
Restart=on-failure
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=n8n

# Security settings
NoNewPrivileges=true
PrivateTmp=true
ProtectSystem=strict
ProtectHome=read-only
ReadWritePaths=$N8N_DATA_DIR

# Resource limits
LimitNOFILE=65536
LimitNPROC=4096

[Install]
WantedBy=multi-user.target
EOF

    print_success "Systemd service file created"

    # Create logs directory
    mkdir -p "$N8N_DATA_DIR/logs"
    chown -R $N8N_USER:$N8N_USER "$N8N_DATA_DIR"

    # Reload systemd
    systemctl daemon-reload
    print_success "Systemd daemon reloaded"
}

configure_firewall() {
    print_header "Configuring Firewall (UFW)"

    # Check if UFW is installed
    if ! command -v ufw &> /dev/null; then
        print_warning "UFW is not installed. Skipping firewall configuration"
        return
    fi

    print_info "Would you like to configure UFW firewall? (y/n)"
    read -t 10 -r CONFIGURE_FW || CONFIGURE_FW="n"

    if [[ $CONFIGURE_FW =~ ^[Yy]$ ]]; then
        # Enable UFW if not already enabled
        if ! ufw status | grep -q "Status: active"; then
            print_info "Enabling UFW..."
            ufw --force enable
        fi

        # Allow SSH
        ufw allow 22/tcp comment 'SSH'
        print_success "SSH port allowed"

        # Allow N8N port
        ufw allow $N8N_PORT/tcp comment 'N8N'
        print_success "N8N port $N8N_PORT allowed"

        # Show status
        ufw status numbered
        print_success "Firewall configured"
    else
        print_info "Skipping firewall configuration"
    fi
}

setup_nginx_proxy() {
    print_header "Nginx Reverse Proxy Setup (Optional)"

    print_info "Would you like to install Nginx as a reverse proxy? (y/n)"
    read -t 10 -r INSTALL_NGINX || INSTALL_NGINX="n"

    if [[ ! $INSTALL_NGINX =~ ^[Yy]$ ]]; then
        print_info "Skipping Nginx installation"
        return
    fi

    # Install Nginx
    print_info "Installing Nginx..."
    apt-get install -y -qq nginx

    # Create Nginx configuration
    NGINX_CONF="/etc/nginx/sites-available/n8n"

    cat > "$NGINX_CONF" << EOF
server {
    listen 80;
    server_name _;

    location / {
        proxy_pass http://localhost:$N8N_PORT;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_cache_bypass \$http_upgrade;

        # Increase timeouts
        proxy_connect_timeout 600;
        proxy_send_timeout 600;
        proxy_read_timeout 600;
        send_timeout 600;
    }
}
EOF

    # Enable site
    ln -sf "$NGINX_CONF" /etc/nginx/sites-enabled/n8n
    rm -f /etc/nginx/sites-enabled/default

    # Test and reload Nginx
    nginx -t && systemctl restart nginx
    systemctl enable nginx

    print_success "Nginx reverse proxy configured"
    print_info "N8N will be accessible on port 80"
}

start_n8n_service() {
    print_header "Starting N8N Service"

    # Enable service to start on boot
    systemctl enable n8n
    print_success "N8N service enabled for auto-start"

    # Start the service
    print_info "Starting N8N..."
    systemctl start n8n

    # Wait for service to start
    sleep 5

    # Check service status
    if systemctl is-active --quiet n8n; then
        print_success "N8N service is running"
    else
        print_error "N8N service failed to start"
        print_info "Checking logs..."
        journalctl -u n8n -n 50 --no-pager
        exit 1
    fi
}

show_installation_summary() {
    print_header "Installation Complete!"

    SERVER_IP=$(hostname -I | awk '{print $1}')

    cat << EOF

${GREEN}╔════════════════════════════════════════════════════════════════╗
║                  N8N Installation Summary                      ║
╚════════════════════════════════════════════════════════════════╝${NC}

${CYAN}Access Information:${NC}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  🌐 Web Interface:  http://$SERVER_IP:$N8N_PORT
  📁 Data Directory: $N8N_DATA_DIR
  👤 User:           $N8N_USER
  🔧 Service:        n8n.service

${CYAN}Service Management:${NC}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Start:    sudo systemctl start n8n
  Stop:     sudo systemctl stop n8n
  Restart:  sudo systemctl restart n8n
  Status:   sudo systemctl status n8n
  Logs:     sudo journalctl -u n8n -f

${CYAN}Configuration:${NC}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Environment: $N8N_HOME/.n8n-env
  Edit:        sudo nano $N8N_HOME/.n8n-env
  After edit:  sudo systemctl restart n8n

${CYAN}Installed Versions:${NC}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  Node.js:     $(node -v)
  npm:         $(npm -v)
  N8N:         $(n8n --version)

${CYAN}Next Steps:${NC}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  1. Open http://$SERVER_IP:$N8N_PORT in your browser
  2. Create your first workflow
  3. Configure authentication in $N8N_HOME/.n8n-env
  4. Set up SSL/HTTPS (recommended for production)

${YELLOW}⚠️  Security Recommendations:${NC}
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  • Enable basic authentication
  • Set up SSL/TLS certificate
  • Configure firewall rules
  • Regular backups of $N8N_DATA_DIR
  • Keep N8N updated: sudo npm update -g n8n

${GREEN}Thank you for installing N8N! 🚀${NC}

EOF
}

################################################################################
# Backup and Uninstall Functions
################################################################################

create_backup() {
    if [ -d "$N8N_DATA_DIR" ]; then
        BACKUP_FILE="/tmp/n8n-backup-$(date +%Y%m%d-%H%M%S).tar.gz"
        print_info "Creating backup: $BACKUP_FILE"
        tar -czf "$BACKUP_FILE" -C "$N8N_HOME" .n8n
        print_success "Backup created: $BACKUP_FILE"
    fi
}

################################################################################
# Main Installation Process
################################################################################

main() {
    clear

    cat << "EOF"
███╗   ██╗ █████╗ ███╗   ██╗    ██╗███╗   ██╗███████╗████████╗ █████╗ ██╗     ██╗
████╗  ██║██╔══██╗████╗  ██║    ██║████╗  ██║██╔════╝╚══██╔══╝██╔══██╗██║     ██║
██╔██╗ ██║╚█████╔╝██╔██╗ ██║    ██║██╔██╗ ██║███████╗   ██║   ███████║██║     ██║
██║╚██╗██║██╔══██╗██║╚██╗██║    ██║██║╚██╗██║╚════██║   ██║   ██╔══██║██║     ██║
██║ ╚████║╚█████╔╝██║ ╚████║    ██║██║ ╚████║███████║   ██║   ██║  ██║███████╗███████╗
╚═╝  ╚═══╝ ╚════╝ ╚═╝  ╚═══╝    ╚═╝╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚══════╝╚══════╝

        Automated Installation Script for Ubuntu
        Version 2.0.0

EOF

    print_info "Starting N8N installation process..."
    sleep 2

    # Pre-installation checks
    check_root
    check_ubuntu
    check_system_requirements

    # Installation steps
    update_system
    install_nodejs
    create_n8n_user
    install_n8n
    setup_environment
    setup_systemd_service
    configure_firewall
    setup_nginx_proxy
    start_n8n_service

    # Show summary
    show_installation_summary

    print_success "Installation completed successfully!"
    exit 0
}

################################################################################
# Script Entry Point
################################################################################

# Handle script arguments
case "${1:-install}" in
    install)
        main
        ;;
    backup)
        create_backup
        ;;
    *)
        echo "Usage: $0 {install|backup}"
        exit 1
        ;;
esac
