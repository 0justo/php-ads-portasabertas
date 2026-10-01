<?php
// Abre a rodada de um nível do Gráfico Fugitivo e guarda a resposta certa só na sessão.
// A tela recebe os valores (para desenhar as barras) e a pergunta, mas nunca qual barra é a certa.
// Sem parâmetros começa um jogo novo; com ?continuar=1 sobe de nível.

session_start();
require_once __DIR__ . '/grafico-funcoes.php';

$e = jn_abrir('jogo_grafico');
$n = JG_NIVEIS[$e['nivel']];
$g = jg_gerar($e['nivel']);
$e['resp'] = $g['r'];
$_SESSION['jogo_grafico'] = $e;

jn_json([
    'nivel'   => $e['nivel'],
    'nome'    => $n['nome'],
    'tempo'   => $n['t'],
    'valores' => $g['v'],
    'caps'    => $g['caps'],
    'texto'   => $g['txt'],
    'ocultar' => $n['oc'],
    'mostrar_numeros' => $n['num'],
    'pts'     => $e['pts'],
    'seq'     => $e['seq'],
    'dica'    => $n['n'] . ' servidores' . ($n['oc'] ? ' · o gráfico some em ' . $n['oc'] . 's' : ''),
]);
