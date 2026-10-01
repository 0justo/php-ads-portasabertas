<?php
// Funções compartilhadas do jogo O Corta-Vírus (Infra & Segurança).
// Usadas por jogo-infra.php (textos), reiniciar-virus.php (agenda secreta),
// estado-virus.php (o que está aparecendo agora) e verificar-virus.php (cliques).

require_once __DIR__ . '/comum.php';

const JV_TEMPO  = 10;    // cada nível dura exatamente 10 segundos
const JV_PASTAS = 3;     // 3 pastas alinhadas no painel
const JV_REDE   = 0.4;   // tolerância para o clique atravessar a rede depois que o vírus some
const JV_FOLGA  = 2;     // folga para a rede no fim do jogo

// 5 níveis. visivel = segundos que o vírus fica na tela, pausa = intervalo entre vírus (ms),
// seq = posições previsíveis (0,1,2,0,1,2...), dupla = chance de 2 vírus ao mesmo tempo,
// falso = chance de aparecer um falso alarme, taxa = fração das ameaças que precisa ser
// isolada para passar, tempo = segundos perdidos a cada clique errado.
const JV_NIVEIS = [
    1 => ['nome' => 'Ameaça previsível',   'visivel' => 1.4, 'pausa' => [600, 900], 'seq' => true,  'dupla' => 0.0, 'falso' => 0.0, 'taxa' => 0.5,  'tempo' => 0],
    2 => ['nome' => 'Posições aleatórias', 'visivel' => 1.1, 'pausa' => [500, 800], 'seq' => false, 'dupla' => 0.0, 'falso' => 0.0, 'taxa' => 0.55, 'tempo' => 0],
    3 => ['nome' => 'Ataque veloz',        'visivel' => 0.9, 'pausa' => [300, 600], 'seq' => false, 'dupla' => 0.0, 'falso' => 0.0, 'taxa' => 0.6,  'tempo' => 0.5],
    4 => ['nome' => 'Ameaça dupla',        'visivel' => 0.8, 'pausa' => [350, 650], 'seq' => false, 'dupla' => 0.4, 'falso' => 0.3, 'taxa' => 0.6,  'tempo' => 0.75],
    5 => ['nome' => 'Firewall total',      'visivel' => 0.7, 'pausa' => [300, 550], 'seq' => false, 'dupla' => 0.6, 'falso' => 0.5, 'taxa' => 0.6,  'tempo' => 1.0],
];

const JV_TITULOS = ['Estagiário de Infra', 'Aprendiz de Redes', 'Analista de Segurança', 'Guardião do Firewall'];

// Agenda secreta do nível: cada evento tem o segundo em que começa, as pastas com vírus de
// verdade (pasta => já isolado?) e, às vezes, uma pasta com falso alarme.
function jv_gerar_agenda($nivel)
{
    $c = JV_NIVEIS[$nivel];
    $agenda = [];
    $t = 0.4 + random_int(0, 300) / 1000;   // o primeiro vem logo no começo
    $k = 0;
    while ($t + $c['visivel'] <= JV_TEMPO) {
        if ($c['seq']) {
            $reais = [$k % JV_PASTAS];
        } else {
            $pastas = range(0, JV_PASTAS - 1);
            shuffle($pastas);
            $qtd = (random_int(1, 100) <= $c['dupla'] * 100) ? 2 : 1;
            $reais = array_slice($pastas, 0, $qtd);
        }
        $falso = null;
        $livres = array_values(array_diff(range(0, JV_PASTAS - 1), $reais));
        if ($livres && random_int(1, 100) <= $c['falso'] * 100) {
            $falso = $livres[array_rand($livres)];
        }
        $agenda[] = [
            'inicio' => $t,
            'reais'  => array_fill_keys($reais, false),
            'falso'  => $falso,
        ];
        $t += $c['visivel'] + random_int($c['pausa'][0], $c['pausa'][1]) / 1000;
        $k++;
    }
    return $agenda;
}

function jv_total(array $agenda)
{
    $n = 0;
    foreach ($agenda as $ev) $n += count($ev['reais']);
    return $n;
}

function jv_bloqueados(array $agenda)
{
    $n = 0;
    foreach ($agenda as $ev) $n += count(array_filter($ev['reais']));
    return $n;
}

function jv_duracao(array $e)
{
    return JV_TEMPO - $e['perdido'];
}
