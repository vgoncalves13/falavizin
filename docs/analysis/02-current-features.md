# Inventário de funcionalidades atuais

**Critério de contagem:** 36 capacidades agrupadas em 12 módulos. “Funcional” significa que o caminho feliz existe e é testado ou demonstrável; não significa pronto para produção. Problemas transversais estão em `04-technical-audit.md`.

## 1. Autenticação e conta

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F01 | Registro e sessão | Visitante registra, entra e sai | Breeze, `RegisteredUserController`, `AuthenticatedSessionController`; views `auth/*` | `/register`, `/login`, `/logout` | **Funcional.** `routes/auth.php:14-32`; testes em `tests/Feature/Auth` |
| F02 | Recuperação e confirmação de senha | Token por e-mail, redefinição e confirmação | Controllers de auth e views correspondentes | `/forgot-password`, `/reset-password/{token}`, `/confirm-password` | **Funcional.** `routes/auth.php:34-57`; testes de auth |
| F03 | Verificação de e-mail | Scaffold exibe aviso e aceita link | `EmailVerificationPromptController`, `VerifyEmailController` | `/verify-email*` | **Parcial.** Rotas existem (`routes/auth.php:38-48`), mas `User` não implementa `MustVerifyEmail` (`app/Models/User.php:5,13-16`) |
| F04 | Perfil e exclusão da conta | Usuário altera dados; exclusão desvincula negócios | `ProfileController`; `profile/edit.blade.php` | `/profile` GET/PATCH/DELETE | **Funcional com ressalvas.** `app/Http/Controllers/ProfileController.php:14-70`; não há transação/auditoria |
| F05 | Minha conta | Agrega posts, negócios, comentários, favoritos e salvos | `ProfileController::account`; `profile/account.blade.php` | `/minha-conta` | **Funcional, pouco escalável.** Carrega coleções sem paginação (`ProfileController.php:14-24`) |

## 2. Perfis públicos e descoberta de pessoas

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F06 | Perfil público | Visitante vê conteúdo aprovado de um usuário | `UserProfileController`; `users/show.blade.php` | `/moradores/{user}` | **Funcional.** `app/Http/Controllers/UserProfileController.php:12-27`; sem paginação |

## 3. Feed e publicações

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F07 | Lista do feed | Filtra categoria/bairro e ordena por recente ou tendência | `FeedList`; `feed/index.blade.php`; `post-card` | `/feed` | **Funcional.** Eager loading e paginação em `app/Livewire/Feed/FeedList.php:38-60` |
| F08 | Criar publicação | Autenticado informa categoria, conteúdo, imagem e variações; limite 5/h; nasce pendente | `CreatePost`, `CreatePostAction`; `feed/create.blade.php` | `/criar-post` | **Funcional.** `app/Livewire/Feed/CreatePost.php:39-125`; `app/Actions/CreatePostAction.php:17-57` |
| F09 | Ver publicação | Exibe conteúdo, autor, categoria e relacionados | `PostController::show`; `PostPolicy`; `feed/show.blade.php` | `/feed/{post:slug}` | **Funcional.** B004 restringe conteúdo não aprovado a autor/admin |
| F10 | Editar/excluir publicação | Autor/admin edita ou remove via Policy | `EditPost`, `PostController::destroy`, `PostPolicy` | `/feed/{post}/editar`, DELETE `/feed/{post}` | **Funcional.** `routes/web.php:47-50`; `app/Policies/PostPolicy.php` |
| F11 | Salvar e compartilhar | Usuário alterna item salvo; compartilhamento usa Web Share/clipboard | `SaveButton`, componente Blade `share-button` | Ações Livewire/Alpine no detalhe/card | **Funcional.** `app/Livewire/Feed/SaveButton.php`; `resources/views/components/share-button.blade.php` |

## 4. Interações comunitárias

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F12 | Comentários e respostas | Autenticado comenta, responde, edita e exclui; 15 comentários/h | `CommentSection`; `comment-section.blade.php` | Ações Livewire no post | **Funcional com falha de integridade.** `CommentSection.php:46-151`; pai não é escopado ao post (`:103-122`) |
| F13 | Votos em posts | Único voto por usuário/alvo; alterna útil/não útil | `VoteButtons`; model polimórfico `Vote` | Ação Livewire | **Funcional com possível abuso.** `VoteButtons.php:35-63`; pontos podem ser ganhos repetidamente |
| F14 | Votos em comentários | Usuário marca comentário útil | `CommentSection::vote` | Ação Livewire | **Funcional com ressalva.** Aceita ID arbitrário (`CommentSection.php:174-203`) |
| F15 | Denúncias | Usuário informa motivo; item recebe `reported_at/by/reason`; limite 5/dia | `ReportModal`; `ReportContentAction` | Ação Livewire | **Funcional.** `app/Livewire/Shared/ReportModal.php`; `app/Actions/ReportContentAction.php` |

## 5. Eventos, enquetes e resolução

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F16 | Publicação de evento | Post pode receber datas inicial/final | `CreatePost`, campos em `posts` | `/criar-post` | **Funcional básico.** `database/migrations/2026_02_06_163344_add_event_fields_to_posts_table.php` |
| F17 | Enquetes | Autor cria opções e término; usuário vota uma vez | `PollVote`, `Poll`, `PollOption`, `PollVote` model | Ação Livewire no post | **Funcional com erro possível.** Opção não é validada contra a enquete (`app/Livewire/Feed/PollVote.php:30-54`) |
| F18 | Resolução de problemas | Autor/admin altera status de resolução | `ResolutionStatus`; `PostResolutionStatus` | Ação Livewire | **Funcional.** `app/Livewire/Feed/ResolutionStatus.php`; migration `add_resolution_fields_to_posts_table.php` |

## 6. Negócios e serviços locais

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F19 | Catálogo e filtros | Busca, categoria, destaque, “aberto agora” e lista/mapa | `BusinessList`; `businesses/index.blade.php` | `/servicos` | **Parcial.** Funciona, mas “aberto agora” carrega tudo em memória (`BusinessList.php:52-66`) |
| F20 | Perfil do negócio | Exibe capa, galeria, contato, endereço, horário, mapa e ofertas | `BusinessController::show`; `BusinessPolicy`; `businesses/show.blade.php` | `/servicos/{business:slug}` | **Funcional.** B003 corrigiu o popup e B004 restringiu status não aprovado |
| F21 | Cadastro/edição manual | Proprietário cadastra e edita dados/fotos | `BusinessForm`, Actions e Requests; views create/edit | `/cadastrar-negocio`, `/meu-negocio/{business}/editar` | **Parcial.** Horários digitados não são persistidos (`BusinessForm.php:118-167`; Actions omitem o campo) |
| F22 | Galeria de fotos | Upload, redimensionamento, capa e ordenação básica | `BusinessPhoto`, `PhotoGallery`, Actions | Perfil/formulário | **Funcional com ressalvas.** Não há constraint de capa única nem limpeza completa de arquivos |
| F23 | Favoritar negócio | Usuário alterna favorito e consulta na conta | `FavoriteButton`; pivot `business_user_favorites` | Ação Livewire | **Funcional.** `app/Livewire/Business/FavoriteButton.php`; migration da pivot |
| F24 | Avaliações e resposta | Uma avaliação por usuário/negócio; proprietário responde | `ReviewSection`; `Review`, Policy | Ação Livewire | **Funcional com vulnerabilidade.** Resposta busca review fora do negócio corrente (`ReviewSection.php:67-106`) |
| F25 | Reivindicação | Usuário pede token por e-mail e confirma propriedade | `ClaimBusinessController`, `ClaimBusinessAction`, mail | POST `/servicos/{business}/reivindicar`, GET `/reivindicar/{token}` | **Incompleta/insegura.** Prova o e-mail do solicitante, não vínculo com o negócio; sem expiração (`ClaimBusinessController.php:15-41`) |

## 7. Promoções e comercial

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F26 | Catálogo de promoções | Visitante vê promoções ativas agrupadas por negócio | `PromotionController::index`; `promotions/index.blade.php` | `/promocoes` | **Funcional.** `app/Models/Promotion.php:54-62` |
| F27 | Gerenciar promoção | Proprietário cria, edita e exclui promoções | `PromotionForm`, Controller, Request, Policy | rotas em `/meu-negocio/*/promocoes` | **Parcial/vulnerável.** Livewire ignora limite semanal e não autoriza o negócio na criação (`PromotionForm.php:64-90`) |
| F28 | Planos free/featured | Usuário solicita upgrade; admin aprova/rejeita | `BusinessPlanController`; views admin/plans | `/meu-negocio/{business}/plano`, `/admin/planos*` | **Parcial.** Workflow administrativo existe; não há cobrança nem definição forte de entitlement |
| F29 | Posts patrocinados | Admin alterna um flag de patrocínio | `PostSponsorController`, `SponsoredPostsController` | `/admin/posts/{post}/patrocinar`, `/admin/posts-patrocinados*` | **Parcial/duplicada.** Dois caminhos administrativos sobrepõem responsabilidade; não há vigência |

## 8. Moderação e administração

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F30 | Fila de moderação | Admin filtra pendentes/denunciados, aprova/rejeita individualmente ou em lote | `ModerationController`; `admin/moderation/index.blade.php` | `/admin/moderacao*` | **Funcional com lacunas.** `routes/web.php:70-78`; ausência de trilha de auditoria e invalidação incompleta de cache |
| F31 | Dashboard e configurações | Métricas, configurações e coordenadas do bairro | `StatsController`, `SettingsController`; views admin | `/admin`, `/admin/configuracoes` | **Funcional básico.** `routes/web.php:80-89`; consultas usam SQL específico do MySQL |

## 9. Notificações

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F32 | Central de notificações | Sino, lista, marcar lida; banco e alguns e-mails | componentes em `app/Livewire/Notifications`; classes em `app/Notifications` | `/notificacoes` | **Parcial.** Canais existem, mas notificações de e-mail são síncronas e sem preferências |

## 10. Reputação e inteligência comunitária

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F33 | Pontos e ranking | Eventos concedem pontos e ranking lista moradores | `AwardPointsAction`, `PointEvent`, `RankingController` | `/ranking` | **Parcial/com erro.** Eventos não são idempotentes (`AwardPointsAction.php:12-23`) |
| F34 | Pulso do bairro | Agrega problemas, resoluções e categorias | `NeighborhoodPulse`, `PulseController`; home/página própria | `/pulso-do-bairro` | **Funcional demonstrável.** `app/Livewire/Home/NeighborhoodPulse.php`; depende da qualidade dos dados |

## 11. Busca, categorias e SEO

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F35 | Busca e sitemap | Pesquisa posts/negócios; sitemap XML; páginas de categoria | `SearchController`, `SitemapController`, `CategoryController` | `/buscar`, `/sitemap.xml`, `/categoria/{category:slug}` | **Funcional com escala limitada.** `LIKE` é coerente com MVP; sitemap carrega todos os registros (`SitemapController.php:16-28`) |

## 12. Importação e enriquecimento externo

| ID | Funcionalidade | Fluxo, regras e envolvidos | Implementação e telas | Rotas | Status/evidência |
|---|---|---|---|---|---|
| F36 | Importar Google Places | Admin/CLI pesquisa, evita duplicatas e cria negócios; UI despacha enriquecimento | `GooglePlacesService`, `GooglePlacesImport`, command, `EnrichBusinessFromGoogle` | `/admin/importar-google`; `businesses:import-google` | **Parcial.** CLI e UI divergem; nove jobs locais falharam na observação; faltam timeout/retry adequados |

## Resumo por status

- **Funcionais no caminho feliz:** F01, F02, F04, F06–F11, F15–F16, F18, F20, F22–F23, F26, F30–F31, F34–F35 (20).
- **Parciais ou com ressalvas relevantes:** F03, F05, F12–F14, F17, F19, F21, F24–F25, F27–F29, F32–F33, F36 (16).
- **Aparentemente abandonadas/não utilizadas:** não contam entre as 36 capacidades; views scaffold e placeholders estão listados em `03-incomplete-features.md`.

Esses números são uma classificação de auditoria, não uma métrica de cobertura ou prontidão comercial.

## Mapa técnico por módulo

Este mapa complementa as linhas F01–F36 e explicita os artefatos de dados e execução compartilhados por cada funcionalidade.

| Módulo | Models/tabelas | Controllers/Actions/Jobs | Livewire/views principais |
|---|---|---|---|
| Autenticação e conta | `User`, `users`, `password_reset_tokens`, `sessions` | Controllers em `app/Http/Controllers/Auth`; `ProfileController` | `resources/views/auth`, `profile` |
| Perfis públicos | `User`, posts, businesses | `UserProfileController` | `users/show.blade.php` |
| Feed | `Post`, `Category`, posts/categories, pivot `post_user_saves` | `PostController`, `CreatePostAction` | `FeedList`, `CreatePost`, `EditPost`, `SaveButton`; views `feed/*` |
| Interações | `Comment`, `Vote`; comments/votes e campos reportáveis | `ReportContentAction` | `CommentSection`, `VoteButtons`, `ReportModal` |
| Eventos/enquetes/resolução | `Post`, `Poll`, `PollOption`, `PollVote`; tabelas homônimas | criação dentro de `CreatePostAction` | `PollVote`, `ResolutionStatus` |
| Negócios | `Business`, `BusinessPhoto`, `Review`, pivots favorites | `BusinessController`, create/update/claim Actions, Policies | `BusinessList`, `BusinessForm`, `PhotoGallery`, `FavoriteButton`, `ReviewSection` |
| Promoções/comercial | `Promotion`, `Business`, `Post`; promotions e campos de plano/patrocínio | `PromotionController`, `BusinessPlanController`, controllers de sponsor | `PromotionForm`; views `promotions/*`, `admin/sponsored-posts` |
| Moderação/admin | models reportáveis e `Setting` | `ModerationController`, `StatsController`, `SettingsController` | views `admin/*`, `AppSettings` |
| Notificações | `DatabaseNotification`, tabela notifications | classes em `app/Notifications`, mail de claim | `NotificationBell`; `notifications/index` |
| Reputação/Pulso | `PointEvent`, `User`, `Post` | `AwardPointsAction`, `RankingController`, `PulseController` | `NeighborhoodPulse`; views ranking/pulse |
| Busca/SEO | `Post`, `Business`, `Category` | `SearchController`, `SitemapController`, `CategoryController` | views `search`, `categories`, XML sitemap |
| Google Places | `Business`, `BusinessPhoto`, settings/jobs/failed_jobs | `GooglePlacesService`, command, `EnrichBusinessFromGoogle` | `GooglePlacesImport`; view admin de importação |
