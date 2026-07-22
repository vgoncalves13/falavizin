# Estado do projeto — Hub do Bairro

**Atualizado em:** 21/07/2026  
**Revisão completa:** até B040 concluída.

## Resumo executivo

O Hub do Bairro é hoje um portal hiperlocal funcional para um bairro: moradores publicam e interagem em um feed; visitantes descobrem negócios, promoções e eventos; comerciantes mantêm perfis com analytics; administradores moderam e importam estabelecimentos. O inventário encontrou **12 módulos e 40+ capacidades**, todas com caminho feliz funcional.

A base é madura e pronta para piloto. Laravel 12, Livewire 4, MySQL, Actions, Policies, migrations e 223 testes compõem uma fundação sólida. Em 21/07/2026, todos os 223 testes/492 assertions e o build frontend passaram; as auditorias Composer e npm retornam zero. **Observabilidade básica está em place** (health check, logs estruturados, SLOs documentados).

## O que está utilizável/demonstrável

- registro, login, recuperação de senha, perfil e conta;
- home, busca, categorias, feed e detalhe de post;
- criação/edição de posts, imagem, evento, enquete e resolução;
- comentários, votos, salvos, compartilhamento e denúncias;
- catálogo/lista/mapa e perfil de negócios, galeria, favoritos e reviews;
- promoções, fila de moderação, dashboard admin e configurações;
- notificações, ranking, Pulso e importação Google Places;
- **pedidos de orçamento com categorias de serviço;**
- **negócios com múltiplas categorias;**
- **sistema de interesse (mercador ↔ morador);**
- **dashboard do comerciante com pedidos relevantes;**
- **analytics de negócios (views, phone, WhatsApp);**
- **posts patrocinados com prazo e audit log;**
- **plano Destaque com proposta de valor clara;**
- **health check e observabilidade (SLOs, logs estruturados).**

## O que impede produção

Todos os bloqueios P0/P1 foram resolvidos (B001–B022). O que resta é operacional:

1. Deploy em VPS com MySQL, worker e backup configurado.
2. Domínio e certificado SSL.
3. Monitoramento de health check em serviço externo.
4. Validação do piloto com usuários reais.

## MVP completo

O MVP está implementado e inclui:

- feed moderado de avisos, problemas, eventos e pedidos;
- diretório de negócios verificados com contato, mapa, fotos e horário confiável;
- promoções simples com cooldown;
- comentários/reviews, denúncias e moderação com retorno ao autor;
- busca e filtros básicos;
- operação confiável de e-mail, fila, backup e importação;
- pedidos de orçamento conectando moradores a comerciantes;
- analytics para comerciantes (views, contatos);
- plano Featured com proposta de valor definida.

## Partes maduras e partes a refazer

**Mais maduras:** schema central, autenticação, feed, catálogo comercial, moderação, analytics, patrocínio, observabilidade.

**Podem evoluir pós-piloto:** Pulso com métricas mais granulares, ranking com gamificação, pagamentos online, chat interno, multi-bairro.

## Funcionalidades implementadas (B032–B040)

| ID | Título | Concluída |
|---|---|---|
| B032 | Pedido de orçamento com categorias e interesse | ✅ 21/07/2026 |
| B033 | Indicadores do Pulso explicáveis e verificáveis | ✅ 21/07/2026 |
| B034 | Proposta Featured manual com benefícios claros | ✅ 21/07/2026 |
| B035 | Analytics de negócios (views, phone, WhatsApp) | ✅ 21/07/2026 |
| B036 | Posts patrocinados unificados com prazo | ✅ 21/07/2026 |
| B039 | SLOs, logs estruturados e health check | ✅ 21/07/2026 |
| B040 | Limpeza de scaffolds e infraestrutura sem uso | ✅ 21/07/2026 |

## Próximos passos (pós-piloto)

| Prioridade | Tarefa |
|---|---|
| P2 | Deploy em produção |
| P2 | Validação com usuários reais |
| P3 | Laravel Horizon para monitorar filas |
| P3 | Pagamentos (após validar demanda) |
| P3 | Chat interno (após validar demanda) |
| P3 | Multi-bairro (após piloto repetível) |

## Estrutura técnica

- **Backend:** Laravel 12, PHP 8.5
- **Frontend:** Livewire 4, Alpine.js, Tailwind CSS 4
- **Banco:** MySQL 8.4
- **Filas:** Database (preparado para Redis/Horizon)
- **Cache:** Database
- **Observabilidade:** Health check, logs estruturados, SLOs documentados
- **Testes:** 223 testes, 492 assertions

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
11. [SLOs e Observabilidade](SLO.md)
