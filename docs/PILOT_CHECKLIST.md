# Checklist do Piloto — Hub do Bairro

**Bairro piloto:** Jardim América  
**Objetivo:** 2 semanas sem P0, 10–30 usuários e 10+ negócios ativos  
**Status:** Em preparação

---

## 1. Pré-lançamento (antes de ir ao ar)

### 1.1 Infraestrutura
- [ ] VPS provisionada (Ubuntu 22.04+, 2 vCPU, 4GB RAM)
- [ ] Domínio configurado (ex: `hubdobairro.com.br`)
- [ ] SSL/Let's Encrypt instalado
- [ ] Docker + Docker Compose funcionando
- [ ] Deploy executado com sucesso (`scripts/deploy.sh`)
- [ ] Worker de filas rodando (Supervisor configurado)
- [ ] Backup diário configurado (cron `scripts/backup.sh`)
- [ ] SMTP configurado (Mailgun/SendGrid) — emails enviando
- [ ] RAPIDAPI_KEY configurada (importação Google Places)

### 1.2 Configuração da aplicação
- [ ] `.env` de produção configurado (ver `RUNBOOK.md` seção 1.3)
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` correto
- [ ] Bairro configurado em Settings (`neighborhood_name`, `neighborhood_lat`, `neighborhood_lng`)
- [ ] Categorias seedadas (`php artisan db:seed`)
- [ ] Usuário admin criado
- [ ] Cache de configuração, rotas e views limpo

### 1.3 Conteúdo inicial
- [ ] 3–5 posts de exemplo (avisos, eventos, problemas)
- [ ] 5–10 negócios reais cadastrados (importar via Google Places ou manual)
- [ ] 2–3 promoções de exemplo
- [ ] Fotos de capa para negócios (mínimo 5)

### 1.4 Legal e conformidade
- [ ] Termos de uso publicados
- [ ] Política de privacidade publicada
- [ ] LGPD: controlador definido, dados mínimos coletados
- [ ] Email de contato para dúvidas/reclamações

---

## 2. Lançamento (dia 1)

### 2.1 Equipe
- [ ] Moderador(es) definido(s) e treinados
- [ ] Canal de suporte criado (email ou formulário)
- [ ] Contatos de escalação preenchidos no `RUNBOOK.md`

### 2.2 Validação final
- [ ] Testes passando (`php artisan test --compact`)
- [ ] Build frontend OK (`npm run build`)
- [ ] Auditorias zeradas (`composer audit`, `npm audit`)
- [ ] Smoke test manual: criar conta, publicar post, cadastrar negócio
- [ ] Email de boas-vindas enviando

### 2.3 Go-live
- [ ] Aplicação acessível publicamente
- [ ] Primeiros convidados adicionados (5–10 vizinhos)
- [ ] Monitoramento ativo (logs, fila, erros)

---

## 3. Primeiras 2 semanas

### 3.1 Métricas diárias
| Métrica | Meta | Como medir |
|---|---|---|
| Novos usuários | 2–5/dia | Dashboard admin → Estatísticas |
| Posts publicados | 1–3/dia | Dashboard admin → Estatísticas |
| Negócios cadastrados | 1–2/dia | Dashboard admin → Estatísticas |
| Posts aprovados vs rejeitados | >80% aprovação | Dashboard moderação |
| Tempo de moderação | <24h | Logs de moderação |
| Erros P0 | 0 | Logs do Laravel |

### 3.2 Métricas semanais
| Métrica | Meta | Como medir |
|---|---|---|
| Usuários ativos (semana) | 10–30 | Dashboard admin |
| Negócios ativos | 10+ | Dashboard admin |
| Comentários por post | 1–3 | Query manual ou dashboard |
| Posts resolvidos (problemas) | >50% | Dashboard Pulso |
| Reports/resolução | <48h | Logs de moderação |

### 3.3 Checklist diário (operador)
- [ ] Verificar fila de moderação (pendentes e reportados)
- [ ] Verificar logs de erro (`storage/logs/laravel.log`)
- [ ] Verificar espaço em disco (`df -h`)
- [ ] Verificar se worker está rodando (`supervisorctl status`)
- [ ] Responder reports/reclamações pendentes

### 3.4 Checklist semanal (operador)
- [ ] Revisar métricas no dashboard de estatísticas
- [ ] Verificar backups (último backup existe e tamanho OK)
- [ ] Testar restore em ambiente de staging (se disponível)
- [ ] Revisar feedback dos usuários
- [ ] Atualizar conteúdo de exemplo se necessário

---

## 4. Critérios de sucesso do piloto

| Critério | Meta | Prazo |
|---|---|---|
| Usuários cadastrados | 10–30 | 2 semanas |
| Negócios ativos | 10+ | 2 semanas |
| Posts publicados | 30+ | 2 semanas |
| Erros P0 | 0 | 2 semanas |
| Uptime | >99% | 2 semanas |
| Tempo de moderação | <24h | Contínuo |
| Feedback positivo | >70% | Pesquisa final |

---

## 5. Critérios de abort/rollback

Abortar o piloto se:
- Erro P0 que afeta >50% dos usuários por >4h
- Perda de dados confirmada
- Vulnerabilidade de segurança explorada
- Violação de LGPD identificada

Ação de rollback:
1. `docker compose down`
2. Restore do último backup (`scripts/restore.sh`)
3. Comunicar usuários por email
4. Investigar causa raiz antes de reabrir

---

## 6. Pós-piloto (após 2 semanas)

### 6.1 Relatório de resultados
- [ ] Métricas coletadas e comparadas com metas
- [ ] Feedback dos usuários compilado
- [ ] Issues/bugs priorizados
- [ ] Decisão: continuar, pivotar ou encerrar

### 6.2 Próximos passos (se continuar)
- [ ] Implementar melhorias baseadas em feedback
- [ ] Expandir para mais bairros (B038)
- [ ] Adicionar funcionalidades do roadmap (B030+)
- [ ] Configurar analytics mais detalhados (B035)

---

## 7. Contatos de escalação

| Função | Nome | Contato |
|---|---|---|
| Operador/Técnico | _______ | _______ |
| Moderador | _______ | _______ |
| Produto | _______ | _______ |
| Provedor VPS | _______ | _______ |
| Provedor SMTP | _______ | _______ |

---

## 8. Links úteis

| Recurso | URL |
|---|---|
| Aplicação | `https://seu-dominio.com.br` |
| Dashboard admin | `https://seu-dominio.com.br/admin/estatisticas` |
| Moderação | `https://seu-dominio.com.br/admin/moderacao` |
| Mailpit (dev) | N/A em produção |
| Logs | `ssh:~$ tail -f /var/www/hub-do-bairro/storage/logs/laravel.log` |
| Backups | `/var/backups/hub-do-bairro/` |
