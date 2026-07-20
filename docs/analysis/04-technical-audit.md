# Auditoria técnica

## Verificações executadas

| Verificação em 20/07/2026 | Resultado |
|---|---|
| `artisan test --compact` | **206 testes, 436 assertions, todos passando** após B012 |
| `npm run build` | **Passou**; CSS 103,79 kB (18,75 kB gzip), JS 37,17 kB (14,87 kB gzip) |
| `composer validate --strict` | **Passou** |
| `pint --test` | **Falhou** em um arquivo: `tests/Feature/BusinessReviewTest.php` |
| `composer audit` | **0 advisories após B002**; baseline: 20 em 11 pacotes |
| `npm audit` | **0 vulnerabilidades após B002**; baseline: 9, incluindo 2 críticas |
| Migrations | 41 migrations aplicadas no MySQL local |
| Jobs | **0 falhos e 0 pendentes** após recuperação controlada dos 9 erros HTTP 429 na B009 |

Esses resultados são observações reproduzíveis do ambiente auditado. As versões diretas estão em `composer.json:10-18` e `package.json:12-18`; resoluções transitivas estão nos lockfiles.

## Problemas críticos

| ID | Descrição e impacto | Arquivos/evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T01 | ✅ **Resolvido em 20/07/2026.** Dependências tinham vulnerabilidades publicadas em Laravel/Symfony/Guzzle, Axios, Vite e transitivas. | `composer.lock`; `package-lock.json`; auditorias acima | Manter audits no CI; Laravel foi atualizado dentro da linha 12 e o npm sem `--force` | M / P0 concluída |
| T02 | ✅ **Resolvido em 20/07/2026.** Popups Leaflet interpolavam nome, categoria e bairro em HTML. | Correção em `resources/views/businesses/index.blade.php` e `show.blade.php`; regressão em `tests/Feature/BusinessTest.php` | Manter conteúdo dinâmico em nós DOM com `textContent` | S / P0 concluída |
| T03 | ✅ **Resolvido em 20/07/2026.** Conteúdo pendente/rejeitado era acessível por URL pública. | `PostPolicy::view`, `BusinessPolicy::view` e autorização nos métodos `show`; regressões em `PostTest`/`BusinessTest` | Manter exceção somente para autor/proprietário e admin | S / P0 concluída |
| T04 | ✅ **Resolvido em 20/07/2026.** Mutações Livewire aceitavam IDs não escopados e `BusinessForm::save` não reautorizava update. | Componentes `PromotionForm`, `ReviewSection`, `CommentSection`, `PollVote`, `BusinessForm`; seis regressões nos testes Feature | Manter autorização no momento da mutação e resolver filhos pela relação pai | M / P0 concluída |
| T05 | ✅ **Resolvido pelo responsável em 20/07/2026.** A credencial RapidAPI exposta durante o diagnóstico foi rotacionada; o valor nunca foi reproduzido nestes documentos. | Configuração `config/services.php:38-41`; confirmação operacional do responsável | Manter escopo/quota mínimos e mascarar diagnósticos | XS / P0 concluída |

## Problemas de severidade alta

| ID | Descrição e impacto | Arquivos/evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T06 | ✅ **Resolvido em 20/07/2026.** O token autoaprovável foi removido; claim agora cria pedido único pendente, tem rate limit e só um admin pode aprovar/rejeitar. | `ClaimBusinessController`; `ClaimBusinessAction`; `ModerationController`; migration `2026_07_20_130000_*`; `ClaimTest` | Documentar evidência operacional, SLA e adicionar trilha/motivo quando houver volume | L / P0 concluída |
| T07 | ✅ **Resolvido em 20/07/2026.** Horários do formulário não eram gravados e seed/dados usavam formato legado. | Actions, `Business::isOpenNow`, `DatabaseSeeder` e migration `2026_07_20_120000_*`; regressões em `BusinessTest` | Manter formato estruturado e migration aplicada | M / P0 concluída |
| T08 | ✅ **Resolvido em 20/07/2026.** Premiações usam chave idempotente única e transação; voto normal possui origem estável, enquete usa cada `PollVote`, dados foram saneados e totais reconciliados. | `AwardPointsAction`; `VoteButtons`; `PollVote`; migration `2026_07_20_140000_*`; testes de reputação/voto/enquete | Validar regras do ranking e monitorar tentativas de abuso no piloto | M / P1 concluída |
| T09 | ✅ **Resolvido em 20/07/2026.** Canais database permanecem síncronos; e-mails de domínio e reset usam fila após commit, com 3 tentativas, backoff e timeout. | `QueuesMailAfterCommit`; quatro notifications com mail; `QueuedResetPassword`; `QueuedNotificationsTest` | Garantir worker/Supervisor no deploy e alerta de falhas | M / P1 concluída |
| T10 | Operações multi-etapa não usam transação/compensação: criação com imagem/enquete/pontos/notificação, claim, pontos e troca de foto podem ficar pela metade | `CreatePostAction.php:17-57`; `ClaimBusinessAction.php:13-28`; `AwardPointsAction.php:12-23`; `UpdateBusinessAction.php:34-54` | Delimitar transações de banco e limpar arquivos em falhas; notificar após commit | L / P1 |
| T11 | ✅ **Resolvido em 20/07/2026.** Produção semeia apenas categorias; dados demo ficam restritos a local/testing e exigem `DEMO_USER_PASSWORD` sem default. | `DatabaseSeeder.php`; `config/app.php`; `.env.example`; `DatabaseSeederTest.php` | Provisionar o primeiro admin de produção por procedimento deliberado até automação ser necessária | S / P0 concluída |
| T12 | ⚠️ **Incidente operacional resolvido na B009:** nove 429 foram recuperados; job agora tem timeout/backoff progressivo e a UI espaça o lote. Permanece o risco de download de URL/tamanho não validado e falta alerta proativo. | `EnrichBusinessFromGoogle.php`; `GooglePlacesService.php`; `GooglePlacesImport.php`; `EnrichBusinessFromGoogleTest.php` | Restringir host/protocolo/tamanho da foto; adicionar alerta quando a infraestrutura de produção existir | M / P1 parcial |

## Problemas de severidade média

| ID | Descrição e impacto | Arquivos/evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T13 | Filtro “aberto agora” materializa toda a consulta antes de paginar; cresce em memória/latência | `BusinessList.php:52-66`; `Business.php:127-164` | Para o MVP limitar conjunto ou modelar intervalos consultáveis quando houver volume | M / P1 |
| T14 | Consultas sem limite/paginação em conta, perfil público, comentários, reviews, sitemap e pontos do mapa | `ProfileController.php:14-24`; `UserProfileController.php:12-27`; `CommentSection.php:207-225`; `ReviewSection.php:123-135`; `SitemapController.php:16-28`; `BusinessController.php:24-39` | Paginar/cursor, limitar mapa por viewport e dividir sitemap | M / P1 |
| T15 | Cache da home é fragmentado e invalidado de forma incompleta; a própria view faz três queries fora do cache | `HomeController.php:17-88`; `ModerationController.php:198-206`; `home/index.blade.php:143-147` | Serviço/chaveamento central, tags se cabível, invalidação por evento e remover queries da view | M / P1 |
| T16 | Configuração de exemplo contradiz aplicação e ambiente: nome Laravel, locale inglês, SQLite, disco local e mail log | `.env.example:1,7-9,23,30,37-40,50-57`; intenção em `CLAUDE.md` | Tornar `.env.example` executável e seguro para o MVP; documentar variantes | S / P1 |
| T17 | Integridade de banco insuficiente: poll 1:1 sem unique; voto aceita opção de outra poll; capa de foto não é única; rating não tem check | migrations `create_polls`, `create_poll_votes`, `create_business_photos`, `create_reviews` | Adicionar constraints após saneamento; manter validação de aplicação | M / P1 |
| T18 | Estados fixos são inconsistentes: `Promotion` e `Comment` usam strings onde outros models usam enums/casts | `app/Models/Promotion.php`; `app/Models/Comment.php`; projeto usa enums em `app/Enums` | Criar enums/casts e validar em uma camada central | S / P2 |
| T19 | Serviço Google ainda pede field mask `*`; UI enriquece e CLI não, e a busca interativa não possui retry, produzindo custo e resultados diferentes | `GooglePlacesService.php`; `GooglePlacesImport.php`; `ImportBusinessesFromGoogle.php` | Pedir só campos usados; padronizar pipeline e tratamento de quota | M / P1 |
| T20 | Não há trilha de auditoria para moderação, plano, patrocínio, configurações ou claim; notifications não substituem histórico imutável | Controllers em `app/Http/Controllers/Admin`; `ClaimBusinessAction.php` | Registrar ator, ação, alvo, antes/depois e motivo | M / P2 |
| T21 | Rate limits cobrem posts/comentários/votos/denúncias e claims, mas não reviews, respostas, favoritos, salvos ou upgrades | componentes Livewire correspondentes; rota `businesses.claim.request` | Limitar operações de abuso por usuário/IP e testar | S / P1 |
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
- A suíte de 206 testes é uma base forte e roda integralmente no MySQL local.
- Migrations evitam enum nativo do MySQL e incluem índices nas consultas mais óbvias.
- Uploads são processados e armazenados pelo Laravel Storage.

### Acoplamento e duplicação

Criação/edição de negócios e promoções possuem caminho HTTP com Form Request e caminho Livewire com validação própria. Essa duplicação já causou divergências; horários foram corrigidos na B007, mas o limite de promoção continua apenas no Request. A regra deve morar em Action/Policy/objeto de domínio único, com a UI apenas orquestrando.

## Auditoria Ponytail de sobre-engenharia

Formato: localização → corte → substituição mínima.

- `resources/views/welcome.blade.php`, `dashboard.blade.php` → apagar views sem rota → nenhuma substituição. A antiga `businesses/claim.blade.php` foi removida na B006.
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

Os testes atuais provam muitos caminhos felizes, Policies e validações. Não há número de cobertura disponível. As maiores lacunas são falhas prolongadas de integrações, cache, limpeza de arquivos e acessibilidade. Claim, seeds, resposta 429 e idempotência de pontos possuem cobertura dedicada. A aprovação de produção deve exigir testes dos riscos restantes, não apenas manter os 204 atuais verdes.

### Matriz de regressão B010

| Correção | Regressões executadas em 20/07/2026 |
|---|---|
| B003 — XSS em mapas | `BusinessTest::test_map_popups_render_business_data_as_text` |
| B004 — status público | testes de conteúdo pendente em `PostTest` e `BusinessTest` |
| B005 — escopo Livewire | negócios, promoções, reviews, comentários e opções de enquete de outro pai/usuário |
| B006 — claim manual | 8 testes de pedido, concorrência, aprovação, rejeição e autorização |
| B007 — horários | persistência CRUD e período atravessando meia-noite |
| B008 — pontos | origem repetida, recriação de voto e múltiplos votantes de enquete |
| B009 — enriquecimento | resposta 429, backoff e espaçamento do lote |

Resultado direcionado: **94 testes e 202 assertions**, todos passando. Na conclusão da B010, a suíte completa tinha **204 testes e 429 assertions**, todos passando.

## Recuperação do enriquecimento Google Places

1. Execute `php artisan queue:failed` e confirme que a falha pertence a `EnrichBusinessFromGoogle`.
2. Corrija chave/quota e valide um único negócio com `php artisan businesses:enrich-google --id=<id> --sync`.
3. Reenvie cada UUID com `php artisan queue:retry <uuid>` e processe com worker ativo, mantendo intervalo entre registros.
4. Confirme `queue:failed` vazio e ausência de jobs pendentes. Não use `queue:retry all` enquanto a causa do 429 estiver ativa e nunca exponha payloads/headers em diagnóstico compartilhado.
