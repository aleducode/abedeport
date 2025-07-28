#!/bin/bash

# ABE Deportes Database Backup Script
# This script creates compressed backups of the MySQL database

set -e

# Configuration
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="abedeport_backup_${DATE}.sql.gz"
MAX_BACKUPS=7  # Keep last 7 backups

# Create backup directory if it doesn't exist
mkdir -p ${BACKUP_DIR}

echo "Starting database backup at $(date)"

# Create database backup with compression
mysqldump -h${DB_HOST} -u${DB_USER} -p${DB_PASSWORD} \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-database \
    --databases ${DB_NAME} | gzip > ${BACKUP_DIR}/${BACKUP_FILE}

# Verify backup was created
if [ -f "${BACKUP_DIR}/${BACKUP_FILE}" ]; then
    BACKUP_SIZE=$(ls -lh ${BACKUP_DIR}/${BACKUP_FILE} | awk '{print $5}')
    echo "Backup completed successfully: ${BACKUP_FILE} (${BACKUP_SIZE})"
else
    echo "ERROR: Backup failed!"
    exit 1
fi

# Remove old backups (keep only the last MAX_BACKUPS)
cd ${BACKUP_DIR}
ls -t abedeport_backup_*.sql.gz | tail -n +$((MAX_BACKUPS + 1)) | xargs -r rm -f

echo "Cleanup completed. Kept last ${MAX_BACKUPS} backups."

# List current backups
echo "Current backups:"
ls -lh ${BACKUP_DIR}/abedeport_backup_*.sql.gz

echo "Backup process completed at $(date)"