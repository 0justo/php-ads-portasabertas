<?php
$titulo = 'Quiz: qual dev você é?';
$pagina = 'quiz';
include 'includes/header.php';
$total = count($perguntas);
?>

<div class="wrap quiz">
  <header class="quiz-cabeca">
    <p class="kicker">Quiz &middot; <?= $total ?> perguntas</p>
    <h1 class="h1-menor">Qual <mark>dev</mark> você é?</h1>
    <p class="lead">São <?= $total ?> situações do dia a dia, sem pegadinha e sem precisar saber nada de tecnologia. Escolha a resposta que mais parece com você.</p>
  </header>

  <div class="progresso" role="status" aria-live="polite">
    <div class="progresso-barra"><span id="barra"></span></div>
    <p class="progresso-texto"><span id="feitas">0</span> de <?= $total ?> respondidas</p>
  </div>

  <form action="resultado.php" method="post" id="form-quiz">
    <?php foreach ($perguntas as $i => $p): $n = $i + 1; ?>
      <fieldset class="pergunta">
        <legend>
          <span class="q-cabeca"><span class="emo" aria-hidden="true"><?= $p['emoji'] ?></span><span class="sem">Pergunta <?= $n ?> de <?= $total ?></span></span>
          <span class="tit"><?= htmlspecialchars($p['titulo']) ?></span>
        </legend>
        <?php foreach ($p['opcoes'] as $j => [$area, $emoji, $texto]): ?>
          <label class="opcao">
            <input type="radio" name="p<?= $n ?>" value="<?= $area ?>" <?= $j === 0 ? 'required' : '' ?>>
            <span class="opcao-corpo">
              <span class="radio" aria-hidden="true"></span>
              <span class="opcao-texto"><?= htmlspecialchars($texto) ?></span>
            </span>
          </label>
        <?php endforeach; ?>
      </fieldset>
    <?php endforeach; ?>

    <button class="btn btn-principal btn-grande" type="submit">Ver meu resultado</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
