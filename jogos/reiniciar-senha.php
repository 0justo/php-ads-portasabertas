<?php
// Abre a rodada de uma fase do Decodificador: sorteia a senha e guarda só na sessão.
// A tela recebe apenas o tamanho, as cores disponíveis, as tentativas e o tempo.
// Sem parâmetros começa um jogo novo; com ?continuar=1 sobe de fase.

session_start();
require_once __DIR__ . '/senha-funcoes.php';

$e = jn_abrir('jogo_senha', JS_TOTAL);
$n = JS_NIVEIS[$e['nivel']];
$e['senha']      = js_gerar_senha($e['nivel']);
$e['tentativas'] = 0;
$_SESSION['jogo_senha'] = $e;

jn_json([
    'nivel'   => $e['nivel'],
    'total'   => JS_TOTAL,
    'nome'    => $n['nome'],
    'tempo'   => $n['tempo'],
    'tamanho' => $n['tam'],
    'cores'   => js_cores_do_nivel($e['nivel']),
    'repete'  => $n['repete'],
    'max'     => $n['tent'],
    'pts'     => $e['pts'],
    'seq'     => $e['seq'],
    'dica'    => $n['tam'] . ' posições · ' . $n['cores'] . ' cores' . ($n['repete'] ? ' · cores podem repetir' : ''),
]);
