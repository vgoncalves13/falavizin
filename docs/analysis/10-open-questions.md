# Questões em aberto

Estas dúvidas não podem ser resolvidas com segurança apenas pelo repositório. A coluna “impacto” mostra qual decisão elas desbloqueiam.

| ID | Pergunta | Por que está aberta/evidência | Impacto |
|---|---|---|---|
| Q01 | Qual bairro será o piloto e quem pode alterar essa definição? | Home fixa Jardim América (`home/index.blade.php:111`), mas settings e textos livres coexistem | Modelo de localização, conteúdo inicial e importação |
| Q02 | Conteúdo deve nascer pendente ou autoaprovado? | Código usa pending (`CreatePostAction.php:39`); documentação histórica descreve autoaprovação (`CLAUDE.md`) | SLA, UX e equipe de moderação |
| Q03 | Quem operará a moderação e em qual prazo? | Há apenas `is_admin`, sem papel/SLA/auditoria | Permissões, notificações e piloto |
| Q05 | A verificação de e-mail deve ser obrigatória para todos ou só para ações sensíveis? | Scaffold existe, contrato não (`User.php:5`) | Auth e abuso |
| Q06 | Quais benefícios concretos o plano Featured oferece? | Ordenação/badge e pedido administrativo existem; cobrança não | Proposta comercial e entitlements |
| Q07 | Posts patrocinados fazem parte do MVP? Quem paga e como são identificados? | Campos e dois controllers existem sem ciclo financeiro | Simplificação e confiança editorial |
| Q08 | Pontos/ranking têm objetivo validado ou são experimento? | Implementados, mas não idempotentes e com pouco dado local | Corrigir, ocultar ou remover |
| Q09 | O Pulso será informação comunitária, métrica interna ou produto cívico? | Agregador existe sem definição pública de metodologia | UX, confiança e roadmap |
| Q10 | Que tipos de post serão prioritários no piloto? | Categorias incluem avisos/problemas/eventos/pedidos e variações | Navegação, formulário e moderação |
| Q11 | A importação Google Places e exibição de fotos cumprem termos/licença e atribuição exigidos? | API via RapidAPI e armazenamento local de fotos (`EnrichBusinessFromGoogle.php`) | Risco jurídico e arquitetura de dados |
| Q12 | Quem é o controlador de dados e quais políticas de privacidade/retenção se aplicam? | Telefone, endereço, localização, denúncias e analytics potenciais; não há docs legais | LGPD, piloto e analytics |
| Q13 | Qual infraestrutura de produção é pretendida? | Não há deploy/CI; Compose traz serviços não usados | Proxy confiável, worker, storage, backup e SLO |
| Q14 | A alteração local `trustProxies('*')` é intencional e qual proxy ficará à frente da aplicação? | `bootstrap/app.php:18` está modificado fora desta auditoria | Segurança de IP/HTTPS/rate limit |
| Q16 | O banco local contém dados reais, demo ou importados? Podem ser descartados/saneados? | Há usuários, negócios e interações; origem não é determinável apenas pelo código | Migrações corretivas e privacidade |
| Q17 | E-mail SMTP está operacional e existe domínio/remetente validado? | Configuração existe; não foi enviado e-mail real nesta auditoria | Reset e notificações |
| Q18 | É aceitável assumir MySQL exclusivamente? | Código usa SQL MySQL, mas `.env.example` aponta SQLite | CI, desenvolvimento e portabilidade |
| Q19 | Há meta de acessibilidade, navegadores e aparelhos suportados? | Interface é responsiva, mas não há matriz/testes documentados | Critério de aceite UX |
| Q20 | Quais métricas definem sucesso do piloto? | Nenhum plano analítico/objetivo mensurável no repositório | Priorização de engajamento/monetização |
| Q21 | Deve existir recurso formal contra rejeição/banimento? | Moderação aprova/rejeita sem workflow de recurso | Governança comunitária |
| Q22 | Respostas a pedido de orçamento devem ocorrer por WhatsApp ou dentro do portal? | WhatsApp já existe; chat está explicitamente fora do MVP histórico | Complexidade do módulo de pedidos |
| Q23 | Redis, Meilisearch e Selenium foram adicionados por intenção futura ou scaffold? | Serviços estão no Compose, sem consumo atual | Limpeza do ambiente e custo |
| Q24 | Existe backup testado e política de retenção de imagens/soft deletes? | Não há runbook; arquivos podem sobreviver a exclusões | Operação, custo e LGPD |

## Decisões recomendadas antes de codificar novas features

**Decisões registradas em 20/07/2026:** Q04 foi respondida pelo responsável: a propriedade será concedida por aprovação manual do administrador. A B006 implementou esse fluxo; evidência, SLA e trilha operacional continuam como refinamentos. Q15 foi diagnosticada na B009: todos os nove jobs falharam por HTTP 429; a fila foi recuperada após rotação da chave e correção de timeout/backoff/espaçamento.

Responder primeiro Q01–Q03, Q05, Q11–Q14 e Q20. Elas definem escopo do piloto, segurança, operação e critério de sucesso. Q06–Q10 devem ser decididas com evidência de usuários; Q21–Q24 entram no fechamento operacional.
