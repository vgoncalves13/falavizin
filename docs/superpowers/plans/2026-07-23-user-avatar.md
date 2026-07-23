# User Avatar Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permitir upload de foto de perfil e exibir o avatar manual ou do Google em todo o sistema.

**Architecture:** Reutilizar `users.avatar_url`. Uma Action processará uploads no disk `public`; um componente Blade único resolverá URL local/remota e fallback por inicial. O fluxo Google continuará preenchendo apenas avatares ausentes.

**Tech Stack:** Laravel 12, Blade, Tailwind CSS 4, Intervention Image 3, PHPUnit.

---

### Task 1: Upload e persistência do avatar

**Files:**
- Create: `app/Actions/UpdateProfileAction.php`
- Modify: `app/Http/Requests/ProfileUpdateRequest.php`
- Modify: `app/Http/Controllers/ProfileController.php`
- Modify: `resources/views/profile/edit.blade.php`
- Test: `tests/Feature/ProfileTest.php`

- [ ] **Step 1: Escrever testes de upload e validação**

Adicionar testes com `Storage::fake('public')` e `UploadedFile::fake()->image()` comprovando que JPEG/PNG/WebP de até 5 MB é salvo em `avatars/`, que `avatar_url` é atualizado e que arquivo não-imagem é rejeitado.

- [ ] **Step 2: Executar o teste e confirmar falha**

Run: `php artisan test --compact tests/Feature/ProfileTest.php`

Expected: FAIL porque `avatar` ainda não é validado nem persistido.

- [ ] **Step 3: Validar o arquivo**

Adicionar ao `ProfileUpdateRequest::rules()`:

```php
'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
```

- [ ] **Step 4: Implementar a Action mínima**

Criar `UpdateProfileAction::execute(User $user, array $data): void`. Remover `avatar` dos dados pessoais, processar a imagem com `ImageManager(new Driver)->read(...)->scaleDown(512, 512)->toWebp(85)`, salvar em `avatars/{uuid}.webp`, atualizar o usuário e só depois remover o arquivo local anterior. URLs iniciadas por `http://` ou `https://` nunca devem ser apagadas pelo Storage.

- [ ] **Step 5: Usar a Action no controller**

Substituir o `fill/save` de `ProfileController::update()` por:

```php
$oldEmail = $request->user()->email;
$action->execute($request->user(), $request->validated());

if ($oldEmail !== $request->user()->email) {
    $request->user()->forceFill(['email_verified_at' => null])->save();
}
```

- [ ] **Step 6: Adicionar o campo na edição**

Alterar o form para `enctype="multipart/form-data"` e adicionar um `<input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">` com preview Alpine usando `URL.createObjectURL()`.

- [ ] **Step 7: Executar teste e commitar**

Run: `php artisan test --compact tests/Feature/ProfileTest.php`

Expected: PASS.

Commit: `feat: add profile avatar upload`

### Task 2: Componente global de avatar

**Files:**
- Create: `resources/views/components/avatar.blade.php`
- Test: `tests/Feature/AvatarComponentTest.php`

- [ ] **Step 1: Escrever teste do componente**

Renderizar `<x-avatar :user="$user" />` com avatar remoto, avatar local e sem avatar. Confirmar `<img>` nos dois primeiros casos e inicial no último.

- [ ] **Step 2: Executar o teste e confirmar falha**

Run: `php artisan test --compact tests/Feature/AvatarComponentTest.php`

Expected: FAIL porque o componente não existe.

- [ ] **Step 3: Criar o componente**

O componente deve aceitar `user` e `class`, resolver URLs HTTP diretamente e caminhos locais via `Storage::url()`, renderizar imagem circular com `object-cover`, `alt="Foto de {nome}"`, ou inicial com `mb_substr()`.

- [ ] **Step 4: Executar teste e commitar**

Run: `php artisan test --compact tests/Feature/AvatarComponentTest.php`

Expected: PASS.

Commit: `feat: add reusable user avatar component`

### Task 3: Exibir avatar em todo o sistema

**Files:**
- Modify: `resources/views/components/post-card.blade.php`
- Modify: `resources/views/feed/show.blade.php`
- Modify: `resources/views/livewire/feed/comment-section.blade.php`
- Modify: `resources/views/livewire/feed/interest-list.blade.php`
- Modify: `resources/views/livewire/business/review-section.blade.php`
- Modify: `resources/views/profile/account.blade.php`
- Modify: `resources/views/users/show.blade.php`
- Modify: `resources/views/users/ranking.blade.php`
- Modify: `resources/views/layouts/navigation.blade.php`
- Modify: `resources/views/admin/users/index.blade.php`
- Modify: `resources/views/home/index.blade.php`
- Modify: `resources/views/search/index.blade.php`

- [ ] **Step 1: Substituir avatares duplicados**

Trocar cada bloco de círculo + `substr()` por `<x-avatar :user="$user" class="w-8 h-8" />`, ajustando o usuário e tamanho de cada contexto.

- [ ] **Step 2: Verificar que não restaram placeholders de usuário**

Run: `rg -n "substr\\(.*user.*name|heroicon-o-user-circle" resources/views`

Expected: nenhuma ocorrência que represente avatar de usuário.

- [ ] **Step 3: Executar testes e commitar**

Run: `php artisan test --compact tests/Feature/ProfileTest.php tests/Feature/UserProfileTest.php tests/Feature/AvatarComponentTest.php`

Expected: PASS.

Commit: `feat: show user avatars across the application`

### Task 4: Completar sincronização inicial do Google

**Files:**
- Modify: `app/Actions/HandleGoogleAuthentication.php`
- Modify: `tests/Feature/Auth/GoogleAuthTest.php`

- [ ] **Step 1: Escrever teste para conta social já vinculada sem avatar**

Criar usuário sem `avatar_url`, `SocialAccount` Google vinculado e callback com avatar. Confirmar que o usuário recebe a URL.

- [ ] **Step 2: Executar teste e confirmar falha**

Run: `php artisan test --compact tests/Feature/Auth/GoogleAuthTest.php`

Expected: FAIL porque a Action retorna a conta vinculada antes de preencher o avatar.

- [ ] **Step 3: Atualizar somente avatar ausente**

Antes de retornar a conta existente:

```php
if (! $existingAccount->user->avatar_url && $googleUser->getAvatar()) {
    $existingAccount->user->update(['avatar_url' => $googleUser->getAvatar()]);
}
```

Manter o teste existente que garante que foto personalizada não é sobrescrita.

- [ ] **Step 4: Verificação final**

Run: `php artisan test --compact tests/Feature/ProfileTest.php tests/Feature/UserProfileTest.php tests/Feature/AvatarComponentTest.php tests/Feature/Auth/GoogleAuthTest.php`

Run: `vendor/bin/pint --test`

Expected: todos os testes e Pint passam.

- [ ] **Step 5: Commit final**

Commit: `fix: import missing avatar on Google login`
