# Runbook Operacional — Hub do Bairro

Procedimentos para deploy, worker, backup e restore em produção.

---

## Pré-requisitos de Produção

- VPS com Ubuntu 22.04+ (2 vCPU, 4GB RAM mínimo)
- Docker + Docker Compose
- Domínio apontando para o servidor
- Certificado SSL (Let's Encrypt)
- Acesso SSH configurado
- Conta SMTP para e-mails (Mailgun, SendGrid, etc.)
- RAPIDAPI_KEY válida (para importação Google Places)

---

## 1. Deploy Inicial

### 1.1 Preparar o servidor

```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Instalar Docker Compose
sudo apt install docker-compose-plugin -y

# Verificar instalação
docker --version
docker compose version
```

### 1.2 Clonar e configurar

```bash
# Clonar repositório
cd /var/www
git clone <repo-url> hub-do-bairro
cd hub-do-bairro

# Configurar ambiente
cp .env.example .env
```

### 1.3 Configurar .env para produção

```bash
# .env — ajustar para produção
APP_NAME="Hub do Bairro"
APP_ENV=production
APP_KEY=          # Gerar com: php artisan key:generate
APP_DEBUG=false
APP_URL=https://seu-dominio.com.br

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=hub_do_bairro
DB_USERNAME=sail
DB_PASSWORD=<senha-forte>

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_DOMAIN=seu-dominio.com.br

QUEUE_CONNECTION=database
CACHE_STORE=file
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.seu-dominio.com.br
MAIL_PASSWORD=<senha-mailgun>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@seu-dominio.com.br"
MAIL_FROM_NAME="${APP_NAME}"

RAPIDAPI_KEY=<sua-chave-rapidapi>
```

### 1.4 Subir containers

```bash
# Build e iniciar
docker compose -f compose.yaml up -d --build

# Gerar chave
docker compose exec laravel.test php artisan key:generate

# Rodar migrations
docker compose exec laravel.test php artisan migrate --force

# Seed de categorias (obrigatório)
docker compose exec laravel.test php artisan db:seed --force

# Cache de configuração
docker compose exec laravel.test php artisan config:cache
docker compose exec laravel.test php artisan route:cache
docker compose exec laravel.test php artisan view:cache

# Storage link
docker compose exec laravel.test php artisan storage:link
```

### 1.5 Configurar Nginx reverse proxy (fora do Docker)

```nginx
# /etc/nginx/sites-available/hub-do-bairro
server {
    listen 80;
    server_name seu-dominio.com.br;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seu-dominio.com.br;

    ssl_certificate /etc/letsencrypt/live/seu-dominio.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/seu-dominio.com.br/privkey.pem;

    location / {
        proxy_pass http://127.0.0.1:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        proxy_pass http://127.0.0.1:80;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
# Ativar site
sudo ln -s /etc/nginx/sites-available/hub-do-bairro /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# SSL com Let's Encrypt
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d seu-dominio.com.br
```

---

## 2. Deploy de Atualizações

```bash
# 1. Pull das mudanças
cd /var/www/hub-do-bairro
git pull origin master

# 2. Instalar dependências
docker compose exec laravel.test composer install --no-dev --optimize-autoloader
docker compose exec laravel.test npm ci
docker compose exec laravel.test npm run build

# 3. Rodar migrations
docker compose exec laravel.test php artisan migrate --force

# 4. Limpar e recriar caches
docker compose exec laravel.test php artisan config:cache
docker compose exec laravel.test php artisan route:cache
docker compose exec laravel.test php artisan view:cache

# 5. Reiniciar containers (se necessário)
docker compose restart
```

### Script de deploy automatizado

```bash
#!/bin/bash
# deploy.sh — executar na raiz do projeto

set -e

echo "🚀 Iniciando deploy..."

git pull origin master

docker compose exec laravel.test composer install --no-dev --optimize-autoloader
docker compose exec laravel.test npm ci
docker compose exec laravel.test npm run build

docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan config:cache
docker compose exec laravel.test php artisan route:cache
docker compose exec laravel.test php artisan view:cache

docker compose restart

echo "✅ Deploy concluído!"
```

---

## 3. Worker de Filas

### 3.1 Configurar worker

O projeto usa `database` como driver de fila. O worker processa jobs em background (e-mails, enriquecimento de negócios, etc.).

```bash
# Iniciar worker manualmente
docker compose exec laravel.test php artisan queue:work --tries=3 --timeout=60

# Ver status da fila
docker compose exec laravel.test php artisan queue:monitor
```

### 3.2 Worker com Supervisor (recomendado)

Criar `/etc/supervisor/conf.d/hub-do-bairro-worker.conf`:

```ini
[program:hub-do-bairro-worker]
process_name=%(program_name)s_%(process_num)02d
command=docker compose -f /var/www/hub-do-bairro/compose.yaml exec laravel.test php artisan queue:work --tries=3 --timeout=60 --sleep=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/hub-do-bairro-worker.log
stopwaitsecs=3600
```

```bash
# Atualizar Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start "hub-do-bairro-worker:*"

# Verificar status
sudo supervisorctl status "hub-do-bairro-worker:*"

# Logs
sudo tail -f /var/log/hub-do-bairro-worker.log
```

### 3.3 Jobs disponíveis

| Job | Descrição | Trigger |
|---|---|---|
| `EnrichBusinessFromGoogle` | Enriquece negócio com dados do Google Places | Após criação de negócio |

### 3.4 Jobs falhos

```bash
# Listar jobs falhos
docker compose exec laravel.test php artisan queue:failed

# Retry todos
docker compose exec laravel.test php artisan queue:retry all

# Retry específico
docker compose exec laravel.test php artisan queue:retry <id>

# Limpar jobs falhos
docker compose exec laravel.test php artisan queue:flush
```

---

## 4. Backup

### 4.1 Backup do banco de dados

```bash
#!/bin/bash
# backup-db.sh

BACKUP_DIR="/var/backups/hub-do-bairro"
DATE=$(date +%Y%m%d_%H%M%S)
CONTAINER="hub-do-bairro-mysql-1"

mkdir -p $BACKUP_DIR

# Dump do banco
docker compose exec mysql mysqldump \
  -u root \
  -p"$DB_PASSWORD" \
  --single-transaction \
  --routines \
  --triggers \
  hub_do_bairro > "$BACKUP_DIR/db_$DATE.sql"

# Comprimir
gzip "$BACKUP_DIR/db_$DATE.sql"

# Manter últimos 30 dias
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

echo "✅ Backup do banco: $BACKUP_DIR/db_$DATE.sql.gz"
```

### 4.2 Backup dos arquivos (storage)

```bash
#!/bin/bash
# backup-storage.sh

BACKUP_DIR="/var/backups/hub-do-bairro"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup do storage (uploads)
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" \
  -C /var/www/hub-do-bairro \
  storage/app/public

# Manter últimos 30 dias
find $BACKUP_DIR -name "storage_*.tar.gz" -mtime +30 -delete

echo "✅ Backup do storage: $BACKUP_DIR/storage_$DATE.tar.gz"
```

### 4.3 Backup completo automatizado

```bash
#!/bin/bash
# backup.sh — executar diariamente via cron

BACKUP_DIR="/var/backups/hub-do-bairro"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# 1. Backup do banco
docker compose -f /var/www/hub-do-bairro/compose.yaml exec mysql mysqldump \
  -u root \
  -p"password" \
  --single-transaction \
  --routines \
  --triggers \
  hub_do_bairro | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# 2. Backup do storage
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" \
  -C /var/www/hub-do-bairro \
  storage/app/public

# 3. Limpar backups antigos (manter 30 dias)
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "storage_*.tar.gz" -mtime +30 -delete

echo "✅ Backup completo: $BACKUP_DIR/*_$DATE.*"
```

### 4.4 Configurar cron

```bash
# Editar crontab
crontab -e

# Adicionar backup diário às 3h da manhã
0 3 * * * /var/www/hub-do-bairro/scripts/backup.sh >> /var/log/hub-do-bairro-backup.log 2>&1
```

---

## 5. Restore

### 5.1 Restore do banco de dados

```bash
#!/bin/bash
# restore-db.sh

BACKUP_FILE=$1

if [ -z "$BACKUP_FILE" ]; then
  echo "Uso: restore-db.sh <arquivo-backup.sql.gz>"
  exit 1
fi

echo "⚠️  ATENÇÃO: Isso vai SUBSTITUIR o banco de dados atual!"
read -p "Continuar? (s/N): " confirm

if [ "$confirm" != "s" ]; then
  echo "Cancelado."
  exit 0
fi

# Descomprimir e restaurar
gunzip -c "$BACKUP_FILE" | docker compose exec -T mysql mysql \
  -u root \
  -p"password" \
  hub_do_bairro

echo "✅ Banco restaurado de: $BACKUP_FILE"
```

### 5.2 Restore do storage

```bash
#!/bin/bash
# restore-storage.sh

BACKUP_FILE=$1

if [ -z "$BACKUP_FILE" ]; then
  echo "Uso: restore-storage.sh <arquivo-backup.tar.gz>"
  exit 1
fi

echo "⚠️  ATENÇÃO: Isso vai SUBSTITUIR os arquivos atuais!"
read -p "Continuar? (s/N): " confirm

if [ "$confirm" != "s" ]; then
  echo "Cancelado."
  exit 0
fi

# Limpar storage atual
rm -rf /var/www/hub-do-bairro/storage/app/public/*

# Extrair backup
tar -xzf "$BACKUP_FILE" -C /var/www/hub-do-bairro

# Ajustar permissões
chmod -R 775 /var/www/hub-do-bairro/storage/app/public

echo "✅ Storage restaurado de: $BACKUP_FILE"
```

### 5.3 Restore completo

```bash
#!/bin/bash
# restore.sh

DB_BACKUP=$1
STORAGE_BACKUP=$2

if [ -z "$DB_BACKUP" ] || [ -z "$STORAGE_BACKUP" ]; then
  echo "Uso: restore.sh <db-backup.sql.gz> <storage-backup.tar.gz>"
  exit 1
fi

echo "⚠️  ATENÇÃO: Isso vai SUBSTITUIR banco E arquivos!"
read -p "Continuar? (s/N): " confirm

if [ "$confirm" != "s" ]; then
  echo "Cancelado."
  exit 0
fi

# 1. Parar containers
docker compose down

# 2. Restore do banco
gunzip -c "$DB_BACKUP" | docker compose exec -T mysql mysql \
  -u root \
  -p"password" \
  hub_do_bairro

# 3. Restore do storage
rm -rf storage/app/public/*
tar -xzf "$STORAGE_BACKUP" .

# 4. Subir containers
docker compose up -d

# 5. Limpar cache
docker compose exec laravel.test php artisan cache:clear
docker compose exec laravel.test php artisan config:clear

# 6. Ajustar permissões
chmod -R 775 storage/app/public

echo "✅ Restore completo!"
```

---

## 6. Monitoramento

### 6.1 Verificar saúde da aplicação

```bash
# Status dos containers
docker compose ps

# Logs da aplicação
docker compose logs -f laravel.test

# Logs do MySQL
docker compose logs -f mysql

# Tamanho do banco
docker compose exec mysql mysql -u root -p"password" -e "
  SELECT
    table_name AS 'Tabela',
    ROUND(data_length / 1024 / 1024, 2) AS 'Dados (MB)',
    ROUND(index_length / 1024 / 1024, 2) AS 'Índices (MB)'
  FROM information_schema.tables
  WHERE table_schema = 'hub_do_bairro'
  ORDER BY data_length DESC;
"
```

### 6.2 Verificar filas

```bash
# Jobs pendentes
docker compose exec laravel.test php artisan queue:monitor

# Jobs falhos
docker compose exec laravel.test php artisan queue:failed

# Tamanho da fila
docker compose exec mysql mysql -u root -p"password" -e "
  SELECT COUNT(*) as 'Jobs Pendentes' FROM jobs;
  SELECT COUNT(*) as 'Jobs Falhos' FROM failed_jobs;
" hub_do_bairro
```

### 6.3 Verificar storage

```bash
# Espaço em disco
df -h /var/www/hub-do-bairro

# Tamanho do storage
du -sh /var/www/hub-do-bairro/storage/app/public

# Quantidade de uploads
ls -la /var/www/hub-do-bairro/storage/app/public/ | wc -l
```

---

## 7. Troubleshooting

### 7.1 Containers não sobem

```bash
# Verificar logs
docker compose logs

# Verificar portas em uso
sudo netstat -tlnp | grep :80
sudo netstat -tlnp | grep :3306

# Rebuild completo
docker compose down -v
docker compose up -d --build
```

### 7.2 Erro de permissão

```bash
# Ajustar permissões
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7.3 MySQL não conecta

```bash
# Verificar se MySQL está saudável
docker compose exec mysql mysqladmin ping -p"password"

# Reiniciar MySQL
docker compose restart mysql

# Verificar logs do MySQL
docker compose logs mysql
```

### 7.4 Jobs não processam

```bash
# Verificar se worker está rodando
ps aux | grep queue:work

# Verificar jobs pendentes
docker compose exec laravel.test php artisan queue:monitor

# Reiniciar worker
sudo supervisorctl restart "hub-do-bairro-worker:*"
```

### 7.5 Espaço em disco cheio

```bash
# Verificar uso
df -h

# Limpar logs antigos
sudo find /var/log -name "*.gz" -mtime +30 -delete

# Limpar backups antigos
sudo find /var/backups/hub-do-bairro -mtime +30 -delete

# Limpar Docker
docker system prune -a
```

---

## 8. Comandos Rápidos

```bash
# Status rápido
docker compose ps && docker compose exec laravel.test php artisan queue:monitor

# Logs em tempo real
docker compose logs -f

# Shell PHP
docker compose exec laravel.test php artisan tinker

# Restart completo
docker compose down && docker compose up -d

# Backup rápido
/var/www/hub-do-bairro/scripts/backup.sh
```

---

## 9. Contatos e Escalação

| Responsável | Função | Contato |
|---|---|---|
| [Nome] | DevOps / Infra | [email/telefone] |
| [Nome] | Desenvolvimento | [email/telefone] |
| [Nome] | Produto | [email/telefone] |

**Provedor de hospedagem:** [Nome do provedor]  
**Painel de controle:** [URL]  
**Documentação interna:** [URL]
