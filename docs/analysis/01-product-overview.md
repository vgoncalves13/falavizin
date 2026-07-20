# Visão geral do produto

**Data da análise:** 20/07/2026  
**Revisão analisada:** `eec07a1` (com alteração local preexistente em `bootstrap/app.php`)  
**Legenda:** **Fato** = comprovado no repositório; **Inferência** = interpretação fundamentada; **Sugestão** = proposta futura.

## Síntese

**Fato.** O Hub do Bairro é um portal comunitário hiperlocal que reúne feed de moradores, catálogo de negócios e prestadores, promoções e ferramentas de moderação. A intenção original está registrada em `CLAUDE.md:8-17`; a aplicação atual confirma esse núcleo pelas rotas públicas de feed, serviços, promoções, busca e perfis (`routes/web.php:25-43`).

**Inferência.** O problema central é a fragmentação da informação local: avisos, pedidos, eventos e problemas circulam em canais distintos, enquanto comerciantes têm pouca descoberta digital de baixo custo. O produto tenta ser simultaneamente mural comunitário e guia comercial de um único bairro.

## Público e papéis

| Papel | Capacidades observadas | Evidência |
|---|---|---|
| Visitante | Consultar home, feed, posts, negócios, promoções, busca, ranking e perfis | `routes/web.php:25-43` |
| Morador autenticado | Publicar, comentar, votar, salvar, denunciar, avaliar e favoritar | `routes/web.php:45-67`; `app/Livewire/Feed/*`; `app/Livewire/Business/*` |
| Comerciante | Possuir/reivindicar negócio, editar perfil, responder avaliações e criar promoções | `app/Policies/BusinessPolicy.php:16-39`; `app/Http/Controllers/ClaimBusinessController.php:15-41` |
| Administrador | Moderar conteúdo, importar negócios, administrar planos, patrocínios e configurações | `routes/web.php:70-89`; `app/Http/Middleware/EnsureUserIsAdmin.php:14-23` |

Não existe papel separado de moderador nem tabela de papéis. A autorização administrativa depende de `users.is_admin` (`database/migrations/0001_01_01_000000_create_users_table.php:15-24`).

## Fluxos principais

1. Visitante descobre conteúdo pela home, busca, feed ou catálogo.
2. Usuário cria um post, opcionalmente com imagem, evento ou enquete; o conteúdo nasce pendente e segue para moderação (`app/Actions/CreatePostAction.php:17-57`).
3. A comunidade comenta, vota, salva, denuncia e acompanha a resolução de problemas (`app/Livewire/Feed/CommentSection.php`; `VoteButtons.php`; `SaveButton.php`).
4. Um negócio é cadastrado manualmente ou importado do Google Places, recebe fotos/dados e pode ser reivindicado (`app/Actions/CreateBusinessAction.php`; `app/Console/Commands/ImportBusinessesFromGoogle.php`; `app/Actions/ClaimBusinessAction.php`).
5. Proprietários publicam promoções e respondem avaliações; administradores controlam destaque e patrocínio (`app/Livewire/Business/PromotionForm.php`; `ReviewSection.php`; `app/Http/Controllers/Admin/BusinessPlanController.php`).
6. Administradores aprovam/rejeitam conteúdo e tratam denúncias (`app/Http/Controllers/Admin/ModerationController.php`).

## Stack e estrutura

| Camada | Implementação encontrada |
|---|---|
| Backend | PHP 8.5.6 no ambiente auditado; Laravel 12.54.1 (`composer.json:10-18`) |
| Frontend | Blade, Livewire 4.2.1, Alpine.js e Tailwind CSS 4 (`composer.json:14`; `package.json:12-18`) |
| Banco | MySQL no ambiente atual; migrations compatíveis em sua maioria com Laravel (`database/migrations`) |
| Filas/cache/sessão | Driver `database` no ambiente atual; jobs de enriquecimento assíncrono (`app/Jobs/EnrichBusinessFromGoogle.php`) |
| Imagens | Laravel Storage no disco público e Intervention Image (`composer.json:13`; `app/Actions/CreateBusinessAction.php:35-67`) |
| Testes | PHPUnit 11, testes Feature e Unit (`phpunit.xml`; `tests`) |
| Ambiente local | Laravel Sail/Docker Compose com app, MySQL, Redis, Meilisearch, Mailpit e Selenium (`compose.yaml:24-113`) |

A organização segue Models, Actions, Controllers, Form Requests, Policies, Livewire e componentes Blade. A direção é saudável, mas há fluxos duplicados entre controllers/requests e componentes Livewire, com regras divergentes.

## Integrações externas

- **Google Places via RapidAPI:** pesquisa, detalhes e fotos (`config/services.php:38-41`; `app/Services/GooglePlacesService.php:10-96`).
- **OpenStreetMap/Leaflet:** mapa do catálogo e perfil; Leaflet é carregado de CDN (`resources/views/businesses/index.blade.php:75-128`; `resources/views/businesses/show.blade.php:311-353`).
- **SMTP:** reivindicação e notificações de moderação (`app/Mail/BusinessClaimMail.php`; `app/Notifications`).
- **Google Fonts:** tipografia remota no layout (`resources/views/layouts/app.blade.php:16-24`).
- **Storage público:** imagens de posts, negócios e promoções.

Não há pagamentos, chat, push, aplicativo móvel, Elasticsearch/Meilisearch em uso, ou integração oficial com serviços públicos.

## Autenticação e controle de acesso

O projeto usa o scaffold do Breeze: registro, sessão, recuperação e confirmação de senha (`routes/auth.php:14-59`). Policies protegem operações de post, negócio e promoção; um middleware próprio protege o admin (`bootstrap/app.php:14-19`).

Há uma inconsistência importante: as rotas de verificação de e-mail existem, mas `User` não implementa `MustVerifyEmail` (`app/Models/User.php:5,13-16`). Portanto, a aplicação não exige verificação de e-mail. Além disso, alguns métodos Livewire confiam na autorização realizada ao montar a tela ou aceitam IDs não relacionados ao recurso corrente; os casos estão detalhados em `04-technical-audit.md`.

## Separação de dados

**Fato.** Não existe multi-tenancy, organização ou entidade `neighborhood`. Bairro é texto livre em `users.neighborhood` e `businesses.neighborhood`; posts usam `location` (`database/migrations/0001_01_01_000000_create_users_table.php:20-21`; `database/migrations/2026_02_05_200750_create_businesses_table.php:22-24`).

O filtro “meu bairro” compara o texto do usuário no feed (`app/Livewire/Feed/FeedList.php:38-60`). Negócios usam busca textual (`app/Models/Business.php:102-105`). Configurações guardam nome e coordenadas de um bairro para importação, mas não isolam dados (`app/Models/Setting.php`; `app/Livewire/Admin/GooglePlacesImport.php:42-60`).

**Inferência.** O produto está desenhado para operar inicialmente em um único bairro, hoje apresentado na interface como Jardim América (`resources/views/home/index.blade.php:111`). Expandir geograficamente exigirá modelagem explícita antes de prometer isolamento ou segmentação confiável.

## Fontes e limites da análise

Foram examinados código, rotas, migrations, models, views, testes, configuração, `README.md`, `CLAUDE.md`, locks e histórico Git. Em 20/07/2026 foram executados sem mutação de dados: lista de rotas, status das migrations, suíte de testes, build, validação Composer, auditorias de dependência e Pint. Dados do banco local são observações do ambiente, não características garantidas do produto. Não foi medida cobertura porque nenhum relatório de cobertura foi gerado.

## Leitura do histórico Git

O histórico vai de 16 a 20/03/2026. Os commits `44bd6ff`, `047e9ed`, `ea87bf6` e `f68cbd0` registram as quatro semanas do roadmap original; `074e69e` inicia explicitamente o pós-MVP com moderação prévia. Depois vieram, em commits separados, notifications (`582542a`), reviews (`d7d13bc`), pontos/enquetes (`7e54e7a`), ranking (`af59910`), Pulso (`8bdd074`), mapas (`466de43`) e redesign da home (`eec07a1`).

**Inferência.** O projeto não foi abandonado no esqueleto inicial: houve uma expansão rápida e concentrada. Essa velocidade explica a boa amplitude funcional e também divergências entre caminhos paralelos — por exemplo, `d650eac` anuncia limite semanal de promoção, mas a regra ficou no Form Request e não no componente Livewire usado pela UI.
