# Auditoria técnica

## Verificações executadas

| Verificação em 20/07/2026 | Resultado |
|---|---|
| `artisan test --compact` | **225 testes, 494 assertions, todos passando** após B021 |
| `npm run build` | **Passou**; CSS 103,79 kB (18,75 kB gzip), JS 46,16 kB (17,79 kB gzip) |
| `composer validate --strict` | **Passou** |
| `pint --test` | **Passou em 212 arquivos** |
| `composer audit` | **0 advisories após B002**; baseline: 20 em 11 pacotes |
| `npm audit` | **0 vulnerabilidades após B002**; baseline: 9, incluindo 2 críticas |
| Migrations | 42 migrations aplicadas no MySQL local |
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
| T10 | ✅ **Resolvido em 20/07/2026.** Post/poll/pontos e claim usam transações; capas/galeria preparam imagens, compensam arquivo novo em falha e removem o antigo só após commit; seleção de capa valida alvo antes de limpar estado. | Actions de post/claim/business; `PhotoGallery`; `CompositeActionsTest`; `PhotoGalleryTest` | Monitorar órfãos de storage; deleção de arquivo após commit favorece integridade do banco | L / P1 concluída |
| T11 | ✅ **Resolvido em 20/07/2026.** Produção semeia apenas categorias; dados demo ficam restritos a local/testing e exigem `DEMO_USER_PASSWORD` sem default. | `DatabaseSeeder.php`; `config/app.php`; `.env.example`; `DatabaseSeederTest.php` | Provisionar o primeiro admin de produção por procedimento deliberado até automação ser necessária | S / P0 concluída |
| T12 | ⚠️ **Incidente operacional resolvido na B009:** nove 429 foram recuperados; job agora tem timeout/backoff progressivo e a UI espaça o lote. Permanece o risco de download de URL/tamanho não validado e falta alerta proativo. | `EnrichBusinessFromGoogle.php`; `GooglePlacesService.php`; `GooglePlacesImport.php`; `EnrichBusinessFromGoogleTest.php` | Restringir host/protocolo/tamanho da foto; adicionar alerta quando a infraestrutura de produção existir | M / P1 parcial |

## Problemas de severidade média

| ID | Descrição e impacto | Arquivos/evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T13 | Filtro “aberto agora” materializa toda a consulta antes de paginar; cresce em memória/latência | `BusinessList.php:52-66`; `Business.php:127-164` | Para o MVP limitar conjunto ou modelar intervalos consultáveis quando houver volume | M / P1 |
| T14 | ⚠️ **Quase resolvido.** B017 paginou conta/perfis/interações e B018 moveu o mapa para endpoint validado por viewport com teto de 200. Resta apenas o sitemap carregar todos os registros. | componentes paginados; `BusinessController::map`; `MapBusinessesRequest`; `SitemapController` | Dividir sitemap quando o volume real se aproximar do limite operacional | S / P2 parcial |
| T15 | ✅ **Resolvido na B019.** `HomeCache` concentra dez chaves/TTL; observer pós-commit invalida em mudanças de Post, Business, Promotion, Category e User; estatísticas saíram da Blade. | `HomeCache`; `AppServiceProvider`; `HomeController`; `HomeCacheTest` | Monitorar hit rate e só separar invalidação por agregado se houver pressão real | M / P1 concluída |
| T16 | ✅ **Resolvido na B020.** Exemplo usa Hub do Bairro, pt_BR, MySQL/Sail, sessão/cache file, storage public, queue database e SMTP Mailpit. | `.env.example` | Documentar produção separadamente na B022 sem colocar segredos no repositório | XS / P1 concluída |
| T17 | ⚠️ **Parcialmente resolvido na B014.** O banco agora garante uma poll por post, opção pertencente à poll e uma capa por negócio. Resta apenas `reviews.rating` sem `CHECK` 1–5 para escritas externas. | migration `2026_07_20_150000_*`; `DatabaseConstraintsTest`; migration `create_reviews` | Manter regressões e adicionar `CHECK` de rating quando houver escrita fora da aplicação | XS / P2 |
| T18 | Estados fixos são inconsistentes: `Promotion` e `Comment` usam strings onde outros models usam enums/casts | `app/Models/Promotion.php`; `app/Models/Comment.php`; projeto usa enums em `app/Enums` | Criar enums/casts e validar em uma camada central | S / P2 |
| T19 | Serviço Google ainda pede field mask `*`; UI enriquece e CLI não, e a busca interativa não possui retry, produzindo custo e resultados diferentes | `GooglePlacesService.php`; `GooglePlacesImport.php`; `ImportBusinessesFromGoogle.php` | Pedir só campos usados; padronizar pipeline e tratamento de quota | M / P1 |
| T20 | Não há trilha de auditoria para moderação, plano, patrocínio, configurações ou claim; notifications não substituem histórico imutável | Controllers em `app/Http/Controllers/Admin`; `ClaimBusinessAction.php` | Registrar ator, ação, alvo, antes/depois e motivo | M / P2 |
| T21 | Rate limits cobrem posts/comentários/votos/denúncias e claims, mas não reviews, respostas, favoritos, salvos ou upgrades | componentes Livewire correspondentes; rota `businesses.claim.request` | Limitar operações de abuso por usuário/IP e testar | S / P1 |
| T22 | Verificação de e-mail é uma promessa de UI não aplicada ao model | `routes/auth.php:38-48`; `User.php:5,13-16` | Decidir e alinhar contrato, middleware, UI e testes | S / P1 |
| T23 | ✅ **Resolvido por decisão explícita na B020.** MySQL 8 é parte do stack obrigatório e o `.env.example` não sugere mais SQLite. | `StatsController`; `FeedList`; `.env.example` | Manter testes no MySQL e não adicionar abstração de dialeto sem necessidade | XS / P2 concluída |
| T24 | `trustProxies('*')` está na alteração local preexistente e confia em qualquer proxy; perigoso se app puder ser acessado diretamente | `bootstrap/app.php:18` | Validar topologia e restringir proxies/headers confiáveis | XS / P1 |
| T25 | Leaflet é carregado por CDN sem SRI/CSP e repetido em views; falha/comprometimento do terceiro afeta mapa | `businesses/index.blade.php:75-76`; `businesses/show.blade.php:311-312` | Fixar versão, SRI/CSP ou empacotar; ter fallback de lista | S / P2 |

## Problemas de severidade baixa

| ID | Descrição e impacto | Evidência | Recomendação | Esforço / prioridade |
|---|---|---|---|---|
| T26 | README é o padrão Laravel; onboarding e operação dependem de conhecimento tribal | `README.md:1-59` | Substituir por instruções do projeto, arquitetura, comandos e runbook | S / P1 |
| T27 | ✅ **Resolvido até a B021.** Pint passa nos 212 arquivos PHP e é obrigatório no CI. | `pint --test`; `.github/workflows/ci.yml` | Manter formatter no pipeline | XS / P2 concluída |
| T28 | Nome de teste contradiz sua assertion: diz pending, espera Approved | `tests/Feature/Admin/GooglePlacesImportTest.php:141-178` | Renomear ou corrigir regra esperada | XS / P2 |
| T29 | Permanecem testes/views exemplo e assets scaffold não utilizados | `tests/Feature/ExampleTest.php`; `tests/Unit/ExampleTest.php`; views listadas em `03-incomplete-features.md` | Remover ruído depois da estabilização | XS / P3 |
| T30 | ⚠️ **Parcialmente resolvido na B021.** CI cobre Composer, Pint, PHPUnit/MySQL, build e audits. Ainda faltam runbook, healthcheck de fila e cobertura publicada. | `.github/workflows/ci.yml`; README padrão | Entregar operação e restore na B022; cobertura só se orientar decisão | M / P1 parcial |

## Arquitetura e qualidade

### Pontos positivos

- Actions e Policies existem para operações centrais; controllers em geral são pequenos.
- Models possuem relacionamentos e scopes legíveis; listas principais usam eager loading.
- A suíte de 225 testes é uma base forte e roda integralmente no MySQL local.
- Migrations evitam enum nativo do MySQL e incluem índices nas consultas mais óbvias.
- Uploads são processados e armazenados pelo Laravel Storage.

### Acoplamento e duplicação

✅ **Resolvido na B016 para negócios e promoções.** Form Requests expõem as regras reutilizadas pelo Livewire, `UpdateBusinessRequest` herda o contrato de criação, telefone HTTP é normalizado para array e edição de promoção usa `UpdatePromotionAction`. As rotas HTTP compatíveis e a UI Livewire agora convergem nas mesmas validações e Actions.

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

Os testes atuais provam muitos caminhos felizes, Policies e validações. Não há número de cobertura disponível. As maiores lacunas são falhas prolongadas de integrações, cache e acessibilidade. Claim, seeds, resposta 429, pontos e compensação de arquivos possuem cobertura dedicada. A aprovação de produção deve exigir testes dos riscos restantes, não apenas manter os 211 atuais verdes.

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
