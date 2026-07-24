# Mobile Notification Entrypoints Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Exibir o sino com contador na navbar mobile e oferecer a configuração de push após a instalação do PWA.

**Architecture:** Reutilizar o componente Livewire de notificações e o bottom sheet/JavaScript já existentes. Persistir apenas a resposta do onboarding no `localStorage`; nenhuma nova tabela, rota ou dependência.

**Tech Stack:** Laravel 12, Livewire 4, Blade, Tailwind CSS 4, JavaScript nativo e PWA APIs.

---

### Task 1: Sino mobile

**Files:**
- Modify: `resources/views/layouts/navigation.blade.php`
- Modify: `resources/views/livewire/notifications/notification-bell.blade.php`
- Test: `tests/Feature/PwaTest.php`

- [ ] **Step 1: Escrever o teste de renderização**

Adicionar:

```php
public function test_authenticated_mobile_navbar_has_one_persistent_notification_bell(): void
{
    $response = $this->actingAs(User::factory()->create())->get(route('home'));

    $response->assertOk()->assertSee('data-navbar-notification', false);
    $this->assertSame(1, substr_count($response->getContent(), route('notifications.index')));
}
```

- [ ] **Step 2: Executar o teste e confirmar falha**

Run: `vendor/bin/sail php artisan test --compact tests/Feature/PwaTest.php`

Expected: FAIL porque a navbar ainda contém somente a instância desktop.

- [ ] **Step 3: Reutilizar o componente na navbar mobile**

Mover a única instância existente para o grupo compartilhado de ações:

```blade
<div class="flex items-center gap-1 sm:gap-3">
    @auth
        <div data-navbar-notification>
            <livewire:notifications.notification-bell />
        </div>
        {{-- ações desktop existentes --}}
    @endauth
    {{-- botão hambúrguer existente --}}
</div>
```

Remover o link textual mobile e usar no dropdown:

```blade
class="absolute right-0 mt-2 w-[min(20rem,calc(100vw-2rem))] ..."
```

- [ ] **Step 4: Executar o teste**

Run: `vendor/bin/sail php artisan test --compact tests/Feature/PwaTest.php`

Expected: PASS.

### Task 2: Oferta de push após instalação

**Files:**
- Modify: `resources/views/components/pwa-install-prompt.blade.php`
- Modify: `resources/js/pwa.js`
- Test: `tests/Feature/PwaTest.php`

- [ ] **Step 1: Escrever o teste estático**

Adicionar:

```php
public function test_installed_app_offers_push_configuration_without_requesting_permission(): void
{
    $javascript = file_get_contents(resource_path('js/pwa.js'));

    $this->assertStringContainsString('PUSH_OFFER_DISMISS_STORAGE_KEY', $javascript);
    $this->assertStringContainsString("window.addEventListener('appinstalled'", $javascript);
    $this->assertStringContainsString('isStandalone()', $javascript);
    $this->assertStringContainsString('/minha-conta?tab=notifications', $javascript);
}
```

- [ ] **Step 2: Executar o teste e confirmar falha**

Run: `vendor/bin/sail php artisan test --compact tests/Feature/PwaTest.php`

Expected: FAIL pela ausência do fluxo de oferta de push.

- [ ] **Step 3: Implementar com o bottom sheet existente**

Adicionar seletores `data-pwa-title`, `data-pwa-install-description`, `data-pwa-confirm` e `data-pwa-dismiss` ao bottom sheet. Implementar o núcleo:

```js
async function showPushOffer() {
    if (!supportsPush() || Notification.permission === 'denied' || await currentSubscription()) {
        return;
    }

    const prompt = installPromptElement();
    prompt.dataset.mode = 'push';
    prompt.querySelector('[data-pwa-title]').textContent = 'Quer receber novidades da sua vizinhança?';
    prompt.querySelector('[data-pwa-confirm]').textContent = 'Configurar notificações';
    prompt.classList.remove('hidden');
}
```

O clique de confirmação grava a resposta e navega para `/minha-conta?tab=notifications`; o adiamento usa a mesma janela de 14 dias.

- [ ] **Step 4: Validar frontend e testes**

Run:

```bash
vendor/bin/sail npm run build
vendor/bin/sail php artisan test --compact tests/Feature/PwaTest.php
```

Expected: build e testes aprovados.

### Task 3: Regressão

- [ ] **Step 1: Formatar e executar a suíte**

Run:

```bash
vendor/bin/sail pint --dirty
vendor/bin/sail php artisan test --compact
```

Expected: todos os testes passam.

- [ ] **Step 2: Commit**

```bash
git add resources/views/layouts/navigation.blade.php resources/views/livewire/notifications/notification-bell.blade.php resources/views/components/pwa-install-prompt.blade.php resources/js/pwa.js tests/Feature/PwaTest.php
git commit -m "feat: surface notifications in the mobile PWA"
```
