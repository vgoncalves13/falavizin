# Estado do projeto — Hub do Bairro

**Atualizado em:** 20/07/2026  
**Revisão analisada:** `eec07a1`, preservando uma alteração local preexistente em `bootstrap/app.php`.

## Resumo executivo

O Hub do Bairro é hoje um portal hiperlocal demonstrável para um bairro: moradores publicam e interagem em um feed; visitantes descobrem negócios, promoções e eventos; comerciantes mantêm perfis; administradores moderam e importam estabelecimentos. O inventário encontrou **12 módulos e 36 capacidades**, das quais **20 têm caminho feliz funcional** e **16 estão parciais ou possuem ressalvas relevantes**.

A base é maior e mais madura do que o roadmap histórico sugere. Laravel 12, Livewire 4, MySQL, Actions, Policies, migrations e 187 testes compõem uma fundação razoável. Em 20/07/2026, todos os 187 testes/394 assertions e o build frontend passaram; as auditorias Composer e npm também foram zeradas após a B002. Ainda assim, o produto **não deve ir para produção** antes de corrigir falhas de escopo/autorização Livewire e reivindicação insegura de negócios.

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
4. Ações Livewire aceitam IDs fora do recurso e/ou deixam de reautorizar mutações.
5. Claim não comprova propriedade, não expira e não exige e-mail verificado.
6. Horários manuais não são persistidos, afetando “aberto agora”.
7. Pontos não são idempotentes e podem ser acumulados repetidamente.
8. E-mails são síncronos e operações compostas não são transacionais.
9. Há nove jobs locais falhos de enriquecimento, sem recuperação operacional clara.
10. Não há CI, runbook de deploy/backup/worker ou README real do projeto.

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

**Precisam de correção profunda:** claim, autorização Livewire por recurso, pontuação/idempotência, horários e pipeline de integração. **Precisam de definição de produto:** Pulso, ranking, planos Featured e patrocinados.

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
| 5 | B005 | Escopar e reautorizar mutações Livewire | M |
| 6 | B007 | Persistir e normalizar horários de funcionamento | M |
| 7 | B006 | Reformular a reivindicação de negócio | L |
| 8 | B009 | Recuperar jobs de enriquecimento falhos | M |
| 9 | B011 | Remover senhas padrão de seeds operacionais | S |
| 10 | B010 | Consolidar testes de regressão P0 | M |

O backlog completo está em [Backlog priorizado](analysis/09-prioritized-backlog.md).

## Recomendação para começar hoje

**Próxima tarefa: B005 — escopar e reautorizar mutações Livewire.** Corrigir promoção, review, comentário, opção de enquete e `BusinessForm` com buscas pela relação pai e Policies existentes.

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
