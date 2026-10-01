<?php
// Espera que quem incluir este arquivo (resultado.php) já tenha chamado session_start()
// e já tenha $link_vestibular disponível (vem de includes/dados.php).
// A rodada só é sorteada quando o jogador aperta "Começar jogo". Qual barra é a certa
// fica só na sessão: quem confere o clique é o PHP. São 5 níveis (grafico-funcoes.php).

require_once __DIR__ . '/grafico-funcoes.php';
?>
<div class="jogo-grafico" id="jogoGrafico">
  <div class="jogo-cor-cabeca">
    <h2 class="h2-menor">📊 O Gráfico Fugitivo</h2>
    <p class="texto">O painel mostra barras de servidores com o quanto está ocupado em cada um. Leia a ordem no balão e clique na barra certa em <?= JG_NIVEIS[5]['t'] ?> a <?= JG_NIVEIS[1]['t'] ?> segundos. É um clique só! Os números só aparecem depois da sua resposta: antes disso, leia as barras pela escala. A cada nível os valores ficam mais próximos, entram mais barras, as ordens combinam informações e, no final, o gráfico some e você precisa lembrar dele.</p>
  </div>

  <div class="jc-inicio" id="jgInicio">
    <p class="jc-inicio-rotulo">5 níveis · <?= JG_NIVEIS[1]['n'] ?> a <?= JG_NIVEIS[5]['n'] ?> servidores · 1 clique por nível</p>
    <p class="acoes jc-inicio-acoes">
      <button type="button" class="btn btn-principal btn-grande" id="jgComecar">Começar jogo</button>
    </p>
  </div>

  <div class="jn-hud" id="jgHud" hidden></div>

  <div id="jgBloco" hidden>
    <div class="jg-tela">
      <p class="jg-balao" id="jgOrdem" aria-live="polite"></p>
      <div class="jc-timer" id="jgTimer" role="timer"></div>

      <div class="jg-grafico">
        <div class="jg-escala" aria-hidden="true">
          <?php for ($v = 0; $v <= 100; $v += 20): ?>
            <span style="--v:<?= $v ?>"><?= $v ?>%</span>
          <?php endfor; ?>
        </div>
        <div class="jg-barras" id="jgBarras">
          <?php foreach (JG_SERVIDORES as $i => $nome): ?>
            <button type="button" class="jg-barra" data-servidor="<?= $i ?>" aria-label="Servidor <?= $nome ?>">
              <span class="jg-cheio"><span class="jg-valor"></span></span>
              <span class="jg-nome">Servidor <?= $nome ?></span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="jn-feedback" id="jgFeedback" hidden>
      <p class="jn-fb-txt"></p>
      <p class="acoes"><button type="button" class="btn btn-principal jn-fb-btn"></button></p>
    </div>
  </div>

  <div class="jc-resultado" id="jgResultado" hidden>
    <p class="jc-porcentagem" id="jgTitulo"></p>
    <p class="jc-mensagem" id="jgMensagem"></p>
    <!-- Gancho do design: igual para todos os resultados -->
    <p class="jc-gancho" id="jgGancho">Sua mente lê e interpreta padrões de dados num piscar de olhos! Venha se destacar no mercado de Business Intelligence e Ciência de Dados.</p>
    <p class="acoes">
      <a class="btn btn-principal" href="<?= htmlspecialchars($link_vestibular) ?>" target="_blank" rel="noopener">Inscreva-se no vestibular</a>
      <button class="btn" type="button" id="jgJogarDeNovo">Jogar de novo</button>
    </p>
  </div>
</div>

<script src="assets/jogos-comum.js"></script>
<script>
(function () {
  var SERV = <?= json_encode(JG_SERVIDORES) ?>;
  var $ = function (id) { return document.getElementById(id); };
  var inicio = $('jgInicio'), hud = $('jgHud'), bloco = $('jgBloco'), fb = $('jgFeedback'), res = $('jgResultado'),
      ordemEl = $('jgOrdem'), timerEl = $('jgTimer'), barrasEl = $('jgBarras'),
      barras = [].slice.call(document.querySelectorAll('.jg-barra'));
  var D = null, parar = null, aberta = false, hideT = null;

  function nomeHtml(i, revelar) {
    var s = 'Servidor ' + SERV[i];
    if (D.caps) {
      s += '<small>' + (revelar ? (D.valores[i] * D.caps[i] / 100) + ' de ' : '') + D.caps[i] + ' GB</small>';
    }
    return s;
  }

  function erro(msg) {
    if (parar) parar();
    clearTimeout(hideT);
    aberta = false;
    $('jgTitulo').textContent = 'Ops!';
    $('jgMensagem').textContent = (msg && msg.erro) || 'Não deu pra conferir agora. Tenta de novo em instantes.';
    $('jgGancho').hidden = true;
    bloco.hidden = true; res.hidden = false;
  }

  function mostrarFim(d) {
    $('jgTitulo').textContent = d.titulo;
    $('jgMensagem').textContent = JN.fimMsg(d);
    $('jgGancho').hidden = false;
    bloco.hidden = true; res.hidden = false;
  }

  function responder(dados) {
    if (!aberta) return;
    aberta = false;
    if (parar) parar();
    clearTimeout(hideT);
    barras.forEach(function (b) { b.disabled = true; });
    JN.post('jogos/verificar-grafico.php', dados).then(function (j) {
      if (j.erro) return erro(j);
      // revela o gráfico com todos os valores
      barrasEl.classList.remove('oculto');
      barras.forEach(function (b, i) {
        if (i >= D.valores.length) return;
        b.querySelector('.jg-valor').textContent = D.valores[i] + '%';
        b.querySelector('.jg-nome').innerHTML = nomeHtml(i, true);
        if (i === j.correta) b.classList.add('certa');
        else if (i === j.escolha) b.classList.add('errada');
      });
      JN.hud(hud, { nivel: j.nivel, nome: D.nome, pts: j.pts, seq: j.seq, dica: D.dica });
      var txt = j.aprovado ? '✅ Boa leitura! +' + j.ganho + ' pontos.'
        : (j.tempo_esgotado ? '⏰ Tempo esgotado! ' : '❌ Não foi dessa vez! ') + 'A resposta era o Servidor ' + SERV[j.correta] + '.';
      JN.feedback(fb, j.aprovado, txt, j, function () { rodada(true); }, function () { mostrarFim(j); });
    }).catch(erro);
  }

  barras.forEach(function (b) {
    b.addEventListener('click', function () { responder({ escolha: b.dataset.servidor }); });
  });

  function rodada(continuar) {
    JN.get('jogos/reiniciar-grafico.php' + (continuar ? '?continuar=1' : '')).then(function (d) {
      D = d;
      JN.hud(hud, d);
      ordemEl.textContent = d.texto;
      barrasEl.style.gridTemplateColumns = 'repeat(' + d.valores.length + ',minmax(0,1fr))';
      barrasEl.classList.remove('oculto');
      barras.forEach(function (b, i) {
        b.hidden = i >= d.valores.length;
        b.disabled = false;
        b.className = 'jg-barra';
        b.querySelector('.jg-cheio').style.height = '0%';
        b.querySelector('.jg-valor').textContent = d.mostrar_numeros && i < d.valores.length ? d.valores[i] + '%' : '';
        if (i < d.valores.length) {
          b.querySelector('.jg-nome').innerHTML = nomeHtml(i, false);
          // só descreve o valor para leitor de tela quando ele também está visível na tela
          b.setAttribute('aria-label', 'Servidor ' + SERV[i] + (d.mostrar_numeros ? ', ' + d.valores[i] + '% ocupado' : ''));
        }
      });
      fb.hidden = true;
      inicio.hidden = true; res.hidden = true; bloco.hidden = false;
      aberta = true;
      requestAnimationFrame(function () { requestAnimationFrame(function () {
        barras.forEach(function (b, i) { if (i < d.valores.length) b.querySelector('.jg-cheio').style.height = d.valores[i] + '%'; });
      }); });
      if (d.ocultar) hideT = setTimeout(function () { barrasEl.classList.add('oculto'); }, d.ocultar * 1000);
      parar = JN.timer(timerEl, d.tempo, function () { responder({ acao: 'encerrar' }); });
    }).catch(erro);
  }

  $('jgComecar').addEventListener('click', function () { rodada(false); });
  $('jgJogarDeNovo').addEventListener('click', function () {
    res.hidden = true; bloco.hidden = true; hud.hidden = true; inicio.hidden = false;
  });
})();
</script>
