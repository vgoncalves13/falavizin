# Auditoria técnica

## Verificações executadas

| Verificação em 20/07/2026 | Resultado |
|---|---|
| `artisan test --compact` | **193 testes, 402 assertions, todos passando** após B005 |
| `npm run build` | **Passou**; CSS 103,79 kB (18,75 kB gzip), JS 37,17 kB (14,87 kB gzip) |
| `composer validate --strict` | **Passou** |
| `pint --test` | **Falhou** em um arquivo: `tests/Feature/BusinessReviewTest.php` |
| `composer audit` | **0 advisories após B002**; baseline: 20 em 11 pacotes |
| `npm audit` | **0 vulnerabilidades após B002**; baseline: 9, incluindo 2 críticas |
| Migrations | 38 migrations aplicadas no MySQL local |
| Jobs | 9 jobs falhos, todos `EnrichBusinessFromGoogle`, no banco local |

Esses resultados são observações reproduzíveis do ambiente auditado. As versões diretas estão em `composer.json:10-18` e `package.json:12-18`; resoluções transitivas estão nos lockfiles.

## Problemas críticos

| ID | Descrição e impacto | Arquivos/evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T01 | ✅ **Resolvido em 20/07/2026.** Dependências tinham vulnerabilidades publicadas em Laravel/Symfony/Guzzle, Axios, Vite e transitivas. | `composer.lock`; `package-lock.json`; auditorias acima | Manter audits no CI; Laravel foi atualizado dentro da linha 12 e o npm sem `--force` | M / P0 concluída |
| T02 | ✅ **Resolvido em 20/07/2026.** Popups Leaflet interpolavam nome, categoria e bairro em HTML. | Correção em `resources/views/businesses/index.blade.php` e `show.blade.php`; regressão em `tests/Feature/BusinessTest.php` | Manter conteúdo dinâmico em nós DOM com `textContent` | S / P0 concluída |
| T03 | ✅ **Resolvido em 20/07/2026.** Conteúdo pendente/rejeitado era acessível por URL pública. | `PostPolicy::view`, `BusinessPolicy::view` e autorização nos métodos `show`; regressões em `PostTest`/`BusinessTest` | Manter exceção somente para autor/proprietário e admin | S / P0 concluída |
| T04 | ✅ **Resolvido em 20/07/2026.** Mutações Livewire aceitavam IDs não escopados e `BusinessForm::save` não reautorizava update. | Componentes `PromotionForm`, `ReviewSection`, `CommentSection`, `PollVote`, `BusinessForm`; seis regressões nos testes Feature | Manter autorização no momento da mutação e resolver filhos pela relação pai | M / P0 concluída |
| T05 | **Credencial externa exposta durante a inspeção do ambiente.** Um valor configurado foi exibido por ferramenta de diagnóstico. O valor não está reproduzido nestes documentos. | Configuração `config/services.php:38-41`; incidente operacional da sessão de auditoria | Revogar/rotacionar a chave RapidAPI imediatamente, revisar uso/quota e garantir mascaramento de diagnósticos | XS / P0 |

## Problemas de severidade alta

| ID | Descrição e impacto | Arquivos/evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T06 | Reivindicação não comprova propriedade: o token vai ao próprio solicitante, não ao contato conhecido do estabelecimento; não expira, não é único/hasheado e e-mail nem é verificado | `ClaimBusinessController.php:15-41`; `ClaimBusinessAction.php:13-28`; migration de businesses `:33-35`; `User.php:5` | Exigir verificação forte ou aprovação admin; token expirável, único, hasheado; rate limit e auditoria | L / P0 |
| T07 | Horários do formulário não são gravados; filtro “aberto agora” pode mentir. Seed usa formato antigo após migrations de normalização já terem rodado | `BusinessForm.php:118-167`; `CreateBusinessAction.php:20-33`; `UpdateBusinessAction.php:15-25`; `DatabaseSeeder.php:84-102`; `Business.php:127-152` | Persistir em uma Action única, normalizar seed/dados, testar horários noturnos | M / P0 |
| T08 | Pontuação não idempotente: remover e recolocar voto útil pode premiar repetidamente; total desnormalizado pode divergir | `VoteButtons.php:35-63`; `AwardPointsAction.php:12-23`; migration de `point_events` sem chave idempotente | Chave única por razão/origem/beneficiário, transação e comando de reconciliação | M / P1 |
| T09 | E-mails e notificações são síncronos apesar de usarem `Queueable`; falha externa pode degradar request e deixar operação parcialmente concluída | `NewContentNotification.php:9-20`; `ContentModerationNotification.php:9-22`; `ClaimBusinessController.php:22-25` | Implementar `ShouldQueue`, after-commit, retry/backoff e monitoramento | M / P1 |
| T10 | Operações multi-etapa não usam transação/compensação: criação com imagem/enquete/pontos/notificação, claim, pontos e troca de foto podem ficar pela metade | `CreatePostAction.php:17-57`; `ClaimBusinessAction.php:13-28`; `AwardPointsAction.php:12-23`; `UpdateBusinessAction.php:34-54` | Delimitar transações de banco e limpar arquivos em falhas; notificar após commit | L / P1 |
| T11 | Seeder cria administrador e usuários com senha padrão conhecida. Se executado em ambiente compartilhado, gera tomada de conta | `database/seeders/DatabaseSeeder.php:28-62` | Separar dados demo de bootstrap de produção; senha obrigatória via secret ou nenhuma conta | S / P0 |
| T12 | Enriquecimento externo tem jobs falhos sem mecanismo visível de recuperação; download de foto aceita URL retornada externamente | `app/Jobs/EnrichBusinessFromGoogle.php:124-137`; tabela local `failed_jobs`; ausência de scheduler em `routes/console.php` | Restringir host/protocolo, timeouts/tamanho, retries/backoff, dashboard/alerta e runbook | M / P1 |

## Problemas de severidade média

| ID | Descrição e impacto | Arquivos/evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T13 | Filtro “aberto agora” materializa toda a consulta antes de paginar; cresce em memória/latência e não trata expediente atravessando meia-noite | `BusinessList.php:52-66`; `Business.php:127-152` | Corrigir regra; para o MVP limitar conjunto ou modelar intervalos consultáveis | M / P1 |
| T14 | Consultas sem limite/paginação em conta, perfil público, comentários, reviews, sitemap e pontos do mapa | `ProfileController.php:14-24`; `UserProfileController.php:12-27`; `CommentSection.php:207-225`; `ReviewSection.php:123-135`; `SitemapController.php:16-28`; `BusinessController.php:24-39` | Paginar/cursor, limitar mapa por viewport e dividir sitemap | M / P1 |
| T15 | Cache da home é fragmentado e invalidado de forma incompleta; a própria view faz três queries fora do cache | `HomeController.php:17-88`; `ModerationController.php:198-206`; `home/index.blade.php:143-147` | Serviço/chaveamento central, tags se cabível, invalidação por evento e remover queries da view | M / P1 |
| T16 | Configuração de exemplo contradiz aplicação e ambiente: nome Laravel, locale inglês, SQLite, disco local e mail log | `.env.example:1,7-9,23,30,37-40,50-57`; intenção em `CLAUDE.md` | Tornar `.env.example` executável e seguro para o MVP; documentar variantes | S / P1 |
| T17 | Integridade de banco insuficiente: poll 1:1 sem unique; voto aceita opção de outra poll; capa de foto não é única; claim sem unique/expiry; rating sem check | migrations `create_polls`, `create_poll_votes`, `create_business_photos`, `create_businesses`, `create_reviews` | Adicionar constraints após saneamento; manter validação de aplicação | M / P1 |
| T18 | Estados fixos são inconsistentes: `Promotion` e `Comment` usam strings onde outros models usam enums/casts | `app/Models/Promotion.php`; `app/Models/Comment.php`; projeto usa enums em `app/Enums` | Criar enums/casts e validar em uma camada central | S / P2 |
| T19 | Serviço Google pede field mask `*`, sem timeout/retry consistente; UI enriquece e CLI não, produzindo resultados diferentes | `GooglePlacesService.php:15-25,73-96`; `GooglePlacesImport.php:111-121`; `ImportBusinessesFromGoogle.php:82-92` | Pedir só campos usados; padronizar pipeline, timeout, retry e quota | M / P1 |
| T20 | Não há trilha de auditoria para moderação, plano, patrocínio, configurações ou claim; notifications não substituem histórico imutável | Controllers em `app/Http/Controllers/Admin`; `ClaimBusinessAction.php` | Registrar ator, ação, alvo, antes/depois e motivo | M / P2 |
| T21 | Rate limits cobrem posts/comentários/votos/denúncias, mas não reviews, respostas, claims, favoritos, salvos ou upgrades | componentes Livewire correspondentes | Limitar operações de abuso por usuário/IP e testar | S / P1 |
| T22 | Verificação de e-mail é uma promessa de UI não aplicada ao model | `routes/auth.php:38-48`; `User.php:5,13-16` | Decidir e alinhar contrato, middleware, UI e testes | S / P1 |
| T23 | SQL específico de MySQL (`YEARWEEK`, expressão de tendência) conflita com `.env.example` SQLite e reduz portabilidade | `StatsController.php:39-56`; `FeedList.php:49-51`; `.env.example:23` | Assumir/documentar MySQL ou encapsular dialeto; não fingir suporte a SQLite | XS / P2 |
| T24 | `trustProxies('*')` está na alteração local preexistente e confia em qualquer proxy; perigoso se app puder ser acessado diretamente | `bootstrap/app.php:18` | Validar topologia e restringir proxies/headers confiáveis | XS / P1 |
| T25 | Leaflet é carregado por CDN sem SRI/CSP e repetido em views; falha/comprometimento do terceiro afeta mapa | `businesses/index.blade.php:75-76`; `businesses/show.blade.php:311-312` | Fixar versão, SRI/CSP ou empacotar; ter fallback de lista | S / P2 |

## Problemas de severidade baixa

| ID | Descrição e impacto | Evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T26 | README é o padrão Laravel; onboarding e operação dependem de conhecimento tribal | `README.md:1-59` | Substituir por instruções do projeto, arquitetura, comandos e runbook | S / P1 |
| T27 | Um arquivo viola o formatter | `tests/Feature/BusinessReviewTest.php`; resultado do Pint | Executar Pint somente no arquivo após mudança funcional aprovada | XS / P2 |
| T28 | Nome de teste contradiz sua assertion: diz pending, espera Approved | `tests/Feature/Admin/GooglePlacesImportTest.php:141-178` | Renomear ou corrigir regra esperada | XS / P2 |
| T29 | Permanecem testes/views exemplo e assets scaffold não utilizados | `tests/Feature/ExampleTest.php`; `tests/Unit/ExampleTest.php`; views listadas em `03-incomplete-features.md` | Remover ruído depois da estabilização | XS / P3 |
| T30 | Não há CI, roteiro de deploy, healthcheck de fila ou cobertura publicada | ausência de `.github/workflows`; README padrão | Pipeline de test/build/audit e runbook de worker/scheduler/backup | M / P1 |

## Arquitetura e qualidade

### Pontos positivos

- Actions e Policies existem para operações centrais; controllers em geral são pequenos.
- Models possuem relacionamentos e scopes legíveis; listas principais usam eager loading.
- A suíte de 193 testes é uma base forte e roda integralmente no MySQL local.
- Migrations evitam enum nativo do MySQL e incluem índices nas consultas mais óbvias.
- Uploads são processados e armazenados pelo Laravel Storage.

### Acoplamento e duplicação

Criação/edição de negócios e promoções possuem caminho HTTP com Form Request e caminho Livewire com validação própria. Essa duplicação já causou divergência: limite de promoção só no Request e horário omitido nas Actions. A regra deve morar em Action/Policy/objeto de domínio único, com a UI apenas orquestrando.

## Auditoria Ponytail de sobre-engenharia

Formato: localização → corte → substituição mínima.

- `resources/views/welcome.blade.php`, `dashboard.blade.php`, `businesses/claim.blade.php` → apagar views sem rota → nenhuma substituição.
- `resources/views/components/**/⚡*.blade.php` → apagar três placeholders não usados → usar os componentes Livewire de classe já existentes.
- `resources/views/vendor/pagination/*` → manter apenas o template efetivamente renderizado → paginação Tailwind/Livewire padrão.
- `app/Http/Requests/StorePostRequest.php` → apagar se o fluxo continuar exclusivamente Livewire → regras no componente/Action escolhidos.
- `resources/js/bootstrap.js` + dependência `axios` → remover se a busca final confirmar zero chamadas → APIs nativas do navegador quando necessário.
- `compose.yaml:24-113` → retirar Redis, Meilisearch e Selenium do perfil padrão → MySQL e Mailpit bastam ao MVP atual.
- `PostSponsorController.php` + `SponsoredPostsController.php` → fundir dois controles de patrocínio → um controller/action.
- fluxos HTTP e Livewire de negócio/promoção → eliminar validação duplicada → uma Action/Policy compartilhada.
- invalidação manual espalhada em controllers → reduzir repetição → método/event listener único quando houver evidência de benefício.

`net:` estimativa de **~1.100 linhas, 2 dependências npm diretas e 3 serviços Compose** potencialmente removíveis após confirmação. Isso é uma oportunidade de limpeza, não autorização para apagar nesta etapa.

## Testes e cobertura

Os testes atuais provam muitos caminhos felizes, Policies e validações. Não há número de cobertura disponível. As maiores lacunas são idempotência de pontos, persistência de horários, claim, jobs/retries, cache, limpeza de arquivos e acessibilidade. A aprovação de produção deve exigir esses testes, não apenas manter os 193 atuais verdes.
