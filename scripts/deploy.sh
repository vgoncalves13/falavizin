#!/bin/bash
# deploy.sh — Deploy de atualizações do Hub do Bairro
# Uso: ./scripts/deploy.sh

set -e

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_DIR"

echo "[$(date)] Iniciando deploy..."

# 1. Pull das mudanças
echo "[$(date)] Atualizando código..."
git pull origin master

# 2. Instalar dependências PHP
echo "[$(date)] Instalando dependências PHP..."
docker compose exec laravel.test composer install --no-dev --optimize-autoloader --no-interaction

# 3. Instalar e buildar frontend
echo "[$(date)] Buildando frontend..."
docker compose exec laravel.test npm ci
docker compose exec laravel.test npm run build

# 4. Rodar migrations
echo "[$(date)] Rodando migrations..."
docker compose exec laravel.test php artisan migrate --force

# 5. Limpar e recriar caches
echo "[$(date)] Atualizando caches..."
docker compose exec laravel.test php artisan config:cache
docker compose exec laravel.test php artisan route:cache
docker compose exec laravel.test php artisan view:cache

# 6. Reiniciar containers
echo "[$(date)] Reiniciando containers..."
docker compose restart

echo "[$(date)] ✅ Deploy concluído!"
