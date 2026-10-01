// Ajudantes compartilhados pelos 4 joguinhos da página de resultado.
window.JN = {
  post: function (url, dados) {
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(dados || {}).toString()
    }).then(function (r) { return r.json(); });
  },
  get: function (url) {
    return fetch(url, { cache: 'no-store' }).then(function (r) { return r.json(); });
  },
  // Contagem regressiva de 1 em 1 segundo. Devolve uma função que para o relógio.
  timer: function (el, total, aoZerar) {
    var resto = total, id;
    el.textContent = resto + 's';
    id = setInterval(function () {
      resto--;
      el.textContent = Math.max(resto, 0) + 's';
      if (resto <= 0) { clearInterval(id); aoZerar(); }
    }, 1000);
    return function () { clearInterval(id); };
  },
  hud: function (el, d) {
    el.hidden = false;
    el.innerHTML = '<span>Nível <b>' + d.nivel + '</b>/' + (d.total || 5) + ' · ' + d.nome + '</span>' +
      '<span>Pontos <b>' + d.pts + '</b></span>' +
      (d.seq > 1 ? '<span>🔥 Sequência <b>' + d.seq + '</b></span>' : '') +
      (d.dica ? '<span class="jn-dica">' + d.dica + '</span>' : '');
  },
  // Painel de feedback entre um nível e outro. j = resposta do servidor.
  feedback: function (el, venceu, html, j, avancar, verFim) {
    el.hidden = false;
    el.className = 'jn-feedback ' + (venceu ? 'ok' : 'no');
    el.querySelector('.jn-fb-txt').innerHTML = html;
    var b = el.querySelector('.jn-fb-btn');
    b.textContent = j.fim ? 'Ver resultado' : 'Próximo nível';
    b.onclick = j.fim ? verFim : avancar;
    b.focus();
  },
  fimMsg: function (d) {
    var t = d.total || 5;
    return (d.concluiu ? '🏆 Você concluiu os ' + t + ' níveis! ' : 'Você parou no nível ' + d.nivel + '. ') +
      d.pts + ' pontos · ' + d.vencidos + ' de ' + t + ' níveis vencidos · melhor sequência: ' + d.melhor;
  }
};
