<?php
session_start();
require_once 'includes/dados.php';

// 1. Só aceita quem veio do formulário e respondeu tudo
$respostas = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($i = 1; $i <= count($perguntas); $i++) {
        $r = $_POST["p$i"] ?? '';
        if (isset($areas[$r])) {          // só aceita front, back, dados ou infra
            $respostas[] = $r;
        }
    }
}
if (count($respostas) !== count($perguntas)) {
    header('Location: quiz.php');
    exit;
}

// 2. Conta quantas vezes cada área apareceu
$contagem = array_fill_keys(array_keys($areas), 0);
foreach (array_count_values($respostas) as $area => $qtd) {
    $contagem[$area] = $qtd;
}
arsort($contagem);

// 3. Descobre a vencedora. Em caso de empate, vale a última resposta.
$maior      = max($contagem);
$empatadas  = array_keys($contagem, $maior);
$ultima     = end($respostas);
$empate     = count($empatadas) > 1;
$vencedora  = ($empate && in_array($ultima, $empatadas, true)) ? $ultima : $empatadas[0];
$perfil     = $areas[$vencedora];

$titulo = 'Você é ' . $perfil['nome'];
$pagina = 'resultado';
include 'includes/header.php';
?>

<div class="resultado-topo a-<?= $vencedora ?>">
  <div class="wrap res-topo-in">
    <span class="emo emo-xg" aria-hidden="true"><?= htmlspecialchars($perfil['emoji']) ?></span>
    <div>
      <p class="kicker">Seu resultado</p>
      <h1 class="res-nome"><span class="res-voce">Você é</span> <?= htmlspecialchars($perfil['nome']) ?></h1>
      <p class="lead"><?= htmlspecialchars($perfil['resumo']) ?></p>
      <?php if ($empate): ?>
        <p class="meta">Deu empate no quiz. A sua última resposta desempatou.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="wrap resultado a-<?= $vencedora ?>">
  <div class="res-grade">
    <section>
      <h2 class="h2-menor">O que é essa área?</h2>
      <p><?= htmlspecialchars($perfil['oque']) ?></p>
      <h2 class="h2-menor">Carreiras</h2>
      <ul class="tags" aria-label="Carreiras">
        <?php foreach ($perfil['carreiras'] as $c): ?>
          <li><?= htmlspecialchars($c) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section>
      <h2 class="h2-menor">Matérias do curso que preparam você para isso</h2>
      <ul class="lista">
        <?php foreach ($perfil['materias'] as $m): ?>
          <li><?= htmlspecialchars($m) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
  </div>

  <h2 class="h2-menor">Como você respondeu</h2>
  <ul class="barras">
    <?php foreach ($contagem as $area => $qtd): ?>
      <li class="a-<?= $area ?>">
        <span class="barra-nome"><span aria-hidden="true"><?= htmlspecialchars($areas[$area]['emoji']) ?></span> <?= htmlspecialchars($areas[$area]['nome']) ?></span>
        <span class="barra-trilho"><span class="barra-cheia" style="width: <?= $qtd / count($perguntas) * 100 ?>%"></span></span>
        <span class="barra-qtd"><?= $qtd ?> de <?= count($perguntas) ?></span>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php
    // Cada perfil tem seu próprio joguinho em jogos/jogo-<area>.php.
    // Se o arquivo ainda não existir para essa área, o site simplesmente não mostra nada aqui.
    $arquivo_jogo = __DIR__ . '/jogos/jogo-' . $vencedora . '.php';
    if (is_file($arquivo_jogo)):
  ?>
    <section class="jogo-secao a-<?= $vencedora ?>">
      <?php include $arquivo_jogo; ?>
    </section>
  <?php endif; ?>

  <p class="acoes">
    <a class="btn btn-principal" href="<?= $link_vestibular ?>" target="_blank" rel="noopener">Inscreva-se no vestibular</a>
    <a class="btn" href="quiz.php">Refazer o quiz</a>
    <button class="btn" type="button" id="compartilhar">Compartilhar meu resultado</button>
  </p>
  <p class="meta" id="aviso" role="status"></p>
</div>

<script>
  (function () {
    var btn = document.getElementById('compartilhar');
    var aviso = document.getElementById('aviso');
    var texto = <?= json_encode('Eu sou ' . $perfil['nome'] . ' no quiz de ADS da Fatec Jundiaí. E você?', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var url = location.origin + location.pathname.replace('resultado.php', 'index.php');
    btn.addEventListener('click', function () {
      if (navigator.share) {
        navigator.share({ title: 'Qual dev você é?', text: texto, url: url }).catch(function () {});
      } else if (navigator.clipboard) {
        navigator.clipboard.writeText(texto + ' ' + url).then(function () {
          aviso.textContent = 'Texto copiado. É só colar onde quiser.';
        });
      } else {
        aviso.textContent = 'Copie e envie: ' + texto + ' ' + url;
      }
    });
  })();
</script>

<?php include 'includes/footer.php'; ?>
