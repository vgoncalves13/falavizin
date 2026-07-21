#!/bin/bash
# backup.sh — Backup completo do Hub do Bairro
# Uso: ./scripts/backup.sh
# Executar via cron: 0 3 * * * /var/www/hub-do-bairro/scripts/backup.sh >> /var/log/hub-do-bairro-backup.log 2>&1

set -e

BACKUP_DIR="/var/backups/hub-do-bairro"
DATE=$(date +%Y%m%d_%H%M%S)
PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"

mkdir -p "$BACKUP_DIR"

echo "[$(date)] Iniciando backup..."

# 1. Backup do banco de dados
echo "[$(date)] Backup do banco de dados..."
docker compose -f "$PROJECT_DIR/compose.yaml" exec -T mysql mysqldump \
  -u root \
  -p"${DB_PASSWORD:-password}" \
  --single-transaction \
  --routines \
  --triggers \
  "${DB_DATABASE:-hub_do_bairro}" | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# 2. Backup do storage (uploads)
echo "[$(date)] Backup do storage..."
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" \
  -C "$PROJECT_DIR" \
  storage/app/public

# 3. Limpar backups antigos (manter 30 dias)
echo "[$(date)] Limpando backups antigos..."
find "$BACKUP_DIR" -name "db_*.sql.gz" -mtime +30 -delete
find "$BACKUP_DIR" -name "storage_*.tar.gz" -mtime +30 -delete

# 4. Verificar backups
DB_SIZE=$(du -h "$BACKUP_DIR/db_$DATE.sql.gz" | cut -f1)
STORAGE_SIZE=$(du -h "$BACKUP_DIR/storage_$DATE.tar.gz" | cut -f1)

echo "[$(date)] ✅ Backup concluído!"
echo "  - Banco: $BACKUP_DIR/db_$DATE.sql.gz ($DB_SIZE)"
echo "  - Storage: $BACKUP_DIR/storage_$DATE.tar.gz ($STORAGE_SIZE)"
