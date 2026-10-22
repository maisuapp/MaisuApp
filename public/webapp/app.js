// MaisuApp Modern WebApp - Core Engine, Real Japan Providers & Request Dispatcher

let allProfessionals = [];
let myServiceRequests = [];
let currentCategory = 'all';
let currentLang = 'pt';
let voiceAudioEnabled = true;
let currentThemeMode = 'dark'; // 'light', 'dark', 'system'
let activeRequestPro = null;

// Dicionário Completo de Tradução Multilíngue (i18n)
const i18n = {
  pt: {
    lang_code: "pt",
    lang_label: "Português 🇧🇷",
    nav_explore: "Explorar Serviços",
    nav_messages: "Mensagens & Tradução IA",
    nav_pro: "Para Prestadores",
    nav_requests: "Minhas Solicitações",
    nav_advertise_btn: "Anunciar Serviço",
    hero_badge: "Marketplace Oficial • Japão & Brasil",
    hero_title: "Conectando Serviços com Confiança e IA no Japão",
    hero_subtitle: "Encontre profissionais qualificados e bilíngues em todo o Japão ou envie sua solicitação direta de serviço com tradução simultânea e caução segura Stripe.",
    search_placeholder: "O que você precisa? (ex: Eletricista em Hamamatsu, Tradutor Shiyakusho, Shaken em Gunma...)",
    search_btn: "Buscar com IA",
    quick_tags_label: "Sugestões rápidas:",
    tag_electrician: "⚡ Eletricista em Hamamatsu",
    tag_interpreter: "🏛️ Tradutor Shiyakusho Nagoya",
    tag_shaken: "🚗 Mecânica & Shaken Gunma",
    tag_cleaning: "🧹 Limpeza de Mudança Taikyo",
    cat_all: "Todos os Serviços",
    cat_electrician: "Eletricista & Ar",
    cat_translation: "Tradução & Vistos",
    cat_mechanic: "Mecânica & Shaken",
    cat_cleaning: "Limpeza & Mudanças",
    cat_renovation: "Reformas",
    cat_it: "TI & Aulas",
    verified_pros_title: "Profissionais Verificados em Todo o Japão",
    verified_pros_subtitle: "Classificados por avaliações, histórico no banco de dados e atendimento bilíngue.",
    btn_request_service: "Solicitar Serviço / Orçamento",
    btn_chat_translator: "Falar (Tradutor IA)",
    btn_hire: "Contratar com Stripe",
    reviews_count: "avaliações",
    price_from: "A partir de",
    languages_label: "Idiomas:",
    coverage_label: "Atende em:",
    license_label: "Qualificação:",
    request_modal_title: "Enviar Solicitação de Serviço via MaisuApp",
    request_modal_subtitle: "Sua solicitação será registrada com protocolo único no banco de dados e entregue diretamente ao profissional, com tradução bilíngue.",
    request_pro_label: "Profissional Selecionado:",
    request_name_label: "Seu Nome Completo",
    request_contact_label: "Seu Telefone / WhatsApp / LINE",
    request_city_label: "Sua Cidade / Região no Japão",
    request_date_label: "Data e Horário Preferencial",
    request_budget_label: "Orçamento Estimado (Ienes)",
    request_details_label: "Descreva o que você precisa em detalhes:",
    btn_ai_enhance: "✨ Aprimorar e Traduzir com IA",
    btn_submit_request: "Enviar Solicitação com Protocolo",
    my_requests_title: "Histórico de Solicitações de Serviço",
    my_requests_subtitle: "Acompanhe todos os pedidos enviados via MaisuApp aos profissionais.",
    no_requests_msg: "Você ainda não enviou solicitações. Busque um profissional e clique em 'Solicitar Serviço'!",
    protocol_label: "Protocolo:",
    status_waiting: "Aguardando Resposta do Prestador",
    pro_creator_title: "Criador de Anúncios Profissional com IA",
    pro_creator_subtitle: "Diga o que você faz em poucas palavras. A IA gera um anúncio bilíngue completo (Português e 日本語), com sugestão de preços e pronto para publicar.",
    pro_name_label: "Seu Nome / Nome Comercial",
    pro_category_label: "Categoria do Serviço",
    pro_city_label: "Região / Cidade",
    pro_exp_label: "Tempo de Experiência",
    pro_price_label: "Preço Base Inicial (¥)",
    pro_desc_label: "Conte com suas próprias palavras o que você faz:",
    btn_generate_ad: "Gerar Anúncio Profissional com IA",
    preview_box_title: "Prévia do Anúncio Gerado por IA",
    btn_copy_ad: "Copiar Texto",
    ad_ready_badge: "Pronto para Publicar",
    btn_publish_ad: "Publicar no MaisuApp",
    publish_notice: "O anúncio ficará salvo no banco de dados e visível imediatamente para clientes.",
    chat_title: "Mensagens com Tradução Bilíngue Simultânea",
    chat_subtitle: "Fale no seu próprio idioma. A IA traduz instantaneamente cada mensagem enviada, com tom cortês, leitura fonética (Romaji) e áudio.",
    chat_contact_title: "Conversas Ativas",
    chat_escrow_notice: "Orçamento acordado com proteção de caução Stripe (Escrow).",
    btn_pay_chat: "Pagar via Stripe Escrow",
    chat_audio_active: "Voz Ativa",
    chat_audio_muted: "Voz Mudo",
    chat_placeholder: "Digite em português... (ex: 'Qual o valor para instalar o ar-condicionado?')",
    btn_send_chat: "Traduzir & Enviar",
    btn_simulate_reply: "Simular Resposta do Profissional em Japonês",
    stripe_modal_title: "Checkout Seguro MaisuApp com Stripe",
    stripe_modal_subtitle: "Criptografia ponta a ponta e garantia de retenção em caução até a conclusão do serviço.",
    stripe_service_label: "Serviço:",
    stripe_pro_label: "Profissional:",
    stripe_total_label: "Valor Total a Pagar:",
    stripe_test_cards_label: "Cartões de Teste Oficiais do Stripe:",
    card_success_label: "Aprovado Imediato",
    card_3ds_label: "3D Secure (3DS)",
    card_declined_label: "Recusado (Saldo)",
    card_number_label: "Número do Cartão",
    card_exp_label: "Validade (MM/AA)",
    card_cvc_label: "CVC",
    card_name_label: "Nome no Cartão",
    btn_confirm_pay: "Confirmar Pagamento com Stripe Escrow",
    dns_modal_title: "Configuração do Domínio www.maisuapp.com",
    dns_modal_subtitle: "Instruções exatas para conectar o domínio gerenciado no Wix ao servidor do MaisuApp.",
    dns_check_btn: "Verificar Propagação DNS",
    dns_alert_title: "Atenção sobre o DNS no Wix & Render:",
    dns_alert_body: "1. No Render: Adicione o domínio www.maisuapp.com em 'Settings > Custom Domains'.\n2. No Wix: Mantenha o CNAME www apontando para maisuapp.onrender.com e remova os registros A antigos do Wix no domínio raiz.",
    footer_dns_link: "Configuração do Domínio (maisuapp.com)",
    footer_stripe_link: "Testar Simulador Stripe",
    theme_status_dark: "Modo Escuro ativo",
    theme_status_light: "Modo Claro ativo",
    theme_status_system: "Tema automático do Sistema",
    toast_lang_changed: "Idioma alterado para Português 🇧🇷",
    toast_ad_generated: "Anúncio inteligente gerado com sucesso!",
    toast_ad_published: "Anúncio publicado com sucesso no marketplace!",
    toast_search_done: "Busca com IA concluída com sucesso!",
    toast_request_sent: "Solicitação enviada com sucesso ao profissional! Protocolo gerado.",
    toast_payment_success: "Pagamento aprovado com sucesso via Stripe e seguro em caução!",
    toast_payment_declined: "Pagamento recusado pelo banco emissor (Saldo insuficiente)."
  },
  ja: {
    lang_code: "ja",
    lang_label: "日本語 🇯🇵",
    nav_explore: "サービスを探す",
    nav_messages: "メッセージ・AI翻訳",
    nav_pro: "プロ向け・出品",
    nav_requests: "依頼履歴",
    nav_advertise_btn: "サービスを出品",
    hero_badge: "公式バイリンガルマーケットプレイス • 日本＆ブラジル",
    hero_title: "信頼とAIでつながる、日本全国のスマートサービス",
    hero_subtitle: "日本全国の信頼できるバイリンガル専門家を検索・直接依頼。リアルタイムAI自動翻訳とStripe安心エスクロー決済で、言葉や支払いの不安を解消。",
    search_placeholder: "ご希望のサービスを入力（例：浜松のエアコン工事、市役所の通訳、群馬の車検...）",
    search_btn: "AIで検索",
    quick_tags_label: "人気の検索:",
    tag_electrician: "⚡ 浜松の電気工事・エアコン",
    tag_interpreter: "🏛️ 名古屋市役所・ビザ通訳",
    tag_shaken: "🚗 群馬の自動車整備・車検",
    tag_cleaning: "🧹 退去ハウスクリーニング",
    cat_all: "すべてのサービス",
    cat_electrician: "電気・エアコン",
    cat_translation: "通訳・行政手続き",
    cat_mechanic: "整備・車検",
    cat_cleaning: "清掃・引越し",
    cat_renovation: "リフォーム・内装",
    cat_it: "IT・各種レッスン",
    verified_pros_title: "日本全国の認定プロフェッショナル",
    verified_pros_subtitle: "高評価レビュー・データベース登録実績・確かなバイリンガル対応順で掲載。",
    btn_request_service: "サービス依頼・見積もり",
    btn_chat_translator: "メッセージ（AI自動翻訳）",
    btn_hire: "Stripeで安心決済",
    reviews_count: "件のレビュー",
    price_from: "目安料金：",
    languages_label: "対応言語:",
    coverage_label: "対応エリア:",
    license_label: "保有資格:",
    request_modal_title: "MaisuApp経由でサービス依頼・見積もりを送信",
    request_modal_subtitle: "ご依頼内容は一意の受付番号（プロトコル）でデータベースに記録され、プロフェッショナルへ直接届きます。",
    request_pro_label: "対象プロフェッショナル：",
    request_name_label: "お名前（氏名）",
    request_contact_label: "ご連絡先 (電話番号/LINE/WhatsApp)",
    request_city_label: "ご住所・対応地域（市区町村）",
    request_date_label: "希望訪問日時・タイミング",
    request_budget_label: "ご予算の目安 (円)",
    request_details_label: "ご依頼・ご相談内容の詳細：",
    btn_ai_enhance: "✨ AIで依頼文を推敲・日本語翻訳",
    btn_submit_request: "受付番号付きで依頼を送信する",
    my_requests_title: "サービス依頼・見積もり履歴",
    my_requests_subtitle: "MaisuApp経由でプロフェッショナルに送信されたご依頼の進行状況一覧。",
    no_requests_msg: "現在送信済みの依頼はありません。専門家を検索して「サービス依頼」を行ってください。",
    protocol_label: "受付番号：",
    status_waiting: "プロフェッショナルの確認待ち",
    pro_creator_title: "AIアシスト・プロ向けサービス出品ツール",
    pro_creator_subtitle: "簡単な内容を入力するだけで、AIが日本語とポルトガル語のバイリンガル広告、推奨価格、検索タグを自動生成します。",
    pro_name_label: "氏名または屋号・屋号名",
    pro_category_label: "サービスカテゴリー",
    pro_city_label: "対応エリア・都市",
    pro_exp_label: "経験年数",
    pro_price_label: "基本料金 (¥)",
    pro_desc_label: "サービス内容を自由に入力してください：",
    btn_generate_ad: "AIでプロ広告を自動生成",
    preview_box_title: "AI生成広告プレビュー",
    btn_copy_ad: "テキストをコピー",
    ad_ready_badge: "出品準備完了",
    btn_publish_ad: "MaisuAppに出品する",
    publish_notice: "出品後、データベースに即時保存され、ユーザー検索に反映されます。",
    chat_title: "リアルタイムAI自動翻訳メッセージ",
    chat_subtitle: "母国語のままで会話可能。AIが相手の言語（丁寧な敬語・ローマ字読み付き）へ即時翻訳し、音声読み上げにも対応します。",
    chat_contact_title: "進行中のチャット",
    chat_escrow_notice: "合意済み見積もり：Stripeエスクロー安全決済保護付き。",
    btn_pay_chat: "Stripe安心決済に進む",
    chat_audio_active: "音声オン",
    chat_audio_muted: "音声ミュート",
    chat_placeholder: "日本語でメッセージを入力...（例：エアコンの工事日程について相談したいです）",
    btn_send_chat: "翻訳して送信",
    btn_simulate_reply: "プロからの返答（日本語）をシミュレート",
    stripe_modal_title: "MaisuApp Stripe安心保護チェックアウト",
    stripe_modal_subtitle: "エンドツーエンドの暗号化。作業が完全に完了するまで代金は安全に一時預かり（エスクロー保護）されます。",
    stripe_service_label: "対象サービス：",
    stripe_pro_label: "担当プロ：",
    stripe_total_label: "お支払い合計金額：",
    stripe_test_cards_label: "Stripe公式テストカード：",
    card_success_label: "即時承認テスト",
    card_3ds_label: "3Dセキュア認証",
    card_declined_label: "残高不足テスト",
    card_number_label: "カード番号",
    card_exp_label: "有効期限 (月/年)",
    card_cvc_label: "CVC",
    card_name_label: "カード名義人",
    btn_confirm_pay: "Stripeエスクロー決済を確定する",
    dns_modal_title: "ドメイン設定 www.maisuapp.com (Wix / Render)",
    dns_modal_subtitle: "Wixで管理中のドメインをMaisuAppサーバーへ正しく接続する手順案内。",
    dns_check_btn: "DNS接続状況を確認",
    dns_alert_title: "WixおよびRenderのDNS設定要件：",
    dns_alert_body: "1. Renderダッシュボード: 'Settings > Custom Domains' に www.maisuapp.com を追加してください。\n2. Wix DNSマネージャー: CNAME (www) を maisuapp.onrender.com に向け、古いWixのAレコードを削除してください。",
    footer_dns_link: "ドメイン・DNS設定案内 (maisuapp.com)",
    footer_stripe_link: "Stripe決済シミュレーター",
    theme_status_dark: "ダークモード適用中",
    theme_status_light: "ライトモード適用中",
    theme_status_system: "システム連動モード",
    toast_lang_changed: "言語を日本語に切り替えました 🇯🇵",
    toast_ad_generated: "AIによる広告生成が完了しました！",
    toast_ad_published: "マーケットプレイスに出品が完了しました！",
    toast_search_done: "AIマッチング検索が完了しました！",
    toast_request_sent: "プロフェッショナルへ依頼を送信しました！受付番号が発行されました。",
    toast_payment_success: "Stripe安全決済が承認され、エスクロー保護されました！",
    toast_payment_declined: "カード発行会社により決済が拒否されました（残高不足）。"
  },
  en: {
    lang_code: "en",
    lang_label: "English 🇺🇸",
    nav_explore: "Explore Services",
    nav_messages: "Messages & AI Translate",
    nav_pro: "For Pros",
    nav_requests: "My Requests",
    nav_advertise_btn: "Offer a Service",
    hero_badge: "Official Bilingual Marketplace • Japan & Global",
    hero_title: "Connecting Trusted Services Across Japan with AI",
    hero_subtitle: "Find verified bilingual professionals in Japan or send a direct service request with live translation and secure Stripe escrow.",
    search_placeholder: "What do you need? (e.g. Electrician in Hamamatsu, City Hall translator, Shaken in Gunma...)",
    search_btn: "Search with AI",
    quick_tags_label: "Popular searches:",
    tag_electrician: "⚡ Electrician in Hamamatsu",
    tag_interpreter: "🏛️ City Hall Translator Nagoya",
    tag_shaken: "🚗 Mechanic & Shaken Gunma",
    tag_cleaning: "🧹 Move-out Cleaning",
    cat_all: "All Services",
    cat_electrician: "Electrician & AC",
    cat_translation: "Translation & Visas",
    cat_mechanic: "Auto & Shaken",
    cat_cleaning: "Cleaning & Moving",
    cat_renovation: "Renovation",
    cat_it: "IT & Tutoring",
    verified_pros_title: "Verified Professionals Across Japan",
    verified_pros_subtitle: "Ranked by customer reviews, database history, and bilingual capabilities.",
    btn_request_service: "Request Service / Quote",
    btn_chat_translator: "Chat (AI Translator)",
    btn_hire: "Hire with Stripe",
    reviews_count: "reviews",
    price_from: "Starting from",
    languages_label: "Languages:",
    coverage_label: "Coverage:",
    license_label: "License:",
    request_modal_title: "Send Service Request via MaisuApp",
    request_modal_subtitle: "Your request will be recorded with a unique tracking protocol in the database and delivered directly to the professional.",
    request_pro_label: "Selected Professional:",
    request_name_label: "Your Full Name",
    request_contact_label: "Your Phone / WhatsApp / LINE",
    request_city_label: "Your City / Prefecture in Japan",
    request_date_label: "Preferred Date & Time",
    request_budget_label: "Estimated Budget (JPY)",
    request_details_label: "Describe what you need in detail:",
    btn_ai_enhance: "✨ Enhance & Translate with AI",
    btn_submit_request: "Send Request with Tracking Protocol",
    my_requests_title: "Service Requests History",
    my_requests_subtitle: "Track all service requests sent to professionals via MaisuApp.",
    no_requests_msg: "You haven't sent any requests yet. Find a professional and click 'Request Service'!",
    protocol_label: "Protocol:",
    status_waiting: "Awaiting Provider Response",
    pro_creator_title: "AI-Powered Service Ad Creator for Pros",
    pro_creator_subtitle: "Describe your service in a few words. The AI generates a complete bilingual ad (Portuguese & Japanese) with pricing recommendations and SEO tags.",
    pro_name_label: "Your Name / Business Name",
    pro_category_label: "Service Category",
    pro_city_label: "Region / City",
    pro_exp_label: "Years of Experience",
    pro_price_label: "Base Starting Price (¥)",
    pro_desc_label: "Describe what you do in your own words:",
    btn_generate_ad: "Generate Pro Ad with AI",
    preview_box_title: "AI-Generated Ad Preview",
    btn_copy_ad: "Copy Text",
    ad_ready_badge: "Ready to Publish",
    btn_publish_ad: "Publish on MaisuApp",
    publish_notice: "Your ad will be saved in the database and immediately visible to clients searching in your area.",
    chat_title: "Live Bilingual Translated Chat",
    chat_subtitle: "Speak in your native language. The AI translates each message in real time with correct formality, phonetic reading (Romaji), and audio playback.",
    chat_contact_title: "Active Conversations",
    chat_escrow_notice: "Agreed quote protected with Stripe Escrow.",
    btn_pay_chat: "Pay with Secure Stripe Escrow",
    chat_audio_active: "Voice Active",
    chat_audio_muted: "Voice Muted",
    chat_placeholder: "Type your message... (e.g. 'What is the price to install the air conditioner?')",
    btn_send_chat: "Translate & Send",
    btn_simulate_reply: "Simulate Pro Reply in Japanese",
    stripe_modal_title: "MaisuApp Secure Stripe Checkout",
    stripe_modal_subtitle: "End-to-end encryption with escrow protection: funds are safely held until service completion.",
    stripe_service_label: "Selected Service:",
    stripe_pro_label: "Assigned Professional:",
    stripe_total_label: "Total Amount:",
    stripe_test_cards_label: "Official Stripe Test Cards:",
    card_success_label: "Instant Approved",
    card_3ds_label: "3D Secure (3DS)",
    card_declined_label: "Declined (Funds)",
    card_number_label: "Card Number",
    card_exp_label: "Expiry (MM/YY)",
    card_cvc_label: "CVC",
    card_name_label: "Cardholder Name",
    btn_confirm_pay: "Confirm Payment with Stripe Escrow",
    dns_modal_title: "Domain Setup Guide www.maisuapp.com",
    dns_modal_subtitle: "Clear instructions to connect your Wix-managed domain to the MaisuApp server on Render.",
    dns_check_btn: "Check DNS Status",
    dns_alert_title: "Important DNS Notice for Wix & Render:",
    dns_alert_body: "1. On Render: Add www.maisuapp.com under 'Settings > Custom Domains'.\n2. On Wix DNS: Point CNAME www to maisuapp.onrender.com and remove old Wix A records on the apex domain.",
    footer_dns_link: "Domain & DNS Setup (maisuapp.com)",
    footer_stripe_link: "Test Stripe Checkout",
    theme_status_dark: "Dark Mode active",
    theme_status_light: "Light Mode active",
    theme_status_system: "System Theme active",
    toast_lang_changed: "Language changed to English 🇺🇸",
    toast_ad_generated: "Professional ad generated by AI successfully!",
    toast_ad_published: "Service ad published successfully on the marketplace!",
    toast_search_done: "AI search completed successfully!",
    toast_request_sent: "Request sent successfully to the professional! Protocol generated.",
    toast_payment_success: "Payment approved via Stripe and held safely in escrow!",
    toast_payment_declined: "Payment declined by issuing bank (Insufficient funds)."
  }
};

// Inicialização ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initLanguage();
  loadProfessionals();
  loadServiceRequestsHistory();
});

// 1. Inicialização de Idioma (i18n)
function initLanguage() {
  const savedLang = localStorage.getItem('maisuapp_lang');
  if (savedLang && i18n[savedLang]) {
    currentLang = savedLang;
  } else {
    const navLang = (navigator.language || '').toLowerCase();
    if (navLang.startsWith('ja')) currentLang = 'ja';
    else if (navLang.startsWith('en')) currentLang = 'en';
    else currentLang = 'pt';
  }
  applyTranslations(currentLang, false);
}

function setAppLanguage(lang, label) {
  if (!i18n[lang]) return;
  currentLang = lang;
  localStorage.setItem('maisuapp_lang', lang);
  applyTranslations(lang, true);
  closeLanguageModal();
}

function applyTranslations(lang, showNotification = true) {
  const dict = i18n[lang] || i18n.pt;

  const labelEl = document.getElementById('current-lang-label');
  if (labelEl) labelEl.innerText = dict.lang_label;

  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (dict[key]) {
      el.innerText = dict[key];
    }
  });

  document.querySelectorAll('[data-i18n-html]').forEach(el => {
    const key = el.getAttribute('data-i18n-html');
    if (dict[key]) {
      el.innerHTML = dict[key];
    }
  });

  document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
    const key = el.getAttribute('data-i18n-placeholder');
    if (dict[key]) {
      el.setAttribute('placeholder', dict[key]);
    }
  });

  if (allProfessionals && allProfessionals.length > 0) {
    renderFilteredProfessionals();
  }

  renderServiceRequestsHistory();
  updateThemeStatusText(currentThemeMode);

  if (showNotification) {
    showToast(dict.toast_lang_changed, "success");
  }
}

function openLanguageModal() {
  const modal = document.getElementById('lang-modal');
  if (modal) modal.classList.remove('hidden');
}

function closeLanguageModal() {
  const modal = document.getElementById('lang-modal');
  if (modal) modal.classList.add('hidden');
}

// 2. Alternador de Abas (Tabs)
function switchTab(tabId) {
  const tabs = document.querySelectorAll('.tab-content');
  tabs.forEach(tab => tab.classList.remove('active'));

  const targetTab = document.getElementById(`tab-${tabId}`);
  if (targetTab) {
    targetTab.classList.add('active');
  }

  document.querySelectorAll('.nav-tab-btn').forEach(btn => {
    btn.classList.remove('active');
  });

  const activeBtn = document.getElementById(`nav-btn-${tabId}`);
  if (activeBtn) activeBtn.classList.add('active');

  document.querySelectorAll('.mobile-nav-btn').forEach(btn => {
    btn.classList.remove('text-purple-400', 'font-bold');
    btn.classList.add('text-slate-400');
  });
  const mobileActiveBtn = document.getElementById(`mobile-btn-${tabId}`);
  if (mobileActiveBtn) {
    mobileActiveBtn.classList.remove('text-slate-400');
    mobileActiveBtn.classList.add('text-purple-400', 'font-bold');
  }

  if (tabId === 'my-requests') {
    loadServiceRequestsHistory();
  }

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// 3. Carregar e Filtrar Profissionais do Banco de Dados
async function loadProfessionals() {
  try {
    const res = await fetch('/api/professionals');
    const data = await res.json();
    if (data.status === 1 && data.data && data.data.length > 0) {
      allProfessionals = data.data;
    }
  } catch (e) {
    console.warn("Utilizando catálogo local de profissionais:", e);
  }

  renderFilteredProfessionals();
}

function selectCategory(catId) {
  currentCategory = catId;
  document.querySelectorAll('.cat-pill').forEach(pill => {
    if (pill.getAttribute('data-cat') === catId) {
      pill.classList.add('active');
    } else {
      pill.classList.remove('active');
    }
  });
  renderFilteredProfessionals();
}

function renderFilteredProfessionals() {
  const container = document.getElementById('inside-professionals-container');
  if (!container) return;

  const dict = i18n[currentLang] || i18n.pt;

  let list = allProfessionals;
  if (currentCategory !== 'all') {
    list = allProfessionals.filter(p => p.category_id === currentCategory);
  }

  if (list.length === 0) {
    container.innerHTML = `
      <div class="col-span-full py-12 text-center text-slate-400">
        <i class="fa-solid fa-magnifying-glass text-3xl mb-3 opacity-40"></i>
        <p class="text-sm font-semibold">${currentLang === 'ja' ? '該当する専門家が見つかりませんでした。別のカテゴリーをお試しください。' : 'Nenhum profissional encontrado nesta categoria. Tente outra busca!'}</p>
      </div>
    `;
    return;
  }

  container.innerHTML = list.map(pro => {
    let categoryName = pro.category_pt || pro.category;
    if (currentLang === 'ja' && pro.category_ja) categoryName = pro.category_ja;
    else if (currentLang === 'en' && pro.category_en) categoryName = pro.category_en;

    const basePrice = pro.base_price_jpy || 5000;
    const areas = pro.area_coverage ? pro.area_coverage.slice(0, 3).join(', ') : pro.city;

    return `
      <div class="glass-card p-5 flex flex-col justify-between">
        <div>
          <!-- Header do Card -->
          <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center space-x-3">
              <div class="relative">
                <img src="${pro.avatar}" class="w-12 h-12 rounded-2xl object-cover ring-2 ring-purple-500/20" alt="${escapeHtml(pro.name)}">
                ${pro.verified ? `
                  <span class="absolute -bottom-1 -right-1 bg-emerald-500 text-white rounded-full w-4 h-4 flex items-center justify-center text-[9px] shadow" title="Verificado">
                    <i class="fa-solid fa-check"></i>
                  </span>
                ` : ''}
              </div>
              <div>
                <h4 class="font-bold text-sm leading-snug">${escapeHtml(pro.name)}</h4>
                <p class="text-xs text-purple-400 font-medium">${escapeHtml(categoryName)}</p>
              </div>
            </div>
          </div>

          <!-- Métricas: Avaliações e Localização -->
          <div class="flex items-center gap-2 text-xs text-slate-400 mb-2.5 pb-2.5 border-b border-slate-800/40">
            <span class="flex items-center text-amber-400 font-bold">
              ★ ${pro.rating} <span class="text-slate-400 font-normal ml-1">(${pro.review_count} ${dict.reviews_count})</span>
            </span>
            <span>•</span>
            <span class="truncate"><i class="fa-solid fa-location-dot text-slate-500 mr-1"></i>${escapeHtml(pro.city)}</span>
          </div>

          <!-- Bio concisa -->
          <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed mb-3">
            ${escapeHtml(pro.bio)}
          </p>

          <!-- Região de Atendimento -->
          <div class="text-[11px] text-slate-400 mb-2">
            <span class="font-semibold text-slate-300">${dict.coverage_label}</span> ${escapeHtml(areas)}
          </div>

          <!-- Idiomas Falados -->
          <div class="flex items-center gap-1.5 flex-wrap mb-4 text-[11px]">
            <span class="text-slate-400">${dict.languages_label}</span>
            ${(pro.languages || ['Português', '日本語']).map(l => `
              <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-300 font-medium">${escapeHtml(l)}</span>
            `).join('')}
          </div>
        </div>

        <!-- Preço e Ações Principais -->
        <div class="pt-3 border-t border-slate-800/40 space-y-2.5">
          <div class="flex items-baseline justify-between">
            <span class="text-[11px] text-slate-400">${dict.price_from}</span>
            <div class="text-right">
              <strong class="text-base font-extrabold text-emerald-400">¥${basePrice.toLocaleString()}</strong>
              <span class="text-[10px] text-slate-500 block">~ R$ ${Math.round(basePrice * 0.036)}</span>
            </div>
          </div>

          <!-- Botão Primário: Solicitar Serviço / Enviar Pedido via MaisuApp -->
          <button onclick="openServiceRequestModal(${pro.id}, '${escapeHtml(pro.name)}', '${escapeHtml(categoryName)}', '${escapeHtml(pro.city)}', ${basePrice})" class="w-full btn-pink py-2.5 px-3 text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer shadow-sm">
            <i class="fa-solid fa-paper-plane text-xs"></i>
            <span class="truncate">${dict.btn_request_service}</span>
          </button>

          <!-- Ações Secundárias -->
          <div class="grid grid-cols-2 gap-2">
            <button onclick="openChatWithPro('${escapeHtml(pro.name)}')" class="btn-outline py-1.5 px-2 text-xs font-semibold flex items-center justify-center gap-1.5 truncate cursor-pointer">
              <i class="fa-solid fa-comments text-cyan-400"></i> <span class="truncate">${dict.btn_chat_translator}</span>
            </button>
            <button onclick="openStripeCheckoutModal('${escapeHtml(pro.name)}', ${basePrice}, '${escapeHtml(categoryName)}')" class="btn-primary py-1.5 px-2 text-xs font-semibold flex items-center justify-center gap-1.5 truncate cursor-pointer">
              <i class="fa-brands fa-stripe"></i> <span class="truncate">${dict.btn_hire}</span>
            </button>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

// 4. Busca com IA e Descoberta Automática de Prestadores Reais
async function executeAiSearch() {
  const inputEl = document.getElementById('client-search-input');
  const prompt = (inputEl ? inputEl.value : '').trim();
  const dict = i18n[currentLang] || i18n.pt;

  const loadingEl = document.getElementById('ai-search-loading');
  if (loadingEl) loadingEl.classList.remove('hidden');

  try {
    const res = await fetch('/api/ai/matchmaking', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ prompt: prompt || "Eletricista em Hamamatsu", location: prompt || 'Hamamatsu' })
    });
    const result = await res.json();

    if (loadingEl) loadingEl.classList.add('hidden');

    if (result.status === 1) {
      if (result.inside_app_professionals && result.inside_app_professionals.length > 0) {
        allProfessionals = result.inside_app_professionals;
        renderFilteredProfessionals();
      }
      showToast(dict.toast_search_done, "success");
    }
  } catch (e) {
    if (loadingEl) loadingEl.classList.add('hidden');
    console.error("Erro na busca com IA:", e);
    renderFilteredProfessionals();
  }
}

function fillSearchPrompt(text) {
  const inputEl = document.getElementById('client-search-input');
  if (inputEl) {
    inputEl.value = text;
    executeAiSearch();
  }
}

// 5. Modal de Envio de Solicitação de Serviço para o Profissional
function openServiceRequestModal(proId, proName, category, city, basePrice) {
  activeRequestPro = { id: proId, name: proName, category, city, basePrice };
  const modal = document.getElementById('request-modal');
  if (!modal) return;

  const nameTarget = document.getElementById('req-modal-pro-name');
  const catTarget = document.getElementById('req-modal-pro-cat');
  const cityInput = document.getElementById('req-client-city');
  const titleInput = document.getElementById('req-service-title');
  const budgetInput = document.getElementById('req-budget-estimate');

  if (nameTarget) nameTarget.innerText = proName;
  if (catTarget) catTarget.innerText = `${category} • ${city}`;
  if (cityInput) cityInput.value = city;
  if (titleInput) titleInput.value = `Solicitação para ${category}`;
  if (budgetInput) budgetInput.value = `¥${basePrice ? basePrice.toLocaleString() : '8.000'}`;

  // Limpar campos
  const detailsEl = document.getElementById('req-service-details');
  if (detailsEl) detailsEl.value = '';
  const previewJa = document.getElementById('req-preview-ja');
  if (previewJa) previewJa.classList.add('hidden');

  modal.classList.remove('hidden');
}

function closeServiceRequestModal() {
  const modal = document.getElementById('request-modal');
  if (modal) modal.classList.add('hidden');
}

// Aprimoramento e Tradução da Mensagem com IA
async function aiEnhanceServiceRequest() {
  const detailsEl = document.getElementById('req-service-details');
  const rawText = (detailsEl ? detailsEl.value : '').trim();
  const city = document.getElementById('req-client-city').value;
  const btn = document.getElementById('btn-ai-enhance-req');

  if (!rawText) {
    showToast("Por favor, digite algumas palavras sobre o que você precisa antes de aprimorar.", "warning");
    return;
  }

  if (btn) {
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Traduzindo e Formatando...`;
    btn.disabled = true;
  }

  try {
    const res = await fetch('/api/ai/enhance-request', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        raw_text: rawText,
        category: activeRequestPro ? activeRequestPro.category : 'Serviço',
        city: city
      })
    });
    const result = await res.json();

    if (btn) {
      btn.innerHTML = `<i class="fa-solid fa-sparkles mr-1"></i> Aprimorar e Traduzir com IA`;
      btn.disabled = false;
    }

    if (result.status === 1) {
      detailsEl.value = result.enhanced_pt;
      const previewJa = document.getElementById('req-preview-ja');
      const previewJaText = document.getElementById('req-preview-ja-text');
      if (previewJa && previewJaText) {
        previewJa.classList.remove('hidden');
        previewJaText.innerText = result.enhanced_ja;
      }
      showToast("Texto aprimorado e traduzido para o japonês com sucesso!", "success");
    }
  } catch (e) {
    if (btn) {
      btn.innerHTML = `<i class="fa-solid fa-sparkles mr-1"></i> Aprimorar e Traduzir com IA`;
      btn.disabled = false;
    }
    console.error(e);
  }
}

// Submeter Solicitação e Gravar no Banco de Dados
async function submitServiceRequest() {
  const clientName = document.getElementById('req-client-name').value.trim();
  const clientContact = document.getElementById('req-client-contact').value.trim();
  const clientCity = document.getElementById('req-client-city').value.trim();
  const serviceTitle = document.getElementById('req-service-title').value.trim();
  const preferredDate = document.getElementById('req-preferred-date').value.trim();
  const budgetEstimate = document.getElementById('req-budget-estimate').value.trim();
  const details = document.getElementById('req-service-details').value.trim();
  const previewJaText = document.getElementById('req-preview-ja-text');
  const detailsJa = previewJaText ? previewJaText.innerText : '';
  const dict = i18n[currentLang] || i18n.pt;
  const btn = document.getElementById('btn-submit-req-action');

  if (!clientName || !clientContact || !details) {
    showToast("Preencha seu nome, contato e detalhes do pedido.", "warning");
    return;
  }

  if (btn) {
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Enviando Solicitação...`;
    btn.disabled = true;
  }

  try {
    const res = await fetch('/api/service-requests', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        client_name: clientName,
        client_contact: clientContact,
        city: clientCity,
        service_title: serviceTitle,
        preferred_date: preferredDate,
        budget_estimate: budgetEstimate,
        service_details: details,
        service_details_ja: detailsJa,
        professional_id: activeRequestPro ? activeRequestPro.id : 1,
        professional_name: activeRequestPro ? activeRequestPro.name : 'Carlos Kenji Tanaka'
      })
    });
    const result = await res.json();

    if (btn) {
      btn.innerHTML = `<i class="fa-solid fa-paper-plane mr-1.5"></i> ${dict.btn_submit_request}`;
      btn.disabled = false;
    }

    if (result.status === 1) {
      closeServiceRequestModal();
      showToast(`Solicitação enviada com sucesso! Protocolo: ${result.protocol}`, "success");

      // Atualizar lista local de histórico
      loadServiceRequestsHistory();

      // Abrir o chat bilíngue com a mensagem de solicitação preenchida
      openChatWithPro(activeRequestPro ? activeRequestPro.name : 'Carlos Kenji Tanaka');

      // Injetar mensagem de pedido no chat
      const messagesContainer = document.getElementById('chat-messages-container');
      if (messagesContainer) {
        const reqCardHtml = `
          <div class="flex flex-col items-end space-y-1 mb-4">
            <div class="chat-bubble-user p-4 max-w-md text-xs shadow-md space-y-2">
              <div class="flex items-center justify-between pb-1.5 border-b border-white/20">
                <span class="font-bold uppercase tracking-wider text-[10px] text-purple-200">📋 Pedido Formal MaisuApp</span>
                <span class="font-mono text-[10px] bg-white/20 px-1.5 py-0.5 rounded font-bold">${result.protocol}</span>
              </div>
              <p class="font-bold text-sm text-white">${escapeHtml(serviceTitle)}</p>
              <p class="text-slate-100">${escapeHtml(details)}</p>
              <div class="pt-1.5 border-t border-white/20 flex justify-between text-[11px] text-purple-200">
                <span>Data: ${escapeHtml(preferredDate || 'A combinar')}</span>
                <span class="font-bold text-emerald-300">${escapeHtml(budgetEstimate)}</span>
              </div>
            </div>
            ${detailsJa ? `
              <div class="chat-translation-tag p-2 rounded-lg max-w-md text-[11px] text-cyan-200">
                <span class="font-bold text-pink-400 block mb-0.5">🇯🇵 Tradução Oficial Entregue ao Prestador:</span>
                <p>${escapeHtml(detailsJa)}</p>
              </div>
            ` : ''}
          </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', reqCardHtml);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
      }
    } else {
      showToast(result.message || "Erro ao enviar solicitação.", "error");
    }
  } catch (e) {
    if (btn) {
      btn.innerHTML = `<i class="fa-solid fa-paper-plane mr-1.5"></i> ${dict.btn_submit_request}`;
      btn.disabled = false;
    }
    console.error(e);
  }
}

// 6. Carregar e Renderizar Histórico de Solicitações do Cliente
async function loadServiceRequestsHistory() {
  try {
    const res = await fetch('/api/service-requests');
    const data = await res.json();
    if (data.status === 1) {
      myServiceRequests = data.data;
      renderServiceRequestsHistory();
    }
  } catch (e) {
    console.warn("Erro ao carregar solicitações:", e);
  }
}

function renderServiceRequestsHistory() {
  const container = document.getElementById('my-requests-list-container');
  if (!container) return;
  const dict = i18n[currentLang] || i18n.pt;

  if (!myServiceRequests || myServiceRequests.length === 0) {
    container.innerHTML = `
      <div class="p-8 text-center text-slate-400 glass-card">
        <i class="fa-regular fa-clipboard text-3xl mb-3 opacity-40"></i>
        <p class="text-xs font-semibold">${dict.no_requests_msg}</p>
      </div>
    `;
    return;
  }

  container.innerHTML = myServiceRequests.map(req => {
    const dateFormatted = new Date(req.created_at).toLocaleDateString(currentLang === 'ja' ? 'ja-JP' : 'pt-BR', {
      day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
    });

    return `
      <div class="glass-card p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800/40 pb-3">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-white">${escapeHtml(req.service_details.title)}</span>
              <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                ${req.protocol}
              </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">
              Profissional: <strong class="text-purple-300">${escapeHtml(req.professional.name)}</strong> • ${dateFormatted}
            </p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 w-fit">
            <i class="fa-solid fa-clock text-[10px] mr-1"></i> ${dict.status_waiting}
          </span>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
          ${escapeHtml(req.service_details.description_pt || req.service_details.description)}
        </p>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-slate-800/40 text-xs">
          <div class="flex items-center gap-4 text-[11px] text-slate-400">
            <span>Área: <strong class="text-slate-300">${escapeHtml(req.client.city)}</strong></span>
            <span>Orçamento: <strong class="text-emerald-400">${escapeHtml(req.service_details.budget_estimate || 'A combinar')}</strong></span>
          </div>
          <button onclick="openChatWithPro('${escapeHtml(req.professional.name)}')" class="btn-outline px-3 py-1.5 text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer">
            <i class="fa-solid fa-comments text-cyan-400"></i> <span>Abrir Chat com Prestador</span>
          </button>
        </div>
      </div>
    `;
  }).join('');
}

// 7. Criador de Anúncios com IA (Para Prestadores)
async function generateAdWithAi() {
  const btn = document.getElementById('btn-generate-ad');
  const proName = document.getElementById('pro-name-input').value;
  const category = document.getElementById('pro-category-select').value;
  const city = document.getElementById('pro-city-input').value;
  const experience = document.getElementById('pro-exp-input').value;
  const basePrice = document.getElementById('pro-price-input').value;
  const rawDesc = document.getElementById('pro-raw-desc').value;
  const dict = i18n[currentLang] || i18n.pt;

  if (btn) {
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${currentLang === 'ja' ? 'AIが広告を生成中...' : 'Gerando com IA...'}`;
    btn.disabled = true;
  }

  try {
    const res = await fetch('/api/ai/generate-ad', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        pro_name: proName,
        category: category,
        city: city,
        experience: experience,
        base_price: basePrice,
        service_description: rawDesc
      })
    });
    const result = await res.json();

    if (btn) {
      btn.innerHTML = `<i class="fa-solid fa-wand-magic-sparkles mr-1.5"></i> ${dict.btn_generate_ad}`;
      btn.disabled = false;
    }

    if (result.status === 1) {
      const ad = result.ad;
      document.getElementById('preview-title-pt').innerText = ad.title_pt;
      document.getElementById('preview-title-ja').innerText = ad.title_ja;
      document.getElementById('preview-desc-pt').innerText = ad.description_pt;
      document.getElementById('preview-desc-ja').innerText = ad.description_ja;

      const pkgContainer = document.getElementById('preview-packages');
      if (pkgContainer) {
        pkgContainer.innerHTML = ad.packages.map(p => `
          <div class="p-3 rounded-xl ${p.tier.includes('Padrão') ? 'bg-purple-900/30 border border-purple-500/30' : 'bg-slate-900/60 border border-slate-800'} text-center">
            <div class="text-[11px] ${p.tier.includes('Padrão') ? 'text-purple-300 font-bold' : 'text-slate-400'}">${p.tier}</div>
            <div class="text-base font-bold mt-1">¥${p.price_jpy.toLocaleString()}</div>
            <div class="text-[10px] text-slate-400">~ R$ ${p.price_brl}</div>
          </div>
        `).join('');
      }

      showToast(dict.toast_ad_generated, "success");
    }
  } catch (e) {
    if (btn) {
      btn.innerHTML = `<i class="fa-solid fa-wand-magic-sparkles mr-1.5"></i> ${dict.btn_generate_ad}`;
      btn.disabled = false;
    }
    console.error(e);
  }
}

function switchAdPreviewLang(lang) {
  const btnPt = document.getElementById('tab-btn-ad-pt');
  const btnJa = document.getElementById('tab-btn-ad-ja');
  const boxPt = document.getElementById('ad-preview-box-pt');
  const boxJa = document.getElementById('ad-preview-box-ja');

  if (lang === 'ja') {
    btnJa.classList.add('bg-pink-600', 'text-white');
    btnPt.classList.remove('bg-purple-600', 'text-white');
    btnPt.classList.add('text-slate-400');
    boxJa.classList.remove('hidden');
    boxPt.classList.add('hidden');
  } else {
    btnPt.classList.add('bg-purple-600', 'text-white');
    btnJa.classList.remove('bg-pink-600', 'text-white');
    btnJa.classList.add('text-slate-400');
    boxPt.classList.remove('hidden');
    boxJa.classList.add('hidden');
  }
}

async function publishAdToMarketplace() {
  const proName = document.getElementById('pro-name-input').value;
  const category = document.getElementById('pro-category-select').value;
  const city = document.getElementById('pro-city-input').value;
  const basePrice = parseInt(document.getElementById('pro-price-input').value) || 6000;
  const dict = i18n[currentLang] || i18n.pt;

  const newPro = {
    id: allProfessionals.length + 1,
    name: proName || "Novo Prestador",
    category_id: "geral",
    category_pt: category,
    category_ja: category,
    category_en: category,
    avatar: "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=400&q=80",
    rating: 5.0,
    review_count: 1,
    distance_km: 1.2,
    city: city || "Hamamatsu, Shizuoka",
    languages: ["Português", "日本語"],
    base_price_jpy: basePrice,
    verified: true,
    badges: ["Novo Talento", "Criado com IA"],
    bio: document.getElementById('preview-desc-pt').innerText.slice(0, 120) + "..."
  };

  allProfessionals.unshift(newPro);
  renderFilteredProfessionals();
  showToast(dict.toast_ad_published, "success");
  switchTab('client-search');
}

// 8. Chat Bilíngue com Tradução Simultânea
function openChatWithPro(proName) {
  switchTab('live-translator');
  const proNameEl = document.getElementById('chat-active-pro-name');
  if (proNameEl) proNameEl.innerText = proName;
  showToast(`Chat aberto com ${proName}. A tradução simultânea está ativa!`, "info");
}

async function sendChatMessage() {
  const inputEl = document.getElementById('chat-input-text');
  const text = (inputEl ? inputEl.value : '').trim();
  if (!text) return;

  const messagesContainer = document.getElementById('chat-messages-container');
  inputEl.value = '';

  const userMsgId = 'msg-' + Date.now();
  const userHtml = `
    <div class="flex flex-col items-end space-y-1 mb-4" id="${userMsgId}">
      <div class="chat-bubble-user p-3 max-w-md text-xs shadow-sm">
        <p>${escapeHtml(text)}</p>
      </div>
      <div class="chat-translation-tag p-2 rounded-lg max-w-md text-[11px] text-cyan-300">
        <i class="fa-solid fa-spinner fa-spin mr-1"></i> Traduzindo instantaneamente para 日本語...
      </div>
    </div>
  `;
  messagesContainer.insertAdjacentHTML('beforeend', userHtml);
  messagesContainer.scrollTop = messagesContainer.scrollHeight;

  try {
    const res = await fetch('/api/ai/translate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ text, source_lang: 'pt', target_lang: 'ja' })
    });
    const result = await res.json();

    const msgEl = document.getElementById(userMsgId);
    if (msgEl && result.status === 1) {
      const tagEl = msgEl.querySelector('.chat-translation-tag');
      if (tagEl) {
        tagEl.innerHTML = `
          <div class="flex items-center justify-between font-semibold text-pink-400 mb-0.5">
            <span><i class="fa-solid fa-sparkles text-[9px]"></i> 日本語:</span>
            <button onclick="speakText('${escapeHtml(result.translated_text)}')" class="hover:underline text-[10px]">
              <i class="fa-solid fa-volume-high"></i>
            </button>
          </div>
          <p class="text-slate-200">${escapeHtml(result.translated_text)}</p>
        `;
      }
    }
  } catch (e) {
    console.error("Erro na tradução do chat:", e);
  }
}

function simulateProReply() {
  const messagesContainer = document.getElementById('chat-messages-container');
  const proJapanese = "お問い合わせありがとうございます！土曜日の午前中に確認可能です。ご住所を教えていただけますでしょうか？";
  const proPortuguese = "Muito obrigado pelo contato! Posso fazer a vistoria no sábado de manhã. Poderia me confirmar o seu endereço?";

  const proHtml = `
    <div class="flex flex-col items-start space-y-1 mb-4">
      <div class="chat-bubble-other p-3 max-w-md text-xs shadow-sm">
        <div class="text-[10px] text-purple-400 font-bold mb-1">Carlos (日本語 - Original):</div>
        <p>${proJapanese}</p>
      </div>
      <div class="chat-translation-tag p-2 rounded-lg max-w-md text-[11px]">
        <div class="flex items-center justify-between text-emerald-400 font-bold mb-0.5">
          <span><i class="fa-solid fa-sparkles text-[9px]"></i> Tradução para você (Português):</span>
          <button onclick="speakText('${proJapanese}')" class="text-pink-400 hover:underline text-[10px]">
            <i class="fa-solid fa-volume-high"></i> Ouvir
          </button>
        </div>
        <p class="text-slate-200">${proPortuguese}</p>
      </div>
    </div>
  `;
  messagesContainer.insertAdjacentHTML('beforeend', proHtml);
  messagesContainer.scrollTop = messagesContainer.scrollHeight;

  if (voiceAudioEnabled) {
    speakText(proJapanese);
  }
}

function speakText(text) {
  if ('speechSynthesis' in window) {
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'ja-JP';
    utterance.rate = 0.95;
    window.speechSynthesis.speak(utterance);
  }
}

function toggleAudioOutput() {
  voiceAudioEnabled = !voiceAudioEnabled;
  const btn = document.getElementById('btn-audio-toggle');
  const dict = i18n[currentLang] || i18n.pt;
  if (btn) {
    btn.innerHTML = voiceAudioEnabled
      ? `<i class="fa-solid fa-volume-high text-cyan-400 mr-1"></i> <span class="hidden sm:inline">${dict.chat_audio_active}</span>`
      : `<i class="fa-solid fa-volume-xmark text-slate-400 mr-1"></i> <span class="hidden sm:inline">${dict.chat_audio_muted}</span>`;
  }
}

// 9. Modal de Checkout Seguro Stripe (Escrow)
function openStripeCheckoutModal(proName, amount, service) {
  const modal = document.getElementById('stripe-modal');
  if (!modal) return;

  const proEl = document.getElementById('stripe-modal-pro');
  const amountEl = document.getElementById('stripe-modal-amount');
  const serviceEl = document.getElementById('stripe-modal-service');

  if (proEl) proEl.innerText = proName || "Carlos Kenji Tanaka";
  if (amountEl) amountEl.innerText = `¥${(amount || 12000).toLocaleString()}`;
  if (serviceEl) serviceEl.innerText = service || "Instalação de Ar-Condicionado";

  const receipt = document.getElementById('stripe-receipt-details');
  if (receipt) receipt.classList.add('hidden');

  modal.classList.remove('hidden');
}

function closeStripeCheckoutModal() {
  const modal = document.getElementById('stripe-modal');
  if (modal) modal.classList.add('hidden');
}

function fillStripeTestCard(type) {
  const cardNum = document.getElementById('stripe-card-number');
  const cardExp = document.getElementById('stripe-card-exp');
  const cardCvc = document.getElementById('stripe-card-cvc');

  if (type === 'success') {
    cardNum.value = '4242 4242 4242 4242';
    cardExp.value = '12/28';
    cardCvc.value = '123';
  } else if (type === '3ds') {
    cardNum.value = '4000 0000 0000 0027';
    cardExp.value = '12/28';
    cardCvc.value = '123';
  } else if (type === 'declined') {
    cardNum.value = '4000 0000 0000 0002';
    cardExp.value = '12/28';
    cardCvc.value = '123';
  }
}

async function processStripePayment() {
  const btn = document.getElementById('btn-submit-stripe');
  const cardNum = document.getElementById('stripe-card-number').value;
  const receipt = document.getElementById('stripe-receipt-details');
  const receiptMsg = document.getElementById('stripe-receipt-msg');
  const dict = i18n[currentLang] || i18n.pt;

  if (btn) {
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Processando...`;
    btn.disabled = true;
  }

  try {
    const res = await fetch('/api/stripe/process-payment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        card_number: cardNum,
        amount: 12000,
        currency: 'JPY',
        service_title: 'Serviço Profissional MaisuApp'
      })
    });
    const result = await res.json();

    if (btn) {
      btn.innerHTML = `<i class="fa-solid fa-lock mr-1.5"></i> ${dict.btn_confirm_pay}`;
      btn.disabled = false;
    }

    if (res.ok && result.status === 1) {
      if (receipt) receipt.classList.remove('hidden');
      if (receiptMsg) {
        receiptMsg.className = "p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold";
        receiptMsg.innerText = result.message;
      }
      showToast(dict.toast_payment_success, "success");
    } else {
      if (receipt) receipt.classList.remove('hidden');
      if (receiptMsg) {
        receiptMsg.className = "p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold";
        receiptMsg.innerText = result.message || dict.toast_payment_declined;
      }
      showToast(dict.toast_payment_declined, "error");
    }
  } catch (e) {
    if (btn) {
      btn.innerHTML = `<i class="fa-solid fa-lock mr-1.5"></i> ${dict.btn_confirm_pay}`;
      btn.disabled = false;
    }
    console.error(e);
  }
}

// 10. Modal de Configuração de DNS & Wix
function openDnsModal() {
  const modal = document.getElementById('dns-modal');
  if (modal) modal.classList.remove('hidden');
}

function closeDnsModal() {
  const modal = document.getElementById('dns-modal');
  if (modal) modal.classList.add('hidden');
}

function checkDnsStatus() {
  showToast("Consultando servidores DNS do Wix para maisuapp.com...", "info");
  setTimeout(() => {
    showToast("CNAME www aponta para maisuapp.onrender.com. Certifique-se de adicionar o domínio no Render!", "info");
  }, 1200);
}

// 11. Temas: Dark, Light e Sistema
function initTheme() {
  const savedTheme = localStorage.getItem('maisuapp_theme') || 'dark';
  setTheme(savedTheme, false);

  const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
  mediaQuery.addEventListener('change', (e) => {
    if (currentThemeMode === 'system') {
      applyThemeToDom(e.matches ? 'dark' : 'light');
      updateThemeStatusText('system');
    }
  });
}

function setTheme(mode, showNotification = true) {
  currentThemeMode = mode;
  localStorage.setItem('maisuapp_theme', mode);

  ['light', 'dark', 'system'].forEach(m => {
    const btn = document.getElementById(`theme-btn-${m}`);
    if (btn) {
      if (m === mode) btn.classList.add('active');
      else btn.classList.remove('active');
    }
  });

  if (mode === 'system') {
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyThemeToDom(systemPrefersDark ? 'dark' : 'light');
    updateThemeStatusText('system');
  } else {
    applyThemeToDom(mode);
    updateThemeStatusText(mode);
  }
}

function applyThemeToDom(resolvedTheme) {
  const html = document.documentElement;
  if (resolvedTheme === 'dark') {
    html.classList.add('dark');
    html.classList.remove('light');
  } else {
    html.classList.add('light');
    html.classList.remove('dark');
  }
}

function updateThemeStatusText(mode) {
  const statusEl = document.getElementById('theme-system-status');
  if (!statusEl) return;
  const dict = i18n[currentLang] || i18n.pt;

  if (mode === 'system') {
    statusEl.innerHTML = `<i class="fa-solid fa-desktop text-cyan-400"></i> ${dict.theme_status_system}`;
  } else if (mode === 'light') {
    statusEl.innerHTML = `<i class="fa-solid fa-sun text-amber-400"></i> ${dict.theme_status_light}`;
  } else {
    statusEl.innerHTML = `<i class="fa-solid fa-moon text-purple-400"></i> ${dict.theme_status_dark}`;
  }
}

// 12. Toasts e Utilitários
function showToast(message, type = "info") {
  const toast = document.getElementById('toast');
  const icon = document.getElementById('toast-icon');
  const msgEl = document.getElementById('toast-msg');

  if (!toast) return;

  msgEl.innerText = message;
  if (type === 'success') {
    icon.className = "fa-solid fa-circle-check text-emerald-400 text-sm";
    toast.className = "fixed bottom-6 right-6 z-50 glass-card px-4 py-3 border border-emerald-500/40 flex items-center gap-3 text-xs max-w-md shadow-2xl opacity-100 translate-y-0 transition-all duration-300";
  } else if (type === 'error') {
    icon.className = "fa-solid fa-circle-xmark text-rose-400 text-sm";
    toast.className = "fixed bottom-6 right-6 z-50 glass-card px-4 py-3 border border-rose-500/40 flex items-center gap-3 text-xs max-w-md shadow-2xl opacity-100 translate-y-0 transition-all duration-300";
  } else {
    icon.className = "fa-solid fa-circle-info text-cyan-400 text-sm";
    toast.className = "fixed bottom-6 right-6 z-50 glass-card px-4 py-3 border border-cyan-500/40 flex items-center gap-3 text-xs max-w-md shadow-2xl opacity-100 translate-y-0 transition-all duration-300";
  }

  setTimeout(() => {
    toast.classList.add('translate-y-24', 'opacity-0');
  }, 4000);
}

function copyToClipboard(text, successMessage = "Copiado!") {
  navigator.clipboard.writeText(text).then(() => {
    showToast(successMessage, "success");
  }).catch(() => {
    showToast("Erro ao copiar", "error");
  });
}

function copyGeneratedAd() {
  const textPt = document.getElementById('preview-desc-pt').innerText;
  const textJa = document.getElementById('preview-desc-ja').innerText;
  navigator.clipboard.writeText(textPt + "\n\n" + textJa);
  showToast("Anúncio bilíngue copiado!", "success");
}

function toggleVoiceInput() {
  showToast("Reconhecimento de voz ativo...", "info");
  setTimeout(() => {
    fillSearchPrompt(currentLang === 'ja' ? 'エアコン工事と電気修理のプロ' : 'Eletricista ar-condicionado em Hamamatsu');
  }, 800);
}

function escapeHtml(string) {
  const entityMap = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  };
  return String(string).replace(/[&<>"']/g, s => entityMap[s]);
}
