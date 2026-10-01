<?php
$titulo = 'Integrantes';
$pagina = 'integrantes';
include 'includes/header.php';

// Iniciais do avatar: primeira letra do primeiro e do último nome ("dos", "de"... não contam).
function iniciais($nome)
{
    $partes = array_values(array_filter(preg_split('/\s+/u', trim($nome)), function ($p) {
        return !in_array(mb_strtolower($p), ['da', 'das', 'de', 'do', 'dos', 'e'], true);
    }));
    if (!$partes) {
        return '?';
    }
    $letras = mb_substr($partes[0], 0, 1);
    if (count($partes) > 1) {
        $letras .= mb_substr(end($partes), 0, 1);
    }
    return mb_strtoupper($letras);
}

// Cada cartão ganha a cor de uma das 4 áreas, em rodízio.
$cores = ['front', 'back', 'dados', 'infra'];
?>

<div class="wrap pagina">
  <header class="pagina-cabeca">
    <p class="kicker">Quem fez</p>
    <h1 class="h1-menor">Integrantes do <mark>grupo</mark></h1>
    <p class="texto">Disciplina: <strong><?= htmlspecialchars($disciplina['nome']) ?></strong> &middot; Docente: <strong><?= htmlspecialchars($disciplina['docente']) ?></strong></p>
  </header>

  <ul class="grade-integrantes">
    <?php foreach ($integrantes as $i => $pessoa):
      $foto = 'assets/img/integrantes/' . $pessoa['ra'] . '.jpg';
      $tem_foto = preg_match('/^\d+$/', $pessoa['ra']) && is_file(__DIR__ . '/' . $foto);
    ?>
      <li class="area integrante a-<?= $cores[$i % count($cores)] ?>">
        <?php if ($tem_foto): ?>
          <img class="avatar" src="<?= htmlspecialchars($foto) ?>" width="112" height="112" alt="Foto de <?= htmlspecialchars($pessoa['nome']) ?>" loading="lazy">
        <?php else: ?>
          <span class="avatar" aria-hidden="true"><?= htmlspecialchars(iniciais($pessoa['nome'])) ?></span>
        <?php endif; ?>
        <h2 class="area-nome"><?= htmlspecialchars($pessoa['nome']) ?></h2>
        <p class="integrante-ra">RA <?= htmlspecialchars($pessoa['ra']) ?></p>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<?php include 'includes/footer.php'; ?>
