# Estado do projeto — Hub do Bairro

**Atualizado em:** 21/07/2026  
**Revisão de base analisada:** `eec07a1`, com estabilizações concluídas até a B022 e preservando uma alteração local preexistente em `bootstrap/app.php`.

## Resumo executivo

O Hub do Bairro é hoje um portal hiperlocal demonstrável para um bairro: moradores publicam e interagem em um feed; visitantes descobrem negócios, promoções e eventos; comerciantes mantêm perfis; administradores moderam e importam estabelecimentos. O inventário encontrou **12 módulos e 36 capacidades**, das quais **30 têm caminho feliz funcional** e **6 estão parciais ou possuem ressalvas relevantes**.

A base é maior e mais madura do que o roadmap histórico sugere. Laravel 12, Livewire 4, MySQL, Actions, Policies, migrations e 225 testes compõem uma fundação razoável. Em 20/07/2026, todos os 225 testes/494 assertions e o build frontend passaram; as auditorias Composer e npm também foram zeradas após a B002. Ainda assim, o produto **não deve ir para produção** antes de fechar observabilidade e deploy.

## O que está utilizável/demonstrável

- registro, login, recuperação de senha, perfil e conta;
- home, busca, categorias, feed e detalhe de post;
- criação/edição de posts, imagem, evento, enquete e resolução;
- comentários, votos, salvos, compartilhamento e denúncias;
- catálogo/lista/mapa e perfil de negócios, galeria, favoritos e reviews;
- promoções, fila de moderação, dashboard admin e configurações;
- notificações, ranking, Pulso e importação Google Places em estágio demonstrável.

“Demonstrável” não equivale a seguro em produção. As ressalvas por função estão em [Inventário atual](analysis/02-current-features.md).

## O que impede produção

1. ✅ Dependências vulneráveis: corrigidas na B002; `composer audit` e `npm audit` agora retornam zero.
2. ✅ XSS nos popups Leaflet: corrigido na B003 com nós DOM e `textContent`.
3. ✅ Conteúdo não aprovado em rotas públicas: corrigido na B004; somente autor/proprietário e admin mantêm acesso.
4. ✅ Escopo/autorização Livewire: corrigidos na B005 para promoção, review, comentário, enquete e negócio.
5. ✅ Reivindicação insegura por token: substituída na B006 por solicitação pendente e decisão manual exclusiva do admin.
6. ✅ Horários manuais e períodos após meia-noite: corrigidos e normalizados na B007.
7. ✅ Credenciais padrão de seed: removidas na B011; produção cria apenas categorias e demo exige senha explícita.
8. ✅ Pontos não idempotentes: corrigidos na B008 com chave única, transação e reconciliação dos totais.
9. ✅ E-mails e operações compostas: fila após commit na B012; transações e compensação de arquivos na B013.
10. ✅ Nove jobs de enriquecimento falhos: causa 429 identificada, fila recuperada e backoff/timeout corrigidos na B009.
11. ✅ Integridade de enquetes e capas: B014 adicionou unicidade, chave composta e índice funcional após diagnóstico/saneamento.
12. ✅ CI: B021 adicionou MySQL 8, PHP 8.5, Pint, 225 testes, build e audits em push/PR.
13. ✅ Runbook de deploy/backup/worker e README: B022 criou documentação completa com procedimentos operacionais e scripts automatizados.

Detalhes, arquivos, severidade e correções: [Auditoria técnica](analysis/04-technical-audit.md).

## MVP recomendado

Um único bairro, com:

- feed moderado de avisos, problemas, eventos e pedidos;
- diretório de negócios verificados com contato, mapa, fotos e horário confiável;
- promoções simples;
- comentários/reviews, denúncias e moderação com retorno ao autor;
- busca e filtros básicos;
- operação confiável de e-mail, fila, backup e importação.

Ranking, Pulso, patrocínio e planos podem continuar em avaliação/área secundária, mas não devem comandar o lançamento até suas regras estarem validadas. Chat, pagamentos, aplicativo, multi-bairro e busca externa ficam fora do primeiro piloto.

## Partes maduras e partes a refazer

**Mais maduras:** schema central, autenticação básica, feed no caminho feliz, catálogo/perfil comercial, componentes visuais, moderação básica e suíte de testes.

**Precisam de correção profunda:** riscos do pipeline externo e operação sem observabilidade/deploy formal. **Precisam de definição de produto:** Pulso, ranking, planos Featured e patrocinados. O claim agora é seguro contra tomada automática, mas ainda precisa de procedimento operacional e auditoria.

## Cinco maiores oportunidades

1. Transformar problemas locais em um fluxo verificável de reporte → apoio → resolução.
2. Criar confiança comercial com negócios realmente verificados.
3. Conectar pedidos de moradores a prestadores da categoria sem construir chat/pagamento cedo.
4. Organizar eventos e alertas segmentados do bairro com opt-in.
5. Oferecer destaque e métricas úteis a comerciantes após validar disposição a pagar.

## Primeiras dez tarefas

| Ordem | Backlog | Tarefa | Esforço |
|---|---|---|---|
| 1 | B001 | ✅ Rotacionar a credencial RapidAPI exposta — concluída em 20/07/2026 | XS |
| 2 | B002 | ✅ Corrigir advisories Composer e npm bloqueadores — concluída em 20/07/2026 | M |
| 3 | B003 | ✅ Neutralizar XSS nos popups Leaflet — concluída em 20/07/2026 | S |
| 4 | B004 | ✅ Restringir conteúdo não aprovado nas rotas públicas — concluída em 20/07/2026 | S |
| 5 | B005 | ✅ Escopar e reautorizar mutações Livewire — concluída em 20/07/2026 | M |
| 6 | B007 | ✅ Persistir e normalizar horários de funcionamento — concluída em 20/07/2026 | M |
| 7 | B006 | ✅ Substituir reivindicação por aprovação manual do admin — concluída em 20/07/2026 | L |
| 8 | B011 | ✅ Remover senhas padrão de seeds operacionais — concluída em 20/07/2026 | S |
| 9 | B009 | ✅ Recuperar jobs de enriquecimento falhos — concluída em 20/07/2026 | M |
| 10 | B008 | ✅ Tornar premiação de pontos idempotente — concluída em 20/07/2026 | M |

O backlog completo está em [Backlog priorizado](analysis/09-prioritized-backlog.md).

## Recomendação para começar hoje

**Próxima tarefa: B028 — simplificar cadastro de negócio.** Os filtros agora são compartilháveis; agora é hora de simplificar o fluxo de cadastro para comerciantes.

## Índice da análise

1. [Visão geral do produto](analysis/01-product-overview.md)
2. [Inventário de funcionalidades](analysis/02-current-features.md)
3. [Funcionalidades incompletas](analysis/03-incomplete-features.md)
4. [Auditoria técnica](analysis/04-technical-audit.md)
5. [Visão geral do banco](analysis/05-database-overview.md)
6. [Análise de UX](analysis/06-ux-analysis.md)
7. [Oportunidades de produto](analysis/07-feature-opportunities.md)
8. [Roadmap](analysis/08-roadmap.md)
9. [Backlog priorizado](analysis/09-prioritized-backlog.md)
10. [Questões em aberto](analysis/10-open-questions.md)

## Fontes e confiança

O código foi tratado como fonte principal. Conclusões factuais citam arquivos/linhas nos documentos detalhados; inferências e sugestões são rotuladas. O banco local e resultados de comandos são observações datadas. O README atual é genérico (`README.md:1-59`) e `CLAUDE.md` é histórico, portanto divergências foram resolvidas em favor das rotas, models, migrations, views e testes atuais.
