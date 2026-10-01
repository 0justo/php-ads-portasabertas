<?php
// Abre a rodada de uma fase do Alvo da Cor: sorteia a cor-alvo e guarda só na sessão.
// Sem parâmetros começa um jogo novo (fase 1); com ?continuar=1 sobe para a próxima fase.

session_start();
require_once __DIR__ . '/cor-funcoes.php';

$e = jn_abrir('jogo_cor', JC_TOTAL);
$n = JC_NIVEIS[$e['nivel']];
$e['alvo'] = jc_gerar_cor($n['faixa']);
$_SESSION['jogo_cor'] = $e;

jn_json([
    'nivel'       => $e['nivel'],
    'total'       => JC_TOTAL,
    'nome'        => $n['nome'],
    'tempo'       => $n['tempo'],
    'mostrar_rgb' => $n['rgb'],
    'alvo_hex'    => jc_hex($e['alvo']),
    'pts'         => $e['pts'],
    'seq'         => $e['seq'],
    'dica'        => $n['rgb'] ? 'Valores RGB visíveis' : 'Valores RGB ocultos',
]);
