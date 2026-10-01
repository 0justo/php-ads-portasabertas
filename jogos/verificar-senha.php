<?php
// Recebe o palpite, compara com a senha da sessão e devolve as 3 contagens para o painel
// de logs. A senha só é revelada quando a rodada acaba (acertou, tentativas ou tempo).
// Pontos: tentativas que sobraram, tempo que sobrou e posições da senha.

session_start();
require_once __DIR__ . '/senha-funcoes.php';

$e = jn_exigir_aberta('jogo_senha');
$n = JS_NIVEIS[$e['nivel']];
$dt = microtime(true) - $e['inicio'];

function js_fim(array &$e, $venceu, $ganho, $motivo, array $extra = [])
{
    jn_fechar($e, $venceu, $ganho, JS_TOTAL);
    $_SESSION['jogo_senha'] = $e;
    jn_json(array_merge(jn_resumo($e, JS_TITULOS, JS_TOTAL), [
        'rodada_fim' => true, 'aprovado' => $venceu, 'motivo' => $motivo, 'ganho' => $ganho,
        'tentativas' => $e['tentativas'], 'senha' => $e['senha'],
    ], $extra));
}

if (($_POST['acao'] ?? '') === 'encerrar' || $dt > $n['tempo'] + JS_FOLGA) {
    js_fim($e, false, 0, 'tempo');
}

$validas = js_cores_do_nivel($e['nivel']);
$palpite = [];
for ($i = 1; $i <= $n['tam']; $i++) {
    $cor = $_POST["c$i"] ?? null;
    if (!is_string($cor) || !in_array($cor, $validas, true)) {
        jn_json(['erro' => 'Escolha uma cor da lista para cada um dos ' . $n['tam'] . ' campos.'], 400);
    }
    $palpite[] = $cor;
}

$cmp = js_comparar($e['senha'], $palpite);
$e['tentativas']++;
$_SESSION['jogo_senha'] = $e;

if ($cmp['certas'] === $n['tam']) {
    $ganho = 100 * $e['nivel'] + 20 * $n['tam']
           + 15 * $e['nivel'] * ($n['tent'] - $e['tentativas'])
           + (int) round(4 * $e['nivel'] * max(0, $n['tempo'] - $dt))
           + 25 * min($e['seq'], 4);
    js_fim($e, true, $ganho, 'acertou', $cmp + ['palpite' => $palpite]);
}
if ($e['tentativas'] >= $n['tent']) {
    js_fim($e, false, 0, 'tentativas', $cmp + ['palpite' => $palpite]);
}

jn_json([
    'rodada_fim' => false, 'palpite' => $palpite, 'tentativas' => $e['tentativas'],
    'restantes'  => $n['tent'] - $e['tentativas'],
    'certas' => $cmp['certas'], 'deslocadas' => $cmp['deslocadas'], 'ausentes' => $cmp['ausentes'],
]);
