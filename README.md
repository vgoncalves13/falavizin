# Hub do Bairro

Portal comunitário hiperlocal para moradores e comerciantes do bairro.

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12 · PHP 8.5 |
| Frontend | Livewire 4 · Alpine.js · TailwindCSS 4 |
| Banco | MySQL 8.4 |
| Fila | Database (jobs table) |
| Cache | File |
| Storage | Public disk |
| CI | GitHub Actions |

## Requisitos

- Docker + Docker Compose
- Node.js 22+
- Composer

## Setup Local

```bash
# 1. Clonar e instalar dependências
git clone <repo-url> hub-do-bairro
cd hub-do-bairro
composer install
npm install

# 2. Configurar ambiente
cp .env.example .env
# Editar .env se necessário (padrão já funciona com Sail)

# 3. Subir containers
vendor/bin/sail up -d

# 4. Gerar chave e rodar migrations
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate --seed

# 5. Build frontend
npm run build

# 6. Acessar
vendor/bin/sail open
```

### Setup com Sail (recomendado)

```bash
# Atalho completo (instala, configura, migra, builda)
vendor/bin/sail composer setup

# Iniciar desenvolvimento (server + queue + logs + vite)
vendor/bin/sail composer dev
```

## Comandos Úteis

```bash
# Artisan
vendor/bin/sail artisan migrate
vendor/bin/sail artisan migrate:fresh --seed
vendor/bin/sail artisan test --compact
vendor/bin/sail artisan queue:work

# Importar negócios do Google Places
vendor/bin/sail artisan businesses:import-google \
  --neighborhood="Copacabana" \
  --lat=-22.9711 \
  --lng=-43.1822 \
  --radius=1000 \
  --limit=20

# Pint (formatação)
vendor/bin/sail bin pint --dirty --format agent

# Cache
vendor/bin/sail artisan cache:clear
vendor/bin/sail artisan config:clear
vendor/bin/sail artisan view:clear
```

## Estrutura do Projeto

```
app/
├── Actions/          # Lógica de negócio
├── Console/Commands/ # Comandos artisan
├── Enums/            # Enums PHP (PostStatus, BusinessPlan, etc.)
├── Http/
│   ├── Controllers/  # Controllers finos
│   ├── Middleware/    # Middleware customizado
│   └── Requests/     # Form Requests
├── Livewire/         # Componentes Livewire
├── Models/           # Eloquent Models
├── Policies/         # Autorização
└── Services/         # Serviços externos

resources/views/
├── layouts/          # Layouts Blade
├── livewire/         # Views Livewire
├── components/       # Blade components
├── feed/             # Views do feed
├── businesses/       # Views de negócios
└── admin/            # Views admin

database/
├── factories/        # Model factories
├── migrations/       # Migrations
└── seeders/          # Seeders

tests/
├── Feature/          # Feature tests
└── Unit/             # Unit tests
```

## Testes

```bash
# Rodar todos os testes
vendor/bin/sail artisan test --compact

# Rodar teste específico
vendor/bin/sail artisan test --filter=testName

# Rodar testes de um arquivo
vendor/bin/sail artisan test --compact tests/Feature/ExampleTest.php
```

## CI/CD

O GitHub Actions roda automaticamente em push/PR para `master`:

- PHP formatting (Pint)
- Testes (PHPUnit)
- Build frontend (Vite)
- Auditorias de segurança (Composer + npm)

## Produção

Consulte o [Runbook](docs/RUNBOOK.md) para procedimentos de deploy, backup e restore.

## Documentação

- [Status do Projeto](docs/PROJECT_STATUS.md)
- [Runbook Operacional](docs/RUNBOOK.md)
- [Análises](docs/analysis/)
