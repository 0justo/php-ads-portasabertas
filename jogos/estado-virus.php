<?php
// Diz à tela o que está aparecendo AGORA (e só agora): vírus de verdade e falsos alarmes.
// Os próximos ficam escondidos na sessão. A tela chama este arquivo várias vezes por segundo.

// Só lê a sessão e já solta a trava, para não atrasar os cliques.
session_start(['read_and_close' => true]);
require_once __DIR__ . '/virus-funcoes.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$e = $_SESSION['jogo_virus'] ?? null;
if (!is_array($e) || empty($e['aberta'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Essa rodada não está ativa. Clique em "Jogar de novo".']);
    exit;
}

$c = JV_NIVEIS[$e['nivel']];
$dt = microtime(true) - $e['inicio'];
$restante = jv_duracao($e) - $dt;
$itens = [];

if ($restante > 0) {
    foreach ($e['agenda'] as $i => $ev) {
        if ($dt < $ev['inicio'] || $dt >= $ev['inicio'] + $c['visivel']) continue;
        $some = (int) round(($ev['inicio'] + $c['visivel'] - $dt) * 1000);
        foreach ($ev['reais'] as $pasta => $bloqueado) {
            if (!$bloqueado) $itens[] = ['pasta' => $pasta, 'tipo' => 'virus', 'some_em' => $some];
        }
        if ($ev['falso'] !== null) {
            $itens[] = ['pasta' => $ev['falso'], 'tipo' => 'falso', 'some_em' => $some];
        }
    }
}

echo json_encode([
    'fim'      => $restante <= 0,
    'restante' => max(0, (int) round($restante * 1000)),   // ms
    'itens'    => $itens,
]);
