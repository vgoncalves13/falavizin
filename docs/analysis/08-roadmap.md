# Roadmap recomendado

**Escala:** XS = poucas horas; S = até 1 dia; M = 2–4 dias; L = ~1 semana; XL = mais de uma semana/deve ser quebrado. Prioridades P0 a P3.

## Fase 0 — Diagnóstico e estabilização

| Item | Objetivo e escopo | Dependências | Pri. / esforço | Riscos | Critério de aceite | Resultado esperado |
|---|---|---|---|---|---|---|
| ✅ Rotacionar credencial RapidAPI — concluído em 20/07/2026 | Revogar a credencial exposta na sessão, criar outra com menor privilégio/quota e verificar secrets | Acesso à conta RapidAPI | P0 / XS | Interromper importação temporariamente | Chave antiga inválida; nova não aparece em logs/config output; chamada controlada passa | Incidente contido |
| ✅ Corrigir vulnerabilidades conhecidas — concluído em 20/07/2026 | Atualizar locks dentro de versões compatíveis; revisar Laravel/Symfony/Guzzle/Axios/Vite/transitivas | Backup/branch e suíte atual verde | P0 / M | Regressões de patch | Audits zerados; suíte e build passam | Base atualizável e segura |
| ✅ Fechar exposição pública e XSS — concluído nas B003/B004 | Restringir status e construir popups sem HTML inseguro | Testes de status e payload malicioso | P0 / S | Bloquear autor/admin indevidamente | Popup é texto inerte; visitante é bloqueado em não aprovados | Conteúdo moderado protegido |
| ✅ Corrigir escopo Livewire — concluído na B005 | Reautorizar BusinessForm/Promotion e buscar review/comment/poll option pela relação pai | Policies existentes | P0 / M | Quebrar interações legítimas | Seis regressões negativas e suíte completa passam | Integridade/autorização restauradas |
| ✅ Estabilizar horários — concluído na B007 | Persistir formato único, corrigir seed/dados e períodos noturnos | Schema JSON atual | P0 / M | Saneamento incorreto | Migration aplicada; CRUD e horários noturnos testados | Catálogo confiável |
| ✅ Substituir claim por aprovação manual — concluído na B006 | Pedido pendente, exclusão mútua, rate limit, decisão admin e notificação | Decisão operacional do responsável | P0 / L | Fricção e carga administrativa | Solicitante não assume negócio sem decisão admin; 8 testes passam | Tomada automática eliminada |
| ✅ Isolar dados demonstrativos — concluído na B011 | Produção cria apenas categorias; local/testing exigem senha explícita sem default | Decisão de ambientes | P0 / S | Seed local deixa de funcionar sem configuração | Três regressões e suíte completa passam | Credenciais conhecidas eliminadas |
| ✅ Recuperar fila de enriquecimento — concluído na B009 | Identificar nove 429, limitar duração, espaçar lote, aplicar backoff e recuperar individualmente | Chave rotacionada e serviço disponível | P1 / M | Quota e duplicação | Zero jobs falhos/pendentes; teste de 429 e runbook registrados | Importação operável |
| ✅ Consolidar regressões críticas — concluído na B010 | Mapear e executar cobertura B003–B009 | Correções concluídas | P0 / M | Falsa confiança por testes genéricos | Matriz dedicada passa com 94 testes/202 assertions | Baseline crítico verificável |

## Fase 1 — Limpeza e base técnica

| Item | Objetivo e escopo | Dependências | Pri. / esforço | Riscos | Critério de aceite | Resultado esperado |
|---|---|---|---|---|---|---|
| Formalizar operação do claim manual | Definir evidência, motivo, SLA e trilha de decisão sobre o fluxo entregue na B006 | Operador de moderação definido | P1 / M | Fricção e LGPD | Cada decisão futura registra ator, data e motivo; procedimento é documentado | Propriedade auditável |
| ✅ Tornar pontos idempotentes — concluído na B008 | Chave estável, constraint, transação, saneamento e reconciliação | B005 e dados locais inspecionados | P1 / M | Perda de pontuação artificial antiga | Mesma origem premia uma vez; totais reconciliam; regressões passam | Ranking tecnicamente consistente |
| ✅ Transações de operações compostas — concluído na B013 | Post/poll/pontos, claim, capas e galeria com transação/compensação | B008/B012 e testes de falha | P1 / L | Órfão de storage em falha de deleção | Falha injetada não deixa banco parcial; troca preserva arquivo válido | Consistência operacional |
| ✅ Centralizar cooldown de promoções — concluído na B015 | Regra única na Action, autorização pela Policy existente e lock contra concorrência | B005 | P1 / S | Mensagem de validação nos dois canais | HTTP e Livewire bloqueiam plano free e liberam Featured igualmente | Regra comercial consistente |
| ✅ Centralizar regras duplicadas restantes — concluído na B016 | Regras reutilizáveis, Requests herdados, normalização de telefone e Action de edição | B005/B007/B015 | P1 / L | Compatibilidade das rotas HTTP | Paridade HTTP/Livewire e 218 testes verdes | Menos drift e bugs |
| ✅ Reforçar constraints — concluído na B014 | Sanear poll/post, opção/poll e capa; aplicar unique, FK composta e índice funcional | B005/B013 e queries de diagnóstico | P1 / M | Compatibilidade do índice MySQL | Migration reversível e três regressões bloqueiam casos inválidos | Integridade no banco |
| ✅ Colocar e-mails em fila — concluído na B012 | Database imediato; mail/reset após commit, timeout, retry e backoff | Queue database e worker do script `composer dev` | P1 / M | Worker parado atrasa e-mails | SMTP não bloqueia request; testes provam canais/conexões | UX resiliente |
| ✅ Paginar coleções — concluído na B017 | Conta, perfil, comentários e reviews com páginas independentes e métricas globais | B016 | P1 / M | Estado de abas e médias parciais | Quatro regressões e suíte de 222 testes passam | Crescimento básico sem coleções irrestritas |
| Limitar mapa e consolidar cache | Viewport/página no mapa, remover queries da view e centralizar invalidação | Métricas/query plan | P1 / L | Mudança de UX | Payload limitado, zero queries em Blade e cache invalidado por mutação | Escala básica restante |
| Baseline de engenharia | `.env.example`, README, CI, formatter, audit, worker/scheduler/backup | Decisões de deploy | P1 / M | Divergência local/CI | Setup limpo reproduz testes/build; CI verde; runbook testado | Desenvolvimento reproduzível |
| Baseline de acessibilidade | Teclado, foco, labels, dialogs, reduced motion e teste automatizado inicial | Fluxos estabilizados | P1 / M | Regressão visual | Jornadas essenciais passam teclado e axe sem violações graves | Produto inclusivo |
| Limpeza Ponytail | Remover views/placeholders/deps/serviços confirmadamente sem uso | `rg`, build e testes | P2 / S | Remover uso indireto | Build/testes verdes e diff reduz artefatos sem rota/import | Menor manutenção |

## Fase 2 — MVP utilizável

| Item | Objetivo e escopo | Dependências | Pri. / esforço | Riscos | Critério de aceite | Resultado esperado |
|---|---|---|---|---|---|---|
| Definir bairro piloto | Fonte canônica para nome/coord.; alinhar home, perfil, feed e importação | Modelo decidido | P1 / M | Migração de texto livre | Toda tela usa a mesma configuração; filtros têm resultado previsível | Posicionamento hiperlocal claro |
| Fechar jornada do conteúdo | Criar → pendente → moderar → notificar → corrigir/republicar | Fases 0–1 | P1 / L | Carga de moderação | Teste E2E cobre aprovação e rejeição com motivo | Feed utilizável em operação real |
| Fechar jornada comercial | Cadastrar/reivindicar → completar perfil/fotos/horário → publicar promoção | Claim e horários seguros | P1 / L | Dados externos incorretos | Comerciante piloto conclui sem suporte técnico; status é visível | Catálogo sustentável |
| Simplificar conta e navegação | Paginar/tabs, status, quatro destinos primários e filtros na URL | UX baseline | P1 / M | Descoberta de recursos secundários | Teste com usuários conclui tarefas principais; links são compartilháveis | Menor carga cognitiva |
| Operação de moderação | Motivo, histórico mínimo, ações em lote confirmadas e SLA | Auditoria | P1 / L | Volume/abuso | Toda decisão registra ator/data/motivo e pode ser consultada | Governança mínima |
| Piloto controlado | Seed/import real revisado, termos/privacidade, backup, suporte e checklist | Todos os itens P0/P1 essenciais | P0 / M | Conteúdo frio e suporte | 10–30 usuários e 10+ negócios usam por 2 semanas sem incidente P0 | Evidência real de valor |

## Fase 3 — Engajamento

| Item | Objetivo e escopo | Dependências | Pri. / esforço | Riscos | Critério de aceite | Resultado esperado |
|---|---|---|---|---|---|---|
| Calendário local | Filtrar eventos futuros, detalhe e exportação/lembrete | Eventos e notifications | P2 / M | Eventos vencidos | Eventos passados saem do padrão; timezone testado | Motivo recorrente de visita |
| Preferências e digest | Opt-in por categoria/canal/frequência | Fila e bairro canônico | P2 / M | Fadiga/LGPD | Usuário controla entrega; unsubscribe funciona | Retenção sem spam |
| Pedidos de orçamento | Pedido estruturado e manifestação de negócios verificados | Claim seguro e anti-spam | P2 / L | Marketplace prematuro | Piloto mede pedidos e contatos sem chat/pagamento | Liga demanda e oferta |
| Pulso verificável | Métricas com janela, fonte e resolução confirmada | Dados e moderação maduros | P2 / L | Manipulação | Cada indicador explica período e amostra; reconcilia com posts | Diferencial cívico |
| Templates locais | Vagas, pets, doação e alertas como variações leves de post | Taxonomia validada | P3 / M | Fragmentação | Só templates com demanda comprovada entram | Expansão barata do conteúdo |

## Fase 4 — Monetização

| Item | Objetivo e escopo | Dependências | Pri. / esforço | Riscos | Critério de aceite | Resultado esperado |
|---|---|---|---|---|---|---|
| Definir proposta Featured | Entitlements, preço, prazo, cancelamento e política, ainda manual | Dados do piloto | P2 / M | Benefício não percebido | Comerciantes aceitam proposta e métricas são definidas | Hipótese comercial validada |
| Métricas ao comerciante | Impressões/cliques agregados e transparentes | Analytics first-party/consentimento | P2 / L | Privacidade/dados enganosos | Eventos deduplicados e painel reconciliável | Valor mensurável |
| Patrocínio simples | Unificar workflow, posições, validade, identificação e relatório | Moderação e analytics | P2 / M | Confiança editorial | Todo anúncio é identificado, expira e possui histórico | Receita manual controlada |
| Cobrança/assinatura | Provedor, webhook idempotente, fiscal, falhas e suporte | Hipótese paga comprovada | P3 / XL | Chargeback/compliance | Ciclo completo sandbox + cancelamento/reconciliação | Receita automatizada |
| Benefícios/cupons | Código simples e medição de resgate | Negócios verificados | P3 / L | Fraude | Validade e limite aplicados; comerciante confirma resgate | Incentivo de uso local |

## Fase 5 — Escala

| Item | Objetivo e escopo | Dependências | Pri. / esforço | Riscos | Critério de aceite | Resultado esperado |
|---|---|---|---|---|---|---|
| Multi-bairro | Modelar localidades, escopo, onboarding e moderação por área | Operação piloto repetível | P3 / XL | Vazamento/cold start | Testes garantem escopo; segundo bairro opera isolado | Expansão geográfica |
| Observabilidade e SLO | Logs estruturados, erros, métricas, alertas e auditoria | Infra definida | P2 / L | Ruído/custo | SLOs e alertas acionáveis; incidente simulado detectado | Operação previsível |
| Storage/cache/fila de produção | Object storage/CDN, workers escaláveis, cache adequado | Métricas de carga | P3 / L | Custo/consistência | Teste de carga e restore atendem SLO | Escala técnica |
| Busca e geo avançados | Full-text/proximidade somente após `LIKE`/viewport saturarem | Dados canônicos e métricas | P3 / XL | Infra prematura | Benchmark prova ganho e fallback existe | Descoberta em maior volume |
| PWA/push | Instalação e notificações opt-in | Retenção web comprovada | P3 / L | Permissão/fadiga | Instalação e unsubscribe funcionam; uso incremental medido | Canal recorrente |

## Sequência crítica

```text
segredos/dependências → autorização/status/XSS → horários/claim manual →
transações/fila/constraints → jornada moderada + comercial → piloto →
engajamento medido → monetização → expansão
```

Não iniciar Fase 3–5 enquanto houver P0 aberto. As fases são gates de risco, não apenas calendário.
