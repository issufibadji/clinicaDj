/* ═══════════════════════════════════════════════════════════════
   Clínica DR.João Mendes — Landing Page Scripts
   ═══════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

  /* ── 1. Navbar: fundo ao scrollar ─────────────────────────── */
  const navbar = document.getElementById('navbar');
  const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 80);
  window.addEventListener('scroll', onScroll, { passive: true });

  /* ── 2. Mobile menu ────────────────────────────────────────── */
  const menuBtn  = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  menuBtn?.addEventListener('click', () => {
    const open = mobileMenu.classList.toggle('hidden');
    menuBtn.setAttribute('aria-expanded', String(!open));
  });
  // Fecha ao clicar em um link do menu mobile
  mobileMenu?.querySelectorAll('a').forEach(a =>
    a.addEventListener('click', () => mobileMenu.classList.add('hidden'))
  );

  /* ── 3. Intersection Observer — animações de entrada ───────── */
  const animObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        animObserver.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('[data-animate], [data-animate-stagger]')
    .forEach(el => animObserver.observe(el));

  /* ── 4. Contador animado das estatísticas ──────────────────── */
  const countObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el      = e.target;
      const target  = parseInt(el.dataset.count, 10);
      const suffix  = el.dataset.suffix || '';
      let current   = 0;
      const step    = target / 70;
      const timer   = setInterval(() => {
        current += step;
        if (current >= target) {
          el.textContent = target + suffix;
          clearInterval(timer);
          return;
        }
        el.textContent = Math.floor(current) + suffix;
      }, 16);
      countObserver.unobserve(el);
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('[data-count]').forEach(el => countObserver.observe(el));

  /* ── 5. Animação dos arcos SVG de estatísticas ─────────────── */
  const arcObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('animated');
        arcObserver.unobserve(e.target);
      }
    });
  }, { threshold: 0.4 });

  document.querySelectorAll('.stat-svg-wrap').forEach(el => arcObserver.observe(el));

  /* ── 6. Lista de especialidades — tab ativo ────────────────── */
  document.querySelectorAll('.specialty-item').forEach(item => {
    item.addEventListener('click', () => {
      document.querySelectorAll('.specialty-item').forEach(i => i.classList.remove('active'));
      item.classList.add('active');
    });
  });

  /* ── 7. Formulário de contato — validação básica ───────────── */
  const contactForm = document.getElementById('contact-form');
  contactForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const required = contactForm.querySelectorAll('[required]');
    let valid = true;
    required.forEach(field => {
      field.style.borderColor = '';
      if (!field.value.trim()) {
        field.style.borderColor = '#ef4444';
        valid = false;
      }
    });
    if (!valid) {
      showAlert('Por favor, preencha todos os campos obrigatórios.', 'error');
      return;
    }
    // Simula envio bem-sucedido
    showAlert('Sua solicitação foi recebida! Entraremos em contato em breve.', 'success');
    contactForm.reset();
  });

  function showAlert(msg, type) {
    const alertEl = document.getElementById('form-alert');
    if (!alertEl) return;
    alertEl.textContent = msg;
    alertEl.className = type === 'success'
      ? 'mt-4 p-3 rounded-xl text-sm bg-green-500/20 border border-green-500/40 text-green-300'
      : 'mt-4 p-3 rounded-xl text-sm bg-red-500/20 border border-red-500/40 text-red-300';
    alertEl.classList.remove('hidden');
    setTimeout(() => alertEl.classList.add('hidden'), 5000);
  }

  /* ── 8. Botão quick-form → scroll para contato ─────────────── */
  document.getElementById('quick-book-btn')?.addEventListener('click', () => {
    document.getElementById('contato')?.scrollIntoView({ behavior: 'smooth' });
  });

  /* ── 9. Smooth scroll para links âncoras da navbar ─────────── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', (e) => {
      const target = document.querySelector(anchor.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

});
