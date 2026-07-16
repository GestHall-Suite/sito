<?php
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
            <tr><td>App gestionale (<code>suite/</code>)</td><td><span class="badge badge-green">✓ Produzione</span></td><td>Cassa, turni, AWP, dashboard, documenti, push</td></tr>
            <tr><td>Hub license server (<code>hub/</code>)</td><td><span class="badge badge-green">✓ Produzione</span></td><td>API license, ghost login, pannello rivenditori</td></tr>
            <tr><td>Sito marketing (<code>sito/</code>)</td><td><span class="badge badge-blue">~ In sviluppo</span></td><td>Astro 7, portale cliente, ordini</td></tr>
            <tr><td>Billing (Stripe)</td><td><span class="badge badge-amber">⏳ Fase 2</span></td><td>Checkout → webhook → hub → email chiave</td></tr>
            <tr><td>License check in-app</td><td><span class="badge badge-amber">⏳ Fase 2</span></td><td>Call a hub API con cache 24h; fallback su impostazioni.piano</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Architettura -->
    <div class="docs-section">
      <h2 class="section-title">Architettura</h2>
      <div class="card-grid">
        <div class="card card-accent">
          <h3>App gestionale</h3>
          <p>PHP 8+ / PDO / HTML CSS JS vanilla. Nessun framework. Multi-tenant: una installazione per sala. White-label via <code>impostazioni.brand_*</code>. PJAX navigation (piano Suite).</p>
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
          <div class="roadmap-item">
            <div class="roadmap-q">Q3 2026</div>
            <div class="roadmap-body">
              <h4>Portale cliente + richieste piano</h4>
              <p>Link firmato da impostazioni → sito <code>/cliente/</code> → richiesta cambio piano → approvazione hub.</p>
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
