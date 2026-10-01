<?php
// Monta a agenda secreta de vírus de um nível e guarda só na sessão.
// A tela não recebe a agenda: ela pergunta ao estado-virus.php o que está aparecendo agora.
// Sem parâmetros começa um jogo novo; com ?continuar=1 sobe de nível.

session_start();
require_once __DIR__ . '/virus-funcoes.php';

$e = jn_abrir('jogo_virus');
$n = JV_NIVEIS[$e['nivel']];
$e['agenda']  = jv_gerar_agenda($e['nivel']);
$e['perdido'] = 0.0;   // segundos tirados por cliques errados
$e['pts_rodada'] = 0;
$e['combo']   = 0;
$_SESSION['jogo_virus'] = $e;

jn_json([
    'nivel'  => $e['nivel'],
    'nome'   => $n['nome'],
    'tempo'  => JV_TEMPO,
    'pts'    => $e['pts'],
    'seq'    => $e['seq'],
    'dica'   => $n['falso'] > 0 ? '⚠️ é falso alarme: não clique!' : ($n['tempo'] > 0 ? 'Clique errado tira ' . $n['tempo'] . 's' : ''),
]);
