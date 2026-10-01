<?php
// Todo o conteúdo do site fica aqui. Para mudar um texto, mude aqui.

$link_vestibular = 'https://vestibular.fatec.sp.gov.br/';
$link_curso      = 'https://fatecjd.edu.br/site/analise-e-desenvolvimento-de-sistemas/';

// Ficha do curso (index.php)
$ficha = [
    'Nome'       => 'Tecnologia em Análise e Desenvolvimento de Sistemas',
    'Modalidade' => 'Tecnólogo, presencial',
    'Duração'    => '3 anos (6 semestres)',
    'Período'    => 'Vespertino do 1º ao 4º semestre; noturno no 5º e no 6º',
    'Vagas'      => '25 pelo Vestibular Fatec + 15 pelo Provão Paulista',
    'Estágio'    => '240 horas, a partir do 3º semestre',
    'TG'         => 'Trabalho de Graduação de 160 horas, a partir do 5º semestre',
    'Carga total'=> '2.400 h de aula + 400 h de estágio e TG = 2.800 h',
    'Coordenador'=> 'Prof. João José Ferreira de Aguiar',
    'Endereço'   => 'Av. União dos Ferroviários, 1760, Ponte de Campinas, Jundiaí/SP',
];

// Destaques do curso (index.php): [emoji, título, número grande, texto]. Ficam vermelhos ao passar o mouse.
$numeros = [
    ['🎓', 'Duração',                '3 anos',   '6 semestres, presencial, 2.800 h no total'],
    ['💸', 'Mensalidade',            'R$ 0',     'a Fatec é uma faculdade pública'],
    ['🌤️', 'Períodos',               '2 turnos', 'vespertino do 1º ao 4º semestre, noturno no 5º e no 6º'],
    ['🎟️', 'Vagas',                  '25 + 15',  'Vestibular Fatec e Provão Paulista'],
    ['💼', 'Estágio',                '240 h',    'a partir do 3º semestre'],
    ['📘', 'Trabalho de Graduação',  '160 h',    'a partir do 5º semestre'],
];

// Linha do tempo do curso (matriz curricular diurna)
$semestres = [
    ['1º semestre', 'Algoritmos e Lógica de Programação, Arquitetura e Organização de Computadores, Programação em Microinformática'],
    ['2º semestre', 'Engenharia de Software I, Linguagem de Programação, Laboratório de Hardware'],
    ['3º semestre', 'Engenharia de Software II, Interação Humano-Computador, Estruturas de Dados. O estágio já pode começar.'],
    ['4º semestre', 'Engenharia de Software III, Programação Orientada a Objetos, Redes de Computadores'],
    ['5º semestre', 'Laboratório de Engenharia de Software, Gestão de Equipes, Laboratório de Redes ou Tópicos Especiais. Começa o Trabalho de Graduação.'],
    ['6º semestre', 'Gestão e Governança de TI, Inteligência Artificial ou Auditoria de Sistemas. Reta final do TG.'],
];

// Os 4 perfis do quiz. A única fonte é dados/areas.xml.
// A função monta o mesmo array de sempre: $areas['front']['nome'], ['materias'] etc.
// Se o XML não puder ser lido, devolve [] e o header mostra um aviso amigável.
function carregar_areas($arquivo)
{
    if (!is_file($arquivo)) {
        return [];
    }
    libxml_use_internal_errors(true);          // XML quebrado não vira warning na tela
    $xml = simplexml_load_file($arquivo);
    libxml_clear_errors();
    if ($xml === false) {
        return [];
    }

    $lista = [];
    foreach ($xml->area as $a) {
        $id = (string) $a['id'];
        if ($id === '') {
            continue;
        }
        $materias = [];
        foreach ($a->materias->materia as $m) {
            $materias[] = trim((string) $m);
        }
        $carreiras = [];
        foreach ($a->carreiras->carreira as $c) {
            $carreiras[] = trim((string) $c);
        }
        $lista[$id] = [
            'oque'     => trim((string) $a->oque),
            'nome'     => trim((string) $a->nome),
            'emoji'    => trim((string) $a->emoji),
            'imagem'   => trim((string) $a->imagem),
            'resumo'   => trim((string) $a->resumo),
            'materias' => $materias,
            'carreiras'=> $carreiras,
        ];
    }
    return $lista;
}

$areas = carregar_areas(__DIR__ . '/../dados/areas.xml');
$erro_areas = $areas ? '' : 'Não conseguimos carregar as áreas do curso agora. Avise alguém do grupo ou tente de novo em instantes.';

// Página de integrantes (integrantes.php). Foto opcional em assets/img/integrantes/<ra>.jpg
$integrantes = [
    ['nome' => 'Carlos Eduardo Moreira dos Santos', 'ra' => '40028922'],
    ['nome' => 'Larissa',  'ra' => '40028922'],
    ['nome' => 'Jessica',  'ra' => '40028922'],
    ['nome' => 'Gabriela', 'ra' => '40028922'],
    ['nome' => 'Giovana',  'ra' => '40028922'],
];
$disciplina = ['nome' => 'Linguagem de Programação 4', 'docente' => 'Luciana Pereira Baptista'];

// As 5 perguntas: situações do dia a dia de quem está no ensino médio.
// Ninguém precisa saber nada de tecnologia. Cada opção: [área, emoji, texto]
$perguntas = [
    [
        'emoji'  => '🎮',
        'titulo' => 'Seu grupo de amigos decidiu organizar um campeonato de videogame. Qual é o seu papel?',
        'opcoes' => [
            ['front', '🎨', 'Criar o cartaz, o logo e deixar tudo com cara de evento de verdade'],
            ['back',  '🧩', 'Inventar as regras e o sistema de pontos para tudo funcionar direitinho'],
            ['dados', '📊', 'Cuidar da tabela, do ranking e das estatísticas de cada jogador'],
            ['infra', '🛡️', 'Garantir que internet, som, cabos e consoles estejam funcionando'],
        ],
    ],
    [
        'emoji'  => '📱',
        'titulo' => 'Você ganhou um celular novo. A primeira coisa que faz é…',
        'opcoes' => [
            ['infra', '🛡️', 'Colocar senha, ativar a segurança e fazer o backup das fotos'],
            ['front', '🎨', 'Escolher papel de parede, organizar os ícones e deixar com a sua cara'],
            ['back',  '🧩', 'Fuçar em tudo para descobrir o que ele consegue fazer'],
            ['dados', '📊', 'Ver quanta bateria e quanto tempo de uso cada app gasta'],
        ],
    ],
    [
        'emoji'  => '✨',
        'titulo' => 'Seu app favorito lançou uma novidade. O que te dá mais curiosidade?',
        'opcoes' => [
            ['dados', '📊', 'Como ele sabe do que eu gosto e sugere sempre algo que eu curto?'],
            ['infra', '🛡️', 'Como ele aguenta milhões de pessoas usando ao mesmo tempo sem cair?'],
            ['front', '🎨', 'O visual novo: as telas, os botões, as animações'],
            ['back',  '🧩', 'Como será que fizeram essa função nova funcionar?'],
        ],
    ],
    [
        'emoji'  => '🎬',
        'titulo' => 'Você vai criar o seu próprio canal de vídeos. O que te anima mais?',
        'opcoes' => [
            ['back',  '🧩', 'Criar um robô que responde os comentários e posta sozinho'],
            ['dados', '📊', 'Ver os números: quem assiste, em que horário e o que bomba'],
            ['infra', '🛡️', 'Montar o setup e proteger a conta contra hackers'],
            ['front', '🎨', 'Editar os vídeos e criar a identidade visual do canal'],
        ],
    ],
    [
        'emoji'  => '🍿',
        'titulo' => 'Daqui a 10 anos vão fazer um filme sobre a sua carreira. Ele conta a história de quem…',
        'opcoes' => [
            ['front', '🎨', 'Criou um app que todo mundo ama usar'],
            ['dados', '📊', 'Descobriu algo nos dados que mudou uma decisão gigante'],
            ['back',  '🧩', 'Construiu um sistema enorme que nunca para de funcionar'],
            ['infra', '🛡️', 'Salvou uma empresa inteira de um ataque hacker'],
        ],
    ],
];
