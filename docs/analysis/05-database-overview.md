# Visão geral do banco de dados

## Modelo textual

```text
User 1 ── * Post * ── 1 Category
  │           ├── * Comment ── * Comment (respostas)
  │           ├── * Vote (polimórfico: Post/Comment)
  │           ├── 0..1 Poll ── * PollOption
  │           │                    └── * PollVote ── 1 User
  │           └── * User (salvos, pivot post_user_saves)
  ├── * Business * ── 1 Category
  │        ├── * BusinessPhoto
  │        ├── * Promotion
  │        ├── * Review ── 1 User
  │        └── * User (favoritos, pivot business_user_favorites)
  ├── * Notification
  └── * PointEvent ── 1 alvo polimórfico

Setting (chave/valor global; sem tenant)
```

## Tabelas de domínio

| Tabela | Finalidade e campos importantes | Relações/evidência |
|---|---|---|
| `users` | Conta, telefone, bairro, `is_admin`, total `points` | Migration base e `add_neighborhood_to_users`, `add_points_to_users`; relações em `app/Models/User.php` |
| `categories` | Taxonomia compartilhada; nome, slug, ícone, type, ordem | `database/migrations/2026_02_05_200745_create_categories_table.php`; `CategoryType` |
| `posts` | Publicações, imagem, local, status, evento, patrocínio, denúncia e resolução | Migration `create_posts` e migrations `add_*_to_posts`; `app/Models/Post.php` |
| `comments` | Comentários hierárquicos, status, denúncia e soft delete | `create_comments`, `add_parent_id_to_comments`, `add_reporting_fields_to_comments`; `Comment.php` |
| `votes` | Voto polimórfico em post/comentário, único por usuário/alvo | `create_votes_table.php:14-23`; `Vote.php` |
| `businesses` | Perfil comercial, owner opcional, localização, horário JSON, plano/status, claim, denúncia e pedido de upgrade | `create_businesses_table.php`; migrations posteriores; `Business.php` |
| `business_photos` | Fotos, capa e ordenação | `create_business_photos_table.php`; `BusinessPhoto.php` |
| `promotions` | Oferta, período, ativo/status, imagem e denúncia | `create_promotions_table.php`; migrations posteriores; `Promotion.php` |
| `reviews` | Nota 1–5, comentário, resposta do proprietário e timestamps | `create_reviews_table.php`; `Review.php` |
| `polls` | Enquete opcional ligada a post e término | `create_polls_table.php`; `Poll.php` |
| `poll_options` | Opções da enquete | `create_poll_options_table.php`; `PollOption.php` |
| `poll_votes` | Escolha do usuário por poll | `create_poll_votes_table.php`; `PollVote.php` |
| `point_events` | Razão, pontos e origem polimórfica da reputação | `create_point_events_table.php`; `PointEvent.php` |
| `settings` | Configuração global chave/valor, inclusive bairro/coord. de importação | `create_settings_table.php`; `Setting.php` |

## Tabelas de associação e infraestrutura

- `business_user_favorites`: favoritos únicos de usuário/negócio.
- `post_user_saves`: posts salvos únicos.
- `notifications`: notificações database do Laravel.
- `password_reset_tokens`, `sessions`: autenticação/sessão.
- `cache`, `cache_locks`: cache no banco quando configurado.
- `jobs`, `job_batches`, `failed_jobs`: fila no banco.
- `migrations`: controle de schema.

As pivots possuem constraints de unicidade adequadas. As tabelas de infraestrutura vêm das migrations padrão do Laravel (`database/migrations/0001_01_01_000001_create_cache_table.php` e `0001_01_01_000002_create_jobs_table.php`).

## Enums e estados

| Campo | Enum PHP/valores | Observação |
|---|---|---|
| `posts.status` | `PostStatus`: pending, approved, rejected | Cast em `Post` |
| `posts.resolution_status` | `PostResolutionStatus` | Fluxo de problemas |
| `businesses.status` | `BusinessStatus`: pending, approved, rejected | Cast em `Business` |
| `businesses.plan` | `BusinessPlan`: free, featured | Cast em `Business` |
| `categories.type` | `CategoryType`: post, business, both | Cast em `Category` |
| `votes.type` | `VoteType`: helpful, not_helpful | Cast em `Vote` |
| `point_events.reason` | `PointEventReason` | Cast em `PointEvent` |
| `comments.status` | strings approved/rejected | **Sem enum/cast** |
| `promotions.status` | strings pending/approved/rejected | **Sem enum/cast** |

As migrations seguem a convenção saudável de `string` + enum PHP, não `ENUM` do MySQL.

## Integridade e riscos de modelagem

| Risco | Impacto | Evidência | Correção sugerida |
|---|---|---|---|
| `polls.post_id` não é unique apesar de relação `hasOne` | Múltiplas enquetes por post podem existir no banco | `create_polls_table.php:14-19`; `Post.php:90-93` | Unique em `post_id` após saneamento |
| `poll_votes` não garante que `option_id` pertence a `poll_id` | Voto semanticamente inconsistente | `create_poll_votes_table.php:14-21` | Validação por relação; modelagem/chave coerente |
| Capa de negócio sem unicidade | Duas fotos podem ser `is_cover=true` | `create_business_photos_table.php:14-20` | Transação e constraint/estratégia de capa |
| Claim sem expiração/unique/hash | Token reutilizável/colidível e exposto em claro | `create_businesses_table.php:33-35` | Tabela própria ou campos hash/expiry/attempts |
| Pontos sem idempotência | Eventos duplicados e total divergente | `create_point_events_table.php`; `AwardPointsAction.php:12-23` | Chave idempotente e reconciliação |
| Denúncia embutida no conteúdo | Só um reporte corrente, sem histórico/denunciante/decisão | migrations `add_reporting_fields_*`; `ReportContentAction.php` | Tabela `reports` quando moderação exigir histórico |
| Bairro como texto livre | Variação de grafia impede segmentação confiável | `users.neighborhood`; `businesses.neighborhood`; `posts.location` | Entidade/ID canônico antes de expansão |
| Horários em JSON com formatos históricos | Filtro inconsistente e difícil de consultar | migrations de normalização; `DatabaseSeeder.php:97`; `Business.php:127-152` | Um schema JSON versionado ou tabela de intervalos |
| Review sem check DB para 1–5 | Escrita fora da aplicação pode violar regra | `create_reviews_table.php`; validação em `ReviewSection` | Check constraint opcional + validação |

## Índices e consultas

Há índices úteis para status/data de posts, bairro/status e plano/status de negócios, categoria e promoções ativas, conforme migrations principais. As chaves únicas cobrem slugs, Google Place ID, pivots de favorito/salvo, voto polimórfico e review por usuário/negócio.

Possíveis ajustes devem ser guiados por `EXPLAIN` em dados reais. Candidatos atuais: índices para `reported_at`, `plan_upgrade_requested_at`, `is_sponsored` combinado com status/data e `event_starts_at`, além de paginação/viewport antes de adicionar índices indiscriminadamente. Busca `%termo%` não aproveitará B-tree; para o MVP, MySQL `LIKE` é uma decisão consciente, não motivo imediato para instalar Meilisearch.

## Dados locais observados

No ambiente auditado havia 12 usuários, 24 categorias, 28 negócios, 88 fotos, 12 posts, 32 comentários, 45 votos, 6 promoções, 27 reviews, 5 notificações, 2 eventos de ponto, 1 enquete/5 opções/8 votos e 9 jobs falhos. Esses números apenas ajudam a avaliar demonstrabilidade; não pertencem ao schema nem devem ser usados como métricas de produção.

## Tabelas/campos aparentemente obsoletos

Nenhuma tabela de domínio está claramente obsoleta. Os candidatos são infraestrutura não usada em todos os ambientes (`job_batches`, cache DB) e campos comerciais ainda sem ciclo completo (`plan_upgrade_requested_at`, `is_sponsored`). Eles devem ser mantidos até a decisão de produto, não removidos só por baixa utilização.
