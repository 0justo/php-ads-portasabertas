<?php
// Funções compartilhadas do jogo O Alvo da Cor (Front-End / UX).
// Usadas por jogo-front.php (textos), reiniciar-cor.php (sorteio) e verificar-cor.php (nota).

require_once __DIR__ . '/comum.php';

const JC_TOTAL = 3;   // este jogo tem 3 fases
const JC_FOLGA = 2;   // folga para a rede quando o PHP confere o tempo

// 3 fases, sem porcentagem mínima de acerto: passa quem envia a cor dentro do tempo da fase
// (a proximidade só rende mais ou menos pontos). O tempo fica entre 10 e 15 s e encurta a cada
// fase; os números RGB somem na fase 2 e as cores-alvo ficam cada vez mais parecidas entre si
// (faixa menor = canais R, G e B mais próximos).
const JC_NIVEIS = [
    1 => ['nome' => 'Cores vivas',    'tempo' => 15, 'rgb' => true,  'faixa' => 255],
    2 => ['nome' => 'Sem números',    'tempo' => 12, 'rgb' => false, 'faixa' => 140],
    3 => ['nome' => 'Tons parecidos', 'tempo' => 10, 'rgb' => false, 'faixa' => 70],
];

const JC_TITULOS = ['Estagiário de UX', 'Aprendiz de Design', 'Designer de Interfaces', 'Mestre das Cores'];

// Cor-alvo aleatória. Com faixa 255 os três canais são livres; com faixa menor,
// R, G e B ficam a no máximo faixa/2 de um valor central, formando tons parecidos.
function jc_gerar_cor($faixa)
{
    $m = intdiv($faixa, 2);
    $centro = random_int($m, 255 - $m);
    $cor = [];
    foreach (['r', 'g', 'b'] as $k) {
        $cor[$k] = max(0, min(255, $centro + random_int(-$m, $m)));
    }
    return $cor;
}

function jc_hex(array $c)
{
    return sprintf('#%02X%02X%02X', $c['r'], $c['g'], $c['b']);
}
