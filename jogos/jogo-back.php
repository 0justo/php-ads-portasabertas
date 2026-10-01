<?php
// Espera que quem incluir este arquivo já tenha chamado session_start()
// e já tenha $link_vestibular disponível (vem de includes/dados.php).
// A senha só é sorteada quando o jogador aperta "Começar jogo" e fica só na sessão.
// São 3 fases; as regras de cada uma ficam em senha-funcoes.php.

require_once __DIR__ . '/senha-funcoes.php';
?>
<div class="jogo-senha" id="jogoSenha">
  <div class="jogo-cor-cabeca">
    <h2 class="h2-menor">🧩 O Decodificador de Senha</h2>
    <p class="texto">O servidor escondeu uma sequência de cores. Monte um palpite e envie: o painel de logs conta quantas cores estão no lugar certo, quantas fazem parte da senha em outra posição e quantas não fazem parte. Na 1ª fase são só <?= JS_NIVEIS[1]['cores'] ?> cores possíveis; nas outras duas, <?= JS_NIVEIS[2]['cores'] ?>. Na última a senha cresce e as cores podem repetir, com menos tentativas e menos tempo.</p>
  </div>

  <div class="jc-inicio" id="jsInicio">
    <p class="jc-inicio-rotulo"><?= JS_TOTAL ?> fases · de <?= JS_NIVEIS[1]['cores'] ?> a <?= JS_NIVEIS[JS_TOTAL]['cores'] ?> cores · de <?= JS_NIVEIS[1]['tam'] ?> a <?= JS_NIVEIS[JS_TOTAL]['tam'] ?> posições</p>
    <p class="acoes jc-inicio-acoes">
      <button type="button" class="btn btn-principal btn-grande" id="jsComecar">Começar jogo</button>
    </p>
  </div>

  <div class="jn-hud" id="jsHud" hidden></div>

  <div id="jsBloco" hidden>
    <div class="js-tela">
      <form class="js-painel" id="jsForm">
        <div class="jc-timer" id="jsTimer" role="timer"></div>
        <p class="js-rest" id="jsRest"></p>

        <span class="jc-rotulo" id="jsRotuloCampos">Seu palpite (toque num campo cheio para limpar)</span>
        <div class="js-campos" id="jsCampos" role="group" aria-labelledby="jsRotuloCampos"></div>

        <span class="jc-rotulo" id="jsRotuloLista">Escolha as cores</span>
        <ul class="js-lista" id="jsLista" aria-labelledby="jsRotuloLista"></ul>

        <p class="acoes js-acoes">
          <button type="submit" class="btn btn-principal" id="jsEnviar" disabled>Enviar</button>
          <button type="button" class="btn" id="jsLimpar">Limpar</button>
        </p>
      </form>

      <div class="js-logs-caixa">
        <span class="jc-rotulo">Painel de logs</span>
        <ol class="js-logs" id="jsLogs" aria-live="polite"></ol>
      </div>
    </div>

    <div class="jn-feedback" id="jsFeedback" hidden>
      <p class="jn-fb-txt"></p>
      <p class="acoes"><button type="button" class="btn btn-principal jn-fb-btn"></button></p>
    </div>
  </div>

  <div class="jc-resultado" id="jsResultado" hidden>
    <p class="jc-porcentagem" id="jsTitulo"></p>
    <p class="jc-mensagem" id="jsMensagem"></p>
    <!-- Gancho do design: igual para todos os resultados -->
    <p class="jc-gancho" id="jsGancho">Sua mente trabalha perfeitamente com lógica estruturada e validação de dados! Venha construir os motores e regras da internet no nosso curso.</p>
    <p class="acoes">
      <a class="btn btn-principal" href="<?= htmlspecialchars($link_vestibular) ?>" target="_blank" rel="noopener">Inscreva-se no vestibular</a>
      <button class="btn" type="button" id="jsJogarDeNovo">Jogar de novo</button>
    </p>
  </div>
</div>

<script src="assets/jogos-comum.js"></script>
<script>
(function () {
  var NOMES = <?= json_encode(JS_CORES, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
  var $ = function (id) { return document.getElementById(id); };
  var inicio = $('jsInicio'), hud = $('jsHud'), bloco = $('jsBloco'), fb = $('jsFeedback'), res = $('jsResultado'),
      form = $('jsForm'), timerEl = $('jsTimer'), camposEl = $('jsCampos'), listaEl = $('jsLista'),
      logs = $('jsLogs'), enviarBtn = $('jsEnviar'), restEl = $('jsRest');
  var D = null, palpite = [], parar = null, aberta = false, ocupado = false;

  function bolinha(c) { return '<span class="js-bolinha js-cor-' + c + '" aria-hidden="true"></span>'; }
  function grade(el, n) { el.style.gridTemplateColumns = 'repeat(' + n + ',minmax(0,1fr))'; }

  function desenharCampos() {
    var h = '';
    for (var i = 0; i < D.tamanho; i++) {
      var c = palpite[i];
      h += c
        ? '<button type="button" class="js-campo cheio js-cor-' + c + '" data-pos="' + i + '" aria-label="Posição ' + (i + 1) + ': ' + NOMES[c] + '. Toque para limpar">' + NOMES[c] + '</button>'
        : '<span class="js-campo" aria-label="Posição ' + (i + 1) + ': vazia">?</span>';
    }
    camposEl.innerHTML = h;
    enviarBtn.disabled = !aberta || palpite.filter(Boolean).length < D.tamanho;
  }

  function erro(msg) {
    if (parar) parar();
    aberta = false;
    $('jsTitulo').textContent = 'Ops!';
    $('jsMensagem').textContent = (msg && msg.erro) || 'Não deu pra conferir agora. Tenta de novo em instantes.';
    $('jsGancho').hidden = true;
    bloco.hidden = true; res.hidden = false;
  }

  function mostrarFim(d) {
    $('jsTitulo').textContent = d.titulo;
    $('jsMensagem').textContent = JN.fimMsg(d);
    $('jsGancho').hidden = false;
    bloco.hidden = true; res.hidden = false;
  }

  function log(j) {
    if (logs.querySelector('.js-log-vazio')) logs.innerHTML = '';
    var li = document.createElement('li');
    li.innerHTML = '<span class="js-log-num">#' + j.tentativas + '</span><span class="js-log-cores">' + j.palpite.map(bolinha).join('') + '</span>' +
      '<span>' + j.certas + ' no lugar · ' + j.deslocadas + ' fora do lugar · ' + j.ausentes + ' fora da senha</span>';
    logs.appendChild(li);
    logs.scrollTop = logs.scrollHeight;
  }

  function fimRodada(j) {
    aberta = false; if (parar) parar();
    enviarBtn.disabled = true;
    JN.hud(hud, { nivel: j.nivel, total: j.total, nome: D.nome, pts: j.pts, seq: j.seq, dica: D.dica });
    var senha = '<span class="js-log-cores">' + j.senha.map(bolinha).join('') + '</span>';
    var txt = j.aprovado
      ? '✅ Senha quebrada em ' + j.tentativas + ' tentativa(s)! +' + j.ganho + ' pontos. ' + senha
      : (j.motivo === 'tempo' ? '⏰ O tempo acabou. ' : '❌ Acabaram as tentativas. ') + 'A senha era: ' + senha;
    JN.feedback(fb, j.aprovado, txt, j, function () { rodada(true); }, function () { mostrarFim(j); });
  }

  function enviar() {
    if (!aberta || ocupado || palpite.filter(Boolean).length < D.tamanho) return;
    ocupado = true;
    var dados = {};
    palpite.forEach(function (c, i) { dados['c' + (i + 1)] = c; });
    JN.post('jogos/verificar-senha.php', dados).then(function (j) {
      ocupado = false;
      if (j.erro) return erro(j);
      if (j.rodada_fim) {
        if (j.palpite) log(j);
        return fimRodada(j);
      }
      log(j);
      restEl.textContent = 'Tentativas restantes: ' + j.restantes;
    }).catch(function () { ocupado = false; erro(); });
  }

  function rodada(continuar) {
    JN.get('jogos/reiniciar-senha.php' + (continuar ? '?continuar=1' : '')).then(function (d) {
      D = d; palpite = [];
      JN.hud(hud, d);
      grade(camposEl, d.tamanho);
      listaEl.innerHTML = d.cores.map(function (c) {
        return '<li><button type="button" class="js-opcao js-cor-' + c + '" data-cor="' + c + '">' + bolinha(c) + NOMES[c] + '</button></li>';
      }).join('');
      logs.innerHTML = '<li class="js-log-vazio">&gt; aguardando o primeiro palpite…</li>';
      restEl.textContent = 'Tentativas restantes: ' + d.max;
      fb.hidden = true;
      inicio.hidden = true; res.hidden = true; bloco.hidden = false;
      aberta = true; ocupado = false;
      desenharCampos();
      parar = JN.timer(timerEl, d.tempo, function () {
        if (!aberta) return;
        JN.post('jogos/verificar-senha.php', { acao: 'encerrar' }).then(function (j) { j.erro ? erro(j) : fimRodada(j); }).catch(erro);
      });
    }).catch(erro);
  }

  listaEl.addEventListener('click', function (ev) {
    var b = ev.target.closest('.js-opcao');
    if (!b || !aberta) return;
    for (var i = 0; i < D.tamanho; i++) {
      if (!palpite[i]) { palpite[i] = b.dataset.cor; break; }
    }
    desenharCampos();
  });
  camposEl.addEventListener('click', function (ev) {
    var b = ev.target.closest('button.js-campo');
    if (!b || !aberta) return;
    palpite[+b.dataset.pos] = undefined;
    desenharCampos();
  });
  $('jsLimpar').addEventListener('click', function () { palpite = []; desenharCampos(); });
  form.addEventListener('submit', function (ev) { ev.preventDefault(); enviar(); });

  $('jsComecar').addEventListener('click', function () { rodada(false); });
  $('jsJogarDeNovo').addEventListener('click', function () {
    res.hidden = true; bloco.hidden = true; hud.hidden = true; inicio.hidden = false;
  });
})();
</script>
