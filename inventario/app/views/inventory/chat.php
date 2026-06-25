<?php
// app/views/inventory/chat.php
?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/facturacion/facturacion/config/sidebar3.php'; 
include "../../facturacion/config/boton_volver.php";?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panamericana de Viajes · Agente IA</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/facturacion/estilos/estilos.css">
    <script src="https://cdn.platform.openai.com/deployments/chatkit/chatkit.js"></script>

    <style>
        /* ─── TOKENS ──────────────────────────────────── */
        :root {
            --erp-navbar-h: 64px;
            --sidebar-w: 256px;
            --topbar-h: 52px;
            --content-max: 860px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --brand:     #1a6cf6;
            --brand-dim: #1358cc;
            --transition: 0.2s ease;
        }

        /* ─── DARK THEME (default) ─────────────────────── */
        [data-theme="dark"] {
            --bg-app:        #0f0f11;
            --bg-sidebar:    #141416;
            --bg-surface:    #1c1c1f;
            --bg-hover:      #242428;
            --bg-active:     #2a2a30;
            --border:        rgba(255,255,255,0.08);
            --border-strong: rgba(255,255,255,0.14);
            --text-primary:  #f2f2f5;
            --text-secondary:#a0a0b0;
            --text-muted:    #66667a;
            --shadow-sm:     0 1px 3px rgba(0,0,0,0.5);
            --shadow-md:     0 4px 16px rgba(0,0,0,0.45);
        }

        /* ─── LIGHT THEME ──────────────────────────────── */
        [data-theme="light"] {
            --bg-app:        #f0f2f7;
            --bg-sidebar:    #ffffff;
            --bg-surface:    #ffffff;
            --bg-hover:      #f5f6fb;
            --bg-active:     #eaecf5;
            --border:        rgba(0,0,0,0.07);
            --border-strong: rgba(0,0,0,0.12);
            --text-primary:  #14141e;
            --text-secondary:#4b4b6a;
            --text-muted:    #9090a8;
            --shadow-sm:     0 1px 3px rgba(0,0,0,0.08);
            --shadow-md:     0 4px 16px rgba(0,0,0,0.1);
        }

        /* ─── RESET & BASE ──────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            width: 100%;
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg-app);
            color: var(--text-primary);
            overflow: hidden; /* evita doble scrollbar */
            -webkit-font-smoothing: antialiased;
        }

        /* APP SHELL
         * sidebar3.php mete TODO en <div class="content"> con margin-top:75px.
         * position:fixed saca .app del flujo del DOM y lo ancla al viewport.
         * top = navbar ERP (JS lo mide y sobreescribe --erp-navbar-h).
         */
        .app {
            position: fixed;
            top: var(--erp-navbar-h);
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            background: var(--bg-app);
            transition: background var(--transition), color var(--transition);
            overflow: hidden;
            z-index: 1000;
        }

        /* Neutralizar margin/padding de .content de sidebar3.php */
        .content {
            margin-top: 0 !important;
            padding: 0 !important;
        }

        /* ─── SIDEBAR ────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            height: 100%;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 14px 10px;
            transition: background var(--transition), border-color var(--transition), transform 0.3s ease;
            overflow: hidden;
        }

        /* Brand */
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 8px 14px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 10px;
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--brand), var(--brand-dim));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.82rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(26,108,246,0.35);
        }

        .brand-name {
            font-size: 0.87rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            white-space: nowrap;
        }

        .brand-sub {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 1px;
        }

        /* Nav actions */
        .nav-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            padding: 8px 10px;
            border: none;
            background: transparent;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            font-size: 0.835rem;
            font-weight: 500;
            cursor: pointer;
            text-align: left;
            transition: background var(--transition), color var(--transition);
        }

        .nav-btn:hover, .nav-btn.active {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .nav-btn.primary {
            background: var(--bg-active);
            color: var(--brand);
            font-weight: 600;
        }

        .nav-btn.primary:hover {
            background: rgba(26,108,246,0.15);
        }

        .nav-btn i {
            width: 16px;
            text-align: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        /* Section title */
        .nav-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 14px 10px 6px;
        }

        /* Quick actions */
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 6px;
        }

        /* Recent scroll */
        .recents-scroll {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1px;
            padding-right: 2px;
        }

        .recents-scroll::-webkit-scrollbar { width: 4px; }
        .recents-scroll::-webkit-scrollbar-track { background: transparent; }
        .recents-scroll::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 99px; }

        .recent-btn {
            display: block;
            width: 100%;
            padding: 7px 10px;
            border: none;
            background: transparent;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            font-size: 0.8rem;
            text-align: left;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: background var(--transition), color var(--transition);
        }

        .recent-btn:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding-top: 12px;
            border-top: 1px solid var(--border);
            margin-top: 8px;
        }

        .workspace-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: var(--bg-hover);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
        }

        .workspace-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            background: linear-gradient(135deg, var(--brand), var(--brand-dim));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .workspace-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .workspace-role {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* ─── MAIN AREA ───────────────────────────────────── */
        .main {
            flex: 1;
            min-width: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: var(--bg-app);
            transition: background var(--transition);
            overflow: hidden;
        }

        /* Topbar */
        .topbar {
            height: var(--topbar-h);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-bottom: 1px solid var(--border);
            background: var(--bg-sidebar);
            transition: background var(--transition), border-color var(--transition);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Hamburger (mobile) */
        .btn-menu {
            display: none;
            width: 34px;
            height: 34px;
            border: none;
            background: transparent;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: background var(--transition);
        }

        .btn-menu:hover { background: var(--bg-hover); color: var(--text-primary); }

        .topbar-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topbar-title-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 6px rgba(34,197,94,0.6);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            border: none;
            background: transparent;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            transition: background var(--transition), color var(--transition);
        }

        .icon-btn:hover { background: var(--bg-hover); color: var(--text-primary); }

        /* ─── THEME TOGGLE PILL ──────────────────────────── */
        .theme-pill {
            display: flex;
            align-items: center;
            gap: 0;
            background: var(--bg-active);
            border: 1px solid var(--border-strong);
            border-radius: 99px;
            padding: 3px;
            transition: background var(--transition), border-color var(--transition);
        }

        .theme-opt {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 26px;
            border: none;
            background: transparent;
            border-radius: 99px;
            color: var(--text-muted);
            font-size: 0.75rem;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease;
        }

        .theme-opt.selected {
            background: var(--bg-sidebar);
            color: var(--brand);
            box-shadow: var(--shadow-sm);
        }

        [data-theme="light"] .theme-opt.selected {
            background: var(--brand);
            color: #fff;
        }

        /* CHATKIT FRAME
         * Con .app en position:fixed, flex:1 y height:100% funcionan bien.
         * NO necesitamos calc() explícito aquí — el viewport ya está definido.
         */
        .chatkit-frame {
            flex: 1;
            min-height: 0;
            display: flex;
            justify-content: center;
            padding: 0 20px 12px;
            overflow: hidden;
        }

        .chatkit-col {
            width: 100%;
            max-width: var(--content-max);
            height: 100%;
            min-height: 0;
            overflow: hidden;
        }

        #chat-mount {
            width: 100%;
            height: 100%;
            min-height: 0;
        }

        openai-chatkit {
            display: block;
            width: 100%;
            height: 100%;
            min-height: 0;
        }

        /* Error state */
        .chat-error {
            margin: 20px auto;
            max-width: 640px;
            padding: 16px 20px;
            color: #fca5a5;
            background: rgba(127,29,29,0.3);
            border: 1px solid rgba(248,113,113,0.3);
            border-radius: var(--radius-md);
            font-size: 0.86rem;
            white-space: pre-wrap;
        }

        /* ─── SIDEBAR OVERLAY (mobile) ────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
            backdrop-filter: blur(2px);
        }

        /* ─── RESPONSIVE ─────────────────────────────────── */
        @media (max-width: 860px) {
            :root { --sidebar-w: 240px; }

            .sidebar {
                position: fixed;
                top: var(--erp-navbar-h);
                left: 0;
                height: calc(100dvh - var(--erp-navbar-h));
                z-index: 100;
                transform: translateX(-100%);
                box-shadow: var(--shadow-md);
            }

            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .btn-menu { display: flex; }

            .chatkit-frame { padding: 0 8px 8px; }

            /* Con .app en position:fixed no hay que recalcular altura en mobile */
        }

        @media (max-width: 540px) {
            .topbar-title span:not(.topbar-title-dot) { display: none; }
        }
    </style>
</head>

<body>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<div class="app">

    <!-- ── SIDEBAR ── -->
    <aside class="sidebar" id="sidebar">

        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-plane-departure"></i>
            </div>
            <div>
                <div class="brand-name">Panamericana IA</div>
                <div class="brand-sub">Agente inteligente</div>
            </div>
        </div>

        <div class="quick-actions">
            <button id="btn-new-chat" class="nav-btn primary" type="button">
                <i class="fa-regular fa-pen-to-square"></i>
                Nuevo chat
            </button>
            <button id="btn-history" class="nav-btn" type="button">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Historial
            </button>
        </div>

        <div class="nav-label">Accesos rápidos</div>

        <div class="quick-actions">
            <button class="nav-btn" type="button" data-message="Necesito agendar o consultar una reunión en el calendario.">
                <i class="fa-solid fa-calendar-days"></i>
                Agente Agenda
            </button>
            <button class="nav-btn" type="button" data-message="Necesito consultar o gestionar un informacion en ZOHO CRM.">
                <i class="fa-solid fa-handshake"></i>
                Agente CRM
            </button>
            <button class="nav-btn" type="button" data-message="Necesito abrir o consultar un caso de soporte en Desk.">
                <i class="fa-solid fa-headset"></i>
                Agente Desk
            </button>
            <button class="nav-btn" type="button" data-message="Necesito redactar, buscar o gestionar un correo en Gmail.">
                <i class="fa-solid fa-envelope"></i>
                Agente Gmail
            </button>
            <button class="nav-btn" type="button" data-message="Necesito soporte operativo.">
                <i class="fa-solid fa-folder-open"></i>
                Agente Soporte
            </button>
        </div>

        <div class="nav-label">Recientes</div>

        <div class="recents-scroll">
            <button class="recent-btn" type="button" data-message="Ayúdame a validar ventas en Zoho CRM.">Verificación de ventas Zoho CRM</button>
            <button class="recent-btn" type="button" data-message="¿Qué contiene este archivo?">Qué contiene este archivo</button>
            <button class="recent-btn" type="button" data-message="Necesito revisar un registro de inventario.">Consulta inventario</button>
            <button class="recent-btn" type="button" data-message="Necesito soporte operativo.">Soporte operativo</button>
            <button class="recent-btn" type="button" data-message="Validar adjuntos del chat.">Validación de adjuntos</button>
        </div>

        <div class="sidebar-footer">
            <div class="workspace-card">
                <div class="workspace-icon">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <div class="workspace-name">Panamericana Viajes</div>
                    <div class="workspace-role">Espacio de trabajo</div>
                </div>
            </div>
        </div>

    </aside>

    <!-- ── MAIN ── -->
    <main class="main">

        <header class="topbar">
            <div class="topbar-left">
                <button class="btn-menu" id="btn-menu" type="button" aria-label="Menú">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="topbar-title">
                    <span class="topbar-title-dot"></span>
                    <span>Panamericana de Viajes · Agente IA</span>
                </div>
            </div>

            <div class="topbar-right">

                <!-- Theme pill toggle -->
                <div class="theme-pill" role="group" aria-label="Tema">
                    <button class="theme-opt selected" id="btn-dark" type="button" title="Modo oscuro" aria-pressed="true">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                    <button class="theme-opt" id="btn-light" type="button" title="Modo claro" aria-pressed="false">
                        <i class="fa-solid fa-sun"></i>
                    </button>
                </div>

                <button id="btn-main-history" class="icon-btn" type="button" title="Historial">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </button>
                <button id="btn-main-new" class="icon-btn" type="button" title="Nuevo chat">
                    <i class="fa-regular fa-pen-to-square"></i>
                </button>
            </div>
        </header>

        <section class="chatkit-frame">
            <div class="chatkit-col">
                <div id="chat-mount"></div>
            </div>
        </section>

    </main>
</div>

<script>
(function () {
    /* ── THEME ────────────────────────────────────── */
    const html    = document.documentElement;
    const btnDark = document.getElementById('btn-dark');
    const btnLight = document.getElementById('btn-light');

    function setTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('pana-theme', theme);
        btnDark.classList.toggle('selected',  theme === 'dark');
        btnLight.classList.toggle('selected', theme === 'light');
        btnDark.setAttribute('aria-pressed',  theme === 'dark');
        btnLight.setAttribute('aria-pressed', theme === 'light');
    }

    // Restore saved preference
    const saved = localStorage.getItem('pana-theme');
    if (saved === 'light' || saved === 'dark') setTheme(saved);

    btnDark.addEventListener('click',  () => setTheme('dark'));
    btnLight.addEventListener('click', () => setTheme('light'));

    /* ── MOBILE SIDEBAR ───────────────────────────── */
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const btnMenu = document.getElementById('btn-menu');

    function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('open'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('open'); }

    btnMenu?.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
    overlay.addEventListener('click', closeSidebar);

    /* ── CHATKIT ──────────────────────────────────── */
    const mount = document.getElementById('chat-mount');
    let chatkitEl = null;

    function showError(msg) {
        console.error(msg);
        if (mount) mount.innerHTML = '<div class="chat-error">' + String(msg) + '</div>';
    }

    async function waitForChatKit() {
        for (let i = 0; i < 120; i++) {
            if (customElements.get('openai-chatkit')) return;
            await new Promise(r => setTimeout(r, 100));
        }
        throw new Error('openai-chatkit no quedó registrado.');
    }

    function getChatKitOptions() {
        return {
            api: {
                async getClientSecret() {
                    const r = await fetch('index.php?action=chat_session', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' }
                    });
                    const j = await r.json();
                    if (!j.client_secret) throw new Error('No llegó client_secret desde chat_session.');
                    return j.client_secret;
                }
            },
            composer: {
                placeholder: 'Escribe tu pregunta o adjunta un archivo...',
                attachments: { enabled: true, maxCount: 10, maxSize: 26214400 }
            }
        };
    }

    function applyOptions(el) {
        if (el && typeof el.setOptions === 'function') {
            el.setOptions(getChatKitOptions());
            return true;
        }
        return false;
    }

    async function newChat() {
        if (!chatkitEl) return;
        if (typeof chatkitEl.setThreadId === 'function') { chatkitEl.setThreadId(null); return; }
        location.reload();
    }

    async function showHistory() {
        if (!chatkitEl) return;
        if (typeof chatkitEl.showHistory === 'function') { chatkitEl.showHistory(); return; }
        console.warn('showHistory no disponible.');
    }

    async function sendQuick(text) {
        if (!chatkitEl || !text) return;
        if (typeof chatkitEl.sendUserMessage === 'function') {
            chatkitEl.sendUserMessage({ text, newThread: false });
            // Close sidebar on mobile after sending
            closeSidebar();
        }
    }

    function bindButtons() {
        document.getElementById('btn-new-chat')?.addEventListener('click', newChat);
        document.getElementById('btn-main-new')?.addEventListener('click', newChat);
        document.getElementById('btn-history')?.addEventListener('click', showHistory);
        document.getElementById('btn-main-history')?.addEventListener('click', showHistory);

        document.querySelectorAll('[data-message]').forEach(btn => {
            btn.addEventListener('click', () => sendQuick(btn.getAttribute('data-message')));
        });
    }

    async function initChatKit() {
        try {
            if (!mount) throw new Error('No existe #chat-mount');

            await waitForChatKit();

            mount.innerHTML = '<openai-chatkit></openai-chatkit>';
            chatkitEl = mount.querySelector('openai-chatkit');

            if (!chatkitEl) throw new Error('No se pudo crear openai-chatkit.');

            window.panamericanaChatKit = chatkitEl;

            applyOptions(chatkitEl);
            [500, 1500, 3000].forEach(ms => setTimeout(() => applyOptions(chatkitEl), ms));

            bindButtons();
        } catch (err) {
            showError('Error cargando el chat: ' + (err.message || err));
        }
    }

    /* ── ALTURA DINÁMICA DEL NAVBAR ERP ──────────────────
     * sidebar3.php inyecta un navbar cuya altura real puede variar.
     * Lo medimos al cargar y en cada resize, y actualizamos --erp-navbar-h
     * en el root para que los calc() de chatkit se recalculen solos.
     */
    function measureErpNavbar() {
        // El navbar del ERP (sidebar3.php) tiene class .navbar.fixed-top
        const erpNav = document.querySelector('nav.navbar.fixed-top, .navbar.fixed-top');
        let navbarH = 0;
        if (erpNav) {
            navbarH = erpNav.getBoundingClientRect().bottom;
        }
        if (navbarH <= 0) navbarH = 64; // fallback
        document.documentElement.style.setProperty('--erp-navbar-h', navbarH + 'px');
    }

    measureErpNavbar();
    window.addEventListener('resize', measureErpNavbar);

    window.addEventListener('load', () => {
        measureErpNavbar(); // re-medir cuando todo cargó (fuentes, imágenes del navbar, etc.)
        initChatKit();
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>