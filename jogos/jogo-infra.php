<?php
// Espera que quem incluir este arquivo já tenha chamado session_start()
// e já tenha $link_vestibular disponível (vem de includes/dados.php).
// A agenda de vírus só é sorteada quando o jogador aperta "Começar jogo" e fica só na sessão.
// A tela pergunta ao PHP, várias vezes por segundo, o que está aparecendo agora;
// cada clique é conferido no PHP. São 5 níveis de 10 segundos (virus-funcoes.php).

require_once __DIR__ . '/virus-funcoes.php';
?>
<div class="jogo-virus" id="jogoVirus">
  <div class="jogo-cor-cabeca">
    <h2 class="h2-menor">🛡️ O Corta-Vírus</h2>
    <p class="texto">Você é o firewall. Em cada nível, durante <?= JV_TEMPO ?> segundos, vírus 🦠 piscam nas <?= JV_PASTAS ?> pastas do servidor. Clique na pasta infectada enquanto o vírus estiver visível (ou aperte 1, 2 ou 3 no teclado). Nos primeiros níveis o vírus fica mais tempo e aparece em ordem previsível; depois ele some rápido, muda de lugar, aparece em duas pastas ao mesmo tempo e surgem falsos alarmes ⚠️. Clicar onde não há vírus custa pontos e, mais adiante, segundos.</p>
  </div>

  <div class="jc-inicio" id="jvInicio">
    <p class="jc-inicio-rotulo">5 níveis · <?= JV_PASTAS ?> pastas · <?= JV_TEMPO ?> segundos cada · reflexo rápido</p>
    <p class="acoes jc-inicio-acoes">
      <button type="button" class="btn btn-principal btn-grande" id="jvComecar">Começar jogo</button>
    </p>
  </div>

  <div class="jn-hud" id="jvHud" hidden></div>

  <div id="jvTela" hidden>
    <div class="jv-painel">
      <div class="jv-topo">
        <span class="jv-firewall">Firewall ativo</span>
        <span class="jv-placar">Bloqueados: <strong id="jvPlacar">0</strong></span>
      </div>
      <div class="jc-timer" id="jvTimer" role="timer"></div>
      <div class="jv-pastas">
        <?php for ($i = 0; $i < JV_PASTAS; $i++): ?>
          <button type="button" class="jv-pasta" data-pasta="<?= $i ?>" aria-label="Pasta <?= $i + 1 ?>">
            <span class="jv-icone" aria-hidden="true">📁</span>
            <span class="jv-virus" aria-hidden="true">🦠</span>
            <span class="jv-nome">Pasta <?= $i + 1 ?></span>
          </button>
        <?php endfor; ?>
      </div>
      <p class="jv-status" id="jvStatus" aria-live="assertive"></p>
    </div>

    <div class="jn-feedback" id="jvFeedback" hidden>
      <p class="jn-fb-txt"></p>
      <p class="acoes"><button type="button" class="btn btn-principal jn-fb-btn"></button></p>
    </div>
  </div>

  <div class="jc-resultado" id="jvResultado" hidden>
    <p class="jc-porcentagem" id="jvTitulo"></p>
    <p class="jc-mensagem" id="jvMensagem"></p>
    <!-- Gancho do design: igual para todos os resultados -->
    <p class="jc-gancho" id="jvGancho">Reflexos rápidos para proteger a rede e mitigar riscos! Venha se especializar em segurança digital e defesa de servidores no nosso vestibular.</p>
    <p class="acoes">
      <a class="btn btn-principal" href="<?= htmlspecialchars($link_vestibular) ?>" target="_blank" rel="noopener">Inscreva-se no vestibular</a>
      <button class="btn" type="button" id="jvJogarDeNovo">Jogar de novo</button>
    </p>
  </div>
</div>

<script src="assets/jogos-comum.js"></script>
<script>
(function () {
  var $ = function (id) { return document.getElementById(id); };
  var inicio = $('jvInicio'), hud = $('jvHud'), tela = $('jvTela'), fb = $('jvFeedback'), res = $('jvResultado'),
      timerEl = $('jvTimer'), placarEl = $('jvPlacar'), statusEl = $('jvStatus'),
      pastas = [].slice.call(document.querySelectorAll('.jv-pasta'));
  var D = null, rodando = false, encerrando = false;

  function erro(msg) {
    rodando = false;
    $('jvTitulo').textContent = 'Ops!';
    $('jvMensagem').textContent = (msg && msg.erro) || 'Não deu pra conferir agora. Tenta de novo em instantes.';
    $('jvGancho').hidden = true;
    tela.hidden = true; res.hidden = false;
  }

  function mostrarFim(d) {
    $('jvTitulo').textContent = d.titulo;
    $('jvMensagem').textContent = JN.fimMsg(d);
    $('jvGancho').hidden = false;
    tela.hidden = true; res.hidden = false;
  }

  // desenha só o que o PHP disse que está aparecendo agora
  function desenhar(itens) {
    pastas.forEach(function (p, i) {
      var it = itens.filter(function (x) { return x.pasta === i; })[0];
      p.classList.remove('infectada', 'falsa');
      if (it) {
        p.classList.add(it.tipo === 'virus' ? 'infectada' : 'falsa');
        p.querySelector('.jv-virus').textContent = it.tipo === 'virus' ? '🦠' : '⚠️';
      }
    });
  }

  function encerrar() {
    if (encerrando) return;
    encerrando = true; rodando = false;
    JN.post('jogos/verificar-virus.php', { acao: 'encerrar' }).then(function (j) {
      if (j.erro) return erro(j);
      pastas.forEach(function (p) { p.disabled = true; p.classList.remove('infectada', 'falsa'); });
      JN.hud(hud, { nivel: j.nivel, nome: D.nome, pts: j.pts, seq: j.seq, dica: D.dica });
      var txt = (j.aprovado ? '✅ ' : '❌ ') + 'Você isolou ' + j.bloqueados + ' de ' + j.total + ' ameaças (mínimo ' + j.minimo + ').' +
        (j.aprovado ? ' +' + j.ganho + ' pontos!' : ' O sistema caiu.');
      JN.feedback(fb, j.aprovado, txt, j, function () { rodada(true); }, function () { mostrarFim(j); });
    }).catch(erro);
  }

  function consultar() {
    if (!rodando) return;
    JN.get('jogos/estado-virus.php').then(function (s) {
      if (!rodando) return;
      if (s.erro) return erro(s);
      timerEl.textContent = Math.ceil(s.restante / 1000) + 's';
      if (s.fim) { desenhar([]); return encerrar(); }
      desenhar(s.itens);
      setTimeout(consultar, 100);
    }).catch(erro);
  }

  function clicar(i) {
    if (!rodando) return;
    var p = pastas[i];
    JN.post('jogos/verificar-virus.php', { pasta: i }).then(function (r) {
      if (r.erro) return;   // rodada já fechou: quem trata é o encerrar()
      placarEl.textContent = r.bloqueados;
      if (r.resultado === 'virus') {
        p.classList.remove('infectada'); p.classList.add('isolada');
        statusEl.textContent = '🛡️ Ameaça isolada!';
        setTimeout(function () { p.classList.remove('isolada'); }, 350);
      } else {
        p.classList.add('errou');
        statusEl.textContent = r.resultado === 'falso' ? '⚠️ Era um falso alarme! Penalidade dobrada.' : '❌ Pasta limpa. Perdeu pontos.';
        setTimeout(function () { p.classList.remove('errou'); }, 350);
      }
    });
  }

  function rodada(continuar) {
    JN.get('jogos/reiniciar-virus.php' + (continuar ? '?continuar=1' : '')).then(function (d) {
      D = d;
      JN.hud(hud, d);
      fb.hidden = true; statusEl.textContent = ''; placarEl.textContent = '0';
      timerEl.textContent = d.tempo + 's';
      pastas.forEach(function (p) { p.disabled = false; p.classList.remove('infectada', 'falsa', 'isolada', 'errou'); });
      inicio.hidden = true; res.hidden = true; tela.hidden = false;
      encerrando = false; rodando = true;
      consultar();
    }).catch(erro);
  }

  pastas.forEach(function (p, i) { p.addEventListener('click', function () { clicar(i); }); });
  document.addEventListener('keydown', function (ev) {
    if (rodando && ev.key >= '1' && ev.key <= String(pastas.length)) clicar(+ev.key - 1);
  });

  $('jvComecar').addEventListener('click', function () { rodada(false); });
  $('jvJogarDeNovo').addEventListener('click', function () {
    rodando = false; res.hidden = true; tela.hidden = true; hud.hidden = true; inicio.hidden = false;
  });
})();
</script>
