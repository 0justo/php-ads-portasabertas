<?php
// Espera que quem incluir este arquivo já tenha chamado session_start()
// e já tenha $link_vestibular disponível (vem de includes/dados.php).
// O jogo só sorteia a cor quando o jogador aperta "Começar jogo".
// São 3 fases (uma cor por fase); as regras de cada fase ficam em cor-funcoes.php.

require_once __DIR__ . '/cor-funcoes.php';
?>
<div class="jogo-cor" id="jogoCor">
  <div class="jogo-cor-cabeca">
    <h2 class="h2-menor">🎨 O Alvo da Cor</h2>
    <p class="texto">Do lado esquerdo aparece uma cor-alvo. Mexa nos controles de vermelho, verde e azul do lado direito, reproduza o mesmo tom e clique em Enviar antes que o tempo da fase acabe (<?= JC_NIVEIS[JC_TOTAL]['tempo'] ?> a <?= JC_NIVEIS[1]['tempo'] ?> segundos). Não existe porcentagem mínima: quanto mais perto da cor e mais rápido o envio, mais pontos. Passou da fase, a coisa aperta: o tempo diminui, os valores RGB somem e as cores-alvo ficam parecidas.</p>
  </div>

  <div class="jc-inicio" id="jcInicio">
    <p class="jc-inicio-rotulo"><?= JC_TOTAL ?> fases · <?= JC_NIVEIS[JC_TOTAL]['tempo'] ?> a <?= JC_NIVEIS[1]['tempo'] ?> segundos por cor</p>
    <p class="acoes jc-inicio-acoes">
      <button type="button" class="btn btn-principal btn-grande" id="jcComecar">Começar jogo</button>
    </p>
  </div>

  <div class="jn-hud" id="jcHud" hidden></div>

  <div id="jcTela" hidden>
    <div class="jogo-cor-grade">
      <div class="jc-alvo-caixa">
        <span class="jc-rotulo">Cor alvo</span>
        <div class="jc-swatch jc-alvo" id="jcAlvoCor"></div>
        <div class="jc-timer" id="jcTimer" role="timer" aria-live="off"></div>
      </div>

      <div class="jc-jogador">
        <span class="jc-rotulo">Sua mistura</span>
        <div class="jc-swatch jc-sua" id="jcSuaCor"></div>

        <label class="jc-controle jc-r">
          <span>Vermelho <output id="jcRVal">128</output></span>
          <input type="range" min="0" max="255" value="128" id="jcR" aria-label="Vermelho">
        </label>
        <label class="jc-controle jc-g">
          <span>Verde <output id="jcGVal">128</output></span>
          <input type="range" min="0" max="255" value="128" id="jcG" aria-label="Verde">
        </label>
        <label class="jc-controle jc-b">
          <span>Azul <output id="jcBVal">128</output></span>
          <input type="range" min="0" max="255" value="128" id="jcB" aria-label="Azul">
        </label>

        <button type="button" class="btn btn-principal" id="jcEnviar">Enviar</button>
      </div>
    </div>

    <div class="jn-feedback" id="jcFeedback" hidden>
      <p class="jn-fb-txt"></p>
      <p class="acoes"><button type="button" class="btn btn-principal jn-fb-btn"></button></p>
    </div>
  </div>

  <div class="jc-resultado" id="jcResultado" hidden>
    <p class="jc-porcentagem" id="jcTitulo"></p>
    <p class="jc-mensagem" id="jcMensagem"></p>
    <!-- Gancho do design: igual para todos os resultados -->
    <p class="jc-gancho" id="jcGancho">Você tem olhos clínicos para Design, Cores e Web! Venha dominar a criação de interfaces incríveis no nosso vestibular.</p>
    <p class="acoes">
      <a class="btn btn-principal" href="<?= htmlspecialchars($link_vestibular) ?>" target="_blank" rel="noopener">Inscreva-se no vestibular</a>
      <button class="btn" type="button" id="jcJogarDeNovo">Jogar de novo</button>
    </p>
  </div>
</div>

<script src="assets/jogos-comum.js"></script>
<script>
(function () {
  var $ = function (id) { return document.getElementById(id); };
  var inicio = $('jcInicio'), hud = $('jcHud'), tela = $('jcTela'), fb = $('jcFeedback'), res = $('jcResultado'),
      alvoEl = $('jcAlvoCor'), suaEl = $('jcSuaCor'), timerEl = $('jcTimer'), enviarBtn = $('jcEnviar');
  var sl  = { r: $('jcR'), g: $('jcG'), b: $('jcB') };
  var out = { r: $('jcRVal'), g: $('jcGVal'), b: $('jcBVal') };
  var parar = null, aberta = false, mostrar = true;

  function pintar() {
    suaEl.style.backgroundColor = 'rgb(' + sl.r.value + ',' + sl.g.value + ',' + sl.b.value + ')';
    ['r', 'g', 'b'].forEach(function (k) { out[k].textContent = mostrar ? sl[k].value : '•••'; });
  }
  ['r', 'g', 'b'].forEach(function (k) { sl[k].addEventListener('input', pintar); });

  function erro(msg) {
    if (parar) parar();
    aberta = false;
    $('jcTitulo').textContent = 'Ops!';
    $('jcMensagem').textContent = (msg && msg.erro) || 'Não deu pra conferir agora. Tenta de novo em instantes.';
    $('jcGancho').hidden = true;
    tela.hidden = true; res.hidden = false;
  }

  function mostrarFim(d) {
    $('jcTitulo').textContent = d.titulo;
    $('jcMensagem').textContent = JN.fimMsg(d);
    $('jcGancho').hidden = false;
    tela.hidden = true; res.hidden = false;
  }

  // estourou = o relógio zerou: o PHP encerra a fase sem pontos
  function enviar(estourou) {
    if (!aberta) return;
    aberta = false; if (parar) parar(); enviarBtn.disabled = true;
    var dados = { r: sl.r.value, g: sl.g.value, b: sl.b.value };
    if (estourou === true) dados.acao = 'encerrar';
    JN.post('jogos/verificar-cor.php', dados).then(function (j) {
      if (j.erro) return erro(j);
      JN.hud(hud, { nivel: j.nivel, total: j.total, nome: hud.dataset.nome, pts: j.pts, seq: j.seq, dica: hud.dataset.dica });
      var txt = j.aprovado
        ? '✅ ' + j.porcentagem + '% de proximidade. +' + j.ganho + ' pontos!'
        : '⏰ Tempo esgotado! Você chegou a ' + j.porcentagem + '% de proximidade, mas não enviou a tempo.';
      txt += '<br><small>Alvo ' + j.alvo_hex + ' · sua cor ' + j.sua_hex + '</small>';
      alvoEl.style.backgroundColor = j.alvo_hex;
      JN.feedback(fb, j.aprovado, txt, j, function () { rodada(true); }, function () { mostrarFim(j); });
    }).catch(erro);
  }

  function rodada(continuar) {
    JN.get('jogos/reiniciar-cor.php' + (continuar ? '?continuar=1' : '')).then(function (d) {
      hud.dataset.nome = d.nome; hud.dataset.dica = d.dica;
      JN.hud(hud, d);
      mostrar = d.mostrar_rgb;
      alvoEl.style.backgroundColor = d.alvo_hex;
      ['r', 'g', 'b'].forEach(function (k) { sl[k].value = 128; });
      pintar();
      fb.hidden = true; enviarBtn.disabled = false;
      inicio.hidden = true; res.hidden = true; tela.hidden = false;
      aberta = true;
      parar = JN.timer(timerEl, d.tempo, function () { enviar(true); });
    }).catch(erro);
  }

  $('jcComecar').addEventListener('click', function () { rodada(false); });
  enviarBtn.addEventListener('click', function () { enviar(); });
  $('jcJogarDeNovo').addEventListener('click', function () {
    res.hidden = true; tela.hidden = true; hud.hidden = true; inicio.hidden = false;
  });
})();
</script>
