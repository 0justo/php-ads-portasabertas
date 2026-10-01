/* ADS Fatec Jundiaí — só melhorias visuais. O site e o quiz funcionam sem este arquivo. */
(function () {
  'use strict';

  var reduzir = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  function limitar(n, min, max) { return Math.min(max, Math.max(min, n)); }

  /* Chama fn no máximo uma vez por quadro enquanto a página rola */
  function aoRolar(fn) {
    var esperando = false;
    function pedir() {
      if (esperando) return;
      esperando = true;
      requestAnimationFrame(function () { esperando = false; fn(); });
    }
    window.addEventListener('scroll', pedir, { passive: true });
    window.addEventListener('resize', pedir);
    fn();
  }

  /* 0. Menu no celular: se ele rola de lado, deixa o link da página atual à vista */
  var menu = document.querySelector('.topo nav');
  var ativo = menu && menu.querySelector('[aria-current="page"]');
  if (ativo && menu.scrollWidth > menu.clientWidth) {
    var esquerda = ativo.getBoundingClientRect().left - menu.getBoundingClientRect().left + menu.scrollLeft;
    menu.scrollLeft = esquerda - (menu.clientWidth - ativo.offsetWidth) / 2;
  }

  /* 1. Hero: título e prédio andam em velocidades diferentes ao rolar */
  var hero = document.querySelector('[data-hero]');
  if (hero && !reduzir) {
    aoRolar(function () {
      var p = limitar(window.scrollY / (hero.offsetHeight || 1), 0, 1);
      hero.style.setProperty('--hp', p.toFixed(4));
    });
  }

  /* 2. Foto do curso: deslize suave dentro da moldura */
  var fotos = [].slice.call(document.querySelectorAll('[data-parallax]'));
  if (fotos.length && !reduzir) {
    aoRolar(function () {
      var vh = window.innerHeight;
      fotos.forEach(function (el) {
        var r = el.getBoundingClientRect();
        var t = (r.top + r.height / 2 - vh / 2) / (vh / 2 + r.height / 2);   // -1 a 1
        el.style.setProperty('--fy', (-limitar(t, -1, 1)).toFixed(3));
      });
    });
  }

  /* 3. Grade: a linha vermelha enche e o semestre atual fica em destaque */
  var trilha = document.querySelector('[data-trilha]');
  if (trilha) {
    var lista = trilha.querySelector('.linha-tempo');
    var itens = [].slice.call(lista.children);
    aoRolar(function () {
      var linha = window.innerHeight * 0.55;
      var atual = -1;
      itens.forEach(function (li, i) {
        if (li.getBoundingClientRect().top < linha) atual = i;
      });
      var r = lista.getBoundingClientRect();
      lista.style.setProperty('--p', limitar((linha - r.top) / Math.max(r.height, 1), 0, 1).toFixed(4));
      itens.forEach(function (li, i) {
        if (i === atual) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
        li.classList.toggle('passou', i < atual);
      });
    });
  }

  /* 4. Quiz: barra de progresso e, ao clicar, rola até a próxima pergunta.
        O formulário continua sendo um form comum: sem JS ele funciona igual. */
  var form = document.getElementById('form-quiz');
  if (form) {
    var barra = document.getElementById('barra');
    var feitas = document.getElementById('feitas');
    var perguntas = [].slice.call(form.querySelectorAll('.pergunta'));

    var atualizar = function () {
      var n = 0;
      perguntas.forEach(function (p) { if (p.querySelector('input:checked')) n++; });
      feitas.textContent = n;
      barra.style.width = (n / perguntas.length * 100) + '%';
    };
    form.addEventListener('change', atualizar);
    atualizar();

    // detail > 0 = mouse ou toque (teclado não é arrastado para a próxima pergunta)
    form.addEventListener('click', function (e) {
      if (e.detail === 0 || !e.target.closest) return;
      var atual = e.target.closest('.pergunta');
      if (!atual) return;
      var proxima = atual.nextElementSibling;
      if (!proxima || !proxima.classList.contains('pergunta') || proxima.querySelector('input:checked')) return;
      setTimeout(function () {
        try { proxima.scrollIntoView({ behavior: reduzir ? 'auto' : 'smooth', block: 'start' }); } catch (err) {}
      }, 300);
    });
  }
})();
