#!/bin/bash

################################################################################
# N8N Uninstall Script for Ubuntu
# Version: 1.0.0
# Description: Remove N8N and optionally clean up all data
################################################################################

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

N8N_USER="n8n"
N8N_HOME="/home/$N8N_USER"
BACKUP_DIR="/tmp/n8n-backup-$(date +%Y%m%d-%H%M%S)"

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

check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "This script must be run as root or with sudo"
        exit 1
    fi
}

create_backup() {
    print_header "Creating Backup"

    if [ -d "$N8N_HOME/.n8n" ]; then
        mkdir -p "$BACKUP_DIR"
        print_warning "Creating backup at: $BACKUP_DIR"

        tar -czf "$BACKUP_DIR/n8n-backup.tar.gz" -C "$N8N_HOME" .n8n 2>/dev/null || true

        if [ -f "$BACKUP_DIR/n8n-backup.tar.gz" ]; then
            print_success "Backup created successfully"
            echo "Backup location: $BACKUP_DIR/n8n-backup.tar.gz"
        else
            print_warning "Backup creation failed or no data to backup"
        fi
    else
        print_warning "No N8N data directory found to backup"
    fi
}

stop_service() {
    print_header "Stopping N8N Service"

    if systemctl is-active --quiet n8n; then
        systemctl stop n8n
        print_success "N8N service stopped"
    else
        print_warning "N8N service is not running"
    fi

    if systemctl is-enabled --quiet n8n 2>/dev/null; then
        systemctl disable n8n
        print_success "N8N service disabled"
    fi
}

remove_service() {
    print_header "Removing Systemd Service"

    if [ -f "/etc/systemd/system/n8n.service" ]; then
        rm -f /etc/systemd/system/n8n.service
        systemctl daemon-reload
        print_success "Service file removed"
    else
        print_warning "Service file not found"
    fi
}

remove_n8n() {
    print_header "Removing N8N"

    if command -v n8n &> /dev/null; then
        npm uninstall -g n8n
        print_success "N8N uninstalled"
    else
        print_warning "N8N is not installed"
    fi
}

remove_user_data() {
    print_header "Removing User and Data"

    print_warning "Do you want to remove N8N user and all data? (y/N)"
    read -r REMOVE_DATA

    if [[ $REMOVE_DATA =~ ^[Yy]$ ]]; then
        if id "$N8N_USER" &>/dev/null; then
            userdel -r "$N8N_USER" 2>/dev/null || rm -rf "$N8N_HOME"
            print_success "User and data removed"
        else
            print_warning "User $N8N_USER does not exist"
        fi
    else
        print_warning "Keeping user and data"
        echo "Data location: $N8N_HOME/.n8n"
    fi
}

remove_nginx() {
    print_header "Nginx Configuration"

    if [ -f "/etc/nginx/sites-available/n8n" ]; then
        print_warning "Remove Nginx configuration for N8N? (y/N)"
        read -r REMOVE_NGINX

        if [[ $REMOVE_NGINX =~ ^[Yy]$ ]]; then
            rm -f /etc/nginx/sites-available/n8n
            rm -f /etc/nginx/sites-enabled/n8n
            nginx -t && systemctl reload nginx
            print_success "Nginx configuration removed"
        fi
    fi
}

remove_firewall_rules() {
    print_header "Firewall Rules"

    if command -v ufw &> /dev/null; then
        print_warning "Remove N8N firewall rules? (y/N)"
        read -r REMOVE_FW

        if [[ $REMOVE_FW =~ ^[Yy]$ ]]; then
            ufw delete allow 5678/tcp 2>/dev/null || true
            print_success "Firewall rules removed"
        fi
    fi
}

main() {
    clear

    cat << "EOF"
███╗   ██╗ █████╗ ███╗   ██╗    ██╗   ██╗███╗   ██╗██╗███╗   ██╗███████╗████████╗ █████╗ ██╗     ██╗
████╗  ██║██╔══██╗████╗  ██║    ██║   ██║████╗  ██║██║████╗  ██║██╔════╝╚══██╔══╝██╔══██╗██║     ██║
██╔██╗ ██║╚█████╔╝██╔██╗ ██║    ██║   ██║██╔██╗ ██║██║██╔██╗ ██║███████╗   ██║   ███████║██║     ██║
██║╚██╗██║██╔══██╗██║╚██╗██║    ██║   ██║██║╚██╗██║██║██║╚██╗██║╚════██║   ██║   ██╔══██║██║     ██║
██║ ╚████║╚█████╔╝██║ ╚████║    ╚██████╔╝██║ ╚████║██║██║ ╚████║███████║   ██║   ██║  ██║███████╗███████╗
╚═╝  ╚═══╝ ╚════╝ ╚═╝  ╚═══╝     ╚═════╝ ╚═╝  ╚═══╝╚═╝╚═╝  ╚═══╝╚══════╝   ╚═╝   ╚═╝  ╚═╝╚══════╝╚══════╝

                        Uninstall Script

EOF

    print_warning "This will uninstall N8N from your system"
    echo "Press Ctrl+C to cancel, or Enter to continue..."
    read

    check_root

    # Uninstallation steps
    create_backup
    stop_service
    remove_service
    remove_n8n
    remove_nginx
    remove_firewall_rules
    remove_user_data

    print_header "Uninstallation Complete"

    cat << EOF

${GREEN}N8N has been uninstalled from your system.${NC}

${CYAN}Backup Information:${NC}
  Location: $BACKUP_DIR

${CYAN}What was removed:${NC}
  ✓ N8N application
  ✓ Systemd service

${YELLOW}Note:${NC}
  - If you kept the user data, it's still in: $N8N_HOME
  - To remove Node.js: sudo apt-get remove nodejs
  - Your backup is in: $BACKUP_DIR

Thank you for using N8N!

EOF
}

main
