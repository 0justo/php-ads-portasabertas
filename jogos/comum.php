<?php
// Regras que os 4 jogos têm em comum: níveis (5 por padrão; o jogo pode pedir outro total), uma rodada por nível,
// pontuação, título final e estado guardado na sessão.
// Passar de nível = vencer a rodada. Perdeu a rodada = fim de jogo.

const JN_NIVEIS = 5;

function jn_json($dados, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

// Abre a rodada de um nível. Com ?continuar=1 (e nível anterior vencido) sobe de nível;
// senão começa um jogo novo, no nível 1.
function jn_abrir($chave, $total = JN_NIVEIS)
{
    $e = $_SESSION[$chave] ?? null;
    if (isset($_GET['continuar']) && is_array($e) && !empty($e['liberado']) && $e['nivel'] < $total) {
        $e['nivel']++;
    } else {
        $e = ['nivel' => 1, 'vencidos' => 0, 'pts' => 0, 'seq' => 0, 'melhor' => 0];
    }
    $e['liberado'] = false;
    $e['fim']      = false;
    $e['aberta']   = true;
    $e['inicio']   = microtime(true);
    return $e;
}

// Devolve o estado da rodada aberta ou responde erro (só aceita POST).
function jn_exigir_aberta($chave)
{
    $e = $_SESSION[$chave] ?? null;
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_array($e) || empty($e['aberta'])) {
        jn_json(['erro' => 'Essa rodada não está ativa. Clique em "Jogar de novo".'], 400);
    }
    return $e;
}

// Encerra a rodada: vencer libera o próximo nível (ou termina, no 5); perder termina o jogo.
function jn_fechar(array &$e, $venceu, $pontos, $total = JN_NIVEIS)
{
    $e['aberta'] = false;
    if ($venceu) {
        $e['vencidos']++;
        $e['seq']++;
        $e['pts']   += $pontos;
        $e['melhor'] = max($e['melhor'], $e['seq']);
        $e['liberado'] = $e['nivel'] < $total;
        $e['fim']      = $e['nivel'] >= $total;
    } else {
        $e['seq'] = 0;
        $e['fim'] = true;
    }
}

// Pontos de uma rodada vencida: precisão (0 a 1), tempo que sobrou (0 a 1) e sequência de acertos.
function jn_pontos($nivel, $precisao, $resto, $seq)
{
    return (int) round(100 * $nivel * $precisao + 50 * $nivel * max(0, min(1, $resto)) + 25 * min(max($seq - 1, 0), 4));
}

// $nomes: 4 títulos, do mais baixo ao mais alto.
function jn_titulo($vencidos, array $nomes, $total = JN_NIVEIS)
{
    return $nomes[$vencidos >= $total ? 3 : ($vencidos >= ceil($total * 0.6) ? 2 : ($vencidos >= 1 ? 1 : 0))];
}

function jn_resumo(array $e, array $titulos, $total = JN_NIVEIS)
{
    return [
        'nivel'    => $e['nivel'],
        'pts'      => $e['pts'],
        'seq'      => $e['seq'],
        'melhor'   => $e['melhor'],
        'vencidos' => $e['vencidos'],
        'fim'      => $e['fim'],
        'concluiu' => $e['fim'] && $e['vencidos'] >= $total,
        'total'    => $total,
        'titulo'   => jn_titulo($e['vencidos'], $titulos, $total),
    ];
}
