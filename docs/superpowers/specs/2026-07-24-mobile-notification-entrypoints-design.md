# Sino mobile e oferta de push após instalação

## Objetivo

Manter o acesso às notificações sempre visível na navbar mobile e oferecer a configuração de Web Push logo após a instalação do PWA, sem ativar preferências silenciosamente nem abrir a permissão nativa sem ação do usuário.

## Navbar mobile

- Reutilizar o componente Livewire `notifications.notification-bell` já usado no desktop.
- Renderizá-lo para usuários autenticados entre o logo e o botão hambúrguer.
- Manter contador, polling, marcação de leitura e dropdown idênticos ao desktop.
- Ajustar o dropdown para caber na viewport mobile.
- Remover o link duplicado “Notificações” de dentro do menu mobile.

## Oferta após instalação

- Reutilizar o bottom sheet já existente para instalação, com um modo de oferta de notificações.
- Após `appinstalled`, mostrar: “Quer receber novidades da sua vizinhança?”.
- Botões: “Configurar notificações” e “Agora não”.
- “Configurar notificações” abre `/minha-conta?tab=notifications`; se não houver sessão, o middleware de autenticação preserva o destino após o login.
- No primeiro uso em modo standalone, repetir a oferta caso ela ainda não tenha sido respondida.
- Não mostrar se a Push API não for suportada, se a permissão estiver bloqueada ou se já existir uma subscription neste navegador.
- “Agora não” adia a oferta por 14 dias.
- A oferta nunca chama `Notification.requestPermission()` diretamente. A permissão continua sendo solicitada apenas pelo botão explícito da tela de configurações.

## Persistência local

Usar `localStorage` para registrar:

- data de adiamento da oferta;
- confirmação de que o usuário escolheu configurar notificações.

Nenhuma nova tabela ou dependência será criada.

## Testes

- Teste de renderização confirma que o sino mobile usa o mesmo componente e que o link duplicado foi removido.
- Teste estático confirma os gatilhos `appinstalled`, standalone e o prazo de 14 dias.
- Executar testes focados, suíte completa, Pint e build Vite.

## Critérios de aceite

- Usuário autenticado sempre vê sino e contador na navbar mobile.
- O dropdown abre sem ultrapassar a largura da tela.
- Após instalar ou abrir pela primeira vez em standalone, aparece a oferta de push.
- A oferta respeita suporte, permissão, subscription existente e adiamento.
- Nenhuma preferência é ativada automaticamente.
