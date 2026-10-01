<?php
$titulo = 'O que você vai usar';
$pagina = 'tecnologias';
include 'includes/header.php';

// Lê as ferramentas e conceitos de dados/tecnologias.json.
// Se o arquivo sumir ou estiver quebrado, a página mostra um aviso em vez de erro.
$arquivo_tec = __DIR__ . '/dados/tecnologias.json';
$tecnologias = [];
$erro_tec    = '';
$conteudo    = is_file($arquivo_tec) ? file_get_contents($arquivo_tec) : false;
$lidos       = $conteudo === false ? null : json_decode($conteudo, true);

if (is_array($lidos)) {
    // Só entra na tela quem tem todos os campos em texto; a área precisa ser uma das 4 cores.
    $campos = ['id', 'nome', 'imagem', 'descricao', 'materia', 'area'];
    foreach ($lidos as $item) {
        $completo = is_array($item);
        foreach ($campos as $c) {
            $completo = $completo && isset($item[$c]) && is_string($item[$c]);
        }
        if (!$completo) {
            continue;
        }
        if (!in_array($item['area'], ['front', 'back', 'dados', 'infra'], true)) {
            $item['area'] = '';
        }
        $tecnologias[] = $item;
    }
}
if (!$tecnologias) {
    $erro_tec = 'Não conseguimos carregar a lista de tecnologias agora. Tente de novo em instantes.';
}
?>

<div class="wrap pagina">
  <header class="pagina-cabeca">
    <p class="kicker">Tecnologias &middot; na prática</p>
    <h1 class="h1-menor">O que você vai <mark>usar</mark></h1>
    <p class="lead">Ferramentas e ideias que aparecem durante o curso. Não precisa saber nada disso antes: é justamente o que você vai aprender.</p>
  </header>

  <?php if ($erro_tec): ?>
    <p class="aviso" role="alert"><?= htmlspecialchars($erro_tec) ?></p>
  <?php else: ?>
    <div class="grade-tec">
      <?php foreach ($tecnologias as $t): ?>
        <article class="area tec<?= $t['area'] ? ' a-' . $t['area'] : '' ?>">
          <img class="tec-icone" src="<?= htmlspecialchars($t['imagem']) ?>" width="96" height="96" alt="" loading="lazy">
          <h2 class="area-nome"><?= htmlspecialchars($t['nome']) ?></h2>
          <p><?= htmlspecialchars($t['descricao']) ?></p>
          <p class="tec-materia">Onde aparece no curso: <strong><?= htmlspecialchars($t['materia']) ?></strong></p>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<section class="chamada">
  <div class="wrap">
    <p class="kicker kicker-claro">Quiz</p>
    <h2>Qual área combina <mark>com você</mark>?</h2>
    <p class="acoes">
      <a class="btn btn-principal" href="quiz.php">Fazer o quiz</a>
      <a class="btn btn-claro" href="areas.php">Ver as 4 áreas</a>
    </p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
