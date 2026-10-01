<?php
// Recebe o clique numa pasta (pasta = 0, 1 ou 2) e confere no PHP o que havia nela naquele
// instante: vírus de verdade (isola e pontua), falso alarme ou nada (penalidade: perde
// pontos e, do nível 3 em diante, perde segundos). Com acao=encerrar fecha a rodada.
// Passa de nível quem isolar a fração mínima das ameaças do nível.

session_start();
require_once __DIR__ . '/virus-funcoes.php';

$e = jn_exigir_aberta('jogo_virus');
$c = JV_NIVEIS[$e['nivel']];
$dt = microtime(true) - $e['inicio'];

if (($_POST['acao'] ?? '') === 'encerrar' || $dt > jv_duracao($e) + JV_FOLGA) {
    $total = jv_total($e['agenda']);
    $bloq  = jv_bloqueados($e['agenda']);
    $minimo = (int) ceil($total * $c['taxa']);
    $venceu = $bloq >= $minimo;
    $ganho  = $venceu ? max(0, $e['pts_rodada']) : 0;
    jn_fechar($e, $venceu, $ganho);
    $_SESSION['jogo_virus'] = $e;
    jn_json(array_merge(jn_resumo($e, JV_TITULOS), [
        'rodada_fim' => true, 'aprovado' => $venceu, 'ganho' => $ganho,
        'bloqueados' => $bloq, 'total' => $total, 'minimo' => $minimo,
    ]));
}

$pasta = $_POST['pasta'] ?? null;
if (!is_string($pasta) || !preg_match('/^\d$/', $pasta) || (int) $pasta >= JV_PASTAS) {
    jn_json(['erro' => 'Clique em uma das 3 pastas do painel.'], 400);
}
$pasta = (int) $pasta;

// O que estava nessa pasta agora? (com uma pequena tolerância para o clique atravessar a rede)
$resultado = 'nada';
foreach ($e['agenda'] as $i => $ev) {
    if ($dt < $ev['inicio'] || $dt > $ev['inicio'] + $c['visivel'] + JV_REDE) continue;
    if (isset($ev['reais'][$pasta]) && !$ev['reais'][$pasta]) {
        $e['agenda'][$i]['reais'][$pasta] = true;
        $rapidez = max(0, 1 - ($dt - $ev['inicio']) / $c['visivel']);
        $e['combo']++;
        $e['pts_rodada'] += 20 * $e['nivel'] + (int) round(15 * $e['nivel'] * $rapidez) + 5 * min($e['combo'] - 1, 5);
        $resultado = 'virus';
        break;
    }
    if ($ev['falso'] === $pasta) {
        $resultado = 'falso';
    }
}

if ($resultado !== 'virus') {   // falso alarme ou pasta limpa: penalidade
    $e['combo'] = 0;
    $e['pts_rodada'] -= 15 * $e['nivel'] * ($resultado === 'falso' ? 2 : 1);
    $e['perdido']    += $c['tempo'] * ($resultado === 'falso' ? 2 : 1);
}
$_SESSION['jogo_virus'] = $e;

jn_json([
    'resultado'  => $resultado,
    'bloqueados' => jv_bloqueados($e['agenda']),
    'parcial'    => $e['pts_rodada'],
]);
