# FalaVizin PWA e Web Push

## Objetivo

Transformar o FalaVizin em uma PWA instalável e adicionar Web Push ao sistema
de notificações existente, sem criar uma central paralela e sem armazenar
páginas autenticadas em cache.

## Estado atual

- O projeto usa Laravel Notifications com canal `database`, sino Livewire e
  página própria de notificações.
- `users.notification_preferences` é um JSON plano. Os valores atuais são
  consultados por `wantsEmailNotification()`.
- E-mails de moderação e plano são enfileirados após o commit. Notificações
  sociais atualmente usam apenas o banco.
- Comentários, respostas e reações em comentários são tratados por
  `CommentSection`.
- Votos em publicações são tratados por `VoteButtons`, mas ainda não geram
  notificação.
- A fila `database` e o worker de produção já existem.
- Não existem manifest, service worker, Push API, menções ou dependência Web
  Push.

## Decisão arquitetural

Usar `laravel-notification-channels/webpush` 11.0.0, versão estável resolvida
pelo Composer com Laravel 12.64, PHP 8.5 e as dependências atuais.
O pacote integra o canal Web Push às Notifications nativas, oferece múltiplas
subscriptions por usuário, VAPID e remoção de endpoints expirados.

Não será implementado um cliente próprio do protocolo Web Push nem uma segunda
tabela de notificações.

## Escopo de notificações

Receberão push:

- comentário na publicação do usuário;
- resposta ao comentário do usuário;
- reação positiva na publicação do usuário;
- reação positiva no comentário do usuário;
- aprovação ou rejeição de conteúdo do usuário;
- aprovação de upgrade do negócio do usuário.

Continuarão somente nos canais atuais:

- novas solicitações para administradores;
- novos pedidos para comerciantes;
- manifestação de interesse em pedidos;
- solicitação de upgrade enviada aos administradores;
- reset de senha.

Menções não entram no escopo porque o produto não possui esse recurso.

## Canais e preferências

A notificação interna continuará sendo criada independentemente de e-mail ou
push.

O JSON atual será mantido de forma retrocompatível:

```json
{
  "moderation": true,
  "plan_upgrade": true,
  "push": {
    "comment": false,
    "comment_vote": false,
    "post_vote": false,
    "moderation": false,
    "plan_upgrade": false
  }
}
```

As chaves planas existentes continuam representando e-mail. A chave `push`
representa o novo canal. A ausência de uma preferência push equivale a
`false`; nenhuma escolha existente será alterada e nenhuma migration de dados
será necessária.

`User::wantsPushNotification()` será a única fonte de decisão para o canal
push. A existência da subscription representa que o push está habilitado
naquele dispositivo.

## Subscriptions

Será usada a tabela `push_subscriptions` fornecida pelo pacote, associada ao
usuário por relacionamento polimórfico. Cada navegador/dispositivo terá sua
própria linha e o endpoint será único.

Rotas autenticadas e protegidas por CSRF:

- `POST /push-subscriptions`: cria, atualiza ou reassocia a subscription
  apresentada pelo navegador ao usuário autenticado;
- `DELETE /push-subscriptions`: remove somente a subscription do usuário
  autenticado indicada pelo endpoint.

Os dados serão validados por Form Requests. Endpoints deverão usar HTTPS e as
chaves de criptografia terão tamanho limitado. O controller chamará Actions
pequenas para manter o padrão do projeto.

O frontend não pedirá permissão automaticamente. A permissão nativa só será
aberta após o usuário escolher “Receber notificações neste dispositivo”.
Desativar removerá o endpoint no servidor e cancelará a subscription local.

Ao sair da conta, o JavaScript interceptará o envio do formulário de logout,
removerá a subscription atual do usuário e chamará `unsubscribe()` no
navegador antes de continuar. Uma falha de rede não bloqueará o logout: a
subscription local ainda será cancelada e o endpoint inválido será removido
pelo pacote na próxima tentativa de envio.

Na ativação, a interface exibirá os tipos de push disponíveis antes de pedir a
permissão nativa. Nenhum tipo virá selecionado e pelo menos um deverá ser
escolhido. A criação da subscription salvará apenas as escolhas explícitas.
Sincronizações posteriores do mesmo navegador não alterarão preferências.

## Fluxo de envio

As Notifications existentes ganharão condicionalmente
`WebPushChannel::class` em `via()`. Elas implementarão
`ShouldQueueAfterCommit`.

Conexões:

- `database`: `sync`, preservando a central atual;
- `mail`: conexão de fila configurada;
- Web Push: conexão de fila configurada.

Serão mantidas três tentativas, backoff de 60 e 300 segundos e timeout de 30
segundos. Falhas serão registradas com classe, ID da notificação e usuário,
sem endpoint, chaves ou payload privado.

O pacote removerá endpoints que o provedor informar como expirados.

## Eventos e idempotência

`CommentNotification` continuará distinguindo comentário e resposta pelo
`parent_id`. `CommentVoteNotification` será reutilizada.

Será criada `PostVoteNotification` para o voto `helpful`. Votos
`not_helpful` não gerarão alerta.

`VoteButtons` continuará impedindo a própria interação. A idempotência já
persistida em `point_events.idempotency_key` será reutilizada para que remover
e recriar o mesmo voto não crie um novo evento de domínio.

Também será criada a tabela `notification_deliveries`, com restrição única
para:

```text
recipient_id + notification_type + event_key + channel
```

`event_key` representa a identidade estável da entidade e do autor da ação:

- comentário/resposta: `comment:{comment_id}`;
- reação em comentário: `comment:{comment_id}:voter:{user_id}`;
- reação em post: `post:{post_id}:voter:{user_id}`;
- moderação: `content:{type}:{entity_id}:{decision}`;
- upgrade: `business:{business_id}:upgrade-approved`.

Cada Notification usará `shouldSend()` para reservar atomicamente a entrega
por canal antes do envio. Sucesso marcará a entrega como concluída; uma falha
reportada pelo Laravel removerá a reserva para permitir retry. Assim,
notificação interna, e-mail e push têm idempotência persistida independente.

O ID da Notification também será enviado como `tag`, fazendo retries ou
reentregas substituírem a notificação visual anterior no navegador.

## Payload e navegação

O payload terá apenas:

- título curto;
- texto sem conteúdo sensível;
- ícone e badge públicos;
- caminho interno relativo;
- tag/ID da Notification.

O service worker validará que a URL pertence à origem do FalaVizin. Ao clicar,
buscará uma janela da mesma origem, chamará `focus()` e `navigate()`; sem
janela aberta, usará `openWindow()`.

Rotas protegidas continuarão usando o redirecionamento `intended` do Laravel
quando a sessão tiver expirado.

## PWA

O manifest terá:

- `name` e `short_name`: `FalaVizin`;
- `display`: `standalone`;
- `start_url`: `/`;
- `scope`: `/`;
- tema `#FD5C3E`;
- fundo `#FAFAF9`;
- ícones 192 e 512;
- variantes maskable;
- ícone Apple Touch.

Os ícones serão derivados do logotipo existente em
`public/assets/images/logo.png`.

Os layouts terão manifest, `theme-color`, metadados Apple e ícone. No modo
standalone, o CSS respeitará safe areas sem criar uma navegação alternativa.

## Service worker e offline

O service worker será servido em `/sw.js`, dentro do escopo raiz.

Ele armazenará somente:

- `offline.html`;
- ícones e logotipo públicos necessários ao fallback.

Requisições de navegação continuarão network-first. Somente uma falha real de
rede retornará `offline.html`. O handler aceitará exclusivamente requisições
com método `GET` e `request.mode === 'navigate'`. `POST`, `PUT`, `PATCH`,
`DELETE`, uploads, API, Livewire, logout e qualquer requisição mutável serão
ignorados. Respostas HTTP, páginas autenticadas, cookies e formulários nunca
serão gravados no cache.

Cada versão usará um nome de cache diferente e removerá caches antigos na
ativação. O ciclo padrão do service worker será preservado: uma versão nova
aguarda as abas antigas encerrarem antes de assumir o controle.

## Instalação

Um componente global e o módulo `resources/js/pwa.js` tratarão:

- `beforeinstallprompt`;
- ação explícita no botão “Instalar”;
- `appinstalled`;
- detecção de `display-mode: standalone`;
- detecção de recursos disponíveis;
- adiamento de 14 dias em `localStorage`;
- detecção de iOS/iPadOS por plataforma, recursos touch e fallback de
  user-agent;
- instruções “Compartilhar → Adicionar à Tela de Início” somente quando for
  iOS/iPadOS, não estiver em standalone e não houver `beforeinstallprompt`;
- opção permanente “Instalar aplicativo” no menu/configurações.

A sugestão automática aparecerá em páginas públicas e autenticadas de
navegação segura, incluindo o feed. Nunca aparecerá em login, cadastro,
edição, criação, configurações ou envio de formulário.

## Interface de configurações

A seção atual de notificações será ampliada com:

- status do suporte e da permissão;
- botão para ativar push neste dispositivo;
- botão para desativar somente neste dispositivo;
- orientação para permissão bloqueada;
- aviso de que outros dispositivos continuam ativos;
- preferências push por tipo de evento;
- preferências de e-mail atuais, apenas onde o e-mail já existe.

Não há modo escuro global no projeto, portanto o componente seguirá apenas o
tema claro atual. A interface manterá labels, foco visível, `role="switch"` e
mensagens de estado acessíveis.

## Arquivos previstos

Criações principais:

- migration publicada pelo pacote para `push_subscriptions`;
- migration e model para `notification_deliveries`;
- trait compartilhada de idempotência para as Notifications;
- `app/Actions/UpdatePushSubscriptionAction.php`;
- `app/Actions/DeletePushSubscriptionAction.php`;
- `app/Http/Controllers/PushSubscriptionController.php`;
- Form Requests de criação e remoção;
- `app/Notifications/PostVoteNotification.php`;
- `resources/js/pwa.js`;
- `resources/views/components/pwa-install-prompt.blade.php`;
- `public/manifest.webmanifest`;
- `public/sw.js`;
- `public/offline.html`;
- ícones em `public/assets/icons/`;
- testes de subscriptions, PWA e Web Push;
- `docs/PWA_VALIDATION.md`.

Alterações principais:

- `composer.json` e `composer.lock`;
- `.env.example`;
- `routes/web.php`;
- `app/Models/User.php`;
- `app/Providers/AppServiceProvider.php`;
- Notifications incluídas no escopo;
- `app/Notifications/QueuesMailAfterCommit.php`;
- `app/Actions/AwardPointsAction.php`;
- `app/Livewire/Feed/VoteButtons.php`;
- `app/Livewire/Profile/NotificationSettings.php`;
- view das preferências;
- layouts, navegação, `resources/js/app.js` e `resources/css/app.css`;
- testes relacionados a comentários, votos e filas.

## Configuração

Variáveis obrigatórias:

```env
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=https://falavizin.com.br
```

A chave privada ficará apenas no ambiente. As chaves devem permanecer estáveis
entre deploys, pois trocá-las invalida subscriptions existentes.

O Composer validou `laravel-notification-channels/webpush:^11.0` contra o
`composer.lock`, o PHP e o Laravel atuais. A resolução adiciona o pacote
11.0.0 e `minishlink/web-push` 10.1.0 sem atualizar ou remover dependências
existentes.

## Verificação

Testes automatizados cobrirão:

- autenticação e ownership das rotas;
- criação, atualização, remoção e deduplicação de subscriptions;
- cancelamento da subscription durante logout;
- preferências push habilitadas e desabilitadas;
- escolha explícita de tipos durante a ativação;
- ausência de autonotificação;
- persistência da notificação interna;
- enfileiramento do Web Push;
- URL, tag e payload;
- idempotência de voto;
- idempotência persistida por destinatário, tipo, evento e canal;
- comportamento definido pelo pacote para endpoint expirado;
- manifest e arquivos públicos.

Também serão executados:

```bash
vendor/bin/sail php artisan migrate
vendor/bin/sail php artisan test
vendor/bin/sail vendor/bin/pint --test
vendor/bin/sail npm run build
```

O roteiro manual documentará Android/Chromium, desktop, Safari no macOS e PWA
instalada no iPhone/iPad.

## Fora do escopo

- experiência offline completa;
- cache de conteúdo dinâmico;
- agrupamento temporal de várias pessoas em uma única mensagem;
- menções;
- push para todos os tipos administrativos;
- aplicativo nativo;
- novo provedor de filas.

O agrupamento poderá ser adicionado quando o volume real justificar uma
estrutura persistente de agregação.
