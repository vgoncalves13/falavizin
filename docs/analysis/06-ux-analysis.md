# Análise da experiência do usuário

## Leitura geral

**Fato.** A interface pública tem identidade própria em âmbar/stone, tipografia Plus Jakarta Sans/DM Sans, cards e navegação responsiva (`resources/css/app.css`; `resources/views/layouts/app.blade.php:16-24`). Home, feed, catálogo e perfil comercial são demonstráveis. Há empty states, indicadores Livewire e confirmações em várias operações.

**Inferência.** O produto comunica bem “comunidade local”, mas apresenta mais recursos do que o usuário novo consegue compreender de imediato: feed, pulso, ranking, planos, patrocinados, enquetes e múltiplos tipos de interação competem antes de a proposta central estar consolidada.

## Fluxos principais

### Descobrir o bairro

**Atual:** a home apresenta busca, categorias, negócios, promoções, feed e Pulso. O bairro “Jardim América” aparece fixo (`home/index.blade.php:111`), embora existam settings administrativos. Três métricas são consultadas diretamente pela view (`:143-147`).

**Melhoria:** usar o bairro configurado em uma única fonte, deixar a busca como ação principal e limitar a home a três blocos prioritários no piloto: acontecimentos, serviços e promoções. Mostrar claramente a data/abrangência do Pulso.

### Publicar e acompanhar um post

**Atual:** usuário escolhe categoria e pode adicionar imagem, evento ou enquete. Há loading e validação. O post nasce pendente (`CreatePostAction.php:39-53`), mas a experiência posterior de status não é centralizada.

**Melhoria:** confirmar “enviado para análise”, indicar prazo e status na conta, explicar rejeição e permitir correção/reenvio. Mostrar campos progressivamente conforme o tipo, reduzindo a carga inicial.

### Participar de uma conversa

**Atual:** comentários, respostas, votos, salvar, compartilhar e denunciar aparecem no detalhe. Listas de comentários não são paginadas (`CommentSection.php:207-225`).

**Melhoria:** ordenar conversa explicitamente, carregar respostas sob demanda, manter foco após envio, expor estado de limite e tornar denunciar um modal acessível com confirmação final.

### Encontrar um serviço

**Atual:** busca/filtros, lista/mapa, “aberto agora”, destaque e perfil completo. O modo mapa usa Leaflet por CDN; desde a B003, os popups constroem conteúdo dinâmico com nós DOM seguros (`businesses/index.blade.php:75-128`).

**Melhoria:** manter lista como fallback, sincronizar filtros com URL, indicar quando horário é desconhecido, oferecer ação principal por negócio (WhatsApp/ligar) e limitar marcadores à área visível.

### Cadastrar ou reivindicar negócio

**Atual:** cadastro Livewire permite dados, horário e fotos; a persistência e períodos noturnos foram corrigidos na B007. A B006 substituiu o link inseguro por pedido pendente, estado visível e aprovação/rejeição manual pelo admin.

**Melhoria:** dividir em cadastro essencial e enriquecimento posterior; no claim explicar evidência necessária, prazo esperado, resultado da decisão e canal de suporte.

### Avaliar e responder

**Atual:** estrelas, comentário e resposta do proprietário ficam no perfil. Ações têm feedback, mas botões de estrela não possuem nomes acessíveis claros (`resources/views/livewire/business/review-section.blade.php:31-55`).

**Melhoria:** labels “1 a 5 estrelas”, confirmação, edição previsível, política de conteúdo e resposta escopada ao negócio. Exibir distribuição de notas só quando houver volume suficiente.

### Moderação

**Atual:** admin filtra pendentes/reportados e executa ações individuais/em lote. Não há histórico de decisão.

**Melhoria:** preview seguro, motivo obrigatório na rejeição, confirmação em lote, ator/data e desfazer controlado. A fila deve mostrar idade/SLA.

## Problemas de navegação e estado

| Problema | Evidência/impacto | Recomendação | Severidade UX |
|---|---|---|---|
| Filtros Livewire não ficam na URL | `FeedList` e `BusinessList` não usam atributos URL; voltar/compartilhar perde contexto | Sincronizar busca, categoria, ordem e modo | Médio |
| ✅ Conta e perfis cresciam sem paginação | B017 adicionou tabs/contagens e paginadores independentes; regressões em `ProfileTest` e `UserProfileTest` | Observar uso e ajustar tamanho de página | Resolvido para o MVP |
| Bairro fixo e configuração desconectada | `home/index.blade.php:111`; `Setting.php` | Fonte única e fallback explícito | Alto |
| Auth visualmente inconsistente | views reset/verify/confirm preservam scaffold enquanto login/register foram personalizados | Unificar layout, idioma e feedback | Médio |
| Recurso pendente sem jornada clara | status existe, mas não há central de pendências/rejeições | Timeline/status e orientação de correção | Alto |
| Mapas dependem de CDN | views do catálogo/perfil | Fallback e mensagem de erro | Médio |

## Acessibilidade

Problemas encontrados por inspeção estática:

- menu mobile sem `aria-expanded`/`aria-controls` explícitos (`resources/views/layouts/navigation.blade.php:129-141`);
- modais/lightbox sem garantia de `role="dialog"`, `aria-modal`, foco preso e restauração;
- estrelas e alguns botões apenas com ícone sem nome acessível (`review-section.blade.php:31-55`);
- imagens com alt genérico ou vazio fora de contexto e sem `loading="lazy"` em galerias (`resources/views/livewire/business/photo-gallery.blade.php:32-36`);
- ticker/animações da home sem tratamento visível de `prefers-reduced-motion` (`home/index.blade.php:4-76`);
- SVGs inline próprios em vez do sistema Heroicons (`home/index.blade.php:100,130,137`), criando inconsistência;
- ausência de evidência de testes automatizados de acessibilidade.

Recomendação: rodada de teclado + leitor de tela nas seis jornadas principais, contraste, foco, reduced motion e axe/Lighthouse em CI. Esforço M para baseline, não uma reformulação visual.

## Feedback, erros e estados vazios

Existem flash messages e loaders, mas erros externos (mapa, e-mail, Places, storage) raramente têm estado de recuperação específico. Formulários devem preservar entrada, explicar próximo passo e diferenciar falha temporária de validação. A moderação precisa justificar rejeição; importação deve mostrar falhas reprocessáveis; claim deve informar prazo e segurança.

## Responsividade

Tailwind e navegação mobile indicam intenção responsiva, e os grids possuem breakpoints. Pontos de risco são mapa com altura fixa, tabelas administrativas, galeria/lightbox e grande densidade da home. A auditoria não substitui teste visual em aparelhos reais; isso permanece pergunta/critério no roadmap.

## Simplificação recomendada para o piloto

Na navegação primária: **Início, Acontecimentos, Serviços, Promoções** e uma ação contextual “Publicar”. Ranking, Pulso, planos e ferramentas administrativas podem continuar acessíveis de forma secundária até haver dados e compreensão suficientes. Isso reduz carga cognitiva sem apagar recursos já construídos.
