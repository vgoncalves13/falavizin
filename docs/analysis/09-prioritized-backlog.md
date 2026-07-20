# Backlog inicial priorizado

Prioridade: P0 bloqueia produção; P1 é necessária ao MVP; P2 é importante; P3 é posterior. A primeira sequência recomendada prioriza contenção e falhas P0, não a ordem numérica dos IDs.

| ID | Módulo | Título | Descrição | Tipo | Pri. | Esf. | Dependências | Fase | Critério de aceite |
|---|---|---|---|---|---|---|---|---|---|
| B001 | Integrações | ✅ Rotacionar a credencial RapidAPI exposta — concluída em 20/07/2026 | Revogar chave observada na auditoria, restringir e instalar substituta sem revelar valor | segurança | P0 | XS | acesso RapidAPI | 0 | chave antiga inválida; nova mascarada e chamada passa |
| B002 | Dependências | ✅ Corrigir advisories Composer e npm bloqueadores — concluída em 20/07/2026 | Atualizar versões compatíveis e revisar transitivas | segurança | P0 | M | — | 0 | audits zerados; 187 testes e build verdes |
| B003 | Mapas | ✅ Neutralizar XSS nos popups Leaflet — concluída em 20/07/2026 | Substituir interpolação HTML por texto/nós seguros | segurança | P0 | S | — | 0 | payload vira `textContent`; teste de regressão passa |
| B004 | Moderação | ✅ Restringir conteúdo não aprovado nas rotas públicas — concluída em 20/07/2026 | Aplicar status scope com exceção explícita para autor/admin | bug | P0 | S | — | 0 | visitante recebe 403; autor/proprietário e admin acessam |
| B005 | Livewire | Escopar e reautorizar todas as mutações por ID | Corrigir promoção, review, comentário, poll e BusinessForm | segurança | P0 | M | Policies atuais | 0 | testes cruzados retornam 403/validation e não alteram dados |
| B006 | Negócios | Reformular reivindicação com prova e expiração | Verificação de e-mail, aprovação/evidência, token seguro, rate limit | segurança | P0 | L | decisão operacional | 1 | nenhum solicitante sem prova assume negócio; token expira |
| B007 | Negócios | Persistir e normalizar horários de funcionamento | Unificar formato em formulário, Actions, seed e dados | bug | P0 | M | definição do schema JSON | 0 | CRUD e aberto agora passam horários diurnos/noturnos |
| B008 | Reputação | Tornar premiação de pontos idempotente | Impedir ganho repetido e reconciliar total do usuário | bug | P1 | M | constraint/migração | 1 | mesma origem/razão não premia duas vezes; totals reconciliam |
| B009 | Integrações | Recuperar jobs de enriquecimento falhos | Diagnosticar, corrigir, aplicar retry/backoff e runbook | infraestrutura | P1 | M | B001 | 0 | nove falhas triadas; retry seguro; alerta documentado |
| B010 | Testes | Cobrir regressões P0 de segurança e integridade | Testes negativos para B003–B009 | segurança | P0 | M | B003–B009 | 0 | cada falha possui teste que reproduz e prova correção |
| B011 | Seeds | Remover senhas padrão de seeds operacionais | Separar demo de produção e exigir secret seguro | segurança | P0 | S | decisão de ambientes | 1 | seed de produção não cria credencial conhecida |
| B012 | Notificações | Enfileirar e-mails após commit | Implementar queue, retry/backoff e comportamento de falha | infraestrutura | P1 | M | worker estável | 1 | SMTP fora não reverte/bloqueia request; retry funciona |
| B013 | Dados | Adicionar transações a operações compostas | Post, claim, pontos e troca de arquivos atômicos | refatoração | P1 | L | testes de falha | 1 | falha injetada não deixa estado parcial |
| B014 | Banco | Reforçar constraints de polls, fotos e claims | Sanear dados e criar unicidade/integridade | débito técnico | P1 | M | backup; B005/B006 | 1 | banco rejeita relações inválidas |
| B015 | Promoções | Centralizar Policy e limite semanal | Eliminar divergência Request/Livewire | bug | P1 | S | B005 | 1 | ambos os caminhos aplicam exatamente a mesma regra |
| B016 | Arquitetura | Unificar fluxos de escrita HTTP e Livewire | Escolher UI canônica e Actions compartilhadas | refatoração | P1 | L | B005/B007/B015 | 1 | não há regras duplicadas entre Request/componente |
| B017 | Performance | Paginar conta, perfis, comentários e reviews | Evitar carregamento irrestrito | melhoria | P1 | M | UX definida | 1 | páginas têm limites, navegação e queries constantes |
| B018 | Mapas | Limitar negócios do mapa por viewport/página | Não serializar catálogo completo | performance | P1 | M | mapa seguro | 1 | payload tem limite/viewport e fallback de lista |
| B019 | Cache | Centralizar cache e invalidação da home | Remover queries da Blade e chaves esquecidas | refatoração | P1 | M | eventos de domínio | 1 | zero query na view; mutação relevante atualiza seção |
| B020 | Ambiente | Alinhar `.env.example` ao MVP | MySQL, pt_BR, nome, mail, storage e defaults seguros | documentação | P1 | XS | decisão deploy | 1 | clone limpo configura ambiente sem adivinhação |
| B021 | Engenharia | Criar CI de test, build, Pint e audits | Verificações em toda mudança | infraestrutura | P1 | M | B002 | 1 | PR falha em regressão, estilo ou advisory bloqueador |
| B022 | Operação | Documentar setup, deploy, worker, backup e restore | Substituir README padrão e criar runbook | documentação | P1 | S | ambiente/infra definidos | 1 | outra pessoa sobe e restaura ambiente seguindo docs |
| B023 | Acessibilidade | Corrigir foco, dialogs, labels e reduced motion | Baseline nas jornadas principais | melhoria | P1 | M | UI estável | 1 | teclado e axe sem violações graves nos fluxos críticos |
| B024 | Localização | Definir entidade/configuração canônica do bairro piloto | Remover hardcode e alinhar filtros/importação | refatoração | P1 | M | decisão de produto | 2 | toda tela usa bairro único configurado |
| B025 | Moderação | Registrar motivo e trilha de decisões | Ator, alvo, antes/depois, motivo e data | nova funcionalidade | P1 | L | schema de auditoria | 2 | toda ação admin é consultável e atribuível |
| B026 | Conteúdo | Exibir status e correção de publicação | Conta mostra pendência/rejeição e permite corrigir | melhoria | P1 | M | B004/B025 | 2 | autor entende estado e próximo passo |
| B027 | Busca | Persistir filtros na URL | Feed/catálogo compartilháveis e navegáveis | melhoria | P2 | S | componentes estabilizados | 2 | recarregar/voltar preserva filtro e ordem |
| B028 | Comercial | Simplificar cadastro de negócio | Etapas essenciais e enriquecimento posterior | melhoria | P1 | M | B007/B024 | 2 | comerciante piloto conclui sem ajuda técnica |
| B029 | Operação | Executar piloto controlado do bairro | Checklist, conteúdo inicial, suporte e métricas | infraestrutura | P0 | M | B001–B028 essenciais | 2 | 2 semanas sem P0, 10–30 usuários e 10+ negócios ativos |
| B030 | Eventos | Criar calendário de eventos futuros | Filtro, expiração, timezone e exportação | nova funcionalidade | P2 | M | feed estável | 3 | eventos futuros ordenados; passados fora do padrão |
| B031 | Notificações | Adicionar preferências e digest | Canal, categoria e frequência opt-in | nova funcionalidade | P2 | M | B012/B024 | 3 | preferências e unsubscribe são respeitados |
| B032 | Comunidade | Estruturar pedido de orçamento | Pedido por categoria e interesse de negócio verificado | nova funcionalidade | P2 | L | B006/B012 | 3 | piloto conecta demanda/oferta sem chat/pagamento |
| B033 | Pulso | Tornar indicadores explicáveis e verificáveis | Janela, amostra, fonte e resolução confirmada | melhoria | P2 | L | B025/B024 | 3 | cada indicador é reconciliável com dados fonte |
| B034 | Comercial | Definir e testar proposta Featured manual | Benefícios, preço, vigência e métricas | nova funcionalidade | P2 | M | dados do piloto | 4 | entrevistas/piloto validam disposição a pagar |
| B035 | Analytics | Medir impressões e contatos comerciais | Eventos first-party agregados, deduplicados e consentidos | nova funcionalidade | P2 | L | privacidade; B034 | 4 | painel reconciliável sem PII desnecessária |
| B036 | Patrocínio | Unificar ciclo de posts patrocinados | Um controller/action, validade, identificação e histórico | refatoração | P2 | M | B025/B035 | 4 | anúncio identificado, expira e é auditável |
| B037 | Pagamentos | Implementar assinatura apenas após validação | Provedor, webhooks, cancelamento e reconciliação | nova funcionalidade | P3 | XL | B034 comprovado | 4 | ciclo sandbox completo e falhas tratadas |
| B038 | Plataforma | Modelar multi-bairro com isolamento | Localidades, scoping, onboarding e moderação | nova funcionalidade | P3 | XL | piloto repetível | 5 | segundo bairro opera sem vazamento de dados |
| B039 | Observabilidade | Definir SLOs, logs, métricas e alertas | Monitorar web, fila, integrações e backups | infraestrutura | P2 | L | deploy definido | 5 | incidente simulado é detectado e diagnosticável |
| B040 | Limpeza | Remover scaffolds e infraestrutura sem uso | Views, placeholders, Axios e serviços Compose confirmados | débito técnico | P2 | S | build/teste/rg | 1 | nenhuma referência; redução comprovada; tudo verde |

## Ordem imediata

~~B001~~ → ~~B002~~ → ~~B003~~ → ~~B004~~ → **B005** → B007 → B006 → B011 → B009 → B010. B010 acompanha cada correção, embora apareça como item consolidado. B008 vem imediatamente depois desse bloco.
