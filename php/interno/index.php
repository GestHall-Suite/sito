<?php
// TEMP DEBUG — rimuovere dopo il test
error_reporting(E_ALL); ini_set('display_errors', '1');
declare(strict_types=1);

// ── Configurazione ──────────────────────────────────────────────────────────
// Genera l'hash con: php -r "echo password_hash('LA_TUA_PASSWORD', PASSWORD_BCRYPT);"
// e sostituisci la stringa qui sotto.
const INTERNO_PASS_HASH = '$2y$12$placeholder.change.this.before.deploying.to.production.ok';

const SESSION_NAME     = 'gh_interno';
const SESSION_LIFETIME = 28800; // 8 ore

// ── Sessione ─────────────────────────────────────────────────────────────────
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/interno',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

$h     = fn(mixed $v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$error = '';

// ── Logout ───────────────────────────────────────────────────────────────────
if (($_GET['az'] ?? '') === 'logout') {
    session_destroy();
    header('Location: /interno/');
    exit;
}

// ── POST login ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pwd   = $_POST['password'] ?? '';
    $valid = INTERNO_PASS_HASH !== '$2y$12$placeholder.change.this.before.deploying.to.production.ok'
          && password_verify($pwd, INTERNO_PASS_HASH);

    if ($valid) {
        session_regenerate_id(true);
        $_SESSION['interno_ok']  = true;
        $_SESSION['interno_ts']  = time();
        header('Location: /interno/');
        exit;
    }
    $error = 'Password non corretta.';
}

// ── Session check ─────────────────────────────────────────────────────────────
$authed = !empty($_SESSION['interno_ok'])
       && (time() - ($_SESSION['interno_ts'] ?? 0)) < SESSION_LIFETIME;

if (!$authed && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // will show login form below
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Area interna · GestHall Suite</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }
    html { -webkit-text-size-adjust: 100% }
    img, svg { display: block; max-width: 100% }
    a { color: inherit; text-decoration: none }
    button { cursor: pointer; font: inherit; border: none; background: none }

    :root {
      --bg:         oklch(99.5% 0.004 245);
      --surface:    oklch(97% 0.009 245);
      --surface-2:  oklch(94% 0.013 245);
      --border:     oklch(87% 0.022 245);
      --border-sub: oklch(92% 0.015 245);
      --text:       oklch(17% 0.045 245);
      --muted:      oklch(50% 0.042 245);
      --faint:      oklch(65% 0.028 245);
      --accent:     oklch(0.72 0.16 168);
      --accent-dim: oklch(0.62 0.13 168);
      --accent-sub: oklch(96% 0.04 168);
      --navy:       oklch(19% 0.075 245);
      --red:        oklch(0.55 0.22 27);
      --red-sub:    oklch(97% 0.015 27);
      --green:      oklch(0.6 0.18 145);
      --green-sub:  oklch(96% 0.02 145);
      --amber:      oklch(0.7 0.16 80);
      --amber-sub:  oklch(97% 0.02 80);
      --rx-m: 14px; --rx-l: 20px;
      --sh: 0 1px 3px oklch(17% 0.045 245 / .06), 0 4px 16px oklch(17% 0.045 245 / .06);
      --sh-lg: 0 8px 40px oklch(17% 0.045 245 / .12);
      --font-head: 'Bricolage Grotesque', system-ui, sans-serif;
      --font-body: 'Barlow', system-ui, sans-serif;
      --ease: cubic-bezier(0.19, 1, 0.22, 1);
    }

    html, body {
      background: var(--bg);
      color: var(--text);
      font-family: var(--font-body);
      font-size: 15px;
      line-height: 1.65;
      min-height: 100dvh;
    }

    h1, h2, h3, h4 {
      font-family: var(--font-head);
      line-height: 1.1;
      letter-spacing: -0.03em;
      text-wrap: balance;
    }

    /* ── Top bar ─────────────────────────────── */
    .top {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0 24px;
      height: 56px;
      background: var(--navy);
      color: #fff;
    }
    .top-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: var(--font-head);
      font-size: 15px;
      font-weight: 700;
      letter-spacing: -.02em;
      color: #fff;
    }
    .top-logo-sq {
      width: 28px; height: 28px; border-radius: 7px; background: var(--accent);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .top-badge {
      font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em;
      background: oklch(100% 0 0 / .12); color: oklch(90% 0.01 245);
      padding: 2px 8px; border-radius: 5px; margin-left: 4px;
    }
    .top-spacer { flex: 1 }
    .top-logout {
      font-size: 13px; font-weight: 500; color: oklch(80% 0.01 245);
      display: flex; align-items: center; gap: 6px; padding: 6px 12px;
      border: 1px solid oklch(100% 0 0 / .15); border-radius: 8px;
      transition: background .15s, color .15s;
    }
    .top-logout:hover { background: oklch(100% 0 0 / .08); color: #fff }

    /* ── Container ───────────────────────────── */
    .container { max-width: 1120px; margin: 0 auto; padding: 0 24px }

    /* ── Login page ──────────────────────────── */
    .login-wrap {
      min-height: calc(100dvh - 56px);
      display: flex; align-items: center; justify-content: center;
      padding: 40px 24px;
    }
    .login-card {
      width: 100%; max-width: 420px;
      background: var(--surface); border: 1px solid var(--border-sub);
      border-radius: var(--rx-l); box-shadow: var(--sh-lg);
      padding: 40px;
    }
    .login-logo {
      display: flex; align-items: center; gap: 10px;
      font-family: var(--font-head); font-size: 20px; font-weight: 700;
      letter-spacing: -.03em; margin-bottom: 28px;
    }
    .login-logo-sq {
      width: 36px; height: 36px; border-radius: 9px; background: var(--accent);
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .login-card h1 { font-size: 22px; font-weight: 800; margin-bottom: 6px }
    .login-sub { font-size: 14px; color: var(--muted); margin-bottom: 28px }
    .login-label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em }
    .login-input {
      width: 100%; padding: 11px 14px; border: 1px solid var(--border);
      border-radius: 10px; background: var(--bg); color: var(--text);
      font: inherit; font-size: 15px; outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .login-input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px oklch(0.72 0.16 168 / .15);
    }
    .login-btn {
      width: 100%; margin-top: 20px; padding: 13px;
      background: var(--accent); color: oklch(10% 0 0);
      border-radius: 11px; font-family: var(--font-body); font-size: 15px;
      font-weight: 700; cursor: pointer; transition: background .15s, transform .1s;
    }
    .login-btn:hover { background: oklch(0.77 0.16 168); transform: translateY(-1px) }
    .login-btn:active { transform: translateY(0) }
    .login-err {
      margin-bottom: 18px; padding: 11px 14px;
      background: var(--red-sub); border: 1px solid oklch(0.55 0.22 27 / .2);
      border-radius: 10px; color: var(--red); font-size: 13.5px; font-weight: 500;
    }
    .login-setup-warn {
      margin-top: 20px; padding: 12px 14px;
      background: var(--amber-sub); border: 1px solid oklch(0.7 0.16 80 / .25);
      border-radius: 10px; color: var(--amber); font-size: 12.5px; line-height: 1.55;
    }
    .login-setup-warn code {
      font-size: 11.5px; background: oklch(0.7 0.16 80 / .12);
      padding: 1px 5px; border-radius: 4px;
    }

    /* ── Docs content ────────────────────────── */
    .docs-header {
      padding: clamp(32px, 5vw, 56px) 0 clamp(20px, 3vw, 32px);
      border-bottom: 1px solid var(--border);
    }
    .docs-eyebrow {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .1em; color: var(--accent); margin-bottom: 10px;
    }
    .docs-header h1 {
      font-size: clamp(26px, 4vw, 38px); font-weight: 800; margin-bottom: 8px;
    }
    .docs-header p { font-size: 15px; color: var(--muted) }
    .docs-meta {
      display: flex; align-items: center; gap: 16px; margin-top: 14px;
      font-size: 12px; color: var(--faint);
    }
    .docs-meta-badge {
      padding: 2px 8px; border-radius: 5px; font-weight: 700; font-size: 11px;
      background: var(--surface-2); color: var(--muted); border: 1px solid var(--border-sub);
    }

    /* ── Sections ────────────────────────────── */
    .docs-body { padding: clamp(28px, 4vw, 48px) 0 clamp(48px, 7vw, 80px) }
    .docs-section { margin-bottom: 44px }
    .section-title {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .08em; color: var(--muted); margin-bottom: 16px;
      padding-bottom: 10px; border-bottom: 1px solid var(--border-sub);
    }

    /* ── Cards ───────────────────────────────── */
    .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px }
    .card {
      background: var(--surface); border: 1px solid var(--border-sub);
      border-radius: var(--rx-m); padding: 20px; box-shadow: var(--sh);
    }
    .card h3 { font-size: 14px; font-weight: 700; margin-bottom: 6px }
    .card p { font-size: 13px; color: var(--muted); line-height: 1.6 }
    .card-accent { border-left: 3px solid var(--accent) }

    /* ── Status table ────────────────────────── */
    .status-table { width: 100%; border-collapse: collapse; font-size: 13.5px }
    .status-table th {
      text-align: left; padding: 9px 14px; font-size: 11px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .04em; color: var(--faint);
      background: var(--surface-2); border-bottom: 1px solid var(--border);
    }
    .status-table td { padding: 11px 14px; border-bottom: 1px solid var(--border-sub) }
    .status-table tr:last-child td { border-bottom: none }
    .status-table-wrap {
      background: var(--surface); border: 1px solid var(--border-sub);
      border-radius: var(--rx-m); overflow: hidden; box-shadow: var(--sh);
    }

    /* ── Badges ──────────────────────────────── */
    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 20px;
    }
    .badge-green { background: var(--green-sub); color: var(--green) }
    .badge-amber { background: var(--amber-sub); color: var(--amber) }
    .badge-blue  { background: var(--accent-sub); color: var(--accent-dim) }
    .badge-muted { background: var(--surface-2); color: var(--muted); border: 1px solid var(--border-sub) }

    /* ── Procedure ──────────────────────────── */
    .proc { display: flex; flex-direction: column; gap: 0 }
    .proc-step {
      display: grid; grid-template-columns: 28px 1fr; gap: 16px;
      padding: 16px 0; border-bottom: 1px solid var(--border-sub);
    }
    .proc-step:last-child { border-bottom: none }
    .proc-num {
      width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
      background: var(--accent-sub); color: var(--accent); font-size: 12px;
      font-weight: 800; display: flex; align-items: center; justify-content: center;
    }
    .proc-body h4 { font-size: 14px; font-weight: 700; margin-bottom: 4px }
    .proc-body p  { font-size: 13px; color: var(--muted); line-height: 1.55 }
    .proc-body code {
      font-family: var(--font-mono, monospace); font-size: 12px;
      background: var(--surface-2); padding: 1px 5px; border-radius: 4px; color: var(--text);
    }
    .proc-body .cmd {
      display: block; margin-top: 8px; padding: 10px 13px;
      background: var(--navy); color: oklch(88% 0.01 168); border-radius: 8px;
      font-family: var(--font-mono, monospace); font-size: 12.5px; line-height: 1.7;
      word-break: break-all;
    }
    .proc-tab-wrap { overflow-x: auto }
    .proc-tab {
      width: 100%; border-collapse: collapse; font-size: 13px; min-width: 480px;
    }
    .proc-tab th {
      text-align: left; padding: 8px 12px; font-size: 11px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .04em; color: var(--faint);
      background: var(--surface-2); border-bottom: 1px solid var(--border);
    }
    .proc-tab td { padding: 10px 12px; border-bottom: 1px solid var(--border-sub); vertical-align: top }
    .proc-tab tr:last-child td { border-bottom: none }
    .proc-tab code {
      font-family: var(--font-mono, monospace); font-size: 11.5px;
      background: var(--surface-2); padding: 1px 5px; border-radius: 4px;
    }
    .alert-box {
      padding: 13px 16px; border-radius: 10px; font-size: 13px; line-height: 1.55;
      margin-bottom: 14px;
    }
    .alert-amber { background: var(--amber-sub); border: 1px solid oklch(0.7 0.16 80 / .2); color: oklch(0.42 0.1 70) }
    .alert-green { background: var(--green-sub); border: 1px solid oklch(0.6 0.18 145 / .2); color: oklch(0.38 0.12 145) }
    .alert-blue  { background: var(--accent-sub); border: 1px solid oklch(0.72 0.16 168 / .2); color: var(--accent-dim) }

    /* ── Roadmap ─────────────────────────────── */
    .roadmap { display: flex; flex-direction: column; gap: 0 }
    .roadmap-item {
      display: flex; gap: 20px; padding: 18px 0;
      border-bottom: 1px solid var(--border-sub);
    }
    .roadmap-item:last-child { border-bottom: none }
    .roadmap-q {
      flex-shrink: 0; width: 80px; font-size: 12px; font-weight: 700;
      color: var(--accent); padding-top: 3px;
    }
    .roadmap-body { flex: 1 }
    .roadmap-body h4 { font-size: 14px; font-weight: 700; margin-bottom: 4px }
    .roadmap-body p { font-size: 13px; color: var(--muted) }
    .roadmap-done { opacity: .55 }

    /* ── KPI inline ──────────────────────────── */
    .kpi-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px }
    .kpi {
      flex: 1 1 180px; background: var(--surface);
      border: 1px solid var(--border-sub); border-radius: var(--rx-m);
      padding: 16px 20px; box-shadow: var(--sh);
    }
    .kpi-val { font-family: var(--font-head); font-size: 28px; font-weight: 800; letter-spacing: -.04em; color: var(--text) }
    .kpi-label { font-size: 12px; color: var(--muted); margin-top: 2px }

    @media (max-width: 600px) {
      .login-card { padding: 28px 20px }
      .top { padding: 0 16px }
    }
  </style>
</head>
<body>

<!-- Top bar (sempre visibile) -->
<header class="top">
  <div class="top-logo">
    <div class="top-logo-sq" aria-hidden="true">
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
        <rect x="1" y="1" width="6" height="6" rx="1.5" fill="rgba(0,0,0,.4)"/>
        <rect x="9" y="1" width="6" height="6" rx="1.5" fill="rgba(0,0,0,.4)"/>
        <rect x="1" y="9" width="6" height="6" rx="1.5" fill="rgba(0,0,0,.4)"/>
        <rect x="9" y="9" width="6" height="6" rx="1.5" fill="rgba(0,0,0,.7)"/>
      </svg>
    </div>
    GestHall Suite
    <span class="top-badge">Interno</span>
  </div>
  <div class="top-spacer"></div>
  <?php if ($authed): ?>
  <a href="/interno/?az=logout" class="top-logout">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    Esci
  </a>
  <?php endif; ?>
</header>

<?php if (!$authed): ?>
<!-- ── Login form ──────────────────────────────────────────────────────────── -->
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
      <div class="login-logo-sq" aria-hidden="true">
        <svg width="20" height="20" viewBox="0 0 16 16" fill="none" aria-hidden="true">
          <rect x="1" y="1" width="6" height="6" rx="1.5" fill="rgba(0,0,0,.4)"/>
          <rect x="9" y="1" width="6" height="6" rx="1.5" fill="rgba(0,0,0,.4)"/>
          <rect x="1" y="9" width="6" height="6" rx="1.5" fill="rgba(0,0,0,.4)"/>
          <rect x="9" y="9" width="6" height="6" rx="1.5" fill="rgba(0,0,0,.7)"/>
        </svg>
      </div>
      GestHall Suite
    </div>
    <h1>Area interna</h1>
    <p class="login-sub">Documentazione tecnica e risorse riservate al team.</p>

    <?php if ($error): ?>
    <div class="login-err" role="alert"><?= $h($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <label class="login-label" for="pwd">Password</label>
      <input
        class="login-input"
        type="password"
        id="pwd"
        name="password"
        autocomplete="current-password"
        autofocus
        required
      >
      <button type="submit" class="login-btn">Accedi</button>
    </form>

    <?php if (INTERNO_PASS_HASH === '$2y$12$placeholder.change.this.before.deploying.to.production.ok'): ?>
    <div class="login-setup-warn">
      <strong>Setup richiesto.</strong> Genera l'hash della password con:<br>
      <code>php -r "echo password_hash('TUA_PASSWORD', PASSWORD_BCRYPT);"</code><br>
      e sostituisci la costante <code>INTERNO_PASS_HASH</code> in <code>interno/index.php</code>.
    </div>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>
<!-- ── Docs content ────────────────────────────────────────────────────────── -->
<div class="container">

  <div class="docs-header">
    <p class="docs-eyebrow">Team &amp; Partner</p>
    <h1>Area interna</h1>
    <p>Stato del progetto, architettura, roadmap e risorse riservate.</p>
    <div class="docs-meta">
      <span class="docs-meta-badge">Riservato</span>
      <span>Aggiornato luglio 2026</span>
    </div>
  </div>

  <div class="docs-body">

    <!-- Stato progetto -->
    <div class="docs-section">
      <h2 class="section-title">Stato progetto</h2>
      <div class="kpi-row">
        <div class="kpi"><div class="kpi-val">v1.x</div><div class="kpi-label">Versione produzione</div></div>
        <div class="kpi"><div class="kpi-val">PHP&nbsp;8+</div><div class="kpi-label">Runtime richiesto</div></div>
        <div class="kpi"><div class="kpi-val">3</div><div class="kpi-label">Piani attivi (Ess · Pro · Suite)</div></div>
        <div class="kpi"><div class="kpi-val">Hub&nbsp;v1</div><div class="kpi-label">License server</div></div>
      </div>
      <div class="status-table-wrap">
        <table class="status-table">
          <thead><tr><th>Componente</th><th>Stato</th><th>Note</th></tr></thead>
          <tbody>
            <tr><td>App gestionale (<code>suite/</code>)</td><td><span class="badge badge-green">✓ Produzione</span></td><td>Cassa, turni, AWP, dashboard, documenti, push, portale piano</td></tr>
            <tr><td>Hub license server (<code>hub/</code>)</td><td><span class="badge badge-green">✓ Produzione</span></td><td>API license, ghost login, pannello rivenditori, richieste piano</td></tr>
            <tr><td>Sito marketing (<code>sito/</code>)</td><td><span class="badge badge-blue">~ In sviluppo</span></td><td>Astro 7 · PHP <code>/interno/</code> e <code>/cliente/</code> già attivi</td></tr>
            <tr><td>Portale cliente cambio piano</td><td><span class="badge badge-blue">~ In attivazione</span></td><td>Richiede installation_key allineata tra suite e hub</td></tr>
            <tr><td>Billing (Stripe)</td><td><span class="badge badge-amber">⏳ Fase 2</span></td><td>Checkout → webhook → hub → email chiave</td></tr>
            <tr><td>License check in-app</td><td><span class="badge badge-amber">⏳ Fase 2</span></td><td>Call a hub API con cache 24h; fallback su impostazioni.piano</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Procedure operative -->
    <div class="docs-section">
      <h2 class="section-title">Procedure operative</h2>

      <div style="display:flex;flex-direction:column;gap:20px">

        <!-- superadmin_key -->
        <div class="status-table-wrap" style="padding:20px 24px">
          <h3 style="font-size:15px;font-weight:700;margin-bottom:4px">1 · Generare la superadmin_key</h3>
          <p style="font-size:13px;color:var(--muted);margin-bottom:16px">La chiave è generata <strong>dalla suite</strong> e comunicata al hub. Non va mai generata dal hub.</p>
          <div class="alert-blue" style="margin-bottom:16px">
            <strong>Percorso:</strong> nella suite, aprire <code>account/ghost_generate.php</code> — al primo accesso la chiave viene generata automaticamente e mostrata a schermo. Copiarla e comunicarla al supporto GestHall.
          </div>
          <div class="proc">
            <div class="proc-step">
              <div class="proc-num">1</div>
              <div class="proc-body">
                <h4>Suite → ghost_generate.php</h4>
                <p>Al primo accesso la chiave viene auto-generata da <code>ensure_superadmin_key()</code>, salvata in <code>impostazioni.superadmin_key</code> e mostrata una sola volta. Copiarla subito.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">2</div>
              <div class="proc-body">
                <h4>Hub → installazione.php → "Imposta chiave"</h4>
                <p>Incollare la chiave copiata nel campo <em>Superadmin key</em> della scheda installazione nel hub. Questo abilita il ghost login da quel momento.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">3</div>
              <div class="proc-body">
                <h4>Verifica</h4>
                <p>Da hub → installazione → "Genera link ghost login" — il link deve aprire la suite senza errori. Se dice "chiave non valida" le due copie non coincidono.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Nuova installazione -->
        <div class="status-table-wrap" style="padding:20px 24px">
          <h3 style="font-size:15px;font-weight:700;margin-bottom:4px">2 · Attivazione nuova installazione</h3>
          <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Flusso completo per collegare una nuova installazione suite al hub e abilitare portale cliente + ghost login.</p>
          <div class="proc">
            <div class="proc-step">
              <div class="proc-num">1</div>
              <div class="proc-body">
                <h4>Hub → nuova.php</h4>
                <p>Creare la scheda installazione. Il hub genera automaticamente la <code>chiave</code> (64 hex). Completare nome sala, URL, piano, scadenza. <em>Lasciare superadmin_key vuota per ora.</em></p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">2</div>
              <div class="proc-body">
                <h4>Installare la suite sul server del cliente</h4>
                <p>Setup via <code>install/setup.php</code>. Al termine eliminare la cartella <code>install/</code>.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">3</div>
              <div class="proc-body">
                <h4>Suite: generare superadmin_key</h4>
                <p>Aprire <code>account/ghost_generate.php</code> nella suite del cliente — la chiave viene generata e mostrata. Copiarla.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">4</div>
              <div class="proc-body">
                <h4>Hub → installazione.php → "Imposta chiave"</h4>
                <p>Incollare la superadmin_key copiata dalla suite. Da questo momento il ghost login funziona.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">5</div>
              <div class="proc-body">
                <h4>Hub → installazione.php → copiare la <code>chiave</code></h4>
                <p>Cliccare il bottone <em>Copia</em> accanto al campo chiave installazione.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">6</div>
              <div class="proc-body">
                <h4>Suite: Impostazioni → Piano → "Portale clienti"</h4>
                <p>Incollare la <code>chiave</code> copiata dal hub nel campo di testo e salvare. Da questo momento il portale cambio piano è attivo.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Installazione esistente -->
        <div class="status-table-wrap" style="padding:20px 24px">
          <h3 style="font-size:15px;font-weight:700;margin-bottom:4px">3 · Attivare il portale piano su installazione esistente</h3>
          <p style="font-size:13px;color:var(--muted);margin-bottom:16px">Per installazioni già attive con ghost login funzionante — solo la parte portale cliente manca.</p>
          <div class="alert-green" style="margin-bottom:16px">
            La <code>superadmin_key</code> è già allineata (ghost login funziona) — non toccarla.
          </div>
          <div class="proc">
            <div class="proc-step">
              <div class="proc-num">1</div>
              <div class="proc-body">
                <h4>Hub → installazione.php → copiare la <code>chiave</code></h4>
                <p>Bottone <em>Copia</em> accanto al campo chiave installazione nella scheda hub.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">2</div>
              <div class="proc-body">
                <h4>Suite: Impostazioni → Piano → "Portale clienti"</h4>
                <p>Incollare la chiave nel campo di testo e salvare. Il link "Gestisci piano online" appare immediatamente.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">3</div>
              <div class="proc-body">
                <h4>Query DB suite (se la colonna manca)</h4>
                <p>Lanciare una volta sul DB della suite del cliente se l'installazione è precedente all'aggiornamento:</p>
                <code class="cmd">INSERT IGNORE INTO impostazioni (chiave, valore) VALUES ('installation_key', '');</code>
              </div>
            </div>
          </div>
        </div>

        <!-- Chiavi: quadro sinottico -->
        <div class="status-table-wrap">
          <div style="padding:16px 20px 8px"><h3 style="font-size:14px;font-weight:700">Chiavi: quadro sinottico</h3></div>
          <div class="proc-tab-wrap">
          <table class="proc-tab">
            <thead><tr><th>Chiave</th><th>Chi la genera</th><th>Dove nel hub</th><th>Dove nella suite</th><th>Usata per</th></tr></thead>
            <tbody>
              <tr>
                <td><code>superadmin_key</code></td>
                <td><strong>Suite</strong> (ghost_generate.php)</td>
                <td>installazione.php → "Imposta chiave"</td>
                <td>impostazioni.superadmin_key</td>
                <td>Ghost login · firma link portale cliente</td>
              </tr>
              <tr>
                <td><code>chiave / installation_key</code></td>
                <td><strong>Hub</strong> (nuova.php, auto)</td>
                <td>installazione.php → campo "Chiave" (Copia)</td>
                <td>impostazioni.installation_key</td>
                <td>License API · portale cliente (identifica l'installazione)</td>
              </tr>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Dev locale -->
        <div class="status-table-wrap" style="padding:20px 24px">
          <h3 style="font-size:15px;font-weight:700;margin-bottom:4px">4 · Sviluppo locale — far funzionare /interno e /cliente</h3>
          <p style="font-size:13px;color:var(--muted);margin-bottom:14px">Il server <code>astro dev</code> non serve PHP. Per testare le pagine PHP localmente:</p>
          <div class="proc">
            <div class="proc-step">
              <div class="proc-num">A</div>
              <div class="proc-body">
                <h4>Opzione rapida — PHP server separato</h4>
                <p>Serve solo le pagine PHP, non il sito Astro. Utile per testare login e portale isolati.</p>
                <code class="cmd">cd sito/php &amp;&amp; php -S localhost:8080</code>
                <p style="margin-top:8px">Poi aprire <code>localhost:8080/interno/</code> e <code>localhost:8080/cliente/</code>.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">B</div>
              <div class="proc-body">
                <h4>Opzione completa — tutto da un unico server PHP</h4>
                <p>Build Astro + copia PHP nel dist + PHP server unificato. I link interni (da /docs a /interno) funzionano.</p>
                <code class="cmd">cd sito &amp;&amp; npm run build &amp;&amp; cp -r php/* dist/ &amp;&amp; php -S localhost:3000 -t dist</code>
                <p style="margin-top:8px">Aprire <code>localhost:3000</code> — tutto funziona sulla stessa origine.</p>
              </div>
            </div>
          </div>
          <div class="alert-amber" style="margin-top:14px">
            Il cookie di sessione in <code>interno/index.php</code> usa <code>'secure' =&gt; !empty($_SERVER['HTTPS'])</code> — in locale (HTTP) il cookie viene comunque impostato senza Secure, quindi il login funziona normalmente.
          </div>
        </div>

        <!-- Nuova installazione su sottodominio -->
        <div class="status-table-wrap" style="padding:20px 24px">
          <h3 style="font-size:15px;font-weight:700;margin-bottom:4px">5 · Attivazione nuovo cliente — sottodominio su SiteGround</h3>
          <p style="font-size:13px;color:var(--muted);margin-bottom:14px">Procedura completa: da "cliente firma il contratto" a "suite operativa". Il cliente non ha bisogno di un proprio server — tutto gira su <code>nomesala.gesthallsuite.it</code>.</p>
          <div class="proc">
            <div class="proc-step">
              <div class="proc-num">1</div>
              <div class="proc-body">
                <h4>Crea il sottodominio su SiteGround</h4>
                <p>Panel SiteGround → Domains → Subdomains → aggiungi <code>nomesala.gesthallsuite.it</code>. Scegli o crea una cartella dedicata (es. <code>public_html/nomesala/</code>). Il wildcard SSL <code>*.gesthallsuite.it</code> deve essere attivo — se non lo è, attivarlo in Security → SSL/TLS prima di questo passo.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">2</div>
              <div class="proc-body">
                <h4>Copia la suite nella cartella</h4>
                <p>Carica i file della suite (escludi <code>.env</code>, <code>config.php</code>, <code>uploads/</code> di altri clienti) nella cartella del sottodominio. Via File Manager SiteGround o SFTP.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">3</div>
              <div class="proc-body">
                <h4>Crea il database MySQL</h4>
                <p>Panel SiteGround → MySQL Databases → crea un nuovo DB e utente dedicati al cliente. Poi importa lo schema:</p>
                <code class="cmd">mysql -u utente -p nome_db &lt; install/schema.sql</code>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">4</div>
              <div class="proc-body">
                <h4>Configura <code>includes/config.php</code></h4>
                <p>Copia <code>includes/config.example.php</code> → <code>includes/config.php</code> e imposta le credenziali DB, il nome sala e la base URL (<code>https://nomesala.gesthallsuite.it/</code>).</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">5</div>
              <div class="proc-body">
                <h4>Crea la scheda installazione nel hub</h4>
                <p>Hub → Nuova installazione: inserisci nome sala, URL <code>https://nomesala.gesthallsuite.it/</code>, piano, scadenza. Il hub genera la <code>chiave</code> automaticamente.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">6</div>
              <div class="proc-body">
                <h4>Sincronizza le chiavi (segui la Procedura 2)</h4>
                <p>Completa la Procedura 2 (Attivazione nuova installazione) per collegare la suite al hub: superadmin_key e installation_key devono coincidere in entrambi i sistemi.</p>
              </div>
            </div>
            <div class="proc-step">
              <div class="proc-num">7</div>
              <div class="proc-body">
                <h4>Crea l'utente responsabile via ghost login</h4>
                <p>Hub → installazione → Genera link ghost login → aprilo nella suite → vai su Impostazioni → Utenti → crea l'account del cliente. Poi comunicagli le credenziali.</p>
              </div>
            </div>
          </div>
          <div class="alert-box alert-green" style="margin-top:14px">
            Il wildcard SSL <code>*.gesthallsuite.it</code> copre automaticamente ogni nuovo sottodominio. Non serve richiedere un certificato per ogni cliente.
          </div>
        </div>

      </div>
    </div>

    <!-- Migrazione dominio temporaneo → produzione -->
    <div class="docs-section">
      <h2 class="section-title">Migrazione dominio temporaneo → gesthallsuite.it</h2>
      <p style="font-size:14px;color:var(--muted);margin-bottom:20px">Guida completa per passare da <code>matteon26.sg-host.com</code> ai domini definitivi senza perdere DB e dati. Tutti i file e il DB rimangono nelle stesse cartelle SiteGround — si aggiornano solo i puntamenti DNS e i <code>config.php</code>.</p>

      <div class="status-table-wrap" style="padding:20px 24px;margin-bottom:16px">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:12px">Fase 1 — Configurazione DNS e SiteGround</h3>
        <div class="proc">
          <div class="proc-step">
            <div class="proc-num">1</div>
            <div class="proc-body">
              <h4>Aggiungi gesthallsuite.it all'account SiteGround</h4>
              <p>Site Tools → Domains → Add Domain → inserisci <code>gesthallsuite.it</code>. Come Document Root scegli la cartella che contiene il sito Astro già caricato (o lascia che SiteGround crei la cartella e poi sposti i file lì).</p>
              <p style="margin-top:6px">Se il dominio è stato acquistato su SiteGround, i nameserver sono già configurati e la propagazione è quasi istantanea. Se è stato acquistato altrove, punta i nameserver a SiteGround (<code>ns1.siteground.net</code> / <code>ns2.siteground.net</code>) e aspetta fino a 24–48h.</p>
            </div>
          </div>
          <div class="proc-step">
            <div class="proc-num">2</div>
            <div class="proc-body">
              <h4>Crea i sottodomini</h4>
              <p>Site Tools → Domains → Subdomains (o cPanel → Subdomains). Creali nell'ordine:</p>
              <ul style="margin:8px 0 0 0;padding-left:18px;font-size:13px;line-height:1.8">
                <li><code>hub.gesthallsuite.it</code> → Document root: cartella hub già esistente</li>
                <li><code>gamespalace.gesthallsuite.it</code> → Document root: cartella suite già esistente (quella del dominio temp)</li>
              </ul>
              <p style="margin-top:8px">SiteGround crea i record DNS A automaticamente per ogni sottodominio aggiunto.</p>
            </div>
          </div>
          <div class="proc-step">
            <div class="proc-num">3</div>
            <div class="proc-body">
              <h4>Attiva SSL wildcard <code>*.gesthallsuite.it</code></h4>
              <p>Security → SSL/TLS → Let's Encrypt → scegli il dominio <code>gesthallsuite.it</code> e seleziona l'opzione <strong>Wildcard</strong> (<code>*.gesthallsuite.it</code>). Questo copre hub, gamespalace e tutti i futuri sottodomini clienti in un unico certificato. Attivare solo dopo che il dominio principale ha propagato.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="status-table-wrap" style="padding:20px 24px;margin-bottom:16px">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:12px">Fase 2 — Aggiornamento configurazioni</h3>
        <div class="proc">
          <div class="proc-step">
            <div class="proc-num">4</div>
            <div class="proc-body">
              <h4>Aggiorna <code>config.php</code> della suite (gamespalace)</h4>
              <p>Nel file <code>suite/includes/config.php</code> cambia solo la <code>base_url</code>:</p>
              <code class="cmd">'base_url' =&gt; 'https://gamespalace.gesthallsuite.it/'</code>
              <p style="margin-top:8px">DB host, nome, utente e password rimangono identici — è lo stesso database di prima.</p>
            </div>
          </div>
          <div class="proc-step">
            <div class="proc-num">5</div>
            <div class="proc-body">
              <h4>Aggiorna <code>config.php</code> dell'hub</h4>
              <p>Nel file <code>hub/includes/config.php</code>:</p>
              <code class="cmd">'base_url' =&gt; 'https://hub.gesthallsuite.it/'</code>
              <p style="margin-top:8px">Anche qui il DB è lo stesso, solo l'URL cambia.</p>
            </div>
          </div>
          <div class="proc-step">
            <div class="proc-num">6</div>
            <div class="proc-body">
              <h4>Aggiorna l'URL installazione nel hub</h4>
              <p>Hub → scheda installazione Games Palace → modifica il campo URL da <code>https://matteon26.sg-host.com/</code> a <code>https://gamespalace.gesthallsuite.it/</code>. Questo aggiorna anche il target del ghost login.</p>
            </div>
          </div>
          <div class="proc-step">
            <div class="proc-num">7</div>
            <div class="proc-body">
              <h4>Aggiorna la superadmin_key nella suite (solo se cambia)</h4>
              <p>Se la superadmin_key è già sincronizzata tra hub e suite, non serve fare nulla. Il ghost login e il portale cliente continueranno a funzionare — usano la chiave, non l'URL.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="status-table-wrap" style="padding:20px 24px;margin-bottom:16px">
        <h3 style="font-size:15px;font-weight:700;margin-bottom:12px">Fase 3 — Test prima della propagazione completa</h3>
        <div class="proc">
          <div class="proc-step">
            <div class="proc-num">8</div>
            <div class="proc-body">
              <h4>Trova l'IP del server SiteGround</h4>
              <p>cPanel → Server Information → nota il valore <em>Server IP Address</em> (o Site Tools → Site → cPanel → Server Information).</p>
            </div>
          </div>
          <div class="proc-step">
            <div class="proc-num">9</div>
            <div class="proc-body">
              <h4>Modifica <code>/etc/hosts</code> sul tuo Mac</h4>
              <p>Apri il file (richiede sudo) e aggiungi le righe:</p>
              <code class="cmd">sudo nano /private/etc/hosts</code>
              <p style="margin-top:8px">Aggiungi in fondo (sostituisci con l'IP reale):</p>
              <pre style="background:var(--surface);border:1px solid var(--border);border-radius:6px;padding:10px;font-size:12px;margin-top:6px;overflow-x:auto">1.2.3.4  gesthallsuite.it
1.2.3.4  hub.gesthallsuite.it
1.2.3.4  gamespalace.gesthallsuite.it</pre>
              <p style="margin-top:6px">Salva con Ctrl+O → Enter → Ctrl+X. Svuota la cache DNS: <code class="cmd" style="display:inline;padding:2px 6px;font-size:11px">sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder</code></p>
            </div>
          </div>
          <div class="proc-step">
            <div class="proc-num">10</div>
            <div class="proc-body">
              <h4>Testa tutto sui nuovi domini</h4>
              <p>Con <code>/etc/hosts</code> modificato il tuo Mac risolve subito i nuovi domini. Verifica:</p>
              <ul style="margin:8px 0 0 0;padding-left:18px;font-size:13px;line-height:1.8">
                <li><code>https://gamespalace.gesthallsuite.it/</code> → login suite, tutti i dati presenti</li>
                <li><code>https://hub.gesthallsuite.it/</code> → login hub, installazioni visibili</li>
                <li>Ghost login da hub → apre la suite sul nuovo dominio</li>
                <li>SSL: lucchetto verde su tutti e tre i domini</li>
              </ul>
            </div>
          </div>
          <div class="proc-step">
            <div class="proc-num">11</div>
            <div class="proc-body">
              <h4>Rimuovi le righe da <code>/etc/hosts</code> dopo la propagazione</h4>
              <p>Quando <code>gesthallsuite.it</code> è propagato pubblicamente, rimuovi le righe dal file hosts — altrimenti il tuo Mac ignorerebbe eventuali cambi DNS futuri.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="alert-box alert-amber">
        <strong>Il dominio temporaneo <code>sg-host.com</code></strong> può essere ignorato o eliminato dopo aver verificato che tutto funziona sui nuovi domini. Non è necessario fare redirect: nessun link esterno dovrebbe puntarci.
      </div>
    </div>

    <!-- Architettura -->
    <div class="docs-section">
      <h2 class="section-title">Architettura</h2>
      <div class="card-grid">
        <div class="card card-accent">
          <h3>App gestionale (suite)</h3>
          <p>PHP 8+ / PDO / HTML CSS JS vanilla. Nessun framework. Una installazione per sala, ospitata su sottodominio GestHall (<code>nomesala.gesthallsuite.it</code>). White-label via <code>impostazioni.brand_*</code>. PJAX navigation (piano Suite).</p>
        </div>
        <div class="card card-accent">
          <h3>Hub (<code>hub.gesthallsuite.it</code>)</h3>
          <p>PHP 8+. License API pubblica (<code>/api/license.php?key=</code>). Ghost login HMAC-SHA256. Pannello superadmin + pannello rivenditore.</p>
        </div>
        <div class="card card-accent">
          <h3>Sito (<code>gesthallsuite.it</code>)</h3>
          <p>Astro 7 statico servito su Apache. PHP affiancato per <code>/interno/</code> e <code>/cliente/</code>. Stripe Checkout (Fase 2).</p>
        </div>
        <div class="card card-accent">
          <h3>Sicurezza</h3>
          <p>CSRF token su ogni POST. Prepared statement su tutte le query. XSS: <code>htmlspecialchars()</code> sistematico. Push VAPID ECDH puro PHP 8.1+.</p>
        </div>
      </div>

      <h3 style="font-size:15px;font-weight:700;margin:24px 0 12px">Mappa sottodomini</h3>
      <div class="proc-tab-wrap">
        <table class="proc-tab">
          <thead><tr><th>Sottodominio</th><th>Contenuto</th><th>Cartella su SiteGround</th></tr></thead>
          <tbody>
            <tr><td><code>gesthallsuite.it</code></td><td>Sito marketing (Astro build + PHP)</td><td><code>public_html/</code></td></tr>
            <tr><td><code>hub.gesthallsuite.it</code></td><td>Hub — pannello admin/rivenditori</td><td><code>public_html/hub/</code> o directory separata del sottodominio</td></tr>
            <tr><td><code>nomesala.gesthallsuite.it</code></td><td>Suite installazione cliente</td><td><code>public_html/nomesala/</code></td></tr>
            <tr><td><code>*.gesthallsuite.it</code></td><td>Wildcard SSL Let's Encrypt</td><td>Copre tutti i sottodomini clienti automaticamente</td></tr>
          </tbody>
        </table>
      </div>
      <div class="alert-box alert-blue" style="margin-top:12px">
        GestHall ospita tutte le installazioni — il cliente non ha bisogno di un proprio server. Su SiteGround: attivare il certificato wildcard <code>*.gesthallsuite.it</code> in Security → SSL/TLS; creare ogni sottodominio cliente in Domains → Subdomains.
      </div>
    </div>

    <!-- Roadmap -->
    <div class="docs-section">
      <h2 class="section-title">Roadmap</h2>
      <div class="status-table-wrap">
        <div class="roadmap" style="padding:0 20px">
          <div class="roadmap-item roadmap-done">
            <div class="roadmap-q">✓ Fatto</div>
            <div class="roadmap-body">
              <h4>Hub v1 — license server + rivenditori</h4>
              <p>API license pubblica, ghost login, pannello superadmin, schede rivenditori.</p>
            </div>
          </div>
          <div class="roadmap-item roadmap-done">
            <div class="roadmap-q">✓ Fatto</div>
            <div class="roadmap-body">
              <h4>Portale cliente + richieste piano</h4>
              <p>Link firmato HMAC da Impostazioni → Piano → <code>gesthallsuite.it/cliente/</code> → richiesta cambio piano → gestione hub (Richieste piano) → approvazione aggiorna <code>installazioni.piano</code>. Gestione rivenditori inclusa.</p>
            </div>
          </div>
          <div class="roadmap-item roadmap-done">
            <div class="roadmap-q">✓ Fatto</div>
            <div class="roadmap-body">
              <h4>Docs interne (<code>/interno/</code>) + portale cliente (<code>/cliente/</code>)</h4>
              <p>PHP affiancato al sito Astro. Login con bcrypt. Portale cliente con validazione HMAC token.</p>
            </div>
          </div>
          <div class="roadmap-item">
            <div class="roadmap-q">Q3 2026</div>
            <div class="roadmap-body">
              <h4>License check in-app</h4>
              <p>Call a <code>hub/api/license.php</code> con cache 24h. Fallback graceful su <code>impostazioni.piano</code>.</p>
            </div>
          </div>
          <div class="roadmap-item">
            <div class="roadmap-q">Q4 2026</div>
            <div class="roadmap-body">
              <h4>Billing Stripe</h4>
              <p>Checkout hosted → webhook → hub aggiorna piano → email con chiave di attivazione.</p>
            </div>
          </div>
          <div class="roadmap-item">
            <div class="roadmap-q">Q4 2026</div>
            <div class="roadmap-body">
              <h4>Multi-sala</h4>
              <p>Un account con più sedi: condivisione dashboard responsabile, report aggregati.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Business -->
    <div class="docs-section">
      <h2 class="section-title">Modello di business</h2>
      <div class="status-table-wrap">
        <table class="status-table">
          <thead><tr><th>Piano</th><th>Prezzo</th><th>Target</th><th>Rev. share rivenditore</th></tr></thead>
          <tbody>
            <tr><td><span class="badge badge-muted">Essenziale</span></td><td>€39/mese · €390/anno</td><td>Sale piccole, avvio</td><td>Configurabile (default 30%)</td></tr>
            <tr><td><span class="badge badge-blue">Pro</span></td><td>€69/mese · €690/anno</td><td>Sale con più operatori e moduli</td><td>Configurabile</td></tr>
            <tr><td><span class="badge badge-green">Suite</span></td><td>€99/mese · €990/anno</td><td>Catene, white-label</td><td>Configurabile</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Rivenditori -->
    <div class="docs-section">
      <h2 class="section-title">Programma rivenditori</h2>
      <div class="card-grid">
        <div class="card">
          <h3>Come funziona</h3>
          <p>Il superadmin crea una scheda rivenditore nell'hub, imposta la percentuale di revenue share e assegna le installazioni. Il rivenditore vede solo le proprie installazioni nel pannello hub.</p>
        </div>
        <div class="card">
          <h3>Limiti attuali</h3>
          <p>Il rivenditore non può creare installazioni né effettuare ghost login. Il cambio piano e l'approvazione delle richieste sono operazioni superadmin. Il billing automatico è Fase 2.</p>
        </div>
        <div class="card">
          <h3>Fase 2 — portale rivenditore</h3>
          <p>Self-service: il rivenditore potrà creare installazioni, generare chiavi di prova e vedere le commissioni maturate con integrazione Stripe Connect.</p>
        </div>
      </div>
    </div>

  </div><!-- /docs-body -->
</div><!-- /container -->
<?php endif; ?>

</body>
</html>
