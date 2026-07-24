# PWA e Web Push

Este documento registra a configuração e o roteiro de validação da instalação e das notificações Web Push do FalaVizin.

## Requisitos

- HTTPS válido em produção.
- Worker da fila ativo.
- Banco atualizado com as tabelas `push_subscriptions` e `notification_deliveries`.
- Um único par de chaves VAPID estável por ambiente.

## Chaves VAPID

Gere as chaves uma única vez. O comando abaixo apenas exibe os valores:

```bash
vendor/bin/sail php artisan webpush:vapid --show
```

No servidor, adicione os valores gerados a `/var/www/falavizin/.env`:

```dotenv
VAPID_PUBLIC_KEY=chave-publica-gerada
VAPID_PRIVATE_KEY=chave-privada-gerada
VAPID_SUBJECT=https://falavizin.com.br
```

A chave privada não deve ser versionada nem enviada ao navegador. Não rotacione essas chaves sem necessidade: uma troca invalida as subscriptions já registradas.

## Desenvolvimento

```bash
vendor/bin/sail up -d
vendor/bin/sail php artisan migrate
vendor/bin/sail npm run build
vendor/bin/sail php artisan queue:work
```

Service workers e Push API exigem contexto seguro. `localhost` é aceito pelos navegadores para desenvolvimento; ao testar por IP ou outro domínio local, use HTTPS.

## Deploy em produção

Antes do primeiro deploy, configure as três variáveis VAPID no `.env` do servidor. O pipeline existente publica a imagem e o script de deploy executa as migrations e recompila os caches.

Para aplicar manualmente no servidor:

```bash
cd /var/www/falavizin
docker compose --env-file .env -f compose.production.yaml pull
docker compose --env-file .env -f compose.production.yaml up -d
docker compose --env-file .env -f compose.production.yaml exec -T -u www-data web php artisan migrate --force
docker compose --env-file .env -f compose.production.yaml exec -T -u www-data web php artisan optimize
```

Confirme que o worker está ativo:

```bash
docker compose --env-file .env -f compose.production.yaml ps
docker compose --env-file .env -f compose.production.yaml logs --tail=100 worker
```

## Validação no Android

1. Abra `https://falavizin.com.br` no Chrome.
2. Navegue pela home ou pelo feed sem estar preenchendo um formulário.
3. Confirme que a sugestão de instalação aparece e que “Agora não” a adia.
4. Use “Instalar aplicativo” no menu para reabrir a opção manualmente.
5. Instale e confirme que o aplicativo abre em modo standalone, com ícone e cores corretos.
6. Faça login, abra **Minha conta → Notificações** e selecione ao menos um tipo de push.
7. Toque em “Receber notificações neste dispositivo” e aceite a permissão nativa.
8. Com outra conta, comente ou reaja a um conteúdo da primeira conta.
9. Confirme o recebimento e que o toque abre o conteúdo correto.
10. Desative neste dispositivo e confirme que outras sessões continuam intactas.

## Validação no iPhone ou iPad

1. Abra o site no Safari.
2. Confirme que a orientação mostra **Compartilhar → Adicionar à Tela de Início**.
3. Adicione o FalaVizin à Tela de Início e abra por esse ícone.
4. Já em modo instalado, faça login e ative push em **Minha conta → Notificações**.
5. A permissão nativa só deve aparecer após tocar no botão de ativação.
6. Valide comentário, resposta e reação com outra conta.

Web Push no iOS depende de uma versão compatível do sistema e do site aberto a partir da Tela de Início.

## Validação em desktop

1. No Chrome, Edge ou Safari compatível, use “Instalar aplicativo” no menu.
2. Confirme a abertura em janela standalone.
3. Ative o push explicitamente nas configurações.
4. Feche ou deixe a janela em segundo plano e gere uma interação com outra conta.
5. Confirme que uma janela existente recebe foco e navega ao conteúdo; sem janela aberta, uma nova deve ser criada.

## Segurança e comportamento offline

O service worker intercepta exclusivamente navegações `GET` da mesma origem. Ele não intercepta nem armazena respostas de:

- formulários e uploads;
- requisições Livewire;
- APIs;
- login e logout mutáveis;
- qualquer `POST`, `PUT`, `PATCH` ou `DELETE`.

Somente a página pública de indisponibilidade e os ícones do aplicativo são pré-armazenados. Páginas autenticadas e dados privados não são gravados em cache.

## Casos adicionais

- Ao negar a permissão, a tela orienta a alteração nas configurações do navegador e não tenta pedir novamente automaticamente.
- O logout remove a associação no servidor e cancela a subscription no navegador antes de encerrar a sessão.
- O mesmo endpoint não pode pertencer a duas contas. Uma ativação explícita o associa ao usuário atual.
- Push começa desabilitado por tipo; selecionar tipos é obrigatório antes da primeira ativação.
- O ledger `notification_deliveries` impede duplicidade por destinatário, classe da notificação, evento e canal.
- Endpoints que retornam `404` ou `410` são removidos automaticamente pelo canal Web Push.

## Limitações conhecidas

- Agrupamento de várias reações em uma única mensagem fica para uma evolução futura. A versão atual impede reenvio do mesmo evento, mas não agrega eventos diferentes.
- O fallback offline é propositalmente simples e não oferece leitura offline do feed.
- O visual e o momento exato da instalação variam conforme o navegador e o sistema operacional.
