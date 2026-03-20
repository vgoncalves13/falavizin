<?php

namespace Database\Seeders;

use App\Enums\BusinessPlan;
use App\Enums\BusinessStatus;
use App\Enums\PostStatus;
use App\Models\Business;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Post;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $this->seedCategories();

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@hudobairro.com.br',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $owner = User::create([
            'name' => 'Carlos Oliveira',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'neighborhood' => 'Jardim América',
            'points' => 45,
        ]);

        $moradores = collect([
            ['name' => 'Ana Paula Silva',    'email' => 'ana@example.com',     'points' => 23],
            ['name' => 'Roberto Ferreira',   'email' => 'roberto@example.com', 'points' => 15],
            ['name' => 'Mariana Costa',      'email' => 'mariana@example.com', 'points' => 38],
            ['name' => 'José Santos',        'email' => 'jose@example.com',    'points' => 8],
            ['name' => 'Fernanda Lima',      'email' => 'fernanda@example.com','points' => 52],
            ['name' => 'Paulo Mendes',       'email' => 'paulo@example.com',   'points' => 12],
            ['name' => 'Luciana Rodrigues',  'email' => 'luciana@example.com', 'points' => 29],
            ['name' => 'Diego Alves',        'email' => 'diego@example.com',   'points' => 6],
            ['name' => 'Camila Souza',       'email' => 'camila@example.com',  'points' => 41],
            ['name' => 'Thiago Nunes',       'email' => 'thiago@example.com',  'points' => 18],
        ])->map(fn ($data) => User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'neighborhood' => 'Jardim América',
            'points' => $data['points'],
        ]));

        $allUsers = $moradores->prepend($owner);

        $this->seedBusinesses($owner, $moradores, $allUsers);
        $this->seedPosts($allUsers, $moradores);
    }

    private function seedBusinesses($owner, $moradores, $allUsers): void
    {
        $catAlimentacao = Category::where('slug', 'alimentacao')->first();
        $catSaude       = Category::where('slug', 'saude')->first();
        $catPet         = Category::where('slug', 'pet')->first();
        $catEletrica    = Category::where('slug', 'eletrica')->first();
        $catMercado     = Category::where('slug', 'mercado')->first();
        $catBeleza      = Category::where('slug', 'beleza')->first();
        $catAcademia    = Category::where('slug', 'academia')->first();
        $catEducacao    = Category::where('slug', 'educacao')->first();

        $businesses = [];

        // 1. Padaria — featured, claimed pelo owner
        $businesses[] = $padaria = Business::create([
            'user_id'       => $owner->id,
            'category_id'   => $catAlimentacao->id,
            'name'          => 'Padaria Pão de Mel',
            'description'   => 'Desde 1998 servindo o melhor pão francês e doces artesanais do Jardim América. Café da manhã completo, salgados fresquinhos e bolo de pote que faz sucesso!',
            'phone'         => ['(11) 3456-7890'],
            'whatsapp'      => '(11) 94567-8901',
            'address'       => 'Rua das Flores, 142',
            'neighborhood'  => 'Jardim América',
            'city'          => 'São Paulo',
            'opening_hours' => ['seg-sex' => '05:30-19:00', 'sab' => '06:00-14:00', 'dom' => '06:00-12:00'],
            'plan'          => BusinessPlan::Featured,
            'status'        => BusinessStatus::Approved,
            'claimed'       => true,
            'claimed_at'    => now()->subMonths(3),
        ]);

        // 2. Farmácia — claimed pelo morador[0]
        $businesses[] = $farmacia = Business::create([
            'user_id'       => $moradores[0]->id,
            'category_id'   => $catSaude->id,
            'name'          => 'Farmácia Saúde Total',
            'description'   => 'Medicamentos, perfumaria, dermocosméticos e serviços de saúde. Aferição de pressão gratuita todos os dias. Entregamos em domicílio no bairro.',
            'phone'         => ['(11) 3456-1234'],
            'whatsapp'      => '(11) 99876-5432',
            'address'       => 'Av. Central, 890',
            'neighborhood'  => 'Jardim América',
            'city'          => 'São Paulo',
            'opening_hours' => ['seg-sex' => '08:00-22:00', 'sab' => '08:00-20:00', 'dom' => '09:00-18:00'],
            'plan'          => BusinessPlan::Free,
            'status'        => BusinessStatus::Approved,
            'claimed'       => true,
            'claimed_at'    => now()->subMonths(2),
        ]);

        // 3. Pet Shop — claimed pelo morador[1]
        $businesses[] = $petshop = Business::create([
            'user_id'       => $moradores[1]->id,
            'category_id'   => $catPet->id,
            'name'          => 'Pet Shop Patinhas Felizes',
            'description'   => 'Banho e tosa, consultas veterinárias, rações premium e acessórios para seu pet. Agendamento online disponível. Atendemos cães, gatos, roedores e aves.',
            'phone'         => ['(11) 3456-9876'],
            'whatsapp'      => '(11) 98765-4321',
            'address'       => 'Rua dos Pássaros, 55',
            'neighborhood'  => 'Jardim América',
            'city'          => 'São Paulo',
            'opening_hours' => ['seg-sex' => '09:00-18:00', 'sab' => '09:00-16:00'],
            'plan'          => BusinessPlan::Free,
            'status'        => BusinessStatus::Approved,
            'claimed'       => true,
            'claimed_at'    => now()->subMonth(),
        ]);

        // 4. Elétrica — claimed pelo morador[2]
        $businesses[] = $eletrica = Business::create([
            'user_id'       => $moradores[2]->id,
            'category_id'   => $catEletrica->id,
            'name'          => 'Elétrica do João',
            'description'   => 'Instalações elétricas residenciais e comerciais, manutenção preventiva e corretiva, SPDA (para-raios), CFTV e automação residencial. 20 anos de experiência.',
            'phone'         => ['(11) 99123-4567'],
            'whatsapp'      => '(11) 99123-4567',
            'address'       => 'Rua Ipê Amarelo, 23',
            'neighborhood'  => 'Jardim América',
            'city'          => 'São Paulo',
            'opening_hours' => ['seg-sex' => '07:00-18:00', 'sab' => '08:00-13:00'],
            'plan'          => BusinessPlan::Free,
            'status'        => BusinessStatus::Approved,
            'claimed'       => true,
            'claimed_at'    => now()->subWeeks(3),
        ]);

        // 5. Mercado — não reivindicado
        $businesses[] = $mercado = Business::create([
            'user_id'       => null,
            'category_id'   => $catMercado->id,
            'name'          => 'Mercado Bom Preço',
            'description'   => 'Supermercado completo com açougue, padaria, hortifruti e adega. Delivery disponível via WhatsApp.',
            'phone'         => ['(11) 3456-0011'],
            'address'       => 'Av. Principal, 200',
            'neighborhood'  => 'Jardim América',
            'city'          => 'São Paulo',
            'plan'          => BusinessPlan::Free,
            'status'        => BusinessStatus::Approved,
            'claimed'       => false,
        ]);

        // 6. Salão de beleza — claimed pelo morador[3]
        $businesses[] = $salao = Business::create([
            'user_id'       => $moradores[3]->id,
            'category_id'   => $catBeleza->id,
            'name'          => 'Studio Glamour',
            'description'   => 'Corte, coloração, escova progressiva, manicure e pedicure. Especialistas em cabelos cacheados. Agende pelo WhatsApp e ganhe 10% de desconto na primeira visita!',
            'phone'         => ['(11) 97654-3210'],
            'whatsapp'      => '(11) 97654-3210',
            'address'       => 'Rua das Acácias, 78',
            'neighborhood'  => 'Jardim América',
            'city'          => 'São Paulo',
            'opening_hours' => ['ter-sab' => '09:00-19:00'],
            'plan'          => BusinessPlan::Free,
            'status'        => BusinessStatus::Approved,
            'claimed'       => true,
            'claimed_at'    => now()->subWeeks(5),
        ]);

        // 7. Academia — claimed pelo morador[4], featured
        $businesses[] = $academia = Business::create([
            'user_id'       => $moradores[4]->id,
            'category_id'   => $catAcademia->id,
            'name'          => 'Academia Força & Saúde',
            'description'   => 'Musculação, aeróbico, spinning, crossfit e aulas de yoga. Equipamentos novos, professores formados e vestiário completo. Primeira semana grátis!',
            'phone'         => ['(11) 3456-5555'],
            'whatsapp'      => '(11) 95555-4444',
            'address'       => 'Rua do Esporte, 100',
            'neighborhood'  => 'Jardim América',
            'city'          => 'São Paulo',
            'opening_hours' => ['seg-sex' => '06:00-22:00', 'sab' => '08:00-18:00', 'dom' => '09:00-13:00'],
            'plan'          => BusinessPlan::Featured,
            'status'        => BusinessStatus::Approved,
            'claimed'       => true,
            'claimed_at'    => now()->subMonths(4),
        ]);

        // 8. Escola de idiomas — não reivindicado
        $businesses[] = $escola = Business::create([
            'user_id'       => null,
            'category_id'   => $catEducacao->id,
            'name'          => 'Instituto Conecta Idiomas',
            'description'   => 'Cursos de inglês, espanhol e francês para todas as idades. Turmas presenciais e online. Resultado garantido em 6 meses ou devolvemos o dinheiro.',
            'phone'         => ['(11) 3456-7777'],
            'address'       => 'Rua da Educação, 45',
            'neighborhood'  => 'Jardim América',
            'city'          => 'São Paulo',
            'plan'          => BusinessPlan::Free,
            'status'        => BusinessStatus::Approved,
            'claimed'       => false,
        ]);

        // Promoções
        Promotion::create([
            'business_id' => $padaria->id,
            'title'       => '☕ Café da manhã completo por R$ 18,90',
            'description' => 'Inclui pão francês, manteiga, queijo, presunto, suco de laranja natural e café com leite. Disponível de segunda a sexta das 06h às 09h.',
            'starts_at'   => now()->subDays(5),
            'ends_at'     => now()->addDays(25),
            'is_active'   => true,
            'status'      => 'approved',
        ]);

        Promotion::create([
            'business_id' => $padaria->id,
            'title'       => '🎂 Encomende seu bolo e ganhe 10% de desconto',
            'description' => 'Para encomendas de bolos personalizados com pelo menos 3 dias de antecedência. Sabores: chocolate, cenoura, limão, morango e muito mais!',
            'starts_at'   => now()->subDays(2),
            'ends_at'     => now()->addMonth(),
            'is_active'   => true,
            'status'      => 'approved',
        ]);

        Promotion::create([
            'business_id' => $farmacia->id,
            'title'       => 'Leve 3, pague 2 em vitaminas e suplementos',
            'description' => 'Válido para toda a linha de vitaminas, suplementos e probióticos. Não cumulativo com outras promoções.',
            'starts_at'   => now()->subDays(3),
            'ends_at'     => now()->addDays(15),
            'is_active'   => true,
            'status'      => 'approved',
        ]);

        Promotion::create([
            'business_id' => $petshop->id,
            'title'       => 'Banho + Tosa por R$ 69,90 (até 10kg)',
            'description' => 'Inclui banho com shampoo premium, secagem, escovação e tosa higiênica. Agende pelo WhatsApp e garanta sua vaga!',
            'starts_at'   => now()->subDays(1),
            'ends_at'     => now()->addDays(20),
            'is_active'   => true,
            'status'      => 'approved',
        ]);

        Promotion::create([
            'business_id' => $academia->id,
            'title'       => 'Matrícula gratuita + 1ª mensalidade com 50% off',
            'description' => 'Promoção de inauguração do novo espaço de crossfit. Apresente este anúncio na recepção. Plano a partir de R$ 89,90/mês.',
            'starts_at'   => now()->subDays(7),
            'ends_at'     => now()->addDays(14),
            'is_active'   => true,
            'status'      => 'approved',
        ]);

        Promotion::create([
            'business_id' => $salao->id,
            'title'       => 'Escova progressiva + hidratação por R$ 120,00',
            'description' => 'Combo especial de quinta e sexta-feira. Inclui lavagem, hidratação profunda e escova progressiva. Duração de até 4 meses.',
            'starts_at'   => now()->subDays(4),
            'ends_at'     => now()->addDays(10),
            'is_active'   => true,
            'status'      => 'approved',
        ]);

        // Avaliações
        $this->seedReviews($padaria,  $moradores, [
            [5, 'Melhor padaria do bairro! O pão francês é sempre quentinho e crocante. A equipe é super simpática.',         'Muito obrigado! É um prazer servir vocês todo dia de manhã 😊'],
            [5, 'Frequento desde criança. A qualidade nunca caiu. O bolo de cenoura com brigadeiro é incrível!',              'Nossa, que carinho! Fico feliz que sua família faz parte da nossa história.'],
            [4, 'Ótimos produtos, mas às vezes fica muito cheio nos finais de semana e a fila demora.',                       'Entendemos! Estamos ampliando a equipe nos fins de semana para melhorar o atendimento.'],
            [5, 'Encomendei bolo de aniversário e ficou lindo e delicioso. Todos elogiaram muito!',                           null],
            [3, 'Gosto dos produtos mas o estacionamento é complicado. Seria ótimo ter uma opção de delivery.',               'Boa sugestão! Já estamos avaliando o delivery. Obrigado pelo feedback!'],
        ]);

        $this->seedReviews($farmacia, $moradores, [
            [5, 'Atendimento excelente! A farmacêutica me orientou muito bem sobre os medicamentos.',                         'Ficamos felizes em ajudar! Saúde em primeiro lugar.'],
            [4, 'Boa variedade de produtos e preços competitivos. A fila pode ser longa nos horários de pico.',               'Estamos trabalhando para reduzir o tempo de espera. Obrigado!'],
            [5, 'Serviço de aferição de pressão gratuito é ótimo! Uso toda semana.',                                         null],
            [2, 'Fui buscar um remédio que estava em falta há 2 semanas. Podiam avisar pelo WhatsApp quando chega.',          'Pedimos desculpas! Vamos implementar um sistema de aviso. Obrigado pela sugestão.'],
        ]);

        $this->seedReviews($petshop, $moradores, [
            [5, 'Minha cachorra ama ir ao Pet Shop Patinhas! Sai perfumadinha e sempre feliz.',                               'Que fofura! A Bella é sempre bem-vinda aqui 🐾'],
            [4, 'Banho e tosa muito bem feitos. O veterinário é cuidadoso e explica tudo detalhadamente.',                    null],
            [5, 'Melhor petshop do bairro sem dúvida. Ração sempre no estoque e preço justo.',                               'Obrigado! Trabalhamos para ter sempre os melhores produtos.'],
            [3, 'O serviço é bom, mas achei o tempo de espera longo. Agendei para as 10h e só atenderam às 11h30.',          'Pedimos desculpas pelo atraso! Estamos ajustando nossa agenda para melhorar.'],
        ]);

        $this->seedReviews($eletrica, $moradores, [
            [5, 'João é muito profissional! Resolveu o problema elétrico da minha casa em 2 horas. Recomendo muito.',         'Obrigado pela confiança! Qualquer problema, é só chamar.'],
            [5, 'Instalou o ar-condicionado e fez toda a parte elétrica. Serviço impecável e preço justo.',                  null],
            [4, 'Bom trabalho, pontual e limpo. Só achei o orçamento um pouco salgado.',                                     'Prezamos pela qualidade dos materiais usados. Mas sempre negociamos!'],
        ]);

        $this->seedReviews($mercado, $moradores, [
            [4, 'Bom mercado para o dia a dia. Açougue com carnes frescas e o hortifruti é sempre abastecido.',               null],
            [3, 'Preços razoáveis mas poderia melhorar a organização das prateleiras. Às vezes é difícil encontrar os produtos.', null],
            [5, 'Delivery rápido! Pedi pelo WhatsApp e chegou em 40 minutos. Produtos frescos e bem embalados.',              null],
            [2, 'Encontrei um produto com prazo de validade vencido. Fiquei decepcionado, precisam ter mais cuidado.',        null],
        ]);

        $this->seedReviews($salao, $moradores, [
            [5, 'Melhor salão que já fui! A Fernanda entendeu exatamente o que eu queria. Saí amando o resultado.',           'Fico muito feliz! Você ficou linda mesmo 💖'],
            [5, 'Escova progressiva durou mais de 3 meses. Vale cada centavo!',                                               null],
            [4, 'Atendimento ótimo, ambiente agradável. Só dificulta um pouco o agendamento porque sempre está cheio.',       'É um sinal de que estamos no caminho certo! Já ampliamos os horários.'],
        ]);

        $this->seedReviews($academia, $moradores, [
            [5, 'Academia completa! Equipamentos modernos, professores atenciosos e ótima energia. Me sinto motivado!',       'Isso que a gente quer! Bora continuar evoluindo juntos 💪'],
            [4, 'Excelente infraestrutura. A aula de spinning é incrível. Só poderia ter mais vagas.',                        'Estamos abrindo novas turmas! Fique de olho nas nossas novidades.'],
            [5, 'Primeiro semana grátis valeu muito a pena. Me convenci de vez e já me matriculei.',                          null],
            [3, 'Boa academia, mas o estacionamento é pequeno. Nos horários de pico é complicado.',                           'Já estamos conversando com o estacionamento vizinho para parceria!'],
        ]);
    }

    private function seedReviews(Business $business, $moradores, array $reviews): void
    {
        foreach ($reviews as $index => [$rating, $body, $reply]) {
            $user = $moradores[$index % $moradores->count()];

            $review = Review::create([
                'user_id'          => $user->id,
                'business_id'      => $business->id,
                'rating'           => $rating,
                'body'             => $body,
                'owner_reply'      => $reply,
                'owner_replied_at' => $reply ? now()->subDays(rand(1, 10)) : null,
            ]);
        }
    }

    private function seedPosts($allUsers, $moradores): void
    {
        $catAviso       = Category::where('slug', 'aviso')->first();
        $catProblema    = Category::where('slug', 'problema')->first();
        $catEvento      = Category::where('slug', 'evento')->first();
        $catAchado      = Category::where('slug', 'achado-perdido')->first();
        $catAlimentacao = Category::where('slug', 'alimentacao')->first();

        $postsData = [
            // Avisos
            [
                'user'        => $allUsers[0],
                'category'    => $catAviso,
                'title'       => 'Reunião de moradores — sábado às 10h na praça',
                'body'        => "Moradores do Jardim América,\n\nConvocamos todos para uma reunião importante na próxima sábado (28/06) às 10h na praça central.\n\nPauta principal:\n• Situação das ruas esburacadas\n• Iluminação pública\n• Projeto de horta comunitária\n\nSua presença é fundamental para tomarmos decisões que afetam a todos. Traga ideias e sugestões!\n\nAté sábado! 👋",
                'location'    => 'Praça Central do Jardim América',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'comments'    => [
                    [$allUsers[1], 'Confirmado! Vou estar lá com certeza.'],
                    [$allUsers[2], 'Ótima iniciativa! Vou divulgar para os vizinhos.'],
                    [$allUsers[3], 'Que horas começa mesmo? Vi que é às 10h, mas queria confirmar.'],
                ],
            ],
            [
                'user'        => $allUsers[1],
                'category'    => $catAviso,
                'title'       => 'Atenção: água será cortada amanhã das 8h às 14h',
                'body'        => "Informação recebida da Sabesp:\n\nAmanhã, dia 20/06, haverá interrupção no fornecimento de água das 08h às 14h em todo o Jardim América e bairros adjacentes.\n\nO motivo é manutenção preventiva na rede de distribuição.\n\nRecomendo que todos reservem água suficiente para o período. Idosos e pessoas com necessidades especiais, procurem ajuda dos vizinhos se precisar.\n\nFonte: site oficial da Sabesp.",
                'location'    => null,
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'comments'    => [
                    [$allUsers[4], 'Obrigado pelo aviso! Vou encher todos os potes hoje à noite.'],
                    [$allUsers[5], 'Já vi isso no site deles também. Confirmado.'],
                ],
            ],
            // Problemas
            [
                'user'        => $allUsers[2],
                'category'    => $catProblema,
                'title'       => 'Buraco perigoso na Rua das Flores — já causou acidente',
                'body'        => "Moradores,\n\nHá um buraco enorme na Rua das Flores, próximo ao número 280. Ontem à noite uma motocicleta caiu por causa dele. Graças a Deus o rapaz ficou só com machucados leves.\n\nJá abri chamado na prefeitura (protocolo #2024-89234) mas não tiveram resposta ainda. Quem puder, também abra um chamado. Quanto mais denúncias, mais rápido resolvem.\n\nLink para denúncia: prefeitura.sp.gov.br/cuidadedecia\n\nCuidado ao passar por lá, especialmente à noite!",
                'location'    => 'Rua das Flores, 280 — Jardim América',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'comments'    => [
                    [$allUsers[6], 'Que absurdo! Esse buraco está lá há mais de 3 semanas. Já fiz minha denúncia também.'],
                    [$allUsers[7], 'Passei lá hoje cedo e coloquei uns cones que tinha em casa para alertar. Mas precisamos de uma solução definitiva!'],
                    [$allUsers[8], 'Já compartilhei no grupo do bairro. Vamos todos denunciar!'],
                    [$allUsers[0], 'Abri chamado agora. Protocolo #2024-90145. Esperando retorno.'],
                ],
            ],
            [
                'user'        => $allUsers[3],
                'category'    => $catProblema,
                'title'       => 'Lâmpadas queimadas na Rua dos Pássaros — rua escura há 2 semanas',
                'body'        => "Pessoal, a Rua dos Pássaros está completamente escura há 2 semanas. São 4 lâmpadas queimadas seguidas, o que torna a rua perigosa à noite.\n\nJá liguei para a Enel (0800 722 0120) duas vezes e fizeram dois protocolos que não foram atendidos.\n\nSe alguém mais mora ou passa por lá, por favor denuncie também. Quanto mais chamados, mais rápido vem a manutenção.",
                'location'    => 'Rua dos Pássaros — trecho entre a Rua Ipê e Av. Central',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'comments'    => [
                    [$allUsers[1], 'Confirmo! Quase tropeçai aí ontem à noite. Vou ligar agora.'],
                    [$allUsers[9], 'Fiz a denúncia via app Enel. Protocolo gerado. Vamos ver se resolvem.'],
                ],
            ],
            // Eventos
            [
                'user'        => $allUsers[4],
                'category'    => $catEvento,
                'title'       => 'Festa Junina do Jardim América — 28 de junho',
                'body'        => "🎉 Chegou a hora mais esperada do ano!\n\nA Festa Junina do Jardim América será no dia 28 de junho, sábado, das 16h às 22h, na praça central.\n\nO evento contará com:\n🌽 Comidas típicas (canjica, pamonha, milho, quentão)\n💃 Quadrilha com o grupo Arraiá do Bairro\n🎵 Show ao vivo com música sertaneja e forró\n🎪 Jogos e brincadeiras para as crianças\n\nEntrada gratuita! Venha com a família toda!\n\nOrganização: Associação de Moradores do Jardim América",
                'location'    => 'Praça Central do Jardim América',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'event_starts_at' => now()->addDays(8)->setTime(16, 0),
                'event_ends_at'   => now()->addDays(8)->setTime(22, 0),
                'comments'    => [
                    [$allUsers[5], 'Não vejo a hora!! Já avisando para toda a família.'],
                    [$allUsers[6], 'Vai ter como ajudar na organização? Posso contribuir.'],
                    [$allUsers[7], 'As crianças estão animadíssimas! Obrigada pela organização.'],
                    [$allUsers[8], 'Vai ter estacionamento? O da praça é pequeno para um evento assim.'],
                ],
            ],
            [
                'user'        => $allUsers[5],
                'category'    => $catEvento,
                'title'       => 'Mutirão de limpeza do parquinho — próximo domingo',
                'body'        => "Vamos cuidar do nosso bairro juntos!\n\nNo próximo domingo, dia 22, às 9h, faremos um mutirão de limpeza e revitalização do parquinho da Rua das Acácias.\n\nNecessitamos de voluntários para:\n• Pintar os brinquedos\n• Capinar e limpar a área\n• Plantar flores e grama\n• Consertar o banco quebrado\n\nTodos são bem-vindos! Traga luvas, protetor solar e boa vontade. Ferramentas serão fornecidas.\n\nCervejas geladas por conta da organização ao final! 🍺",
                'location'    => 'Parquinho da Rua das Acácias',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'event_starts_at' => now()->addDays(3)->setTime(9, 0),
                'event_ends_at'   => now()->addDays(3)->setTime(13, 0),
                'comments'    => [
                    [$allUsers[9], 'Que iniciativa linda! Vou levar minha família toda.'],
                    [$allUsers[0], 'Posso levar minha betoneira e tinta. Me avisem quanto precisam.'],
                    [$allUsers[1], 'Estarei lá! Vou travar algumas mudas do meu jardim para plantar.'],
                ],
            ],
            // Achado e Perdido
            [
                'user'        => $allUsers[6],
                'category'    => $catAchado,
                'title'       => 'Perdido: Labrador dourado, responde pelo nome de Bolinha',
                'body'        => "😢 Minha família está desesperada!\n\nNosso cachorro Bolinha, labrador dourado de 4 anos, fugiu ontem à tarde pela porta que a criança deixou aberta.\n\nCaracterísticas:\n• Labrador dourado, sem rabo (tem bobinha)\n• Collar azul com plaquinha (mas pode ter caído)\n• Muito dócil e sociável\n• Responde ao nome Bolinha\n• Última vez visto na Rua das Flores por volta das 15h\n\nQuem encontrar ou tiver alguma informação, por favor me ligue: (11) 98765-0000\n\nRecompensa para quem ajudar a encontrá-lo! 🙏",
                'location'    => 'Região da Rua das Flores — Jardim América',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'comments'    => [
                    [$allUsers[7], 'Vi um cachorro parecido perto da Av. Central hoje cedo! Mas não consegui pegar. Vou ficar de olho.'],
                    [$allUsers[8], 'Compartilhando nos grupos! Espero que achem logo.'],
                    [$allUsers[9], 'Minha vizinha disse que viu um labrador na praça ontem à noite. Vou pegar o contato dela pra você.'],
                ],
            ],
            [
                'user'        => $allUsers[7],
                'category'    => $catAchado,
                'title'       => 'Achado: Carteira masculina próxima à padaria',
                'body'        => "Encontrei uma carteira masculina esta manhã na calçada em frente à Padaria Pão de Mel.\n\nContém documentos (não vou listar aqui por segurança) e alguns cartões. Não havia dinheiro.\n\nPara retirar: entre em contato por aqui descrevendo os documentos que estão na carteira e combinamos a entrega.\n\nNão custa nada ser honesto! 😊",
                'location'    => 'Em frente à Padaria Pão de Mel, Rua das Flores',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'comments'    => [
                    [$allUsers[0], 'Que pessoa honesta! Parabéns pela atitude.'],
                    [$allUsers[1], 'Compartilhei para tentar encontrar o dono.'],
                ],
            ],
            // Alimentação
            [
                'user'        => $allUsers[8],
                'category'    => $catAlimentacao,
                'title'       => 'Dica: nova pizzaria abriu no bairro e está incrível',
                'body'        => "Pessoal, abriu uma pizzaria nova na Rua Ipê Amarelo (onde era o antigo sapateiro) e ela é INCRÍVEL!\n\nFui ontem com a família e pedimos:\n🍕 Pizza de calabresa: massa fina, molho caseiro, muito queijo\n🍕 Pizza de frango com requeijão: simplesmente divina\n🥗 Salada Caesar: fresca e bem temperada\n\nValor médio R$ 55-70 (pizza grande para 2 pessoas). Aceitam todas as formas de pagamento. Entregam no bairro por R$ 5.\n\nNome: Pizzaria Bella Napoli\nTelefone: (11) 3456-9999\n\nJá recomendo! ⭐⭐⭐⭐⭐",
                'location'    => 'Rua Ipê Amarelo — Jardim América',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'comments'    => [
                    [$allUsers[9], 'Fui na semana passada também! A beira de catupiry é sensacional.'],
                    [$allUsers[0], 'Boa dica! Vou pedir hoje para o jantar.'],
                    [$allUsers[1], 'Que ótimo! Precisava muito de uma pizzaria boa no bairro.'],
                ],
            ],
            // Post patrocinado
            [
                'user'        => $allUsers[4],
                'category'    => $catAviso,
                'title'       => 'Academia Força & Saúde: últimas vagas para turma de julho',
                'body'        => "📣 Atenção moradores do Jardim América!\n\nAs turmas de julho da Academia Força & Saúde estão quase cheias!\n\nRestam poucas vagas para:\n💪 Musculação — turma das 7h e 20h\n🏃 Spinning — terças e quintas às 19h\n🧘 Yoga — segundas e quartas às 8h\n🥊 Crossfit — vagas abertas\n\nBenefícios exclusivos para moradores do Jardim América:\n✅ Matrícula gratuita\n✅ 1ª mensalidade com 50% de desconto\n✅ Avaliação física grátis\n\nVenha fazer uma visita e conhecer nossas instalações!",
                'location'    => 'Rua do Esporte, 100 — Jardim América',
                'status'      => PostStatus::Approved,
                'is_sponsored'=> true,
                'comments'    => [
                    [$allUsers[2], 'Já sou aluno e recomendo muito! Equipe excelente.'],
                    [$allUsers[3], 'Qual é o valor da mensalidade?'],
                ],
            ],
            // Post com enquete
            [
                'user'        => $allUsers[9],
                'category'    => $catAviso,
                'title'       => 'Qual melhoria você mais quer no bairro?',
                'body'        => "Moradores,\n\nEstamos mapeando as principais necessidades do Jardim América para apresentar à subprefeitura na próxima reunião.\n\nVote na melhoria que você considera mais urgente! Os resultados serão apresentados oficialmente.\n\nSua opinião faz a diferença! Compartilhe com os vizinhos para que mais pessoas participem.",
                'location'    => null,
                'status'      => PostStatus::Approved,
                'is_sponsored'=> false,
                'poll'        => [
                    'question' => 'Qual melhoria é mais urgente no Jardim América?',
                    'options'  => ['Recapeamento das ruas', 'Iluminação pública', 'Reforma do parquinho', 'Mais câmeras de segurança', 'Coleta seletiva de lixo'],
                ],
                'comments'    => [
                    [$allUsers[0], 'Votei! As ruas estão um absurdo.'],
                    [$allUsers[1], 'Difícil escolher só um... mas fui de iluminação pública, está perigoso à noite.'],
                ],
            ],
            // Post pendente (moderação)
            [
                'user'        => $allUsers[3],
                'category'    => $catProblema,
                'title'       => 'Esgoto transbordando na Rua das Acácias',
                'body'        => "Urgente! O esgoto está transbordando na Rua das Acácias desde ontem à tarde. O cheiro está insuportável e tem crianças brincando perto.\n\nJá liguei para a Sabesp mas ainda não mandaram ninguém. Se alguém souber como acelerar o atendimento, por favor me ajude.",
                'location'    => 'Rua das Acácias, altura do número 120',
                'status'      => PostStatus::Pending,
                'is_sponsored'=> false,
                'comments'    => [],
            ],
        ];

        foreach ($postsData as $data) {
            $postAttributes = [
                'user_id'         => $data['user']->id,
                'category_id'     => $data['category']->id,
                'title'           => $data['title'],
                'body'            => $data['body'],
                'location'        => $data['location'] ?? null,
                'status'          => $data['status'],
                'is_sponsored'    => $data['is_sponsored'] ?? false,
                'approved_at'     => $data['status'] === PostStatus::Approved ? now()->subDays(rand(1, 30)) : null,
                'event_starts_at' => $data['event_starts_at'] ?? null,
                'event_ends_at'   => $data['event_ends_at'] ?? null,
            ];

            $post = Post::create($postAttributes);

            // Comentários
            foreach ($data['comments'] as [$commentUser, $commentBody]) {
                Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $commentUser->id,
                    'body'    => $commentBody,
                    'status'  => 'approved',
                ]);
            }

            // Votos
            if ($data['status'] === PostStatus::Approved) {
                $voters = $allUsers->random(rand(2, 6));
                foreach ($voters as $voter) {
                    Vote::firstOrCreate([
                        'user_id'      => $voter->id,
                        'votable_type' => Post::class,
                        'votable_id'   => $post->id,
                    ], ['type' => 'helpful']);
                }
            }

            // Enquete
            if (isset($data['poll'])) {
                $poll = Poll::create([
                    'post_id'  => $post->id,
                    'question' => $data['poll']['question'],
                    'ends_at'  => now()->addDays(14),
                ]);

                foreach ($data['poll']['options'] as $optionText) {
                    PollOption::create(['poll_id' => $poll->id, 'text' => $optionText]);
                }

                // Votos aleatórios na enquete
                $options = $poll->options;
                foreach ($allUsers->random(7) as $voter) {
                    PollVote::create([
                        'poll_id'        => $poll->id,
                        'poll_option_id' => $options->random()->id,
                        'user_id'        => $voter->id,
                    ]);
                }
            }
        }
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Aviso',              'slug' => 'aviso',           'icon' => 'megaphone',            'type' => 'post',     'sort_order' => 1],
            ['name' => 'Problema',           'slug' => 'problema',        'icon' => 'exclamation-triangle', 'type' => 'post',     'sort_order' => 2],
            ['name' => 'Evento',             'slug' => 'evento',          'icon' => 'calendar-days',        'type' => 'post',     'sort_order' => 3],
            ['name' => 'Achado e Perdido',   'slug' => 'achado-perdido',  'icon' => 'magnifying-glass',     'type' => 'post',     'sort_order' => 4],
            ['name' => 'Alimentação',        'slug' => 'alimentacao',     'icon' => 'cake',                 'type' => 'both',     'sort_order' => 5],
            ['name' => 'Mercado',            'slug' => 'mercado',         'icon' => 'shopping-cart',        'type' => 'business', 'sort_order' => 6],
            ['name' => 'Saúde',              'slug' => 'saude',           'icon' => 'heart',                'type' => 'business', 'sort_order' => 7],
            ['name' => 'Pet',                'slug' => 'pet',             'icon' => 'face-smile',           'type' => 'business', 'sort_order' => 8],
            ['name' => 'Elétrica',           'slug' => 'eletrica',        'icon' => 'bolt',                 'type' => 'business', 'sort_order' => 9],
            ['name' => 'Encanamento',        'slug' => 'encanamento',     'icon' => 'wrench',               'type' => 'business', 'sort_order' => 10],
            ['name' => 'Pintura',            'slug' => 'pintura',         'icon' => 'paint-brush',          'type' => 'business', 'sort_order' => 11],
            ['name' => 'Internet',           'slug' => 'internet',        'icon' => 'wifi',                 'type' => 'business', 'sort_order' => 12],
            ['name' => 'Educação',           'slug' => 'educacao',        'icon' => 'academic-cap',         'type' => 'business', 'sort_order' => 13],
            ['name' => 'Beleza',             'slug' => 'beleza',          'icon' => 'sparkles',             'type' => 'business', 'sort_order' => 14],
            ['name' => 'Academia',           'slug' => 'academia',        'icon' => 'trophy',               'type' => 'business', 'sort_order' => 15],
            ['name' => 'Automotivo',         'slug' => 'automotivo',      'icon' => 'cog-6-tooth',          'type' => 'business', 'sort_order' => 16],
            ['name' => 'Banco & Finanças',   'slug' => 'banco',           'icon' => 'banknotes',            'type' => 'business', 'sort_order' => 17],
            ['name' => 'Casa & Construção',  'slug' => 'casa',            'icon' => 'building-office-2',    'type' => 'business', 'sort_order' => 18],
            ['name' => 'Moda',               'slug' => 'moda',            'icon' => 'shopping-bag',         'type' => 'business', 'sort_order' => 19],
            ['name' => 'Serviços',           'slug' => 'servicos',        'icon' => 'briefcase',            'type' => 'business', 'sort_order' => 20],
            ['name' => 'Transporte',         'slug' => 'transporte',      'icon' => 'truck',                'type' => 'business', 'sort_order' => 21],
            ['name' => 'Entretenimento',     'slug' => 'entretenimento',  'icon' => 'musical-note',         'type' => 'business', 'sort_order' => 22],
            ['name' => 'Outros',             'slug' => 'outros',          'icon' => 'ellipsis-horizontal',  'type' => 'business', 'sort_order' => 99],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
