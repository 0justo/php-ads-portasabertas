<?php
// Recebe o único clique do jogador (escolha = índice da barra), confere com a resposta
// guardada na sessão e encerra a rodada. Também encerra quando o relógio da tela zera
// (acao=encerrar) ou quando o clique chega fora do tempo.

session_start();
require_once __DIR__ . '/grafico-funcoes.php';

$e = jn_exigir_aberta('jogo_grafico');
$n = JG_NIVEIS[$e['nivel']];
$dt = microtime(true) - $e['inicio'];
$esgotou = ($_POST['acao'] ?? '') === 'encerrar' || $dt > $n['t'] + JG_FOLGA;

$escolha = -1;
if (!$esgotou) {
    $s = $_POST['escolha'] ?? null;
    if (!is_string($s) || !preg_match('/^\d$/', $s) || (int) $s >= $n['n']) {
        jn_json(['erro' => 'Clique em um dos servidores do painel.'], 400);
    }
    $escolha = (int) $s;
}

$venceu = !$esgotou && $escolha === $e['resp'];
$ganho  = $venceu ? jn_pontos($e['nivel'], 1, 1 - $dt / $n['t'], $e['seq'] + 1) : 0;
jn_fechar($e, $venceu, $ganho);
$_SESSION['jogo_grafico'] = $e;

jn_json(array_merge(jn_resumo($e, JG_TITULOS), [
    'rodada_fim'     => true,
    'aprovado'       => $venceu,
    'tempo_esgotado' => $esgotou,
    'escolha'        => $escolha,
    'correta'        => $e['resp'],
    'ganho'          => $ganho,
]));
