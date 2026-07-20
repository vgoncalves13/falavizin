# Funcionalidades incompletas, planejadas ou sem uso

## Itens iniciados e não concluídos

| Item | Evidência e parte pronta | O que falta | Esforço | Decisão recomendada |
|---|---|---|---|---|
| Verificação de e-mail | Rotas, controllers, views e testes existem (`routes/auth.php:38-48`; `tests/Feature/Auth/EmailVerificationTest.php`), mas `User` não implementa `MustVerifyEmail` (`app/Models/User.php:5,13-16`) | Decidir se é obrigatória, ativar contrato/middleware e testar impactos em login e notificações | S | **Finalizar** antes do piloto aberto |
| ✅ Reivindicação manual — concluída na B006 | Pedido pendente, exclusão mútua, rate limit, tela admin, aprovação/rejeição, notificação e testes existem | Definir procedimento/evidência externa, SLA, motivo e trilha de auditoria | L concluído; melhorias M | **Manter** no MVP; evoluir a operação conforme volume |
| ✅ Horário manual — concluído na B007 | Formulário e Actions persistem formato único; migração normaliza legado; model cobre período noturno | Manter testes e validar UX com dados reais | M concluído | **Manter** |
| Promoções de plano gratuito | `StorePromotionRequest` limita uma por semana (`app/Http/Requests/StorePromotionRequest.php:37-64`) | Aplicar a mesma regra e Policy no fluxo Livewire usado pela UI (`PromotionForm.php:64-90`) | S | **Finalizar**, centralizando a regra |
| Planos comerciais | Solicitação, aprovação e enum free/featured existem (`BusinessPlanController`; `app/Enums/BusinessPlan.php`) | Definir benefícios, vigência, cancelamento, preço, cobrança e termos | XL | **Manter manual no piloto** e validar demanda antes de pagamento |
| Patrocínio de posts | Campos e duas interfaces/controllers administrativos existem (`PostSponsorController.php`; `SponsoredPostsController.php`) | Unificar workflow, definir validade/posicionamento, auditoria e métricas | M | **Simplificar** para um único fluxo |
| Notificações | Banco, sino, lista e e-mail existem (`app/Notifications`; `app/Livewire/Notifications`) | Fila real, preferências, retries, templates consistentes e política de entrega | M | **Finalizar base operacional** |
| Pontos e ranking | B008 adicionou idempotência, transação, constraint, reconciliação e regressões para votos/posts/enquetes | Definir regra de produto, conteúdo elegível, antifraude adicional e propósito do ranking | S–M | **Validar com usuários** antes de promover o recurso |
| Pulso do Bairro | Página e componente agregador existem (`PulseController.php`; `NeighborhoodPulse.php`) | Definir métrica útil, janela temporal, bairro correto, fonte/qualidade e critérios de resolução | M | **Validar com usuários**, não expandir às cegas |
| Eventos | Campos de data e apresentação em posts existem | Calendário, timezone/validação, eventos passados e filtros próprios | M | **Finalizar após o MVP estável** |
| Google Places | Serviço, UI, command e job existem; B009 identificou 429, recuperou os nove jobs e adicionou timeout, lote espaçado e backoff progressivo | Harmonizar UI/CLI, reduzir field mask, validar URL/tamanho de fotos e adicionar observabilidade de produção | M | **Manter operacional no piloto** e fechar riscos restantes antes de escalar |
| Moderação fase 2 | Status pendente, fila, denúncia e notificações existem | SLA, motivo estruturado, histórico de decisões, recurso e permissões de moderador | L | **Finalizar o mínimo** antes do piloto aberto |

## Branches conceituais e estados sem ciclo completo

- `PostStatus` e `BusinessStatus` possuem pending/approved/rejected. A exposição pública individual foi corrigida na B004; autor/proprietário e admin mantêm acesso via Policies.
- `Promotion.status` e `Comment.status` são strings, sem enum/cast equivalente aos demais estados (`app/Models/Promotion.php`; `app/Models/Comment.php`).
- `plan_upgrade_requested_at` representa pedido de plano, mas não há assinatura, pagamento, renovação ou downgrade automático (`database/migrations/2026_03_19_230615_add_plan_upgrade_requested_at_to_businesses_table.php`).
- `is_sponsored` é apenas um booleano sem vigência; há controllers administrativos sobrepostos e nenhuma cobrança (`database/migrations/2026_03_19_211451_add_is_sponsored_to_posts_table.php`).
- Denúncia é gravada diretamente no próprio conteúdo, permitindo apenas um estado corrente; não existe entidade com denunciante, histórico ou decisão (`app/Actions/ReportContentAction.php`).

## Código aparentemente abandonado ou não utilizado

| Artefato | Indício | Recomendação | Esforço |
|---|---|---|---|
| `resources/views/dashboard.blade.php` | Nenhuma rota aponta para a dashboard padrão | Excluir | XS |
| `resources/views/welcome.blade.php` | Home real usa `home/index.blade.php`; view padrão não é roteada | Excluir | XS |
| Três componentes `⚡notification-bell`/`⚡favorite-button` | Placeholders anônimos sem uso encontrado em `resources/views/components/**` | Excluir após `rg` final | XS |
| `StorePostRequest.php` | Criação ocorre por Livewire e não existe POST `/criar-post` | Excluir ou centralizar validação nele via fluxo HTTP escolhido | XS |
| Partials Breeze em `resources/views/profile/partials` | A tela atual de perfil não os inclui | Excluir se não houver retorno ao layout Breeze | XS |
| Views de paginação Bootstrap/Semantic UI em `resources/views/vendor` | Aplicação usa Tailwind/Livewire; nenhum uso explícito foi encontrado | Manter apenas o template realmente usado | XS |
| `resources/js/bootstrap.js` e Axios | Só há import do bootstrap; nenhuma chamada Axios no código de aplicação | Remover dependência e arquivo, após validar build | XS |
| Redis, Meilisearch e Selenium no Compose | Aplicação atual usa banco/`LIKE`; não há cliente/dependência funcional | Remover do ambiente MVP ou documentar propósito | S |

## Documentação e planejamento antigos

`CLAUDE.md` é a melhor descrição histórica, mas diverge do código: descreve autoaprovação no MVP e uma estrutura menor, enquanto o código atual cria conteúdo pendente e já inclui enquetes, reputação, reviews, mapas e planos. `README.md:1-59` continua sendo o README padrão do Laravel e não orienta instalação, produto ou operação.

O histórico Git mostra ondas de implementação por funcionalidade, mas não substitui critérios de aceite ou ADRs. Não foram encontrados TODO/FIXME suficientes para representar o débito real; a maior parte dos incompletos foi inferida por divergências entre migrations, Actions, views, testes e rotas.

## Síntese de esforço

- **Concluir antes de piloto:** operação de deploy/worker e consistência: aproximadamente 1–2 semanas, incluindo testes. Dependências, status público, escopo Livewire, claim, horários, seeds e recuperação dos jobs já foram corrigidos nas B002–B011.
- **Completar engajamento atual:** reputação, eventos, notificações e moderação: mais 2–4 semanas.
- **Monetização completa:** só após validação; cobrança e ciclo de assinatura são XL e precisam ser quebrados.
