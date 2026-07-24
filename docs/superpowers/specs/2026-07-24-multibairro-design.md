# FalaVizin multibairro

## Objetivo

Permitir que o FalaVizin opere em vários bairros sem misturar conteúdo,
mantendo claro para o usuário qual bairro está sendo visitado e em qual bairro
uma publicação ou negócio será cadastrado.

O primeiro lançamento continuará concentrado no Engenho da Rainha. A estrutura
multibairro será ativada antes da inclusão do segundo bairro.

## Estado atual

- Usuários e negócios armazenam bairro como texto livre.
- Publicações não possuem bairro próprio; o filtro atual do feed usa o bairro
  textual do autor.
- A configuração `neighborhood_name` representa um único bairro global.
- Home, busca, feed, serviços, promoções, eventos, conteúdo patrocinado e
  caches não possuem isolamento canônico por bairro.
- Comentários, reações, enquetes, promoções e avaliações pertencem a uma
  publicação ou negócio e podem herdar seu bairro.
- Administradores e moderadores já possuem acesso global.

Esse modelo não é suficiente para operar mais de um bairro: nomes podem variar,
uma publicação pode ser feita fora do bairro principal do autor e caches ou
consultas sem filtro podem misturar conteúdo.

## Decisões de produto

- A estrutura representa bairro, cidade e estado, ainda que o lançamento
  inicial continue restrito ao Rio de Janeiro.
- A URL canônica segue
  `/rj/rio-de-janeiro/engenho-da-rainha`.
- Trocar o bairro na navbar muda apenas o contexto visitado.
- Alterar o bairro principal é uma ação separada e explícita.
- O usuário pode publicar em qualquer bairro ativo; o bairro visitado no
  formulário será usado.
- A raiz pública continua sendo um diretório de bairros.
- Visitantes recorrentes verão um atalho “Continuar em [bairro]”, sem perder o
  diretório.
- Usuários autenticados que acessarem a raiz serão redirecionados ao bairro
  principal.
- Cada negócio pertence a um único bairro no MVP.
- Administração e moderação permanecem globais.
- O administrador pode criar, ordenar, ativar e desativar bairros.
- Novos usuários recebem como principal o bairro ativo durante o cadastro.
- Login com Google sem bairro definido exige uma seleção curta antes de
  continuar.
- Todos os dados existentes serão vinculados ao Engenho da Rainha.

## Abordagem escolhida

Criar uma entidade canônica `Neighborhood` e relacioná-la explicitamente a
usuários, publicações e negócios.

As consultas usarão o bairro recebido pela rota. Não será usado global scope do
Eloquent: o comportamento oculto complicaria moderação, administração,
notificações e relatórios globais.

Também não será adotado um framework de multi-tenancy nem banco separado por
bairro. O isolamento necessário neste estágio é obtido com chaves estrangeiras,
índices, route binding e filtros explícitos.

## Modelo de dados

### `neighborhoods`

Campos:

- `id`;
- `name`;
- `slug`;
- `city`;
- `city_slug`;
- `state_code`;
- `latitude`, nullable;
- `longitude`, nullable;
- `is_active`, padrão `true`;
- `sort_order`, padrão `0`;
- timestamps.

Haverá uma restrição única para `state_code + city_slug + slug` e índice para
`is_active + sort_order`.

Latitude e longitude serão opcionais e servirão à importação já existente do
Google Places. Não haverá mapa ou busca por distância neste ciclo.

### Relacionamentos diretos

- `users.neighborhood_id`, nullable: bairro principal do usuário;
- `posts.neighborhood_id`, obrigatório: bairro no qual o conteúdo foi
  publicado;
- `businesses.neighborhood_id`, obrigatório: bairro atendido pelo cadastro.

`users.neighborhood_id` será nullable apenas para permitir o onboarding de
contas sociais e a recuperação de contas cujo bairro principal tenha sido
desativado.

Os campos textuais antigos de bairro em usuários e negócios deixarão de ser a
fonte de verdade e serão removidos depois do backfill na própria entrega.
A cidade do negócio virá do relacionamento com o bairro.

### Relacionamentos herdados

Não será duplicado `neighborhood_id` nas entidades abaixo:

- comentários, votos, respostas, enquetes, opções e interesses herdam o bairro
  da publicação;
- fotos, promoções, avaliações, favoritos e analytics herdam o bairro do
  negócio;
- itens salvos e notificações podem reunir conteúdo de vários bairros e usam o
  relacionamento da entidade de origem.

Essa regra evita divergência entre uma entidade filha e seu conteúdo principal.

## Rotas e contexto

### Rotas locais

As páginas de descoberta e conteúdo ficarão sob:

```text
/{state}/{city}/{neighborhood}
/{state}/{city}/{neighborhood}/feed
/{state}/{city}/{neighborhood}/feed/{post}
/{state}/{city}/{neighborhood}/servicos
/{state}/{city}/{neighborhood}/servicos/{business}
/{state}/{city}/{neighborhood}/promocoes
```

Eventos, Pulso, busca e formulários de criação seguirão o mesmo prefixo quando
dependem de um bairro.

Um middleware resolverá a combinação de UF, cidade e bairro, aceitará somente
bairros ativos, compartilhará o bairro atual com as views e registrará o último
bairro visitado. O route binding de posts e negócios será limitado ao bairro da
URL.

Controllers, Actions e componentes Livewire receberão o `Neighborhood`
explicitamente. Componentes Livewire preservarão seu identificador entre
requisições e não tentarão inferi-lo pelo bairro do usuário.

### Rotas globais

Permanecem sem prefixo:

- login, cadastro e recuperação de senha;
- conta, preferências e notificações;
- favoritos e itens salvos;
- ranking geral;
- administração e moderação;
- endpoints técnicos de PWA e Web Push.

### Compatibilidade

- URLs antigas de um post ou negócio redirecionam permanentemente para a URL
  canônica da entidade.
- URLs antigas de listagens redirecionam temporariamente para o bairro
  principal, último bairro ativo ou primeiro bairro ativo, nessa ordem.
- Um conteúdo solicitado sob o bairro errado retorna `404`.
- Um bairro inativo não possui página pública e não aceita conteúdo novo.

## Seleção e persistência do bairro

### Visitante

A raiz mostra:

- identificação do FalaVizin;
- lista ordenada de bairros ativos;
- cidade e estado;
- atalho “Continuar em [bairro]” quando existir um último bairro ainda ativo.

O último bairro será guardado no navegador por um período longo. Ele oferece
conveniência, mas não substitui a validação do bairro ativo no servidor.

### Usuário autenticado

Ao acessar `/`, o usuário será redirecionado ao bairro principal ativo. Se ele
não tiver um bairro principal válido, verá a seleção de bairro.

O seletor da navbar preserva a página ao trocar entre listagens locais, como
feed, serviços e eventos. Em detalhes, formulários ou páginas sem equivalente,
abre a home do bairro escolhido. A opção “Tornar meu bairro principal” é
autenticada, validada e separada da navegação.

Mudar o bairro visitado nunca altera silenciosamente o perfil.

## Cadastro e autenticação

- Ao visitar um bairro, seu identificador ativo é preservado na sessão.
- O cadastro por e-mail usa esse bairro como principal e o apresenta para
  confirmação.
- Sem contexto anterior, o cadastro exige a escolha de um bairro ativo.
- O fluxo Google preserva o contexto durante o redirecionamento OAuth.
- Uma conta Google nova sem contexto conclui uma seleção curta antes de
  acessar o restante do sistema.
- Uma conta existente mantém seu bairro principal.
- Se o bairro principal for desativado, o usuário escolhe outro bairro ativo no
  próximo acesso; nenhum conteúdo histórico é movido.
- A edição do perfil troca o campo textual atual por uma seleção de bairros
  ativos.

## Publicação e cadastro de negócio

O bairro vem da rota do formulário e será mostrado em destaque:

```text
Publicando em Engenho da Rainha
```

O usuário pode trocar de bairro antes de começar ou enviar o formulário. O
servidor ignora qualquer identificador de bairro não correspondente ao contexto
validado da rota.

`CreatePostAction` e `CreateBusinessAction` receberão o bairro explicitamente.
O bairro principal do autor não será usado como fallback silencioso.

O Google Places Import exigirá um bairro ativo, associará os negócios importados
a ele e poderá usar suas coordenadas.

## Consultas e isolamento

Serão filtrados pelo bairro atual:

- home local;
- feed e publicação individual;
- busca;
- serviços e perfil do negócio;
- promoções;
- eventos;
- Pulso;
- conteúdo patrocinado;
- solicitações locais enviadas a comerciantes.

Models centrais poderão oferecer um scope pequeno `forNeighborhood()` para
evitar repetição. Chamadores globais, como moderação e administração, não usam
esse scope.

Links gerados por notificações, compartilhamento, sitemap e SEO incluem sempre
o caminho canônico do bairro da entidade.

Favoritos, itens salvos, notificações e administração continuam agregando
vários bairros. Nessas telas, cada item exibe o bairro quando isso evita
ambiguidade.

O ranking permanece global neste ciclo e identifica o bairro principal dos
usuários. Criar rankings locais exigiria definir a qual bairro cada evento de
pontuação pertence, o que não faz parte desta entrega.

## Cache e desempenho

Toda chave de cache de conteúdo local incluirá o ID do bairro:

```text
home:{neighborhood_id}:featured-businesses
home:{neighborhood_id}:promotions
home:{neighborhood_id}:recent-posts
```

`HomeCache` receberá o bairro e invalidará somente o conjunto conhecido de
chaves daquela home. Não será usado `Cache::flush()`.

Índices serão criados para `neighborhood_id` e para combinações usadas nas
listagens, como bairro, status e data. As relações já carregadas em listas
continuarão usando eager loading.

## Interface

### Navbar

- Desktop: seletor compacto com ícone de localização, nome do bairro e seta,
  próximo ao logotipo.
- Mobile: barra contextual abaixo da linha principal, preservando espaço para
  logotipo, sino e menu.
- O dropdown lista somente bairros ativos e indica o atual.
- Para usuários autenticados, inclui a ação separada “Tornar meu bairro
  principal”.

### Contexto nas páginas

O bairro aparecerá onde orienta uma decisão:

- “Últimas notícias de Engenho da Rainha”;
- “Serviços em Engenho da Rainha”;
- “Eventos em Engenho da Rainha”;
- “Buscando em Engenho da Rainha”;
- “Publicando em Engenho da Rainha”;
- estados vazios locais.

Não será repetido em todos os cards de uma listagem já contextualizada. Em
listas globais, como favoritos, o item recebe um badge de bairro.

Metadados de SEO, títulos, canonical URLs e compartilhamentos também incluem o
bairro quando representam conteúdo local.

## Administração

A área administrativa terá uma tela simples para:

- listar bairros;
- criar bairro;
- editar nome, slugs, UF, coordenadas e ordem;
- ativar ou desativar.

Somente administradores globais executam essas ações. Não serão criados
moderadores ou administradores por bairro neste ciclo.

Um bairro com conteúdo pode ser desativado, mas não excluído pela interface.
Isso preserva referências históricas.

UF, cidade e slugs só poderão ser alterados antes de o bairro possuir conteúdo,
evitando quebrar URLs públicas. Também não será permitido desativar o último
bairro ativo.

A configuração global atual de nome e coordenadas do bairro será substituída
por essa tela. Os valores de `neighborhood_name`, `neighborhood_lat` e
`neighborhood_lng` serão usados para criar o bairro piloto e depois deixarão de
ser consultados.

## Tratamento de erros e segurança

- Todas as operações validam que o bairro existe e está ativo.
- IDs enviados pelo navegador nunca substituem o bairro validado pela rota.
- Usuários só alteram seu próprio bairro principal.
- Posts e negócios precisam pertencer ao bairro da URL.
- Slugs compostos inválidos e conteúdo cruzado retornam `404`.
- Desativar um bairro não apaga nem transfere conteúdo.
- URLs de retorno após login continuam restritas a caminhos internos.
- Nenhum cookie ou sessão representa autorização; são apenas conveniência de
  navegação.

## Migração e lançamento

1. Criar `neighborhoods` e o registro ativo Engenho da Rainha.
2. Adicionar as chaves estrangeiras.
3. Vincular todos os usuários, posts e negócios existentes ao Engenho da
   Rainha.
4. Atualizar gravações, consultas, cache, URLs e interface.
5. Remover os campos textuais antigos de bairro depois do backfill.
6. Publicar ainda com apenas o Engenho da Rainha ativo.
7. Validar isolamento e somente então cadastrar o segundo bairro.

O seed principal será idempotente e garantirá o bairro piloto em instalações
novas. Dados demonstrativos também deverão usar relacionamentos válidos.

## Testes

Testes automatizados cobrirão:

- criação, edição, ativação, desativação e ordenação por administrador;
- proibição de gerenciamento por usuário comum;
- backfill para Engenho da Rainha;
- redirecionamento da raiz para o bairro principal;
- diretório e atalho do último bairro para visitante;
- URLs canônicas e redirects legados;
- rejeição de combinações de UF, cidade e bairro inválidas;
- isolamento de home, feed, busca, serviços, promoções, eventos, Pulso e
  patrocinados;
- ausência de vazamento entre caches de bairros;
- publicação no bairro visitado, inclusive fora do bairro principal;
- cadastro tradicional e login Google;
- seleção obrigatória quando não houver bairro principal;
- troca temporária e alteração explícita do bairro principal;
- importação de negócio no bairro escolhido;
- links de notificações para o bairro correto;
- comportamento de bairro desativado;
- manutenção das permissões globais de admin e moderação.

Também serão executados pelo Sail:

```bash
vendor/bin/sail php artisan migrate
vendor/bin/sail php artisan test
vendor/bin/sail vendor/bin/pint --test
vendor/bin/sail npm run build
```

## Critérios de aceite

- Visitantes encontram os bairros ativos na raiz.
- Usuários autenticados entram no bairro principal.
- O bairro atual está sempre claro no desktop e mobile.
- Trocar de bairro não muda o bairro principal.
- Publicações e negócios são associados ao bairro selecionado.
- Nenhuma consulta ou cache local mistura bairros.
- Links públicos e notificações abrem o bairro correto.
- Administração e moderação continuam globais.
- URLs antigas relevantes continuam funcionando por redirecionamento.
- A suíte existente e os novos testes passam.

## Fora do escopo

- múltiplos bairros por negócio;
- feed agregado de vários bairros;
- busca “em todos os bairros”;
- ranking por bairro;
- mapa e busca por distância;
- cargos administrativos por bairro;
- bancos ou domínios separados;
- mudança automática do bairro principal;
- transferência em massa de conteúdo entre bairros;
- aplicativo ou experiência PWA específica por bairro.

Esses itens só serão considerados quando uso real demonstrar necessidade.
