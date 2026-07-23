# Avatar do usuário

## Objetivo

Permitir que o usuário altere sua foto de perfil e exibir essa foto em todo o sistema. No login com Google, usar o avatar fornecido pelo provedor somente quando o usuário ainda não tiver uma foto.

## Decisões

- Reutilizar `users.avatar_url`, já existente, sem nova migration.
- Manter URLs remotas do Google como estão.
- Salvar uploads manuais no disk `public`, em `avatars/`, após redimensionamento.
- Um upload manual substitui a foto atual e não será sobrescrito por logins Google posteriores.
- Exibir a inicial do nome quando não houver foto.
- Centralizar a renderização em um componente Blade `<x-avatar>`.

## Fluxo

1. O usuário acessa “Editar perfil”.
2. Seleciona uma imagem e salva o formulário.
3. O Form Request valida o arquivo.
4. A Action redimensiona, salva a nova imagem, atualiza `avatar_url` e remove o upload local anterior.
5. O componente `<x-avatar>` passa a mostrar a foto nas telas que hoje exibem iniciais.

No callback Google, usuários novos ou sem avatar recebem `getAvatar()`. Uma foto já existente permanece intacta.

## Validação e erros

- Aceitar somente imagens JPEG, PNG e WebP.
- Limitar o upload a 5 MB.
- Redimensionar para no máximo 512 × 512, preservando proporção.
- Não apagar a imagem anterior se o novo arquivo falhar ao ser processado ou salvo.

## Superfícies afetadas

- Edição e resumo do perfil.
- Perfil público.
- Feed e detalhes do post.
- Comentários e respostas.
- Avaliações.
- Ranking.
- Menu de navegação.
- Telas administrativas que identificam usuários.

## Testes

- Upload válido salva a imagem e atualiza o usuário.
- Upload inválido é rejeitado.
- Substituição remove somente o upload local anterior.
- Login Google preenche avatar ausente e não sobrescreve foto existente.
- O componente mostra foto ou inicial como fallback.

## Fora do escopo

- Editor de recorte.
- Hospedagem externa/CDN.
- Sincronização contínua com mudanças da foto do Google.
