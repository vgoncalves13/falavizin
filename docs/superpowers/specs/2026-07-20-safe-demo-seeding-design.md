# Seed seguro de dados demonstrativos

## Objetivo

Impedir que `DatabaseSeeder` crie contas com senha pública ou conhecida em produção, preservando uma carga demonstrativa previsível no desenvolvimento e nos testes.

## Comportamento

- Categorias são semeadas em todos os ambientes.
- Usuários, negócios, posts e demais dados demonstrativos são criados somente em `local` e `testing`.
- A senha desses usuários vem de `DEMO_USER_PASSWORD` e nunca possui valor padrão.
- Em `local` ou `testing`, a ausência da variável interrompe o seed com mensagem clara antes de criar usuários.
- Em produção, o seed termina após as categorias e não exige `DEMO_USER_PASSWORD`.

## Implementação mínima

- Expor `DEMO_USER_PASSWORD` pela configuração da aplicação.
- Retornar cedo no `DatabaseSeeder` fora de `local` e `testing`.
- Validar a senha uma vez e reutilizar seu hash para todas as contas demonstrativas.
- Documentar a variável vazia em `.env.example` e defini-la apenas no ambiente isolado dos testes.
- Adicionar um teste de regressão que prove que o seed de produção não cria contas.

Não serão criados comando de provisionamento, seeder adicional ou geração/armazenamento de senha. O primeiro administrador de produção continuará sendo provisionado deliberadamente pela operação até existir necessidade comprovada de automatização.

## Critérios de aceite

1. `DatabaseSeeder` em produção cria categorias e zero usuários demonstrativos.
2. Seed local/testing sem `DEMO_USER_PASSWORD` falha antes de criar usuários.
3. Seed local/testing com a variável cria os dados e a senha configurada autentica uma conta demo.
4. A suíte de testes e o Pint permanecem verdes.
