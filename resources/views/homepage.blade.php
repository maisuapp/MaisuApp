@php 
    $general_settings = \App\model\GeneralSettings::first();
    $stripeKey = config('services.stripe.key') ?: env('STRIPE_KEY');
@endphp
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MaisuApp | Marketplace Inteligente de Serviços • Japão & Brasil</title>
  <meta name="description" content="Encontre profissionais qualificados e bilíngues no Japão ou anuncie seus serviços. Tradução simultânea por IA e pagamentos seguros com Stripe.">
  
  <!-- Favicon Oficial -->
  <link rel="icon" href="{{ asset('webapp/assets/images/favicon.png') }}" type="image/png">
  <link rel="apple-touch-icon" href="{{ asset('webapp/assets/images/maisuapp-icon.png') }}">

  <!-- Tailwind CSS v4 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  
  <!-- Fontes Oficiais: Plus Jakarta Sans + Montserrat + Noto Sans JP -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Noto+Sans+JP:wght@400;500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Ícones FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('webapp/style.css') }}">
</head>
<body class="min-h-screen antialiased selection:bg-purple-600 selection:text-white">

  <!-- Header & Top Navigation -->
  <header class="sticky top-0 z-40 glass-panel border-b px-4 lg:px-8 py-3.5 transition-all">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      
      <!-- Logo Oficial -->
      <div class="flex items-center space-x-3 cursor-pointer group" onclick="switchTab('client-search')">
        <img src="{{ asset('webapp/assets/images/maisuapp-icon.png') }}" class="h-10 w-10 object-contain drop-shadow transition-transform duration-200 group-hover:scale-105" alt="MaisuApp">
        <div class="flex flex-col">
          <div class="flex items-center space-x-2">
            <span class="maisu-brand-logo text-2xl font-black tracking-tight">
              <span class="brand-purple">Maisu</span><span class="brand-pink">App</span>
            </span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
              AI 3.0
            </span>
          </div>
          <span class="text-[10px] text-slate-400 font-medium hidden sm:inline" data-i18n="hero_badge">
            Marketplace Oficial • Japão & Brasil
          </span>
        </div>
      </div>

      <!-- Navigation Tabs (Desktop) -->
      <nav class="hidden md:flex items-center space-x-1.5 p-1 rounded-2xl bg-slate-900/40 border border-white/5">
        <button onclick="switchTab('client-search')" id="nav-btn-client-search" class="nav-tab-btn active flex items-center gap-2">
          <i class="fa-solid fa-compass text-xs text-purple-400"></i>
          <span data-i18n="nav_explore">Explorar Serviços</span>
        </button>
        <button onclick="switchTab('my-requests')" id="nav-btn-my-requests" class="nav-tab-btn flex items-center gap-2">
          <i class="fa-solid fa-clipboard-list text-xs text-emerald-400"></i>
          <span data-i18n="nav_my_requests">Minhas Solicitações</span>
        </button>
        <button onclick="switchTab('live-translator')" id="nav-btn-live-translator" class="nav-tab-btn flex items-center gap-2">
          <i class="fa-solid fa-language text-xs text-pink-400"></i>
          <span data-i18n="nav_messages">Mensagens & Tradução IA</span>
        </button>
        <button onclick="switchTab('pro-creator')" id="nav-btn-pro-creator" class="nav-tab-btn flex items-center gap-2">
          <i class="fa-solid fa-wand-magic-sparkles text-xs text-indigo-400"></i>
          <span data-i18n="nav_pro">Para Prestadores</span>
        </button>
      </nav>

      <!-- Right Controls: Idioma + Tema + CTA Anunciar -->
      <div class="flex items-center space-x-2.5">
        
        <!-- Seletor de Idioma -->
        <button onclick="openLanguageModal()" class="px-3 py-1.5 rounded-xl text-xs font-semibold glass-panel hover:border-purple-500/50 transition flex items-center gap-1.5 cursor-pointer">
          <i class="fa-solid fa-globe text-purple-400"></i>
          <span id="current-lang-label">Português 🇧🇷</span>
          <i class="fa-solid fa-chevron-down text-[10px] opacity-60"></i>
        </button>

        <!-- Seletor de Tema (Light / Dark / Auto) -->
        <div class="hidden sm:flex items-center p-1 rounded-xl glass-panel">
          <button onclick="setTheme('light')" id="theme-btn-light" title="Modo Claro" class="theme-switch-btn">
            <i class="fa-solid fa-sun text-amber-400"></i>
          </button>
          <button onclick="setTheme('dark')" id="theme-btn-dark" title="Modo Escuro" class="theme-switch-btn active">
            <i class="fa-solid fa-moon text-purple-400"></i>
          </button>
          <button onclick="setTheme('system')" id="theme-btn-system" title="Tema do Sistema" class="theme-switch-btn">
            <i class="fa-solid fa-desktop text-cyan-400"></i>
          </button>
        </div>

        <!-- Botão CTA Anunciar -->
        <button onclick="switchTab('pro-creator')" class="btn-pink py-2 px-3.5 text-xs flex items-center gap-1.5 shadow-sm">
          <i class="fa-solid fa-plus text-xs"></i>
          <span class="hidden sm:inline" data-i18n="nav_advertise_btn">Anunciar Serviço</span>
        </button>
      </div>

    </div>

    <!-- Mobile Nav Bar (Bottom bar / secondary) -->
    <div class="md:hidden flex items-center justify-around pt-2.5 mt-2 border-t border-slate-800/40 text-xs">
      <button onclick="switchTab('client-search')" id="mobile-btn-client-search" class="mobile-nav-btn py-1 px-2 text-purple-400 font-bold flex flex-col items-center">
        <i class="fa-solid fa-compass mb-1 text-sm"></i> <span class="text-[10px]" data-i18n="nav_explore">Explorar</span>
      </button>
      <button onclick="switchTab('my-requests')" id="mobile-btn-my-requests" class="mobile-nav-btn py-1 px-2 text-slate-400 flex flex-col items-center">
        <i class="fa-solid fa-clipboard-list mb-1 text-sm"></i> <span class="text-[10px]" data-i18n="nav_my_requests">Solicitações</span>
      </button>
      <button onclick="switchTab('live-translator')" id="mobile-btn-live-translator" class="mobile-nav-btn py-1 px-2 text-slate-400 flex flex-col items-center">
        <i class="fa-solid fa-language mb-1 text-sm"></i> <span class="text-[10px]" data-i18n="nav_messages">Mensagens</span>
      </button>
      <button onclick="switchTab('pro-creator')" id="mobile-btn-pro-creator" class="mobile-nav-btn py-1 px-2 text-slate-400 flex flex-col items-center">
        <i class="fa-solid fa-wand-magic-sparkles mb-1 text-sm"></i> <span class="text-[10px]" data-i18n="nav_pro">Prestadores</span>
      </button>
    </div>
  </header>

  <!-- Hero Section Limpa & Focada (Airbnb / TaskRabbit Style) -->
  <section class="relative pt-10 pb-8 px-4 sm:px-8 border-b border-slate-800/40">
    <div class="max-w-5xl mx-auto text-center space-y-4">
      
      <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/10 text-purple-300 border border-purple-500/20">
        <i class="fa-solid fa-shield-halved text-emerald-400"></i>
        <span data-i18n="hero_badge">Marketplace Oficial • Japão & Brasil</span>
      </div>

      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight" data-i18n="hero_title">
        Conectando Serviços com Confiança e IA
      </h1>

      <p class="text-sm sm:text-base text-slate-400 max-w-2xl mx-auto leading-relaxed" data-i18n="hero_subtitle">
        Encontre profissionais qualificados e bilíngues no Japão ou anuncie suas habilidades. Tradução simultânea em tempo real e pagamentos 100% protegidos com Stripe.
      </p>

      <!-- Barra de Pesquisa Inteligente Unificada -->
      <div class="pt-4 max-w-3xl mx-auto">
        <div class="glass-panel p-2 sm:p-2.5 rounded-2xl sm:rounded-full flex flex-col sm:flex-row items-center gap-2 shadow-lg">
          <div class="flex items-center w-full px-3.5 py-1.5">
            <i class="fa-solid fa-magnifying-glass text-slate-400 mr-3 text-sm"></i>
            <input type="text" id="client-search-input" class="w-full bg-transparent text-sm focus:outline-none placeholder:text-slate-400" placeholder="O que você precisa? (ex: Eletricista em Hamamatsu, Tradutor para Shiyakusho, Shaken...)" data-i18n-placeholder="search_placeholder" onkeydown="if(event.key === 'Enter') executeAiSearch()">
            <button onclick="toggleVoiceInput()" class="text-slate-400 hover:text-pink-400 px-2 transition" title="Pesquisa por voz">
              <i class="fa-solid fa-microphone"></i>
            </button>
          </div>
          <button onclick="executeAiSearch()" class="btn-primary w-full sm:w-auto px-6 py-3 rounded-xl sm:rounded-full text-xs font-bold whitespace-nowrap flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-sparkles"></i>
            <span data-i18n="search_btn">Buscar com IA</span>
          </button>
        </div>

        <!-- Indicador de busca IA -->
        <div id="ai-search-loading" class="hidden mt-3 p-2.5 rounded-xl glass-panel text-center text-xs text-purple-300">
          <i class="fa-solid fa-spinner fa-spin mr-2 text-cyan-400"></i>
          <span>Analisando solicitações, distâncias e profissionais certificados...</span>
        </div>

        <!-- Sugestões Rápidas de Busca -->
        <div class="flex items-center justify-center flex-wrap gap-2 pt-3 text-xs text-slate-400">
          <span class="text-[11px]" data-i18n="quick_tags_label">Sugestões rápidas:</span>
          <button onclick="fillSearchPrompt('Eletricista em Hamamatsu')" class="px-2.5 py-1 rounded-lg bg-slate-800/60 hover:bg-slate-700/60 text-slate-300 transition" data-i18n="tag_electrician">⚡ Eletricista em Hamamatsu</button>
          <button onclick="fillSearchPrompt('Tradutor Shiyakusho prefeitura Nagoya')" class="px-2.5 py-1 rounded-lg bg-slate-800/60 hover:bg-slate-700/60 text-slate-300 transition" data-i18n="tag_interpreter">🏛️ Tradutor Shiyakusho</button>
          <button onclick="fillSearchPrompt('Revisão mecânica e Shaken')" class="px-2.5 py-1 rounded-lg bg-slate-800/60 hover:bg-slate-700/60 text-slate-300 transition" data-i18n="tag_shaken">🚗 Mecânica & Shaken</button>
          <button onclick="fillSearchPrompt('Limpeza residencial de mudança Taikyo')" class="px-2.5 py-1 rounded-lg bg-slate-800/60 hover:bg-slate-700/60 text-slate-300 transition" data-i18n="tag_cleaning">🧹 Limpeza de Mudança</button>
        </div>
      </div>

    </div>
  </section>

  <!-- Filtro por Categorias (Chips) -->
  <section class="py-4 px-4 sm:px-8 border-b border-slate-800/40 bg-slate-900/20">
    <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 overflow-x-auto pb-1 scrollbar-none">
      <button onclick="selectCategory('all')" data-cat="all" class="cat-pill active">
        <i class="fa-solid fa-border-all text-xs"></i> <span data-i18n="cat_all">Todos os Serviços</span>
      </button>
      <button onclick="selectCategory('eletrica')" data-cat="eletrica" class="cat-pill">
        <i class="fa-solid fa-bolt text-xs text-amber-400"></i> <span data-i18n="cat_electrician">Eletricista & Ar</span>
      </button>
      <button onclick="selectCategory('traducao')" data-cat="traducao" class="cat-pill">
        <i class="fa-solid fa-language text-xs text-cyan-400"></i> <span data-i18n="cat_translation">Tradução & Vistos</span>
      </button>
      <button onclick="selectCategory('mecanica')" data-cat="mecanica" class="cat-pill">
        <i class="fa-solid fa-car text-xs text-rose-400"></i> <span data-i18n="cat_mechanic">Mecânica & Shaken</span>
      </button>
      <button onclick="selectCategory('limpeza')" data-cat="limpeza" class="cat-pill">
        <i class="fa-solid fa-broom text-xs text-emerald-400"></i> <span data-i18n="cat_cleaning">Limpeza & Mudanças</span>
      </button>
    </div>
  </section>

  <!-- CONTEÚDO PRINCIPAL (ABAS LIMPAS) -->
  <main class="max-w-7xl mx-auto px-4 sm:px-8 py-8 min-h-[60vh]">

    <!-- ABA 1: EXPLORAR SERVIÇOS & PROFISSIONAIS -->
    <section id="tab-client-search" class="tab-content active space-y-8 animate-fade-in">
      
      <div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="text-xl font-bold flex items-center gap-2" data-i18n="verified_pros_title">
              Profissionais Verificados no Japão
            </h2>
            <p class="text-xs text-slate-400 mt-0.5" data-i18n="verified_pros_subtitle">
              Classificados por avaliações, proximidade e histórico de atendimento comprovado.
            </p>
          </div>
          <span class="text-xs font-semibold text-emerald-400 px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20">
            <i class="fa-solid fa-circle-check text-[10px] mr-1"></i> 100% Verificados
          </span>
        </div>

        <!-- Grid de Profissionais -->
        <div id="inside-professionals-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
          <!-- Injetado dinamicamente via app.js -->
        </div>
      </div>

      <!-- Varredura Externa de Mercado -->
      <div class="pt-8 border-t border-slate-800/40">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-sm font-bold flex items-center gap-2 text-slate-300" data-i18n="external_benchmark_title">
              <i class="fa-solid fa-compass text-cyan-400"></i> Varredura Inteligente do Mercado
            </h3>
            <p class="text-xs text-slate-400" data-i18n="external_benchmark_subtitle">
              Monitoramento de prestadores externos e fornecedores regionais no Japão.
            </p>
          </div>
        </div>

        <div id="outside-benchmarks-container" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Injetado dinamicamente via app.js -->
        </div>
      </div>

    </section>

    <!-- ABA 2: PARA PRESTADORES (CRIADOR DE ANÚNCIOS COM IA) -->
    <section id="tab-pro-creator" class="tab-content space-y-6 animate-fade-in">
      
      <div class="border-b border-slate-800/40 pb-4">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-pink-500/10 text-pink-400 mb-2">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Assistente de Publicação
        </div>
        <h2 class="text-2xl font-bold" data-i18n="pro_creator_title">Criador de Anúncios Profissional com IA</h2>
        <p class="text-xs text-slate-400 mt-1 max-w-2xl" data-i18n="pro_creator_subtitle">
          Diga o que você faz em poucas palavras. A IA gera um anúncio bilíngue completo (Português e 日本語), com sugestão de preços e pronto para publicar.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Formulário do Prestador -->
        <div class="lg:col-span-5 glass-card p-6 space-y-4">
          <div>
            <label class="block text-xs font-semibold mb-1" data-i18n="pro_name_label">Seu Nome / Nome Comercial</label>
            <input type="text" id="pro-name-input" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" value="Marcos Silva (Elétrica & Climatização)">
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1" data-i18n="pro_category_label">Categoria do Serviço</label>
              <select id="pro-category-select" class="w-full clean-input px-3 py-2.5 rounded-xl text-xs">
                <option value="Eletricista">Eletricista & Manutenção</option>
                <option value="Tradução">Tradução & Assessoria</option>
                <option value="Mecânica">Mecânica & Shaken</option>
                <option value="Limpeza">Limpeza & Mudanças</option>
                <option value="Construção">Reformas & Pintura</option>
                <option value="Aulas">Aulas & Treinamentos</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1" data-i18n="pro_city_label">Região / Cidade</label>
              <input type="text" id="pro-city-input" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" value="Hamamatsu, Shizuoka">
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1" data-i18n="pro_exp_label">Tempo de Experiência</label>
              <input type="text" id="pro-exp-input" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" value="10 anos no Japão">
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1" data-i18n="pro_price_label">Preço Base Inicial (¥)</label>
              <input type="number" id="pro-price-input" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" value="6000">
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold mb-1" data-i18n="pro_desc_label">Conte com suas próprias palavras o que você faz:</label>
            <textarea id="pro-raw-desc" rows="4" class="w-full clean-input p-3 rounded-xl text-xs resize-none" placeholder="Ex: Faço troca de fiação residencial, instalação e limpeza de ar-condicionado, conserto de disjuntores em Hamamatsu e cidades vizinhas."></textarea>
          </div>

          <button onclick="generateAdWithAi()" id="btn-generate-ad" class="w-full btn-primary py-3 text-xs font-bold flex items-center justify-center gap-2 cursor-pointer">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span data-i18n="btn_generate_ad">Gerar Anúncio Profissional com IA</span>
          </button>
        </div>

        <!-- Preview do Anúncio Gerado com Abas PT/JA -->
        <div class="lg:col-span-7 glass-card p-6 flex flex-col justify-between space-y-6">
          <div>
            <!-- Header do Preview com Alternador PT / JA -->
            <div class="flex items-center justify-between border-b border-slate-800/40 pb-3 mb-4">
              <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>
                <span class="text-xs font-bold text-pink-400 uppercase tracking-wider" data-i18n="preview_box_title">Prévia do Anúncio Gerado por IA</span>
              </div>
              <div class="flex items-center space-x-1.5">
                <button onclick="switchAdPreviewLang('pt')" id="tab-btn-ad-pt" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-purple-600 text-white transition">Português 🇧🇷</button>
                <button onclick="switchAdPreviewLang('ja')" id="tab-btn-ad-ja" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-400 hover:text-white transition">日本語 🇯🇵</button>
                <button onclick="copyGeneratedAd()" class="ml-2 px-2.5 py-1 rounded-lg bg-slate-800 text-[11px] hover:bg-slate-700 transition" data-i18n="btn_copy_ad">Copiar</button>
              </div>
            </div>

            <!-- Preview Versão Português -->
            <div id="ad-preview-box-pt" class="space-y-4">
              <div>
                <h4 id="preview-title-pt" class="text-base font-bold text-white p-3 rounded-xl bg-slate-900/60 border border-slate-800/80">
                  Eletricista Residencial em Hamamatsu - Instalação de Ar-Condicionado & Atendimento Ágil
                </h4>
              </div>
              <div>
                <div id="preview-desc-pt" class="text-xs text-slate-300 p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 whitespace-pre-line leading-relaxed max-h-48 overflow-y-auto">
                  Olá! Sou especialista em elétrica e climatização com mais de 10 anos de experiência atuando na região de Hamamatsu e arredores.
                  
                  ✅ DIFERENCIAIS:
                  • Atendimento bilíngue (Português e 日本語) sem barreiras de comunicação.
                  • Ferramental de alta precisão e materiais em conformidade com normas JIS.
                  • Orçamento rápido e transparente, sem cobranças surpresas.
                  • Garantia estendida em todos os serviços realizados.
                </div>
              </div>
            </div>

            <!-- Preview Versão Japonês (Alternável) -->
            <div id="ad-preview-box-ja" class="space-y-4 hidden">
              <div>
                <h4 id="preview-title-ja" class="text-base font-bold text-white p-3 rounded-xl bg-slate-900/60 border border-pink-500/30">
                  浜松市のプロ電気工事士・エアコン設置・迅速対応・安心保証付きサービス
                </h4>
              </div>
              <div>
                <div id="preview-desc-ja" class="text-xs text-slate-300 p-4 rounded-xl bg-slate-900/60 border border-pink-500/30 whitespace-pre-line leading-relaxed max-h-48 overflow-y-auto">
                  浜松市および周辺地域にて10年以上の経験を持つ電気・空調設備のプロフェッショナルです。
                  
                  ✅ 特徴・強み：
                  • 日本語・ポルトガル語のバイリンガル対応で安心コミュニケーション。
                  • JIS規格に準拠した確かな技術と高品質な材料を使用。
                  • 明朗会計で事前の見積もりを徹底、追加料金なし。
                  • 全ての施工に安心のアフター保証付き。
                </div>
              </div>
            </div>

            <!-- Pacotes de Preço Sugeridos -->
            <div class="mt-4">
              <div class="text-[11px] text-slate-400 font-semibold mb-2">PACOTES DE PREÇO SUGERIDOS PELA IA:</div>
              <div id="preview-packages" class="grid grid-cols-3 gap-3">
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                  <div class="text-[11px] text-slate-400">Básico</div>
                  <div class="text-base font-bold mt-1">¥6.000</div>
                  <div class="text-[10px] text-slate-400">~ R$ 216</div>
                </div>
                <div class="p-3 rounded-xl bg-purple-900/30 border border-purple-500/40 text-center">
                  <div class="text-[11px] text-purple-300 font-bold">Padrão ⭐</div>
                  <div class="text-base font-bold mt-1">¥13.200</div>
                  <div class="text-[10px] text-slate-300">~ R$ 475</div>
                </div>
                <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-center">
                  <div class="text-[11px] text-slate-400">Premium</div>
                  <div class="text-base font-bold mt-1">¥27.000</div>
                  <div class="text-[10px] text-slate-400">~ R$ 972</div>
                </div>
              </div>
            </div>

          </div>

          <!-- Botão de Publicação -->
          <div class="pt-4 border-t border-slate-800/40 flex items-center justify-between">
            <span class="text-xs text-slate-400" data-i18n="publish_notice">O anúncio ficará visível imediatamente para clientes na sua região.</span>
            <button onclick="publishAdToMarketplace()" class="btn-pink py-2.5 px-5 text-xs font-bold flex items-center gap-2 cursor-pointer">
              <i class="fa-solid fa-cloud-arrow-up"></i>
              <span data-i18n="btn_publish_ad">Publicar no MaisuApp</span>
            </button>
          </div>

        </div>

      </div>

    </section>

    <!-- ABA 3: MENSAGENS COM TRADUÇÃO SIMULTÂNEA -->
    <section id="tab-live-translator" class="tab-content space-y-6 animate-fade-in">
      
      <div class="border-b border-slate-800/40 pb-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-400 mb-2">
            <i class="fa-solid fa-language"></i> Comunicação Sem Barreiras
          </div>
          <h2 class="text-2xl font-bold" data-i18n="chat_title">Mensagens com Tradução Bilíngue Simultânea</h2>
          <p class="text-xs text-slate-400 mt-1 max-w-2xl" data-i18n="chat_subtitle">
            Fale no seu próprio idioma. A IA traduz instantaneamente cada mensagem enviada, com tom cortês, leitura fonética (Romaji) e áudio.
          </p>
        </div>
        <div class="flex items-center space-x-2 text-xs">
          <button onclick="toggleAudioOutput()" id="btn-audio-toggle" class="btn-outline px-3 py-1.5 flex items-center gap-1.5 text-xs">
            <i class="fa-solid fa-volume-high text-cyan-400"></i>
            <span data-i18n="chat_audio_active">Voz Ativa</span>
          </button>
        </div>
      </div>

      <!-- Interface de Mensagens -->
      <div class="glass-card overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[500px]">
        
        <!-- Sidebar de Conversas -->
        <div class="lg:col-span-4 border-r border-slate-800/40 p-4 space-y-3">
          <div class="flex items-center justify-between text-xs text-slate-400 font-bold uppercase tracking-wider mb-2">
            <span data-i18n="chat_contact_title">Conversas Ativas</span>
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
          </div>

          <div class="p-3 rounded-xl bg-purple-950/30 border border-purple-500/30 flex items-center space-x-3 cursor-pointer">
            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80" class="w-10 h-10 rounded-xl object-cover" alt="Carlos">
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold truncate" id="chat-active-pro-name">Carlos Kenji Tanaka</h4>
                <span class="text-[10px] text-emerald-400">Online</span>
              </div>
              <p class="text-[11px] text-slate-400 truncate">エアコンの設置ですね。土曜日に伺えます...</p>
            </div>
          </div>

          <!-- Card de Pagamento Protegido Rápido -->
          <div class="mt-6 p-4 rounded-xl bg-slate-900/60 border border-emerald-500/30 space-y-2">
            <div class="flex items-center justify-between text-xs font-bold">
              <span class="text-slate-300">Orçamento Fechado:</span>
              <span class="text-emerald-400 text-sm">¥12.000</span>
            </div>
            <p class="text-[11px] text-slate-400" data-i18n="chat_escrow_notice">Orçamento acordado com proteção de caução Stripe (Escrow).</p>
            <button onclick="openStripeCheckoutModal('Carlos Kenji Tanaka', 12000, 'Instalação de Ar-Condicionado')" class="w-full btn-primary py-2 text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer">
              <i class="fa-brands fa-stripe"></i> <span data-i18n="btn_pay_chat">Pagar via Stripe Escrow</span>
            </button>
          </div>
        </div>

        <!-- Área de Conversação -->
        <div class="lg:col-span-8 flex flex-col justify-between p-4 sm:p-6">
          
          <!-- Histórico de Mensagens -->
          <div id="chat-messages-container" class="space-y-4 overflow-y-auto max-h-[380px] pr-2">
            
            <!-- Mensagem 1: Cliente -->
            <div class="flex flex-col items-end space-y-1 mb-4">
              <div class="chat-bubble-user p-3.5 max-w-md text-xs shadow-sm">
                <p>Olá Carlos! Qual o orçamento para a troca do ar-condicionado em Hamamatsu?</p>
              </div>
              <div class="chat-translation-tag p-2 rounded-lg max-w-md text-[11px]">
                <div class="flex items-center justify-between text-pink-400 font-semibold mb-0.5">
                  <span><i class="fa-solid fa-sparkles text-[9px]"></i> 日本語:</span>
                  <button onclick="speakText('エアコンの交換・設置の見積もりはいくらになりますか？')" class="text-pink-400 hover:underline text-[10px]">
                    <i class="fa-solid fa-volume-high"></i>
                  </button>
                </div>
                <p class="text-slate-200">エアコンの交換・設置の見積もりはいくらになりますか？</p>
                <span class="text-[10px] text-slate-400 italic block mt-0.5">(Eakon no koukan/setchi no mitsumori wa ikura ni narimasu ka?)</span>
              </div>
            </div>

            <!-- Mensagem 2: Prestador -->
            <div class="flex flex-col items-start space-y-1 mb-4">
              <div class="chat-bubble-other p-3.5 max-w-md text-xs shadow-sm">
                <div class="text-[10px] text-purple-400 font-bold mb-1">Carlos (日本語 - Original):</div>
                <p>こんにちは！エアコンの設置ですね。標準工事であれば12,000円になります。今週の土曜日に伺えますよ。</p>
              </div>
              <div class="chat-translation-tag p-2 rounded-lg max-w-md text-[11px]">
                <div class="flex items-center justify-between text-emerald-400 font-bold mb-0.5">
                  <span><i class="fa-solid fa-sparkles text-[9px]"></i> Tradução para você (Português):</span>
                  <button onclick="speakText('こんにちは！エアコンの設置ですね。標準工事であれば12,000円になります。')" class="text-pink-400 hover:underline text-[10px]">
                    <i class="fa-solid fa-volume-high"></i> Ouvir
                  </button>
                </div>
                <p class="text-slate-200">Olá! Instalação de ar-condicionado, certo? Para a instalação padrão fica em ¥12.000. Posso ir neste sábado.</p>
              </div>
            </div>

          </div>

          <!-- Barra de Envio -->
          <div class="pt-4 border-t border-slate-800/40 space-y-2">
            <div class="flex items-center space-x-2">
              <input type="text" id="chat-input-text" class="flex-1 clean-input px-4 py-2.5 rounded-xl text-xs" placeholder="Digite em português... (ex: 'Perfeito, pode agendar para sábado')" data-i18n-placeholder="chat_placeholder" onkeydown="if(event.key === 'Enter') sendChatMessage()">
              <button onclick="sendChatMessage()" class="btn-pink px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 cursor-pointer">
                <i class="fa-solid fa-paper-plane"></i> <span class="hidden sm:inline" data-i18n="btn_send_chat">Traduzir & Enviar</span>
              </button>
            </div>
            <div class="flex items-center justify-end text-[11px]">
              <button onclick="simulateProReply()" class="text-pink-400 hover:underline" data-i18n="btn_simulate_reply">Simular Resposta do Profissional em Japonês</button>
            </div>
          </div>

        </div>

      </div>

    </section>

    <!-- ABA 4: HISTÓRICO DE SOLICITAÇÕES DO CLIENTE -->
    <section id="tab-my-requests" class="tab-content space-y-6 animate-fade-in">
      <div class="border-b border-slate-800/40 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 mb-2">
            <i class="fa-solid fa-shield-halved"></i> Registros de Solicitações
          </div>
          <h2 class="text-2xl font-bold" data-i18n="my_requests_title">Histórico de Solicitações de Serviço</h2>
          <p class="text-xs text-slate-400 mt-1" data-i18n="my_requests_subtitle">
            Acompanhe suas solicitações enviadas a profissionais e orçamentos em andamento com protocolo único MaisuApp.
          </p>
        </div>
        <button onclick="loadServiceRequestsHistory()" class="btn-outline px-3.5 py-2 text-xs flex items-center gap-1.5 cursor-pointer self-start sm:self-auto">
          <i class="fa-solid fa-rotate"></i> Atualizar
        </button>
      </div>

      <div id="my-requests-list-container" class="space-y-4">
        <!-- Injetado dinamicamente via app.js -->
      </div>
    </section>

  </main>

  <!-- MODAL: ENVIAR SOLICITAÇÃO DE SERVIÇO AO PROFISSIONAL -->
  <div id="request-modal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card max-w-xl w-full p-6 sm:p-7 space-y-5 max-h-[90vh] overflow-y-auto animate-fade-in">
      
      <div class="flex items-start justify-between border-b border-slate-800/40 pb-3">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center text-lg">
            <i class="fa-solid fa-paper-plane"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold" data-i18n="req_modal_title">Solicitar Serviço via MaisuApp</h3>
            <p class="text-[11px] text-slate-400" data-i18n="req_modal_subtitle">Sua solicitação é traduzida e entregue diretamente ao profissional com protocolo rastreável.</p>
          </div>
        </div>
        <button onclick="closeServiceRequestModal()" class="text-slate-400 hover:text-white cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
      </div>

      <!-- Destinatário Selecionado -->
      <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800/80 flex items-center justify-between text-xs">
        <div>
          <span class="text-slate-400 text-[11px] block">Profissional Selecionado:</span>
          <strong id="req-modal-pro-name" class="text-white text-sm">Carlos Kenji Tanaka</strong>
          <span id="req-modal-pro-cat" class="text-purple-300 text-[11px] block">Eletricista • Hamamatsu</span>
        </div>
        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
          <i class="fa-solid fa-shield-check mr-1"></i> Verificado
        </span>
      </div>

      <!-- Formulário de Solicitação -->
      <div class="space-y-3.5 text-xs">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1" data-i18n="req_client_name_label">Seu Nome Completo *</label>
            <input type="text" id="req-client-name" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" placeholder="Ex: Roberto Silva" value="Roberto Silva">
          </div>
          <div>
            <label class="block font-semibold mb-1" data-i18n="req_client_contact_label">Seu Telefone / WhatsApp / LINE *</label>
            <input type="text" id="req-client-contact" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" placeholder="Ex: 090-1234-5678" value="090-8877-6655">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="sm:col-span-2">
            <label class="block font-semibold mb-1" data-i18n="req_service_title_label">Título do Serviço Solicitado</label>
            <input type="text" id="req-service-title" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" placeholder="Ex: Troca de disjuntor e instalação de ar">
          </div>
          <div>
            <label class="block font-semibold mb-1" data-i18n="req_client_city_label">Sua Cidade / Província</label>
            <input type="text" id="req-client-city" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" value="Hamamatsu, Shizuoka">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1" data-i18n="req_preferred_date_label">Data / Período Preferencial</label>
            <input type="text" id="req-preferred-date" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" placeholder="Ex: Próximo sábado de manhã" value="Próximo sábado pela manhã">
          </div>
          <div>
            <label class="block font-semibold mb-1" data-i18n="req_budget_estimate_label">Orçamento Estimado (¥)</label>
            <input type="text" id="req-budget-estimate" class="w-full clean-input px-3.5 py-2.5 rounded-xl text-xs" value="¥12.000">
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block font-semibold" data-i18n="req_service_details_label">Descreva o que precisa com detalhes: *</label>
            <button type="button" onclick="aiEnhanceServiceRequest()" id="btn-ai-enhance-req" class="text-[11px] font-bold text-pink-400 hover:text-pink-300 flex items-center gap-1 cursor-pointer">
              <i class="fa-solid fa-wand-magic-sparkles"></i> <span data-i18n="btn_enhance_ai">Aprimorar & Traduzir p/ Japonês</span>
            </button>
          </div>
          <textarea id="req-service-details" rows="3" class="w-full clean-input p-3 rounded-xl text-xs resize-none" placeholder="Ex: Preciso de orçamento para instalação de um ar condicionado de 12.000 BTUs no segundo andar da minha casa."></textarea>
        </div>

        <!-- Prévia da Tradução em Japonês (Keigo) -->
        <div id="req-preview-ja" class="hidden p-3.5 rounded-xl bg-purple-950/40 border border-purple-500/40 space-y-1.5 animate-fade-in">
          <div class="flex items-center justify-between text-[11px] text-pink-400 font-bold">
            <span class="flex items-center gap-1"><i class="fa-solid fa-language"></i> Tradução Oficial Gerada para o Profissional (Keigo):</span>
            <span class="text-[10px] text-slate-400">Pronto para envio</span>
          </div>
          <p id="req-preview-ja-text" class="text-xs text-slate-200 font-sans leading-relaxed"></p>
        </div>
      </div>

      <!-- Ações do Modal -->
      <div class="pt-3 border-t border-slate-800/40 flex items-center justify-between gap-3">
        <button onclick="closeServiceRequestModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold glass-panel hover:bg-slate-800 text-slate-300 cursor-pointer">
          Cancelar
        </button>
        <button onclick="submitServiceRequest()" id="btn-submit-req-action" class="btn-primary py-2.5 px-6 rounded-xl text-xs font-bold flex items-center gap-2 cursor-pointer shadow-md">
          <i class="fa-solid fa-paper-plane"></i>
          <span data-i18n="btn_submit_request">Enviar Solicitação Formal</span>
        </button>
      </div>

    </div>
  </div>

  <!-- MODAL 1: CHECKOUT SEGURO STRIPE (ESCROW) -->
  <div id="stripe-modal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card max-w-lg w-full p-6 sm:p-7 space-y-5 animate-fade-in">
      
      <div class="flex items-start justify-between border-b border-slate-800/40 pb-3">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
            <i class="fa-solid fa-shield-check"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold" data-i18n="stripe_modal_title">Checkout Seguro MaisuApp com Stripe</h3>
            <p class="text-[11px] text-slate-400" data-i18n="stripe_modal_subtitle">Criptografia ponta a ponta e garantia de retenção em caução.</p>
          </div>
        </div>
        <button onclick="closeStripeCheckoutModal()" class="text-slate-400 hover:text-white cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
      </div>

      <!-- Resumo do Pedido -->
      <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800/80 space-y-1.5 text-xs">
        <div class="flex justify-between">
          <span class="text-slate-400" data-i18n="stripe_service_label">Serviço:</span>
          <strong id="stripe-modal-service" class="text-white">Instalação de Ar-Condicionado</strong>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400" data-i18n="stripe_pro_label">Profissional:</span>
          <span id="stripe-modal-pro" class="text-purple-300 font-medium">Carlos Kenji Tanaka</span>
        </div>
        <div class="flex justify-between pt-2 border-t border-slate-800 text-sm">
          <span class="font-bold" data-i18n="stripe_total_label">Valor Total a Pagar:</span>
          <strong id="stripe-modal-amount" class="text-emerald-400 font-extrabold">¥12.000</strong>
        </div>
      </div>

      <!-- Seletor Rápido de Cartões de Teste Oficiais do Stripe -->
      <div>
        <label class="block text-[11px] font-semibold text-slate-400 mb-1.5" data-i18n="stripe_test_cards_label">Cartões de Teste Oficiais do Stripe:</label>
        <div class="grid grid-cols-3 gap-2 text-xs">
          <button onclick="fillStripeTestCard('success')" class="p-2 rounded-xl bg-slate-800/60 hover:bg-slate-700/60 border border-emerald-500/30 text-left transition cursor-pointer">
            <div class="font-bold text-emerald-400 text-[11px]" data-i18n="card_success_label">Aprovado Imediato</div>
            <div class="text-[9px] text-slate-400 font-mono mt-0.5">4242 •••• 4242</div>
          </button>
          <button onclick="fillStripeTestCard('3ds')" class="p-2 rounded-xl bg-slate-800/60 hover:bg-slate-700/60 border border-cyan-500/30 text-left transition cursor-pointer">
            <div class="font-bold text-cyan-400 text-[11px]" data-i18n="card_3ds_label">3D Secure</div>
            <div class="text-[9px] text-slate-400 font-mono mt-0.5">4000 •••• 0027</div>
          </button>
          <button onclick="fillStripeTestCard('declined')" class="p-2 rounded-xl bg-slate-800/60 hover:bg-slate-700/60 border border-rose-500/30 text-left transition cursor-pointer">
            <div class="font-bold text-rose-400 text-[11px]" data-i18n="card_declined_label">Recusado (Saldo)</div>
            <div class="text-[9px] text-slate-400 font-mono mt-0.5">4000 •••• 0002</div>
          </button>
        </div>
      </div>

      <!-- Campos do Cartão -->
      <div class="space-y-3 text-xs">
        <div>
          <label class="block font-semibold mb-1" data-i18n="card_number_label">Número do Cartão</label>
          <input type="text" id="stripe-card-number" class="w-full clean-input px-3 py-2 rounded-xl font-mono text-xs" value="4242 4242 4242 4242">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1" data-i18n="card_exp_label">Validade (MM/AA)</label>
            <input type="text" id="stripe-card-exp" class="w-full clean-input px-3 py-2 rounded-xl font-mono text-xs" value="12/28">
          </div>
          <div>
            <label class="block font-semibold mb-1" data-i18n="card_cvc_label">CVC</label>
            <input type="text" id="stripe-card-cvc" class="w-full clean-input px-3 py-2 rounded-xl font-mono text-xs" value="123">
          </div>
        </div>
      </div>

      <!-- Feedback de Processamento -->
      <div id="stripe-receipt-details" class="hidden">
        <div id="stripe-receipt-msg" class="p-3 rounded-xl text-xs font-semibold"></div>
      </div>

      <!-- Botão de Confirmação -->
      <button onclick="processStripePayment()" id="btn-submit-stripe" class="w-full btn-primary py-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-md">
        <i class="fa-solid fa-lock"></i>
        <span data-i18n="btn_confirm_pay">Confirmar Pagamento com Stripe Escrow</span>
      </button>

    </div>
  </div>

  <!-- MODAL 2: SELEÇÃO DE IDIOMA DO APP -->
  <div id="lang-modal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card max-w-sm w-full p-6 space-y-4 animate-fade-in">
      <div class="flex items-center justify-between border-b border-slate-800/40 pb-3">
        <h3 class="text-sm font-bold flex items-center gap-2">
          <i class="fa-solid fa-globe text-purple-400"></i> Selecione seu Idioma
        </h3>
        <button onclick="closeLanguageModal()" class="text-slate-400 hover:text-white cursor-pointer"><i class="fa-solid fa-xmark"></i></button>
      </div>

      <div class="space-y-2 text-xs">
        <button onclick="setAppLanguage('pt', 'Português 🇧🇷')" class="w-full p-3 rounded-xl glass-panel hover:border-purple-500 text-left flex items-center justify-between cursor-pointer">
          <span class="font-semibold">Português (Brasil & Comunidade)</span>
          <span class="text-base">🇧🇷</span>
        </button>
        <button onclick="setAppLanguage('ja', '日本語 🇯🇵')" class="w-full p-3 rounded-xl glass-panel hover:border-purple-500 text-left flex items-center justify-between cursor-pointer">
          <span class="font-semibold">日本語 (Japanese)</span>
          <span class="text-base">🇯🇵</span>
        </button>
        <button onclick="setAppLanguage('en', 'English 🇺🇸')" class="w-full p-3 rounded-xl glass-panel hover:border-purple-500 text-left flex items-center justify-between cursor-pointer">
          <span class="font-semibold">English (International)</span>
          <span class="text-base">🇺🇸</span>
        </button>
      </div>
    </div>
  </div>

  <!-- MODAL 3: GUIA & DIAGNÓSTICO DO DOMÍNIO MAISUAPP.COM -->
  <div id="dns-modal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="glass-card max-w-2xl w-full p-6 sm:p-7 space-y-4 max-h-[90vh] overflow-y-auto animate-fade-in">
      
      <div class="flex items-start justify-between border-b border-slate-800/40 pb-3">
        <div>
          <h3 class="text-base font-bold flex items-center gap-2" data-i18n="dns_modal_title">
            <i class="fa-solid fa-globe text-cyan-400"></i> Configuração do Domínio www.maisuapp.com
          </h3>
          <p class="text-xs text-slate-400" data-i18n="dns_modal_subtitle">
            Instruções exatas para conectar o domínio gerenciado no Wix ao servidor do MaisuApp.
          </p>
        </div>
        <button onclick="closeDnsModal()" class="text-slate-400 hover:text-white cursor-pointer"><i class="fa-solid fa-xmark text-lg"></i></button>
      </div>

      <!-- Alerta de Diagnóstico -->
      <div class="p-4 rounded-xl bg-cyan-950/30 border border-cyan-500/30 text-xs space-y-1.5">
        <div class="font-bold text-cyan-300 flex items-center gap-1.5" data-i18n="dns_alert_title">
          <i class="fa-solid fa-circle-info"></i> Atenção sobre o DNS no Wix & Render:
        </div>
        <p class="text-slate-300 leading-relaxed" data-i18n="dns_alert_body">
          1. No Render: Adicione o domínio www.maisuapp.com em 'Settings > Custom Domains'.<br>
          2. No Wix: Mantenha o CNAME www apontando para maisuapp.onrender.com e remova os registros A antigos do Wix no domínio raiz.
        </p>
      </div>

      <!-- Tabela DNS -->
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left glass-panel rounded-xl overflow-hidden">
          <thead class="bg-slate-900/60 font-bold border-b border-slate-800">
            <tr>
              <th class="p-3">Tipo</th>
              <th class="p-3">Host</th>
              <th class="p-3">Aponta para (Valor)</th>
              <th class="p-3 text-right">Ação</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <tr>
              <td class="p-3 font-bold text-pink-400">CNAME</td>
              <td class="p-3 font-mono">www</td>
              <td class="p-3 font-mono font-bold text-cyan-300">maisuapp.onrender.com</td>
              <td class="p-3 text-right">
                <button onclick="copyToClipboard('maisuapp.onrender.com', 'CNAME copiado!')" class="px-2 py-1 rounded bg-slate-800 text-[10px] hover:bg-slate-700">Copiar</button>
              </td>
            </tr>
            <tr>
              <td class="p-3 font-bold text-purple-400">A</td>
              <td class="p-3 font-mono">@</td>
              <td class="p-3 font-mono font-bold text-emerald-400">216.24.57.1</td>
              <td class="p-3 text-right">
                <button onclick="copyToClipboard('216.24.57.1', 'IP copiado!')" class="px-2 py-1 rounded bg-slate-800 text-[10px] hover:bg-slate-700">Copiar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pt-2 flex justify-end">
        <button onclick="checkDnsStatus()" class="btn-primary py-2 px-4 text-xs font-bold flex items-center gap-2 cursor-pointer" data-i18n="dns_check_btn">
          <i class="fa-solid fa-rotate"></i> Verificar Propagação DNS
        </button>
      </div>

    </div>
  </div>

  <!-- Footer Limpo & Discreto -->
  <footer class="mt-12 border-t border-slate-800/40 py-8 px-4 text-xs text-slate-400">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="flex items-center space-x-2">
        <img src="{{ asset('webapp/assets/images/maisuapp-icon.png') }}" class="h-5 w-5 object-contain opacity-80" alt="MaisuApp">
        <span data-i18n="footer_rights">© 2026 MaisuApp. Todos os direitos reservados. Conectando pessoas e serviços.</span>
      </div>
      <div class="flex items-center space-x-4">
        <button onclick="openDnsModal()" class="hover:text-cyan-400 transition cursor-pointer" data-i18n="footer_dns_link">
          Configuração do Domínio (maisuapp.com)
        </button>
        <span>•</span>
        <button onclick="openStripeCheckoutModal('Carlos Kenji Tanaka', 12000, 'Serviço de Teste')" class="hover:text-emerald-400 transition cursor-pointer" data-i18n="footer_stripe_link">
          Testar Simulador Stripe
        </button>
      </div>
    </div>
  </footer>

  <!-- Toast Notification -->
  <div id="toast" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 glass-card px-4 py-3 border flex items-center gap-3 text-xs max-w-md shadow-2xl">
    <i id="toast-icon" class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
    <div id="toast-msg">Notificação</div>
  </div>

  <!-- Scripts -->
  <script src="{{ asset('webapp/app.js') }}"></script>
</body>
</html>
