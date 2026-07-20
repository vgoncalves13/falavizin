# Oportunidades de produto

As sugestões abaixo partem do código existente; não são apresentadas como implementadas. Segurança e estabilização precedem expansão.

## Essenciais para o MVP

| Oportunidade | Problema/público | Fluxo proposto | Dependências | Complexidade | Valor / prioridade / risco |
|---|---|---|---|---|---|
| Status e retorno de moderação | Autor não sabe claramente o destino do conteúdo | Conta mostra pendente/aprovado/rejeitado, motivo e ação de corrigir | Moderação segura, notifications | M | Alto / P0 / risco de SLA não cumprido |
| Verificação assistida de negócio | Claim atual não prova propriedade | Comerciante envia evidência; admin valida; contatos conhecidos podem confirmar | E-mail verificado, auditoria, token seguro | L | Alto / P0 / privacidade e trabalho operacional |
| Bairro canônico único | Texto livre fragmenta filtros | Admin configura bairro do piloto; endereço/post apontam para entidade/ID | Migração e saneamento | M | Alto / P1 / migração de dados |
| Operação confiável | Jobs/e-mails falham sem visibilidade | Painel/runbook de fila, retry e alerta; usuário recebe estado recuperável | Fila estabilizada | M | Alto / P0 / alert fatigue |
| Jornada comercial mínima | Cadastro atual é extenso e horário falha | Cadastro essencial → preview → completar fotos/horários → publicar | Correções de BusinessForm | M | Alto / P1 / dados inicialmente incompletos |

## Importantes após o MVP

| Oportunidade | Problema/público | Fluxo proposto | Dependências | Complexidade | Valor / prioridade / risco |
|---|---|---|---|---|---|
| Calendário do bairro | Eventos se perdem no feed | Lista/calendário por data, lembrete e exportação | Eventos estáveis, timezone | M | Alto / P2 / conteúdo desatualizado |
| Pedido de orçamento local | Morador encontra categoria, mas ainda precisa contatar vários negócios | Post tipo pedido → seleção de categoria → comerciantes respondem com interesse/contato | Anti-spam, privacidade, businesses verificados | L | Alto / P2 / vira marketplace complexo se expandido demais |
| Preferências de notificação | Excesso ou falta de comunicação reduz retenção | Usuário escolhe categorias/canais/frequência | Notificações em fila | M | Médio-alto / P2 / opt-in e LGPD |
| Histórico de denúncias/moderação | Um campo no conteúdo não atende operação real | Report separado → triagem → decisão/motivo → recurso | Auditoria e permissões | L | Alto / P1 / abuso e carga de moderação |
| Alertas segmentados | Avisos urgentes precisam chegar ao público correto | Opt-in por categoria e bairro; digest ou alerta crítico | Bairro canônico e preferências | L | Alto / P2 / fadiga e responsabilidade editorial |
| Vagas locais simples | Comércio e moradores têm demanda recorrente | Post estruturado com função, contato e validade | Moderação e expiração | M | Médio / P2 / conteúdo fraudulento |

## Diferenciais competitivos

| Oportunidade | Problema/público | Fluxo e base existente | Dependências | Complexidade | Valor / prioridade / riscos |
|---|---|---|---|---|---|
| Pulso verificável de problemas | Moradores não enxergam progresso coletivo | Problema reportado → apoio → atualização → resolução confirmada; aproveita `resolution_status` e Pulso | Confiança, localização e moderação | L | Alto / P2 / métricas manipuláveis |
| Selo de comércio verificado | Moradores não sabem quais perfis são legítimos; comerciantes precisam confiança | Claim forte + data/forma de verificação visível | Processo de claim e auditoria | M | Alto / P2 / responsabilidade sobre alegações |
| Benefícios do bairro | Ofertas não geram fidelidade mensurável para moradores/comerciantes | Comerciante cria oferta/cupom simples, morador apresenta código; aproveita promotions | Antifraude básico, validade, métricas | L | Alto / P3 / não iniciar pagamento cedo |
| Matching de pedidos | Morador repete contatos e prestador perde demanda | Negócios da categoria recebem pedido opt-in e respondem sem chat interno | Pedidos, notifications, privacidade | L | Alto / P3 / spam e qualidade do lead |
| Métricas úteis ao comerciante | Comerciante não mede retorno do perfil | Visualizações e cliques de telefone/WhatsApp agregados | Consentimento, analytics first-party | L | Alto para monetização / P3 / privacidade |

## Futuras ou experimentais

| Ideia | Problema/público e fluxo | Dependências/quando considerar | Complexidade | Valor / prioridade / risco |
|---|---|---|---|---|
| PWA e push | Morador recorrente instala o portal e recebe alertas opt-in | Retenção web e preferências comprovadas | L | Médio / P3 / custo, permissão e abuso |
| Busca por proximidade | Morador ordena serviços pela distância calculada | Coordenadas confiáveis e expansão além de um bairro | L | Médio-alto / P3 / privacidade e qualidade geo |
| Multi-bairro | Operador abre nova localidade com conteúdo/gestão isolados | Operação repetível no piloto | XL | Alto / P3 / vazamento, moderação e cold start |
| Aplicativo móvel | Usuário muito recorrente recebe experiência nativa | Retenção que justifique canal/equipe dedicada | XL | Incerto / P3 / duplicação de produto |
| Pagamentos/assinaturas | Comerciante compra plano com ciclo automatizado | Disposição a pagar e entitlements validados | XL | Alto / P3 / fiscal, chargeback e suporte |
| Doações/coleta/pets perdidos | Morador publica formulário guiado e alerta expira | Validar primeiro como templates/categorias | S–M | Médio / P3 / fragmentação prematura |
| Integração com serviços públicos | Morador consulta/envia informação oficial com retorno | Parceiro, dados oficiais e SLA | XL | Alto / P3 / confiabilidade e responsabilidade institucional |

## O que não priorizar agora

Chat interno, fórum separado, gamificação mais profunda, aplicativo nativo, busca externa, pagamentos e expansão multi-bairro aumentariam superfície operacional antes de o núcleo estar seguro. O código já oferece comentários, WhatsApp, `LIKE`, pontos e operação de um bairro; primeiro deve provar uso real dessas alternativas mais simples.
