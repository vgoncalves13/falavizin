# Login com Google — Documentação

## Configuração OAuth no Google Cloud

### 1. Criar projeto no Google Cloud

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um novo projeto ou selecione um existente

### 2. Configurar OAuth Consent Screen

1. Vá para **APIs & Services > OAuth consent screen**
2. Selecione **External** (para testes) ou **Internal** (para organização)
3. Preencha:
   - **App name:** Hub do Bairro
   - **User support email:** seu email
   - **Developer contact information:** seu email
4. Adicione os escopos:
   - `openid`
   - `profile`
   - `email`
5. Adicione usuários de teste (se External)

### 3. Criar credenciais OAuth

1. Vá para **APIs & Services > Credentials**
2. Clique em **Create Credentials > OAuth client ID**
3. Selecione **Web application**
4. Configure:
   - **Name:** Hub do Bairro
   - **Authorized redirect URIs:** adicione conforme ambiente

### 4. Redirect URIs por ambiente

| Ambiente | URI |
|---|---|
| Local | `http://localhost/auth/google/callback` |
| Staging | `https://staging.seu-dominio.com/auth/google/callback` |
| Produção | `https://seu-dominio.com/auth/google/callback` |

### 5. Copiar credenciais

Após criar, copie:
- **Client ID**
- **Client Secret**

## Configuração da aplicação

### `.env`

```dotenv
GOOGLE_CLIENT_ID=seu-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=seu-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

### `.env.example` (já configurado)

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

## Variáveis necessárias

| Variável | Descrição | Obrigatória |
|---|---|---|
| `GOOGLE_CLIENT_ID` | Client ID do Google OAuth | Sim |
| `GOOGLE_CLIENT_SECRET` | Client Secret do Google OAuth | Sim |
| `GOOGLE_REDIRECT_URI` | URI de callback | Sim (usa `${APP_URL}/auth/google/callback`) |
| `APP_URL` | URL base da aplicação | Sim |

## Testando o fluxo

1. Configure as variáveis de ambiente
2. Acesse `/login` ou `/register`
3. Clique em "Continuar com Google"
4. Faça login no Google
5. Você será redirecionado de volta à aplicação

## Erros comuns

### Redirect URI mismatch

**Erro:** `redirect_uri_mismatch`

**Causa:** A URI configurada no Google Cloud não corresponde à URI da aplicação.

**Solução:** Verifique se a URI no Google Cloud exatamente igual à da aplicação (incluindo http/https e porta).

### Invalid state

**Erro:** `InvalidStateException`

**Causa:** A sessão expirou ou o CSRF token é inválido.

**Solução:** Tente novamente. Se persistir, verifique se o `APP_KEY` está configurado.

### Missing email

**Erro:** "Não foi possível obter seu e-mail do Google"

**Causa:** O usuário não autorizou o acesso ao email.

**Solução:** Verifique se os escopos `openid`, `profile` e `email` estão configurados.

## Revogar credenciais

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Vá para **APIs & Services > Credentials**
3. Selecione a credencial
4. Clique em **Delete** ou **Regenerate**

## Fluxo de autenticação

1. Usuário clica "Continuar com Google"
2. Aplicação redireciona para Google com escopos
3. Usuário autoriza no Google
4. Google redireciona para `/auth/google/callback`
5. Aplicação obtém dados do usuário
6. Verifica se já existe `social_account` com o `provider_user_id`
7. Se existe: autentica o usuário vinculado
8. Se não existe: busca usuário com mesmo email
9. Se encontra: vincula Google à conta existente
10. Se não encontra: cria novo usuário
11. Cria registro em `social_accounts`
12. Autentica e redireciona

## Segurança

- State validation do OAuth mantido
- Sessão regenerada após autenticação
- Proteção contra open redirect
- Rate limit na rota de redirect (5/min)
- Tokens não armazenados
- Email verificado automaticamente para contas Google
