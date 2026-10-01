<?php
// Funções compartilhadas do jogo O Decodificador de Senha (Back-End).
// Usadas por jogo-back.php (textos), reiniciar-senha.php (sorteio) e verificar-senha.php (comparação).

require_once __DIR__ . '/comum.php';

const JS_CORES = [
    'vermelho' => 'Vermelho', 'azul' => 'Azul', 'verde' => 'Verde',
    'amarelo' => 'Amarelo', 'roxo' => 'Roxo',
];
const JS_TOTAL = 3;   // este jogo tem 3 fases
const JS_FOLGA = 2;   // folga para a rede quando o PHP confere o tempo

// 3 fases: a 1ª tem só 3 cores possíveis; a 2ª e a 3ª têm 5. Na 3ª a senha cresce para
// 4 posições e pode repetir cores. Tentativas e tempo diminuem a cada fase.
const JS_NIVEIS = [
    1 => ['nome' => 'Três cores',      'tam' => 3, 'cores' => 3, 'repete' => false, 'tent' => 10, 'tempo' => 50],
    2 => ['nome' => 'Cinco cores',     'tam' => 3, 'cores' => 5, 'repete' => false, 'tent' => 9,  'tempo' => 45],
    3 => ['nome' => 'Cores repetidas', 'tam' => 4, 'cores' => 5, 'repete' => true,  'tent' => 8,  'tempo' => 40],
];

const JS_TITULOS = ['Estagiário de Backend', 'Aprendiz de Lógica', 'Engenheiro de Regras', 'Mestre da Lógica'];

function js_cores_do_nivel($nivel)
{
    return array_slice(array_keys(JS_CORES), 0, JS_NIVEIS[$nivel]['cores']);
}

function js_gerar_senha($nivel)
{
    $n = JS_NIVEIS[$nivel];
    $sobra = js_cores_do_nivel($nivel);
    $senha = [];
    for ($i = 0; $i < $n['tam']; $i++) {
        $k = random_int(0, count($sobra) - 1);
        $senha[] = $sobra[$k];
        if (!$n['repete']) {
            array_splice($sobra, $k, 1);   // sem repetição: a cor sai do sorteio
        }
    }
    return $senha;
}

// Devolve as três contagens: cor no lugar certo, cor que existe na senha mas em outra
// posição e cor que não faz parte da senha.
function js_comparar(array $senha, array $palpite)
{
    $certas = 0;
    $sobra_senha = [];
    $sobra_palpite = [];
    foreach ($senha as $i => $cor) {
        if ($palpite[$i] === $cor) {
            $certas++;
        } else {
            $sobra_senha[] = $cor;
            $sobra_palpite[] = $palpite[$i];
        }
    }
    $deslocadas = 0;
    foreach ($sobra_palpite as $cor) {
        $pos = array_search($cor, $sobra_senha, true);
        if ($pos !== false) {
            $deslocadas++;
            unset($sobra_senha[$pos]);   // cada cor da senha só conta uma vez
        }
    }
    return ['certas' => $certas, 'deslocadas' => $deslocadas, 'ausentes' => count($senha) - $certas - $deslocadas];
}
