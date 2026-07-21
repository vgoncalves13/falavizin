#!/bin/bash
# restore.sh — Restore completo do Hub do Bairro
# Uso: ./scripts/restore.sh <db-backup.sql.gz> <storage-backup.tar.gz>

set -e

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DB_BACKUP=$1
STORAGE_BACKUP=$2

if [ -z "$DB_BACKUP" ] || [ -z "$STORAGE_BACKUP" ]; then
  echo "Uso: restore.sh <db-backup.sql.gz> <storage-backup.tar.gz>"
  exit 1
fi

if [ ! -f "$DB_BACKUP" ]; then
  echo "❌ Arquivo de backup do banco não encontrado: $DB_BACKUP"
  exit 1
fi

if [ ! -f "$STORAGE_BACKUP" ]; then
  echo "❌ Arquivo de backup do storage não encontrado: $STORAGE_BACKUP"
  exit 1
fi

echo "⚠️  ATENÇÃO: Isso vai SUBSTITUIR banco E arquivos!"
echo "  - Banco: $DB_BACKUP"
echo "  - Storage: $STORAGE_BACKUP"
read -p "Continuar? (s/N): " confirm

if [ "$confirm" != "s" ]; then
  echo "Cancelado."
  exit 0
fi

cd "$PROJECT_DIR"

echo "[$(date)] Iniciando restore..."

# 1. Parar containers
echo "[$(date)] Parando containers..."
docker compose down

# 2. Subir apenas MySQL para restore
echo "[$(date)] Iniciando MySQL..."
docker compose up -d mysql
sleep 10

# 3. Restore do banco
echo "[$(date)] Restaurando banco de dados..."
gunzip -c "$DB_BACKUP" | docker compose exec -T mysql mysql \
  -u root \
  -p"${DB_PASSWORD:-password}" \
  "${DB_DATABASE:-hub_do_bairro}"

# 4. Restore do storage
echo "[$(date)] Restaurando storage..."
rm -rf storage/app/public/*
tar -xzf "$STORAGE_BACKUP" .

# 5. Subir todos os containers
echo "[$(date)] Iniciando todos os containers..."
docker compose up -d

# 6. Aguardar containers
sleep 5

# 7. Limpar cache
echo "[$(date)] Limpando cache..."
docker compose exec laravel.test php artisan cache:clear
docker compose exec laravel.test php artisan config:clear

# 8. Ajustar permissões
echo "[$(date)] Ajustando permissões..."
chmod -R 775 storage/app/public

echo "[$(date)] ✅ Restore concluído!"
echo "Verifique a aplicação em: $(grep APP_URL .env | cut -d= -f2)"
