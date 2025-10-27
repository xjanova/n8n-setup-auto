#!/bin/bash

################################################################################
# N8N Backup Script
# Version: 1.0.0
# Description: Backup N8N data and configuration
################################################################################

set -e

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuration
N8N_USER="n8n"
N8N_HOME="/home/$N8N_USER"
N8N_DATA_DIR="$N8N_HOME/.n8n"
BACKUP_DIR="${BACKUP_DIR:-/backup/n8n}"
RETENTION_DAYS="${RETENTION_DAYS:-7}"
DATE=$(date +%Y%m%d-%H%M%S)
BACKUP_FILE="$BACKUP_DIR/n8n-backup-$DATE.tar.gz"

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
    echo -e "${CYAN}ℹ $1${NC}"
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "This script must be run as root or with sudo"
        exit 1
    fi
}

check_n8n_exists() {
    if [ ! -d "$N8N_DATA_DIR" ]; then
        print_error "N8N data directory not found: $N8N_DATA_DIR"
        exit 1
    fi
}

create_backup_dir() {
    if [ ! -d "$BACKUP_DIR" ]; then
        mkdir -p "$BACKUP_DIR"
        print_success "Created backup directory: $BACKUP_DIR"
    fi
}

get_data_size() {
    SIZE=$(du -sh "$N8N_DATA_DIR" 2>/dev/null | cut -f1)
    echo "$SIZE"
}

backup_data() {
    print_header "Creating Backup"

    DATA_SIZE=$(get_data_size)
    print_info "N8N data size: $DATA_SIZE"
    print_info "Backup location: $BACKUP_FILE"

    # Create backup
    tar -czf "$BACKUP_FILE" -C "$N8N_HOME" .n8n 2>/dev/null

    if [ -f "$BACKUP_FILE" ]; then
        BACKUP_SIZE=$(du -sh "$BACKUP_FILE" | cut -f1)
        print_success "Backup created successfully"
        print_info "Backup size: $BACKUP_SIZE"
    else
        print_error "Backup creation failed"
        exit 1
    fi
}

verify_backup() {
    print_header "Verifying Backup"

    if tar -tzf "$BACKUP_FILE" >/dev/null 2>&1; then
        print_success "Backup file is valid"
    else
        print_error "Backup file is corrupted"
        exit 1
    fi
}

cleanup_old_backups() {
    print_header "Cleaning Up Old Backups"

    print_info "Retention period: $RETENTION_DAYS days"

    OLD_BACKUPS=$(find "$BACKUP_DIR" -name "n8n-backup-*.tar.gz" -mtime +$RETENTION_DAYS 2>/dev/null)

    if [ -n "$OLD_BACKUPS" ]; then
        echo "$OLD_BACKUPS" | while read -r file; do
            rm -f "$file"
            print_success "Deleted: $(basename $file)"
        done
    else
        print_info "No old backups to delete"
    fi
}

list_backups() {
    print_header "Available Backups"

    if [ -d "$BACKUP_DIR" ]; then
        BACKUPS=$(find "$BACKUP_DIR" -name "n8n-backup-*.tar.gz" -type f 2>/dev/null | sort -r)

        if [ -n "$BACKUPS" ]; then
            echo "$BACKUPS" | while read -r file; do
                SIZE=$(du -sh "$file" | cut -f1)
                DATE=$(stat -c %y "$file" | cut -d' ' -f1,2 | cut -d'.' -f1)
                echo "  📦 $(basename $file) - $SIZE - $DATE"
            done
        else
            print_warning "No backups found"
        fi
    else
        print_warning "Backup directory does not exist"
    fi
}

show_summary() {
    print_header "Backup Summary"

    cat << EOF
${GREEN}Backup completed successfully!${NC}

${CYAN}Backup Information:${NC}
  📁 Location:    $BACKUP_FILE
  📊 Size:        $(du -sh "$BACKUP_FILE" | cut -f1)
  📅 Date:        $(date)
  🔄 Retention:   $RETENTION_DAYS days

${CYAN}Restore Command:${NC}
  sudo systemctl stop n8n
  sudo tar -xzf $BACKUP_FILE -C $N8N_HOME/
  sudo chown -R n8n:n8n $N8N_DATA_DIR
  sudo systemctl start n8n

${CYAN}All Backups:${NC}
EOF

    find "$BACKUP_DIR" -name "n8n-backup-*.tar.gz" -type f 2>/dev/null | sort -r | head -5 | while read -r file; do
        SIZE=$(du -sh "$file" | cut -f1)
        echo "  • $(basename $file) - $SIZE"
    done
}

main_backup() {
    print_header "N8N Backup Script"

    check_root
    check_n8n_exists
    create_backup_dir
    backup_data
    verify_backup
    cleanup_old_backups
    show_summary
}

main_list() {
    print_header "N8N Backup List"
    list_backups
}

main_restore() {
    print_header "N8N Restore"

    if [ -z "$1" ]; then
        print_error "Please specify backup file to restore"
        echo "Usage: $0 restore <backup-file>"
        list_backups
        exit 1
    fi

    RESTORE_FILE="$1"

    if [ ! -f "$RESTORE_FILE" ]; then
        print_error "Backup file not found: $RESTORE_FILE"
        exit 1
    fi

    print_warning "This will restore N8N data from: $RESTORE_FILE"
    print_warning "Current data will be overwritten!"
    echo "Press Ctrl+C to cancel, or Enter to continue..."
    read

    # Stop service
    print_info "Stopping N8N service..."
    systemctl stop n8n

    # Backup current data
    print_info "Backing up current data..."
    tar -czf "$BACKUP_DIR/n8n-before-restore-$(date +%Y%m%d-%H%M%S).tar.gz" -C "$N8N_HOME" .n8n 2>/dev/null || true

    # Restore
    print_info "Restoring data..."
    tar -xzf "$RESTORE_FILE" -C "$N8N_HOME/"

    # Fix permissions
    print_info "Fixing permissions..."
    chown -R n8n:n8n "$N8N_DATA_DIR"

    # Start service
    print_info "Starting N8N service..."
    systemctl start n8n

    sleep 3

    if systemctl is-active --quiet n8n; then
        print_success "Restore completed successfully!"
    else
        print_error "N8N service failed to start"
        print_info "Check logs: sudo journalctl -u n8n -n 50"
        exit 1
    fi
}

# Main script
case "${1:-backup}" in
    backup)
        main_backup
        ;;
    list)
        main_list
        ;;
    restore)
        check_root
        main_restore "$2"
        ;;
    *)
        echo "Usage: $0 {backup|list|restore <file>}"
        echo ""
        echo "Commands:"
        echo "  backup         Create a new backup (default)"
        echo "  list           List all available backups"
        echo "  restore <file> Restore from a backup file"
        echo ""
        echo "Examples:"
        echo "  $0 backup"
        echo "  $0 list"
        echo "  $0 restore /backup/n8n/n8n-backup-20240101-120000.tar.gz"
        exit 1
        ;;
esac
