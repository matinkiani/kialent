#!/bin/sh
set -eu

# Daily MariaDB backup for the kialent production stack.
# Dumps are written to BACKUP_DIR (root-only permissions), compressed,
# verified, and pruned after KEEP_DAYS days.
#
# Installed as /etc/cron.d/kialent-backup on the server.

BACKUP_DIR="${BACKUP_DIR:-/opt/kialent-backups}"
KEEP_DAYS=14
COMPOSE="docker compose -f /opt/kialent/compose.yml -f /opt/kialent/compose.prod.yml"

mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

STAMP=$(date +%Y-%m-%d_%H%M%S)
TARGET="$BACKUP_DIR/kialent-$STAMP.sql.gz"
LOG="$BACKUP_DIR/backup.log"

db_password() {
    $COMPOSE exec -T db printenv MARIADB_PASSWORD
}

pw=$(db_password)

if [ -z "$pw" ]; then
    echo "$(date '+%F %T') ERROR: could not read MARIADB_PASSWORD from db container" >> "$LOG"
    exit 1
fi

$COMPOSE exec -T db mariadb-dump \
    --single-transaction \
    --routines \
    --events \
    --no-tablespaces \
    -ukialent -p"$pw" kialent | gzip > "$TARGET"

chmod 600 "$TARGET"

if ! gzip -t "$TARGET"; then
    echo "$(date '+%F %T') ERROR: gzip check failed for $TARGET" >> "$LOG"
    rm -f "$TARGET"
    exit 1
fi

SIZE=$(stat -c %s "$TARGET")
MIN_SIZE=1024

if [ "$SIZE" -lt "$MIN_SIZE" ]; then
    echo "$(date '+%F %T') ERROR: $TARGET suspiciously small (${SIZE}B)" >> "$LOG"
    rm -f "$TARGET"
    exit 1
fi

find "$BACKUP_DIR" -name 'kialent-*.sql.gz' -mtime +"$KEEP_DAYS" -delete

echo "$(date '+%F %T') OK: $TARGET (${SIZE}B)" >> "$LOG"