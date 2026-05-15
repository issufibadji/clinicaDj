<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Clínica DR. João Mendes — Referência em saúde e bem-estar no Rio de Janeiro.">
  <title>Clínica DR. João Mendes — Cuidamos de você</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50:  '#f0fdf8',
              100: '#ccfbee',
              200: '#99f5dc',
              300: '#5de7c3',
              400: '#2dd4aa',
              500: '#10b981',
              600: '#059669',
              700: '#047857',
              800: '#065f46',
              900: '#064e3b',
            },
            teal: { DEFAULT: '#0d9488', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a' },
          },
          fontFamily: {
            playfair: ['"Playfair Display"', 'serif'],
            sans: ['"DM Sans"', 'sans-serif'],
          },
        }
      }
    }
  </script>

  <link rel="stylesheet" href="/landing/css/landing.css">

  <style>
    /* ── Navbar branca ─────────────────────────────────────── */
    #navbar {
      background: #fff !important;
      border-bottom: 1px solid #e5e7eb;
      backdrop-filter: none;
      -webkit-backdrop-filter: none;
    }
    #navbar.scrolled { box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
    .nav-link        { color: #374151 !important; font-weight: 500; }
    .nav-link:hover  { color: #10b981 !important; }
    .nav-link::after { background: #10b981; }

    /* ── Hero teal escuro ──────────────────────────────────── */
    .hero-ref {
      background: linear-gradient(135deg, #0a2e24 0%, #0d4d38 35%, #0e6b4a 65%, #0d5c3f 100%);
      min-height: 100vh;
      position: relative;
      overflow: hidden;
    }
    /* Textura pontilhada suave */
    .hero-ref::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
      background-size: 24px 24px;
      pointer-events: none;
    }

    /* ── Quick booking bar ─────────────────────────────────── */
    .booking-bar {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 8px 40px rgba(0,0,0,0.14);
      padding: 1.25rem 2rem;
    }
    .booking-bar select,
    .booking-bar input {
      border: 1.5px solid #e5e7eb;
      border-radius: 0.5rem;
      padding: 0.6rem 0.9rem;
      font-size: 0.875rem;
      color: #374151;
      background: #f9fafb;
      width: 100%;
      outline: none;
      transition: border-color 0.2s;
      font-family: 'DM Sans', sans-serif;
    }
    .booking-bar select:focus,
    .booking-bar input:focus { border-color: #10b981; background: #fff; }

    /* ── Seção especialidades ──────────────────────────────── */
    .specs-section {
      background: linear-gradient(135deg, #0a2e24 0%, #0c3d2e 50%, #0e4d38 100%);
    }
    .spec-row {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      padding: 0.9rem 0;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      cursor: pointer;
      color: rgba(255,255,255,0.55);
      font-size: 0.95rem;
      font-weight: 500;
      transition: color 0.2s;
      gap: 0.75rem;
      text-align: right;
    }
    .spec-row:hover { color: rgba(255,255,255,0.85); }
    .spec-row.active {
      color: #fff;
      font-weight: 600;
    }
    .spec-row .spec-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: transparent;
      border: 2px solid rgba(255,255,255,0.3);
      flex-shrink: 0;
      transition: background 0.2s, border-color 0.2s;
    }
    .spec-row.active .spec-dot { background: #10b981; border-color: #10b981; }
    .spec-row:last-child { border-bottom: none; }

    /* ── Círculos de stats ─────────────────────────────────── */
    .stat-circle-wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
    }
    .stat-ring {
      position: relative;
      width: 160px;
      height: 160px;
    }
    .stat-ring svg { width: 100%; height: 100%; }
    .stat-ring-arc {
      stroke-dasharray: 440;
      stroke-dashoffset: 440;
      transform-origin: 50% 50%;
      transform: rotate(-90deg);
      transition: stroke-dashoffset 1.8s cubic-bezier(0.4,0,0.2,1);
    }
    .stat-ring.animated .stat-ring-arc { stroke-dashoffset: var(--ring-offset, 50); }
    .stat-ring-num {
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      font-family: 'Playfair Display', serif;
      font-size: 2rem;
      font-weight: 700;
      color: #0f172a;
      line-height: 1;
    }

    /* ── Team photo banner ─────────────────────────────────── */
    .team-photo-banner {
      position: relative;
      overflow: hidden;
      min-height: 420px;
      background: linear-gradient(135deg, #0a2e24 0%, #0d4d38 50%, #0e6b4a 100%);
    }
    .team-photo-banner::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(10,46,36,0.3) 0%, rgba(10,46,36,0.7) 100%);
    }
    .team-banner-text {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      z-index: 10;
      padding: 3rem 4rem;
      text-align: center;
    }

    /* ── Contact dark ──────────────────────────────────────── */
    .contact-dark { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); }

    /* ── Animações ─────────────────────────────────────────── */
    [data-animate] {
      opacity: 0; transform: translateY(24px);
      transition: opacity 0.6s ease, transform 0.6s ease;
    }
    [data-animate].visible { opacity: 1; transform: none; }
    [data-animate-stagger] > * {
      opacity: 0; transform: translateY(20px);
      transition: opacity 0.5s ease, transform 0.5s ease;
    }
    [data-animate-stagger].visible > *:nth-child(1) { transition-delay: 0.05s; opacity:1; transform:none; }
    [data-animate-stagger].visible > *:nth-child(2) { transition-delay: 0.12s; opacity:1; transform:none; }
    [data-animate-stagger].visible > *:nth-child(3) { transition-delay: 0.19s; opacity:1; transform:none; }
    [data-animate-stagger].visible > *:nth-child(4) { transition-delay: 0.26s; opacity:1; transform:none; }

    @keyframes pulse-green {
      0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,.45); }
      50%      { box-shadow: 0 0 0 14px rgba(16,185,129,0); }
    }
    .pulse-green { animation: pulse-green 2.5s ease infinite; }

    .footer-link { color:rgba(255,255,255,.45); font-size:.85rem; text-decoration:none; transition:color .2s; display:block; margin-bottom:.45rem; }
    .footer-link:hover { color:#10b981; }
  </style>
</head>
<body class="font-sans antialiased" style="overflow-x:hidden;">

  <!-- ════════════════════════════════════════════════════════
       NAVBAR — branca, logo "Dr.", links escuros
  ═════════════════════════════════════════════════════════ -->
  <nav id="navbar" class="fixed top-0 inset-x-0 z-50 transition-shadow duration-300">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 flex items-center justify-between h-16">

      <!-- Logo -->
      <a href="#inicio" class="flex items-center gap-1.5 no-underline group">
        <div class="w-9 h-9 rounded-xl bg-brand-500 flex items-center justify-center shadow-md shadow-brand-500/30 group-hover:scale-105 transition-transform">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16M4 12h16"/>
          </svg>
        </div>
        <div class="leading-tight">
          <p class="font-playfair font-bold text-gray-800 text-sm tracking-tight">DR. João</p>
          <p class="text-brand-600 font-semibold text-xs tracking-wide">Mendes</p>
        </div>
      </a>

      <!-- Links desktop -->
      <div class="hidden md:flex items-center gap-7">
        <a href="#inicio"       class="nav-link text-sm">Home</a>
        <a href="#sobre"        class="nav-link text-sm">Sobre</a>
        <a href="#especialidades" class="nav-link text-sm">Serviços</a>
        <a href="#equipe"       class="nav-link text-sm">Equipe</a>
        <a href="#contato"      class="nav-link text-sm">Contato</a>
      </div>

      <!-- Botão CTA + hamburger -->
      <div class="flex items-center gap-3">
        <a href="#contato"
           class="hidden sm:inline-flex items-center gap-2 border-2 border-brand-500 text-brand-600 hover:bg-brand-500 hover:text-white px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200">
          Agendar Consulta
        </a>
        <button id="mobile-menu-btn" aria-expanded="false" aria-label="Menu"
                class="md:hidden flex flex-col justify-center items-center w-9 h-9 gap-1.5">
          <span class="block w-5 h-0.5 bg-gray-600 rounded transition-all"></span>
          <span class="block w-5 h-0.5 bg-gray-600 rounded transition-all"></span>
          <span class="block w-5 h-0.5 bg-gray-600 rounded transition-all"></span>
        </button>
      </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden bg-white border-t border-gray-100 px-5 py-4 flex-col gap-3 shadow-lg" style="display:none;">
      <a href="#inicio"        class="nav-link py-2 block">Home</a>
      <a href="#sobre"         class="nav-link py-2 block">Sobre</a>
      <a href="#especialidades" class="nav-link py-2 block">Serviços</a>
      <a href="#equipe"        class="nav-link py-2 block">Equipe</a>
      <a href="#contato"       class="nav-link py-2 block">Contato</a>
      <a href="#contato" class="mt-2 justify-center border-2 border-brand-500 text-brand-600 hover:bg-brand-500 hover:text-white px-5 py-2.5 rounded-full text-sm font-semibold transition-all duration-200 inline-flex">Agendar Consulta</a>
    </div>
  </nav>

  <!-- ════════════════════════════════════════════════════════
       HERO — fundo teal escuro + silhueta médica grande
  ═════════════════════════════════════════════════════════ -->
  <section id="inicio" class="hero-ref" style="padding-top:4rem;">

    <!-- Blur blob decorativo -->
    <div class="absolute" style="width:60%;height:80%;top:10%;right:-15%;background:radial-gradient(ellipse at center,rgba(16,185,129,.12) 0%,transparent 70%);border-radius:50%;pointer-events:none;"></div>

    <div class="relative max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-12 items-center" style="min-height:calc(100vh - 4rem); padding-top:5rem; padding-bottom:9rem;">

      <!-- Coluna de texto -->
      <div class="flex flex-col gap-6" data-animate>
        <div class="flex items-center gap-2">
          <div class="w-6 h-0.5 rounded-full bg-brand-400"></div>
          <span class="text-brand-400 text-xs font-semibold tracking-widest uppercase">Clínica de Referência</span>
        </div>

        <h1 class="font-playfair font-bold text-white leading-tight" style="font-size:clamp(2.4rem,5.5vw,4rem);">
          Um ótimo lugar<br>para cuidar de<br>
          <span style="color:#2dd4bf;">você mesmo.</span>
        </h1>

        <p class="text-white/65 text-base leading-relaxed max-w-md">
          Atendimento médico humanizado com equipe especializada, tecnologia de ponta e compromisso com o seu bem-estar. Cuide da sua saúde com quem realmente entende.
        </p>

        <div>
          <a href="#contato"
             class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold px-7 py-3.5 rounded-full text-sm transition-all duration-200 shadow-lg shadow-brand-500/30 hover:shadow-brand-500/50 hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Agendar Consulta
          </a>
        </div>

        <!-- Mini badges -->
        <div class="flex flex-wrap gap-5 mt-2">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background:rgba(16,185,129,.15);">
              <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <span class="text-white/55 text-sm">CFM Registrado</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background:rgba(16,185,129,.15);">
              <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <span class="text-white/55 text-sm">67+ Médicos</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background:rgba(16,185,129,.15);">
              <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
              </svg>
            </div>
            <span class="text-white/55 text-sm">98% Satisfação</span>
          </div>
        </div>
      </div>

      <!-- Coluna visual: card médico grande + floating badges -->
      <div class="flex justify-center lg:justify-end" data-animate>
        <div class="relative" style="width:340px; height:440px;">

          <!-- Card principal com silhueta médica -->
          <div class="w-full h-full rounded-3xl overflow-hidden relative"
               style="background: linear-gradient(155deg, #10b981 0%, #0d9488 50%, #065f46 100%);">

            <!-- Padrão de fundo no card -->
            <div class="absolute inset-0" style="background-image:radial-gradient(rgba(255,255,255,0.08) 1px,transparent 1px);background-size:20px 20px;"></div>

            <!-- Silhueta SVG médica centralizada -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 220 310"
                 class="absolute" style="bottom:0;left:50%;transform:translateX(-50%);width:82%;opacity:.75;">
              <!-- Cabeça -->
              <circle cx="110" cy="52" r="34" fill="rgba(255,255,255,0.85)"/>
              <!-- Pescoço -->
              <rect x="100" y="82" width="20" height="18" rx="6" fill="rgba(255,255,255,0.85)"/>
              <!-- Jaleco/corpo -->
              <path d="M55 108 Q42 122 40 165 L40 240 L180 240 L180 165 Q178 122 165 108 L140 95 L110 110 L80 95 Z"
                    fill="rgba(255,255,255,0.82)"/>
              <!-- Lapela jaleco esquerda -->
              <path d="M80 95 L90 140 L110 130 L110 110 Z" fill="rgba(16,185,129,0.3)"/>
              <!-- Lapela jaleco direita -->
              <path d="M140 95 L130 140 L110 130 L110 110 Z" fill="rgba(16,185,129,0.3)"/>
              <!-- Cruz médica -->
              <rect x="102" y="148" width="16" height="44" rx="4" fill="#10b981"/>
              <rect x="90" y="160" width="40" height="16" rx="4" fill="#10b981"/>
              <!-- Braço esquerdo -->
              <path d="M40 120 Q22 140 24 175 L42 175 Q42 148 55 132" fill="rgba(255,255,255,0.82)"/>
              <!-- Braço direito -->
              <path d="M180 120 Q198 140 196 175 L178 175 Q178 148 165 132" fill="rgba(255,255,255,0.82)"/>
              <!-- Estetoscópio -->
              <path d="M82 108 Q70 140 76 168 Q78 182 90 182 Q102 182 104 170 L104 158"
                    stroke="rgba(255,255,255,0.9)" stroke-width="3.5" fill="none" stroke-linecap="round"/>
              <circle cx="104" cy="155" r="6" fill="rgba(255,255,255,0.9)"/>
              <!-- Pernas simplificadas -->
              <rect x="75" y="238" width="32" height="62" rx="10" fill="rgba(255,255,255,0.7)"/>
              <rect x="113" y="238" width="32" height="62" rx="10" fill="rgba(255,255,255,0.7)"/>
            </svg>
          </div>

          <!-- Badge 15k / ano — topo direito -->
          <div class="absolute -top-4 -right-5 rounded-2xl flex flex-col items-center justify-center text-center shadow-xl"
               style="width:76px;height:76px;background:#fff;border:2px solid #e5e7eb;">
            <span class="font-playfair font-bold text-gray-800 text-2xl leading-none">15k</span>
            <span class="text-brand-600 text-xs font-semibold">/ano</span>
          </div>

          <!-- Badge 99% recuperados — canto inferior esquerdo -->
          <div class="absolute -bottom-4 -left-5 flex items-center gap-2 px-3 py-2.5 rounded-xl shadow-xl"
               style="background:#fff;min-width:140px;border:1px solid #e5e7eb;">
            <div class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center flex-shrink-0">
              <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div>
              <p class="font-bold text-gray-800 text-sm leading-none">99%</p>
              <p class="text-gray-500 text-xs">recuperados</p>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ── Booking bar flutuando sobre a transição ── -->
    <div class="relative max-w-5xl mx-auto px-5 sm:px-8" style="margin-top:-3.5rem;padding-bottom:3.5rem;z-index:20;">
      <div class="booking-bar">
        <p class="text-gray-400 text-[11px] font-bold uppercase tracking-widest mb-3">Agendamento Rápido</p>
        <div class="flex flex-col sm:flex-row gap-3 items-end">
          <div class="flex-1">
            <label class="text-xs text-gray-500 font-medium mb-1 block">Especialidade</label>
            <select>
              <option value="" disabled selected>Escolha o serviço</option>
              <option>Clínica Geral</option>
              <option>Cardiologia</option>
              <option>Pediatria</option>
              <option>Ortopedia</option>
              <option>Dermatologia</option>
              <option>Ginecologia</option>
              <option>Neurologia</option>
              <option>Oncologia</option>
              <option>Anestesiologia</option>
              <option>Cirurgia Bariátrica</option>
            </select>
          </div>
          <div class="flex-1">
            <label class="text-xs text-gray-500 font-medium mb-1 block">Data preferida</label>
            <input type="date">
          </div>
          <div class="flex-1">
            <label class="text-xs text-gray-500 font-medium mb-1 block">Telefone</label>
            <input type="tel" placeholder="+55 (21) 99000-0000">
          </div>
          <div class="flex-shrink-0">
            <button id="quick-book-btn"
                    class="w-full sm:w-auto bg-brand-500 hover:bg-brand-600 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors duration-200 whitespace-nowrap text-sm">
              Agendar →
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════
       ESPECIALIDADES — fundo teal escuro, lista à direita
  ═════════════════════════════════════════════════════════ -->
  <section id="especialidades" class="specs-section py-20 lg:py-24">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid lg:grid-cols-2 gap-16 items-center">

      <!-- Esquerda: texto -->
      <div class="flex flex-col gap-5" data-animate>
        <div class="flex items-center gap-2">
          <div class="w-6 h-0.5 rounded-full bg-brand-400"></div>
          <span class="text-brand-400 text-xs font-semibold tracking-widest uppercase">O que oferecemos</span>
        </div>
        <h2 class="font-playfair font-bold text-white leading-tight" style="font-size:clamp(1.9rem,4vw,2.8rem);">
          Veja o que oferecemos<br>para mantê-lo saudável
        </h2>
        <p class="text-white/55 text-sm leading-relaxed max-w-sm">
          Com práticas médicas preventivas, prescritivas e curativas de nível mundial, nossa clínica cuida de você desde o início do século com dedicação e excelência.
        </p>
        <div class="mt-2">
          <a href="#contato"
             class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold px-7 py-3 rounded-full text-sm transition-all duration-200 shadow-lg shadow-brand-500/30">
            Agendar Consulta →
          </a>
        </div>
      </div>

      <!-- Direita: lista vertical de especialidades -->
      <div data-animate>
        <div class="spec-row active specialty-item">
          <span>Cardiologia</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Anestesiologia</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Cirurgia Bariátrica</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Banco de Sangue</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Endocrinologia &amp; Diabetologia</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Neurologia</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Oncologia Médica</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Ginecologia &amp; Obstetrícia</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Ortopedia</span>
          <div class="spec-dot"></div>
        </div>
        <div class="spec-row specialty-item">
          <span>Pediatria</span>
          <div class="spec-dot"></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════
       STATS — 3 círculos grandes, fundo branco
  ═════════════════════════════════════════════════════════ -->
  <section id="sobre" class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-5 sm:px-8">

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-12" data-animate-stagger>

        <!-- 67+ Médicos -->
        <div class="stat-circle-wrap">
          <div class="stat-ring" style="--ring-offset:52px;">
            <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
              <circle cx="80" cy="80" r="70" stroke="#E5E7EB" stroke-width="12" fill="none"/>
              <circle cx="80" cy="80" r="70" stroke="#10B981" stroke-width="12" fill="none"
                      stroke-dasharray="440" stroke-dashoffset="440"
                      class="stat-ring-arc" stroke-linecap="round"/>
            </svg>
            <span class="stat-ring-num" data-count="67" data-suffix="+">0</span>
          </div>
          <div class="text-center">
            <p class="font-bold text-gray-800 text-base">Médicos Qualificados</p>
            <p class="text-gray-400 text-sm mt-1">Especialistas presentes em nossa clínica</p>
          </div>
        </div>

        <!-- 99% Recuperados -->
        <div class="stat-circle-wrap">
          <div class="stat-ring" style="--ring-offset:4px;">
            <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
              <circle cx="80" cy="80" r="70" stroke="#E5E7EB" stroke-width="12" fill="none"/>
              <circle cx="80" cy="80" r="70" stroke="#10B981" stroke-width="12" fill="none"
                      stroke-dasharray="440" stroke-dashoffset="440"
                      class="stat-ring-arc" stroke-linecap="round"/>
            </svg>
            <span class="stat-ring-num" data-count="99" data-suffix="%">0</span>
          </div>
          <div class="text-center">
            <p class="font-bold text-gray-800 text-base">Pacientes Recuperados</p>
            <p class="text-gray-400 text-sm mt-1">Você e sua vida são mais importantes para nós</p>
          </div>
        </div>

        <!-- 98% Satisfação -->
        <div class="stat-circle-wrap">
          <div class="stat-ring" style="--ring-offset:9px;">
            <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">
              <circle cx="80" cy="80" r="70" stroke="#E5E7EB" stroke-width="12" fill="none"/>
              <circle cx="80" cy="80" r="70" stroke="#10B981" stroke-width="12" fill="none"
                      stroke-dasharray="440" stroke-dashoffset="440"
                      class="stat-ring-arc" stroke-linecap="round"/>
            </svg>
            <span class="stat-ring-num" data-count="98" data-suffix="%">0</span>
          </div>
          <div class="text-center">
            <p class="font-bold text-gray-800 text-base">Taxa de Satisfação</p>
            <p class="text-gray-400 text-sm mt-1">Mais de 10.000 pacientes satisfeitos</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════
       BANNER EQUIPE — fundo teal com silhueta + texto
  ═════════════════════════════════════════════════════════ -->
  <section class="team-photo-banner">

    <!-- Silhuetas decorativas de equipe médica -->
    <div class="absolute inset-0 flex items-end justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 380" class="w-full" style="max-height:380px; opacity:0.45;">
        <!-- Médico central (maior) -->
        <g transform="translate(450,0)">
          <circle cx="0" cy="60" r="38" fill="white"/>
          <path d="M-50,108 Q-65,125 -68,175 L-68,240 L68,240 L68,175 Q65,125 50,108 L28,95 L0,112 L-28,95 Z" fill="white"/>
          <rect x="-9" y="145" width="18" height="46" rx="4" fill="#10b981"/>
          <rect x="-23" y="158" width="46" height="18" rx="4" fill="#10b981"/>
          <path d="M-68,118 Q-88,138 -86,175 L-68,175" fill="white"/>
          <path d="M68,118 Q88,138 86,175 L68,175" fill="white"/>
          <rect x="-20" y="238" width="16" height="60" rx="8" fill="rgba(255,255,255,0.7)"/>
          <rect x="4" y="238" width="16" height="60" rx="8" fill="rgba(255,255,255,0.7)"/>
        </g>
        <!-- Médico esquerdo -->
        <g transform="translate(260,30)">
          <circle cx="0" cy="55" r="32" fill="white"/>
          <path d="M-42,94 Q-56,110 -58,152 L-58,210 L58,210 L58,152 Q56,110 42,94 L22,82 L0,96 L-22,82 Z" fill="white"/>
          <path d="M-58,102 Q-74,120 -72,152 L-58,152" fill="white"/>
          <path d="M58,102 Q74,120 72,152 L58,152" fill="white"/>
          <rect x="-16" y="210" width="13" height="52" rx="6" fill="rgba(255,255,255,0.65)"/>
          <rect x="3" y="210" width="13" height="52" rx="6" fill="rgba(255,255,255,0.65)"/>
        </g>
        <!-- Médico direito -->
        <g transform="translate(640,30)">
          <circle cx="0" cy="55" r="32" fill="white"/>
          <path d="M-42,94 Q-56,110 -58,152 L-58,210 L58,210 L58,152 Q56,110 42,94 L22,82 L0,96 L-22,82 Z" fill="white"/>
          <path d="M-58,102 Q-74,120 -72,152 L-58,152" fill="white"/>
          <path d="M58,102 Q74,120 72,152 L58,152" fill="white"/>
          <rect x="-16" y="210" width="13" height="52" rx="6" fill="rgba(255,255,255,0.65)"/>
          <rect x="3" y="210" width="13" height="52" rx="6" fill="rgba(255,255,255,0.65)"/>
        </g>
        <!-- Médico far-esquerdo -->
        <g transform="translate(90,50)">
          <circle cx="0" cy="50" r="28" fill="rgba(255,255,255,0.8)"/>
          <path d="M-36,84 Q-48,98 -50,134 L-50,185 L50,185 L50,134 Q48,98 36,84 L18,73 L0,85 L-18,73 Z" fill="rgba(255,255,255,0.8)"/>
          <rect x="-14" y="185" width="11" height="48" rx="6" fill="rgba(255,255,255,0.55)"/>
          <rect x="3" y="185" width="11" height="48" rx="6" fill="rgba(255,255,255,0.55)"/>
        </g>
        <!-- Médico far-direito -->
        <g transform="translate(810,50)">
          <circle cx="0" cy="50" r="28" fill="rgba(255,255,255,0.8)"/>
          <path d="M-36,84 Q-48,98 -50,134 L-50,185 L50,185 L50,134 Q48,98 36,84 L18,73 L0,85 L-18,73 Z" fill="rgba(255,255,255,0.8)"/>
          <rect x="-14" y="185" width="11" height="48" rx="6" fill="rgba(255,255,255,0.55)"/>
          <rect x="3" y="185" width="11" height="48" rx="6" fill="rgba(255,255,255,0.55)"/>
        </g>
      </svg>
    </div>

    <!-- Texto sobreposto -->
    <div class="team-banner-text">
      <p class="text-brand-400 text-xs font-semibold tracking-widest uppercase mb-2">Nossa Equipe</p>
      <h2 class="font-playfair font-bold text-white text-3xl mb-1">Conheça quem cuida de você</h2>
      <p class="text-white/60 text-sm">67 especialistas comprometidos com sua saúde e bem-estar.</p>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════
       EQUIPE — cards médicos
  ═════════════════════════════════════════════════════════ -->
  <section id="equipe" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
      <div class="text-center mb-12" data-animate>
        <span class="text-brand-600 text-xs font-bold uppercase tracking-widest">Nossos Profissionais</span>
        <h2 class="font-playfair font-bold text-gray-900 mt-2" style="font-size:clamp(1.7rem,3.5vw,2.4rem);">Médicos Especialistas</h2>
        <p class="text-gray-500 mt-2 max-w-lg mx-auto text-sm">Altamente qualificados e comprometidos com a excelência no atendimento.</p>
      </div>

      <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-7" data-animate-stagger>

        @php
        $team = [
          ['initials'=>'JM','name'=>'DR. João Mendes','role'=>'Clínico Geral e Fundador','crm'=>'CRM/RJ 12345'],
          ['initials'=>'AC','name'=>'DRA. Ana Paula Costa','role'=>'Cardiologista','crm'=>'CRM/RJ 23456'],
          ['initials'=>'CS','name'=>'DR. Carlos Santos','role'=>'Ortopedista','crm'=>'CRM/RJ 34567'],
          ['initials'=>'BL','name'=>'DRA. Beatriz Lima','role'=>'Pediatra','crm'=>'CRM/RJ 45678'],
          ['initials'=>'RS','name'=>'DR. Ricardo Souza','role'=>'Neurologista','crm'=>'CRM/RJ 56789'],
          ['initials'=>'FR','name'=>'DRA. Fernanda Rocha','role'=>'Ginecologista','crm'=>'CRM/RJ 67890'],
        ];
        @endphp

        @foreach($team as $doc)
        <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
          <div class="h-44 flex items-center justify-center font-playfair text-5xl font-bold relative overflow-hidden"
               style="background:linear-gradient(135deg,#10b981,#0d9488);">
            <span class="text-white/80">{{ $doc['initials'] }}</span>
            <!-- Overlay hover -->
            <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300"
                 style="background:rgba(16,185,129,.88);">
              <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
              </svg>
            </div>
          </div>
          <div class="p-5">
            <h3 class="font-bold text-gray-800 font-playfair">{{ $doc['name'] }}</h3>
            <p class="text-sm text-brand-600 font-medium mt-0.5">{{ $doc['role'] }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $doc['crm'] }}</p>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════
       CONTATO — dark
  ═════════════════════════════════════════════════════════ -->
  <section id="contato" class="contact-dark py-20">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">

      <div class="text-center mb-12" data-animate>
        <span class="text-brand-400 text-xs font-bold uppercase tracking-widest">Fale Conosco</span>
        <h2 class="font-playfair font-bold text-white mt-2" style="font-size:clamp(1.7rem,3.5vw,2.4rem);">Agende sua Consulta</h2>
        <p class="text-slate-400 mt-2 max-w-lg mx-auto text-sm">Preencha o formulário e nossa equipe entra em contato em até 24h.</p>
      </div>

      <div class="grid lg:grid-cols-2 gap-12 items-start">

        <!-- Formulário -->
        <div data-animate>
          <form id="contact-form" class="flex flex-col gap-4">
            @foreach([
              ['type'=>'text',  'placeholder'=>'Nome completo',      'required'=>true,  'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
              ['type'=>'tel',   'placeholder'=>'Telefone / WhatsApp', 'required'=>true,  'icon'=>'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
              ['type'=>'email', 'placeholder'=>'E-mail',             'required'=>true,  'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ] as $f)
            <div class="relative">
              <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                </svg>
              </div>
              <input type="{{ $f['type'] }}" placeholder="{{ $f['placeholder'] }}"
                     @if($f['required']) required @endif
                     class="w-full rounded-xl pl-10 pr-4 py-3 text-sm text-white placeholder-white/35
                            outline-none transition-all duration-200"
                     style="background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.12);"
                     onfocus="this.style.borderColor='#10b981';this.style.background='rgba(255,255,255,.09)'"
                     onblur="this.style.borderColor='rgba(255,255,255,.12)';this.style.background='rgba(255,255,255,.06)'">
            </div>
            @endforeach

            <div class="relative">
              <div class="absolute left-3.5 top-3.5 text-slate-400 pointer-events-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6M4 6h16M4 18h16"/>
                </svg>
              </div>
              <select required class="w-full rounded-xl pl-10 pr-4 py-3 text-sm text-white outline-none transition-all duration-200"
                      style="background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.12);">
                <option value="" disabled selected class="text-gray-800">Especialidade</option>
                <option class="text-gray-800">Clínica Geral</option>
                <option class="text-gray-800">Cardiologia</option>
                <option class="text-gray-800">Ortopedia</option>
                <option class="text-gray-800">Neurologia</option>
                <option class="text-gray-800">Pediatria</option>
                <option class="text-gray-800">Ginecologia</option>
                <option class="text-gray-800">Oncologia</option>
                <option class="text-gray-800">Endocrinologia</option>
              </select>
            </div>

            <div id="form-alert" class="hidden"></div>

            <button type="submit"
                    class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl py-3.5 transition-colors duration-200 text-sm tracking-wide">
              Solicitar Agendamento →
            </button>
          </form>
        </div>

        <!-- Info direita -->
        <div class="flex flex-col gap-5" data-animate>
          @foreach([
            ['title'=>'Endereço','text'=>'Av. das Américas, 4200 — Barra da Tijuca<br>Rio de Janeiro, RJ — CEP 22640-102','icon'=>'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
            ['title'=>'Telefone & WhatsApp','text'=>'(21) 3000-1234<br>(21) 99000-5678','icon'=>'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
            ['title'=>'Horário de Atendimento','text'=>'Segunda a Sexta: 07h às 20h<br>Sábado: 08h às 14h<br><span style="color:#34d399;">Urgência: 24h / 7 dias</span>','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
          ] as $info)
          <div class="flex gap-4 p-5 rounded-2xl" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(16,185,129,.15);">
              <svg class="w-5 h-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $info['icon'] }}"/>
              </svg>
            </div>
            <div>
              <h4 class="text-white font-semibold mb-1 text-sm">{{ $info['title'] }}</h4>
              <p class="text-slate-400 text-sm leading-relaxed">{!! $info['text'] !!}</p>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <!-- ════════════════════════════════════════════════════════
       FOOTER
  ═════════════════════════════════════════════════════════ -->
  <footer class="py-14" style="background:#080d1a;">
    <div class="max-w-7xl mx-auto px-5 sm:px-8">
      <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-10">

        <div class="flex flex-col gap-4">
          <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-brand-500 flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16M4 12h16"/>
              </svg>
            </div>
            <div>
              <p class="font-playfair font-bold text-white text-sm">DR. João Mendes</p>
              <p class="text-brand-500 text-xs">Sistema de Saúde</p>
            </div>
          </div>
          <p class="text-slate-500 text-sm leading-relaxed">Referência em saúde e bem-estar no Rio de Janeiro, com mais de 15 anos de atendimento humanizado.</p>
          <div class="flex gap-2 mt-1">
            @foreach(['M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z','M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'] as $svg)
            <a href="#" class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:text-brand-400 transition-colors" style="background:rgba(255,255,255,.05);">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $svg }}"/></svg>
            </a>
            @endforeach
          </div>
        </div>

        <div>
          <h5 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Serviços</h5>
          <a class="footer-link" href="#especialidades">Cardiologia</a>
          <a class="footer-link" href="#especialidades">Clínica Geral</a>
          <a class="footer-link" href="#especialidades">Ortopedia</a>
          <a class="footer-link" href="#especialidades">Neurologia</a>
          <a class="footer-link" href="#especialidades">Pediatria</a>
          <a class="footer-link" href="#especialidades">Ginecologia</a>
        </div>

        <div>
          <h5 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Links Úteis</h5>
          <a class="footer-link" href="#sobre">Sobre a Clínica</a>
          <a class="footer-link" href="#equipe">Nossa Equipe</a>
          <a class="footer-link" href="#especialidades">Especialidades</a>
          <a class="footer-link" href="#contato">Agendamento</a>
          <a class="footer-link" href="#contato">Fale Conosco</a>
        </div>

        <div>
          <h5 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Contato</h5>
          <p class="text-slate-500 text-sm mb-2">Av. das Américas, 4200<br>Barra da Tijuca — RJ</p>
          <p class="text-slate-500 text-sm mb-1">(21) 3000-1234</p>
          <p class="text-slate-500 text-sm mb-4">(21) 99000-5678</p>
          <p class="text-brand-500 text-xs font-semibold">Seg–Sex 07h–20h · Sáb 08h–14h</p>
        </div>
      </div>

      <div class="mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3" style="border-top:1px solid rgba(255,255,255,.07);">
        <p class="text-slate-600 text-xs">© {{ date('Y') }} Clínica DR. João Mendes. Todos os direitos reservados.</p>
        <a href="/login" class="text-slate-600 hover:text-brand-400 text-xs transition-colors underline underline-offset-2">Área Restrita</a>
      </div>
    </div>
  </footer>

  <!-- WhatsApp flutuante -->
  <a href="https://wa.me/5521990005678" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"
     class="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white rounded-full px-4 py-3 shadow-xl pulse-green transition-colors duration-200 font-semibold text-sm">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    <span class="hidden sm:inline">WhatsApp</span>
  </a>

  <script>
  document.addEventListener('DOMContentLoaded', () => {

    /* Navbar scroll */
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 60), {passive:true});

    /* Mobile menu */
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    menuBtn?.addEventListener('click', () => {
      const open = mobileMenu.classList.toggle('hidden');
      menuBtn.setAttribute('aria-expanded', String(!open));
    });
    mobileMenu?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => mobileMenu.classList.add('hidden')));

    /* Smooth scroll */
    document.querySelectorAll('a[href^="#"]').forEach(a => {
      a.addEventListener('click', e => {
        const t = document.querySelector(a.getAttribute('href'));
        if (t) { e.preventDefault(); t.scrollIntoView({behavior:'smooth', block:'start'}); }
      });
    });

    /* Quick book → contato */
    document.getElementById('quick-book-btn')?.addEventListener('click', () => {
      document.getElementById('contato')?.scrollIntoView({behavior:'smooth'});
    });

    /* Specialties click */
    document.querySelectorAll('.specialty-item').forEach(item => {
      item.addEventListener('click', () => {
        document.querySelectorAll('.specialty-item').forEach(i => i.classList.remove('active'));
        item.classList.add('active');
      });
    });

    /* IntersectionObserver — animações */
    const animObs = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); animObs.unobserve(e.target); }});
    }, {threshold: 0.1});
    document.querySelectorAll('[data-animate],[data-animate-stagger]').forEach(el => animObs.observe(el));

    /* Counter animation */
    const countObs = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (!e.isIntersecting) return;
        const el = e.target;
        const target = parseInt(el.dataset.count, 10);
        const suffix = el.dataset.suffix || '';
        let cur = 0;
        const step = target / 70;
        const timer = setInterval(() => {
          cur += step;
          if (cur >= target) { el.textContent = target + suffix; clearInterval(timer); return; }
          el.textContent = Math.floor(cur) + suffix;
        }, 16);
        countObs.unobserve(el);
      });
    }, {threshold: 0.5});
    document.querySelectorAll('[data-count]').forEach(el => countObs.observe(el));

    /* Ring arc animation */
    const ringObs = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('animated'); ringObs.unobserve(e.target); }});
    }, {threshold: 0.4});
    document.querySelectorAll('.stat-ring').forEach(el => ringObs.observe(el));

    /* Contact form */
    const form = document.getElementById('contact-form');
    form?.addEventListener('submit', e => {
      e.preventDefault();
      const required = form.querySelectorAll('[required]');
      let valid = true;
      required.forEach(f => { if (!f.value.trim()) { f.style.borderColor='#ef4444'; valid=false; } else { f.style.borderColor=''; } });
      const alert = document.getElementById('form-alert');
      if (!alert) return;
      if (!valid) {
        alert.textContent = 'Por favor, preencha todos os campos obrigatórios.';
        alert.className = 'p-3 rounded-xl text-sm bg-red-500/20 border border-red-500/40 text-red-300';
      } else {
        alert.textContent = 'Sua solicitação foi recebida! Entraremos em contato em breve.';
        alert.className = 'p-3 rounded-xl text-sm bg-green-500/20 border border-green-500/40 text-green-300';
        form.reset();
      }
      alert.classList.remove('hidden');
      setTimeout(() => alert.classList.add('hidden'), 5000);
    });
  });
  </script>

</body>
</html>
