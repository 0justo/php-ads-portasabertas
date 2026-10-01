<?php
$titulo = 'As 4 áreas';
$pagina = 'areas';
include 'includes/header.php';
// $areas vem de dados/areas.xml, lido com simplexml_load_file() em carregar_areas() (includes/dados.php).
?>

<div class="wrap pagina">
  <header class="pagina-cabeca">
    <p class="kicker">Áreas &middot; depois do curso</p>
    <h1 class="h1-menor">As 4 <mark>áreas</mark></h1>
    <p class="lead">ADS abre portas para caminhos bem diferentes. Veja o que cada área faz, onde dá para trabalhar e quais matérias do curso preparam você para ela.</p>
  </header>

  <?php if ($areas): ?>
    <div class="grade-areas">
      <?php $n = 0; foreach ($areas as $chave => $a): $n++; ?>
        <article class="area a-<?= htmlspecialchars($chave) ?>">
          <?php if ($a['imagem'] !== ''): ?>
            <img class="area-img" src="<?= htmlspecialchars($a['imagem']) ?>" width="320" height="180" alt="Ilustração da área <?= htmlspecialchars($a['nome']) ?>" loading="lazy">
          <?php endif; ?>
          <div class="area-topo">
            <span class="emo emo-g" aria-hidden="true"><?= htmlspecialchars($a['emoji']) ?></span>
            <span class="area-num">0<?= $n ?></span>
          </div>
          <h2 class="area-nome"><?= htmlspecialchars($a['nome']) ?></h2>

          <h3 class="area-sub">O que é</h3>
          <p><?= htmlspecialchars($a['oque']) ?></p>

          <h3 class="area-sub">Carreiras</h3>
          <ul class="tags" aria-label="Carreiras">
            <?php foreach ($a['carreiras'] as $c): ?>
              <li><?= htmlspecialchars($c) ?></li>
            <?php endforeach; ?>
          </ul>

          <h3 class="area-sub">Matérias do curso</h3>
          <ul class="lista">
            <?php foreach ($a['materias'] as $m): ?>
              <li><?= htmlspecialchars($m) ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<section class="chamada">
  <div class="wrap">
    <p class="kicker kicker-claro">Quiz</p>
    <h2>Qual delas é a <mark>sua</mark>?</h2>
    <p class="acoes">
      <a class="btn btn-principal" href="quiz.php">Fazer o quiz</a>
    </p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
