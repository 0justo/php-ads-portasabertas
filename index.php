<?php
$titulo = 'Curso de Análise e Desenvolvimento de Sistemas';
$pagina = 'home';
include 'includes/header.php';
?>

<section class="hero" data-hero>
  <div class="wrap hero-grade">
    <div class="hero-texto">
      <p class="kicker">ADS &middot; Fatec Jundiaí</p>
      <h1 class="hero-titulo"><span>Qual <mark>dev</mark></span> <span>você é?</span></h1>
      <p class="lead">5 perguntas sobre games, celular e redes sociais. No final você descobre qual área de tecnologia combina com você e o que estudar para chegar lá.</p>
      <p class="acoes">
        <a class="btn btn-principal" href="quiz.php">Fazer o quiz</a>
        <a class="btn" href="#curso">Ver o curso</a>
      </p>
    </div>
    <div class="hero-visual">
      <figure class="hero-foto">
        <img src="assets/img/campus-dia.jpg" width="800" height="445" alt="Prédio de tijolinhos da Fatec Jundiaí, com jardim na frente">
      </figure>
      <ul class="adesivos" aria-hidden="true">
        <?php foreach ($areas as $chave => $a): ?>
          <li class="adesivo a-<?= htmlspecialchars($chave) ?>"><span class="emo"><?= htmlspecialchars($a['emoji']) ?></span><?= htmlspecialchars($a['nome']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section class="bloco bloco-fundo" id="curso">
  <div class="wrap curso-topo">
    <div class="curso-texto">
      <p class="kicker">O curso</p>
      <h2>Faculdade pública e <mark>gratuita</mark>, aqui em Jundiaí</h2>
      <p class="texto">A Fatec é uma faculdade pública: você não paga mensalidade. O curso de ADS acontece no campus da Ponte de Campinas, em Jundiaí.</p>
      <p class="texto">O curso forma profissionais para analisar, projetar, documentar, testar e manter sistemas de informação. Quem se forma pode atuar em indústria, comércio, serviços, instituições financeiras, órgãos públicos ou no próprio negócio.</p>
    </div>
    <figure class="foto" data-parallax>
      <img src="assets/img/campus-fachada.jpg" width="620" height="420" alt="Fachada de tijolinhos do prédio da Fatec Jundiaí" loading="lazy">
      <span class="balao-foto"><span class="emo" aria-hidden="true">📍</span>Ponte de Campinas, Jundiaí</span>
    </figure>
  </div>

  <div class="wrap">
    <ul class="numeros">
      <?php foreach ($numeros as [$emoji, $rot, $grande, $texto]): ?>
        <li class="numero">
          <div class="numero-topo">
            <span class="emo" aria-hidden="true"><?= $emoji ?></span>
            <span class="numero-titulo"><?= htmlspecialchars($rot) ?></span>
          </div>
          <strong><?= htmlspecialchars($grande) ?></strong>
          <span class="numero-texto"><?= htmlspecialchars($texto) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>

    <details class="ficha-completa">
      <summary>Ver a ficha completa do curso</summary>
      <dl class="ficha">
        <?php foreach ($ficha as $rotulo => $valor): ?>
          <div class="ficha-linha">
            <dt><?= htmlspecialchars($rotulo) ?></dt>
            <dd><?= htmlspecialchars($valor) ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </details>
  </div>
</section>

<section class="bloco" id="grade">
  <div class="wrap trilha" data-trilha>
    <div class="trilha-lado">
      <p class="kicker">Grade curricular</p>
      <h2>Semestre por semestre</h2>
      <p class="texto">Do primeiro código ao Trabalho de Graduação. Algumas matérias mudam conforme as escolhas do 4º ao 6º semestre.</p>
    </div>

    <ol class="linha-tempo">
      <?php foreach ($semestres as [$sem, $materias]): ?>
        <li>
          <h3><?= $sem ?></h3>
          <p><?= htmlspecialchars($materias) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="bloco bloco-fundo" id="areas">
  <div class="wrap">
    <p class="kicker">Depois do curso</p>
    <h2>Quatro caminhos possíveis</h2>
    <p class="texto">ADS não é uma profissão só. O curso leva para áreas bem diferentes, e o quiz mostra qual delas tem mais a ver com você.</p>
    <div class="grade-areas">
      <?php $n = 0; foreach ($areas as $chave => $a): $n++; ?>
        <article class="area a-<?= htmlspecialchars($chave) ?>">
          <div class="area-topo">
            <span class="emo emo-g" aria-hidden="true"><?= htmlspecialchars($a['emoji']) ?></span>
            <span class="area-num">0<?= $n ?></span>
          </div>
          <h3><?= htmlspecialchars($a['nome']) ?></h3>
          <p><?= htmlspecialchars($a['oque']) ?></p>
          <ul class="tags" aria-label="Carreiras">
            <?php foreach ($a['carreiras'] as $c): ?>
              <li><?= htmlspecialchars($c) ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="chamada">
  <div class="wrap chamada-grade">
    <div>
      <p class="kicker kicker-claro">Quiz</p>
      <h2>Descubra o seu perfil em <mark>1 minuto</mark></h2>
      <p class="acoes">
        <a class="btn btn-principal" href="quiz.php">Fazer o quiz</a>
        <a class="btn btn-claro" href="<?= $link_vestibular ?>" target="_blank" rel="noopener">Ver o vestibular</a>
      </p>
    </div>
    <figure class="chamada-foto">
      <img src="assets/img/campus-noite.jpg" width="547" height="365" alt="Vista aérea do campus da Fatec Jundiaí à noite, com o prédio iluminado" loading="lazy">
    </figure>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
