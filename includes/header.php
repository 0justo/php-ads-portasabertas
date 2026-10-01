<?php
// Uso: $titulo = 'Título da página'; $pagina = 'home'; include 'includes/header.php';
require_once __DIR__ . '/dados.php';
$titulo = $titulo ?? 'ADS na Fatec Jundiaí';
$pagina = $pagina ?? '';
$v_css  = @filemtime(__DIR__ . '/../assets/style.css') ?: 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= htmlspecialchars($titulo) ?> | ADS Fatec Jundiaí</title>
<meta name="description" content="Conheça o curso de Análise e Desenvolvimento de Sistemas da Fatec Jundiaí e descubra qual dev você é.">
<script>document.documentElement.className += ' js';</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600..800&family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="assets/img/favicon.png">
<link rel="stylesheet" href="assets/style.css?v=<?= $v_css ?>">
</head>
<body class="pg-<?= htmlspecialchars($pagina) ?>">
<a class="pular" href="#conteudo">Pular para o conteúdo</a>
<header class="topo">
  <div class="wrap topo-in">
    <a class="marca" href="index.php">
      <img src="assets/img/logo-fatec.png" alt="Fatec Faculdade de Tecnologia" width="795" height="299">
      <span>ADS Jundiaí</span>
    </a>
    <nav aria-label="Principal">
      <a href="index.php#curso"<?= $pagina === 'home' ? ' aria-current="page"' : '' ?>>O curso</a>
      <a href="areas.php"<?= $pagina === 'areas' ? ' aria-current="page"' : '' ?>>Áreas</a>
      <a href="tecnologias.php"<?= $pagina === 'tecnologias' ? ' aria-current="page"' : '' ?>>Tecnologias</a>
      <a href="quiz.php" class="nav-quiz"<?= $pagina === 'quiz' ? ' aria-current="page"' : '' ?>>Quiz</a>
      <a href="integrantes.php"<?= $pagina === 'integrantes' ? ' aria-current="page"' : '' ?>>Integrantes</a>
    </nav>
  </div>
</header>
<main id="conteudo">
<?php if ($erro_areas): // dados/areas.xml não pôde ser lido ?>
  <div class="wrap"><p class="aviso" role="alert"><?= htmlspecialchars($erro_areas) ?></p></div>
<?php endif; ?>
