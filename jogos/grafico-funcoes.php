<?php
// Funções compartilhadas do jogo O Gráfico Fugitivo (Dados).
// Usadas por jogo-dados.php (textos), reiniciar-grafico.php (sorteio)
// e verificar-grafico.php (conferência do clique).

require_once __DIR__ . '/comum.php';

const JG_SERVIDORES = ['A', 'B', 'C', 'D', 'E'];   // no máximo 5 barras
const JG_FOLGA = 2;                                // folga para a rede quando o PHP confere o tempo

// 5 níveis: n = barras, gap = diferença mínima entre barras (em pontos), t = segundos (5 a 10),
// oc = os gráficos somem após oc segundos (0 = não somem), num = mostra o número em cima da barra
// ANTES da resposta (false em todos os níveis: os valores só aparecem depois que o jogador responde).
const JG_NIVEIS = [
    1 => ['nome' => 'Leitura básica',     'n' => 3, 'gap' => 20, 't' => 10, 'oc' => 0, 'num' => false,  'tipos' => ['exato', 'maior', 'menor']],
    2 => ['nome' => 'Comparações',        'n' => 3, 'gap' => 15, 't' => 9,  'oc' => 0, 'num' => false,  'tipos' => ['exato', 'maior', 'menor', 'faixa']],
    3 => ['nome' => 'Condições lógicas',  'n' => 4, 'gap' => 10, 't' => 8,  'oc' => 0, 'num' => false, 'tipos' => ['exato', 'faixa', 'segundo', 'menor']],
    4 => ['nome' => 'Capacidade em GB',   'n' => 4, 'gap' => 5,  't' => 7,  'oc' => 4, 'num' => false, 'tipos' => ['gb', 'gbfree', 'faixa', 'segundo']],
    5 => ['nome' => 'Memória visual',     'n' => 5, 'gap' => 5,  't' => 6,  'oc' => 3, 'num' => false, 'tipos' => ['gb', 'combo', 'gbfree', 'faixa']],
];

const JG_TITULOS = ['Estagiário de Dados', 'Aprendiz de Dados', 'Analista de Padrões', 'Mestre BI'];

// Índice do maior/menor valor; null se houver empate (a pergunta ficaria ambígua).
function jg_extremo(array $a, $max = true)
{
    $v = $max ? max($a) : min($a);
    $ix = array_keys($a, $v);
    return count($ix) === 1 ? $ix[0] : null;
}

// Sorteia uma rodada: valores (% ocupado), capacidades em GB (nível 4+), pergunta e índice da resposta.
function jg_gerar($nivel)
{
    $c = JG_NIVEIS[$nivel];
    $metricas = ['armazenamento ocupado', 'memória RAM em uso', 'processamento em uso'];

    for ($t = 0; $t < 500; $t++) {
        $pool = range(5, 100, 5);
        shuffle($pool);
        $v = [];
        foreach ($pool as $p) {
            $ok = true;
            foreach ($v as $q) {
                if (abs($p - $q) < $c['gap']) $ok = false;
            }
            if ($ok) $v[] = $p;
            if (count($v) == $c['n']) break;
        }
        if (count($v) < $c['n']) continue;

        // A partir do nível 4 cada servidor tem uma capacidade diferente (em GB).
        $caps = array_fill(0, $c['n'], 100);
        if ($nivel >= 4) {
            $cp = [100, 250, 500, 1000, 2000];
            shuffle($cp);
            $caps = array_slice($cp, 0, $c['n']);
        }
        $gb = [];
        $livre = [];
        foreach ($v as $i => $x) {
            $gb[$i] = $x * $caps[$i] / 100;
            $livre[$i] = $caps[$i] - $gb[$i];
        }

        $tipo = $c['tipos'][array_rand($c['tipos'])];
        $m = $nivel >= 4 ? $metricas[0] : $metricas[array_rand($metricas)];
        $imax = jg_extremo($v);
        $r = null;
        $txt = '';

        if ($tipo == 'exato') {
            $r = array_rand($v);
            $txt = "Encontre o servidor com exatamente {$v[$r]}% de $m.";
        } elseif ($tipo == 'maior') {
            $r = $imax;
            $txt = "Clique no servidor com o MAIOR percentual de $m.";
        } elseif ($tipo == 'menor') {
            $r = jg_extremo($v, false);
            $txt = "Clique no servidor com o MENOR percentual de $m.";
        } elseif ($tipo == 'segundo') {
            $s = $v;
            rsort($s);
            $r = array_search($s[1], $v);
            $txt = "Encontre o servidor com o SEGUNDO maior percentual de $m.";
        } elseif ($tipo == 'faixa') {
            $a = random_int(2, 7) * 10;
            $b = $a + random_int(2, 4) * 10;
            $cand = [];
            foreach ($v as $i => $x) {
                if ($x > $a && $x < $b && $i !== $imax) $cand[] = $i;
            }
            if (count($cand) == 1) {
                $r = $cand[0];
                $txt = "Identifique o servidor com $m acima de $a%, mas abaixo de $b%, que não seja o mais cheio.";
            }
        } elseif ($tipo == 'gb') {
            $r = jg_extremo($gb);
            if ($r === $imax) $r = null;   // evita que a resposta seja só "a barra mais alta"
            $txt = 'Encontre o servidor com a MAIOR quantidade de armazenamento ocupado em GB, considerando a capacidade total de cada unidade.';
        } elseif ($tipo == 'gbfree') {
            $r = jg_extremo($livre);
            $txt = 'Clique no servidor com o MAIOR espaço LIVRE em GB, considerando a capacidade total de cada unidade.';
        } elseif ($tipo == 'combo') {
            $x = [30, 40, 50, 60][array_rand([30, 40, 50, 60])];
            $cand = [];
            foreach ($v as $i => $p) {
                if ($p > $x) $cand[$i] = $gb[$i];
            }
            if (count($cand) >= 2) {
                $mn = min($cand);
                $ix = array_keys($cand, $mn);
                if (count($ix) == 1) {
                    $r = $ix[0];
                    $txt = "Entre os servidores com mais de $x% ocupados, encontre o que tem a MENOR quantidade de GB ocupados.";
                }
            }
        }
        if ($r === null) continue;
        return ['v' => $v, 'caps' => $nivel >= 4 ? $caps : null, 'r' => $r, 'txt' => $txt];
    }
    // Plano B (não deve acontecer): pergunta simples.
    return ['v' => [20, 50, 80], 'caps' => null, 'r' => 2, 'txt' => 'Clique no servidor com o MAIOR percentual de armazenamento ocupado.'];
}
