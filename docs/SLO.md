# SLOs e Observabilidade — Hub do Bairro

## Service Level Objectives (SLOs)

| Métrica | SLO | Como medir |
|---|---|---|
| Disponibilidade | 99.5% (≤3.6h downtime/mês) | Health check `/health` |
| Latência P95 | < 2s para páginas web | Logs de performance |
| Latência P99 | < 5s para páginas web | Logs de performance |
| Erros 5xx | < 1% das requests | Logs de erro |
| Jobs na fila | < 100 pendentes | Health check `/health` |
| Falhas de fila | < 5% dos jobs | `failed_jobs` table |

## Endpoints de Monitoramento

### Health Check
```
GET /health
```

Resposta (200 OK):
```json
{
  "status": "healthy",
  "timestamp": "2026-07-21T23:00:00+00:00",
  "checks": {
    "database": { "status": "ok" },
    "cache": { "status": "ok" },
    "queue": { "status": "ok", "pending_jobs": 5 }
  }
}
```

Resposta (503 Unhealthy):
```json
{
  "status": "unhealthy",
  "timestamp": "2026-07-21T23:00:00+00:00",
  "checks": {
    "database": { "status": "error", "message": "Connection refused" },
    "cache": { "status": "ok" },
    "queue": { "status": "ok", "pending_jobs": 0 }
  }
}
```

### Laravel Built-in
```
GET /up
```

## Logs Estruturados

### Canais de Log

| Canal | Arquivo | Retenção | Uso |
|---|---|---|---|
| `stack` | `laravel.log` | 14 dias | Geral |
| `daily` | `laravel.log` | 14 dias | Produção |
| `security` | `security.log` | 30 dias | Autenticação, autorização |
| `performance` | `performance.log` | 14 dias | Requests lentos (>1s) |
| `slack` | — | — | Erros críticos |

### Formato dos Logs

Todos os logs são estruturados em JSON para facilitar parsing:

```json
{
  "message": "Slow request",
  "context": {
    "method": "GET",
    "url": "http://localhost/feed",
    "duration_ms": 1523.45,
    "status": 200,
    "user_id": 42,
    "ip": "192.168.1.100",
    "user_agent": "Mozilla/5.0..."
  },
  "level": 300,
  "datetime": "2026-07-21T23:00:00+00:00",
  "channel": "performance"
}
```

## Alertas Recomendados

### Críticos (PagerDuty/Slack imediato)
- Health check retornando `unhealthy`
- Mais de 10 erros 5xx em 5 minutos
- Fila com mais de 500 jobs pendentes
- Database indisponível

### Warning (Slack/email)
- Request com mais de 5 segundos
- Mais de 50 jobs pendentes
- Disco com menos de 10% livre
- Memory usage acima de 80%

### Info (dashboard)
- Deploy realizado
- Backup concluído
- Jobs de enriquecimento executados

## Comandos Úteis

```bash
# Verificar saúde da aplicação
curl http://localhost/health

# Verificar jobs pendentes
vendor/bin/sail artisan queue:failed

# Monitorar logs em tempo real
vendor/bin/sail artisan pail

# Verificar tamanho dos logs
ls -lh storage/logs/

# Limpar logs antigos
find storage/logs -name "*.log" -mtime +30 -delete
```

## Métricas de Negócio

Disponíveis em `/admin/estatisticas`:

- Usuários, posts, negócios (total e semanais)
- Posts patrocinados ativos
- Visualizações e contatos de negócios
- Problemas abertos vs resolvidos
- Fila de moderação

## Runbook Rápido

### Site fora do ar
1. Verificar `/health` — qual check falhou?
2. Se database: verificar MySQL container
3. Se cache: verificar file permissions
4. Se queue: verificar worker ativo

### Fila travada
1. `vendor/bin/sail artisan queue:failed`
2. `vendor/bin/sail artisan queue:retry all`
3. Verificar logs de erro

### Request lento
1. Verificar `storage/logs/performance.log`
2. Identificar URL e duração
3. Verificar N+1 queries (Laravel Telescope)
