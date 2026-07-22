# Questões em aberto

Estas dúvidas não podem ser resolvidas com segurança apenas pelo repositório. A coluna "impacto" mostra qual decisão elas desbloqueiam.

| ID | Pergunta | Resposta | Status |
|---|---|---|---|
| Q01 | Qual bairro será o piloto e quem pode alterar essa definição? | **Engenho da Rainha, Rio de Janeiro.** Apenas admin pode alterar. | ✅ Respondida em 21/07/2026 |
| Q02 | Conteúdo deve nascer pendente ou autoaprovado? | **Autoaprovado no piloto.** Futuramente IA para reprovar maliciosos e avisar moderação. | ✅ Respondida em 21/07/2026 |
| Q03 | Quem operará a moderação e em qual prazo? | **Adicionar usuários com role de moderação.** | ✅ Respondida em 21/07/2026 |
| Q04 | Reivindicação: token automático ou aprovação manual? | Aprovação manual do admin. | ✅ Resolvida em 20/07/2026 (B006) |
| Q05 | Verificação de e-mail obrigatória ou só para ações sensíveis? | **Só para ações sensíveis.** | ✅ Respondida em 21/07/2026 |
| Q06 | Quais benefícios concretos o plano Featured oferece? | Aparecer primeiro nas buscas, badge "Destaque", aparecer na Home, mais promoções (semanal vs mensal). | ✅ Respondida em 21/07/2026 |
| Q07 | Posts patrocinados fazem parte do MVP? Quem paga? | **Sim.** Operador entra em contato com negócio, recebe pagamento e patrocina manualmente no admin. | ✅ Respondida em 21/07/2026 |
| Q08 | Pontos/ranking têm objetivo validado ou são experimento? | **Continuam no MVP** para gerar engajamento. | ✅ Respondida em 21/07/2026 |
| Q09 | O Pulso será informação comunitária, métrica interna ou produto cívico? | **Informação comunitária.** | ✅ Respondida em 21/07/2026 |
| Q10 | Que tipos de post serão prioritários no piloto? | **Avisos, problemas e pedidos.** | ✅ Respondida em 21/07/2026 |
| Q11 | Importação Google Places cumpre termos/licença? | **Não verificado ainda.** | ⏳ Pendente |
| Q12 | Quem é o controlador de dados e políticas de privacidade? | **Não definido ainda.** | ⏳ Pendente |
| Q13 | Qual infraestrutura de produção é pretendida? | **Contabo VPS, 8GB RAM, CPU partilhada.** Suficiente para piloto. | ✅ Respondida em 21/07/2026 |
| Q14 | Alteração `trustProxies('*')` é intencional? | **Irrelevante** — alteração já foi excluída. | ✅ Resolvida em 21/07/2026 |
| Q15 | Jobs de enriquecimento falhos? | Causa 429 identificada; fila recuperada na B009. | ✅ Resolvida em 20/07/2026 (B009) |
| Q16 | Banco local contém dados reais, demo ou importados? | **Dados demo e importados.** Podem ser excluídos. | ✅ Respondida em 21/07/2026 |
| Q17 | E-mail SMTP está operacional? | **Não.** Precisa comprar domínio ainda. | ⏳ Pendente |
| Q18 | MySQL 8 é obrigatório no MVP? | Sim. | ✅ Resolvida em 20/07/2026 (B020) |
| Q19 | Há meta de acessibilidade, navegadores e aparelhos? | **Sem meta definida.** | ✅ Respondida em 21/07/2026 |
| Q20 | Quais métricas definem sucesso do piloto? | Definidas no [PILOT_CHECKLIST.md](PILOT_CHECKLIST.md): 10-30 usuários, 10+ negócios, 30+ posts, 0 P0, >99% uptime. | ✅ Respondida em 21/07/2026 |
| Q21 | Deve existir recurso formal contra rejeição/banimento? | **Não para o piloto.** Considerar pós-piloto. | ✅ Respondida em 21/07/2026 |
| Q22 | Respostas a pedidos por WhatsApp ou dentro do portal? | **Comerciante demonstra interesse na plataforma**, depois morador pega WhatsApp e entra em contato. | ✅ Respondida em 21/07/2026 |
| Q23 | Redis, Meilisearch e Selenium: intenção futura ou scaffold? | Redis fica para Laravel Horizon. Meilisearch e Selenium removidos na B040. | ✅ Resolvida em 21/07/2026 (B040) |
| Q24 | Existe backup testado e política de retenção? | **Sim.** Runbook definido com backup diário, retenção de 30 dias. | ✅ Respondida em 21/07/2026 |

## Resumo

- **Respondidas:** 19
- **Resolvidas (implementadas):** 5
- **Pendentes:** 2 (Q11 termos Google Places, Q12 LGPD/privacidade)
- **Pendente (infra):** 1 (Q17 domínio/SMTP)

## Ações pendentes

| Prioridade | Ação | Responsável |
|---|---|---|
| Alta | Comprar domínio e configurar SMTP | Operador |
| Alta | Verificar termos do Google Places/RapidAPI | Operador |
| Média | Definir controlador de dados e política de privacidade (LGPD) | Operador |
| Baixa | Implementar role de moderação (Q03) | Desenvolvimento |
