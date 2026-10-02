<?php
declare(strict_types=1);

/**
 * Portale cliente — gestione piano
 *
 * Accesso tramite link firmato HMAC generato da impostazioni.php (suite):
 *   /cliente/?key=CHIAVE&ts=TIMESTAMP&token=HMAC_SHA256
 *
 * Il link è valido per 1 ora. Dopo bisogna rigenarlo dall'app.
 */

const HUB_API_URL = 'https://hub.gesthallsuite.it/api/sito.php';

$h      = fn(mixed $v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$key    = trim($_GET['key']   ?? $_POST['key']   ?? '');
$ts     = (int) ($_GET['ts'] ?? $_POST['ts']     ?? 0);
$token  = trim($_GET['token'] ?? $_POST['token'] ?? '');

$error   = '';
$success = '';
$inst    = null; // dati installazione dal hub

// ── Helper cURL ──────────────────────────────────────────────────────────────

function hub_get(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err    = curl_error($ch);
    curl_close($ch);

    if ($err || !$body) return ['ok' => false, 'error' => 'network', 'msg' => $err ?: 'Nessuna risposta dal server.'];
    $data = json_decode($body, true);
    return is_array($data) ? $data : ['ok' => false, 'error' => 'parse'];
}

function hub_post(string $url, array $fields): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
    ]);
    $body = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err || !$body) return ['ok' => false, 'error' => 'network', 'msg' => $err ?: 'Nessuna risposta.'];
    $data = json_decode($body, true);
    return is_array($data) ? $data : ['ok' => false, 'error' => 'parse'];
}

// ── Params di base mancanti ───────────────────────────────────────────────────

$paramsMissing = $key === '' || $ts === 0 || $token === '';

// ── POST: richiesta cambio piano ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$paramsMissing) {
    $pianoRich = $_POST['piano']  ?? '';
    $saleRich  = max(2, min(50, (int)($_POST['sale'] ?? 2)));
    $email     = trim($_POST['email'] ?? '');
    $note      = trim($_POST['note']  ?? '');

    if ($pianoRich === '' || $email === '') {
        $error = 'Compila tutti i campi obbligatori.';
    } else {
        $res = hub_post(HUB_API_URL, [
            'az'    => 'richiesta',
            'key'   => $key,
            'ts'    => $ts,
            'token' => $token,
            'piano' => $pianoRich,
            'sale'  => $pianoRich === 'multisala' ? $saleRich : '',
            'email' => $email,
            'note'  => $note,
        ]);

        if ($res['ok'] ?? false) {
            $success = 'Richiesta inviata. Riceverai conferma via email entro 24 ore.';
        } elseif (($res['error'] ?? '') === 'token_expired') {
            $error = 'Il link è scaduto. Torna sull\'app in Impostazioni → Piano per generare un nuovo link.';
        } elseif (($res['error'] ?? '') === 'pending_exists') {
            $error = 'Hai già una richiesta in attesa. Attendi la conferma prima di richiedere un\'altra modifica.';
        } else {
            $error = $res['msg'] ?? 'Errore imprevisto. Riprova tra qualche minuto.';
        }
    }
}

// ── GET: info installazione ───────────────────────────────────────────────────
if (!$paramsMissing && $error === '' && $success === '') {
    $apiUrl = HUB_API_URL . '?' . http_build_query(['az' => 'info', 'key' => $key, 'ts' => $ts, 'token' => $token]);
    $res    = hub_get($apiUrl);

    if ($res['ok'] ?? false) {
        $inst = $res;
    } elseif (($res['error'] ?? '') === 'token_expired') {
        $error = 'Il link è scaduto. Torna sull\'app in Impostazioni → Piano per generare un nuovo link.';
    } else {
        $error = match ($res['error'] ?? '') {
            'not_found'     => 'Installazione non trovata: la chiave del gestionale (Impostazioni → Piano → Portale clienti) non corrisponde a quella registrata da GestHall. Contatta il supporto.',
            'invalid_token' => 'Firma del link non valida: la chiave di sicurezza del gestionale non corrisponde a quella registrata da GestHall. Contatta il supporto.',
            'no_secret'     => 'Il portale non è ancora attivo per questa installazione. Contatta il supporto GestHall.',
            'disabled'      => 'Installazione disabilitata. Contatta il supporto GestHall.',
            'network'       => 'Servizio momentaneamente non raggiungibile. Riprova tra qualche minuto.',
            default         => $res['msg'] ?? 'Impossibile verificare l\'installazione. Link non valido.',
        };
    }
}

// ── Piano labels / prezzi ────────────────────────────────────────────────────
$pianiInfo = [
    'essenziale' => [
        'label' => 'Essenziale',
        'price' => '€39/mese',
        'desc'  => 'Cassa, turni, AWP, dashboard. Fino a 4 operatori.',
        'color' => 'oklch(0.60 0.01 245)',
    ],
    'pro' => [
        'label' => 'Pro',
        'price' => '€69/mese',
        'desc'  => 'Tutto Essenziale + ticket, anagrafica, prestiti, documenti, notifiche push, firma digitale, confronto periodi.',
        'color' => 'oklch(0.72 0.16 168)',
    ],
    'suite' => [
        'label' => 'Suite',
        'price' => '€99/mese',
        'desc'  => 'Tutto Pro + white-label (logo e colori), chat, LUL, passaggio consegne, SONOS, radio web, supporto prioritario.',
        'color' => 'oklch(0.55 0.22 290)',
    ],
    'multisala' => [
        'label' => 'Multi-sala',
        'price' => '€149/mese · 2 sale incluse, +€49 per sala',
        'desc'  => 'Tutto Suite + più sale nello stesso account: selettore sala, panoramica di tutte le sale, utenti e impostazioni per sala.',
        'color' => 'oklch(0.55 0.12 210)',
    ],
];
$saleAttuali = max(1, (int)($inst['sale_max'] ?? 1));
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Area clienti — GestHall Suite</title>
  <meta name="robots" content="noindex, nofollow">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0 }

    :root {
      --accent:    #00c391;
      --accent-dk: #00785a;
      --accent-weak: #e3f7f0;
      --navy:      #101828;
      --ink:       #101828;
      --muted:     #5d6679;
      --border:    #e4e7ec;
      --bg:        #f4f5f7;
      --surface:   #fff;
      --red:       #d92d20;
      --amber:     #b54708;
      --green:     #00785a;
      --purple:    #6941c6;
      --radius:    12px;
      --sh:        0 1px 3px rgba(16,24,40,.06), 0 8px 24px -6px rgba(16,24,40,.08);
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: var(--bg);
      color: var(--ink);
      min-height: 100dvh;
      padding: 32px 16px 64px;
    }

    .wrap {
      max-width: 560px;
      margin: 0 auto;
    }

    /* Header */
    .cl-header {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 32px;
    }
    .cl-logo {
      width: 42px;
      height: 42px;
      flex-shrink: 0;
    }
    .cl-header-text strong {
      display: block;
      font-size: 18px;
      font-weight: 800;
      color: var(--navy);
      line-height: 1.2;
    }
    .cl-header-text span {
      font-size: 13px;
      color: var(--muted);
    }

    /* Cards */
    .cl-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--sh);
      padding: 24px;
      margin-bottom: 16px;
    }
    .cl-card-title {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: var(--muted);
      margin-bottom: 12px;
    }

    /* Stato piano attuale */
    .cl-piano-attuale {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .cl-piano-badge {
      display: inline-flex;
      align-items: center;
      padding: 4px 14px;
      border-radius: 20px;
      font-size: 13px;
      font-weight: 800;
      color: #fff;
      white-space: nowrap;
    }
    .cl-piano-info strong {
      display: block;
      font-size: 16px;
      font-weight: 700;
      color: var(--ink);
    }
    .cl-piano-info span {
      font-size: 13px;
      color: var(--muted);
    }
    .cl-scadenza {
      margin-top: 10px;
      font-size: 13px;
      color: var(--muted);
    }
    .cl-scadenza strong { color: var(--ink) }
    .cl-scaduta { color: var(--red) !important }

    /* Alert */
    .cl-alert {
      padding: 14px 16px;
      border-radius: var(--radius);
      font-size: 13.5px;
      margin-bottom: 16px;
      line-height: 1.5;
    }
    .cl-alert-err  { background: oklch(0.97 0.02 25); border: 1px solid oklch(0.85 0.08 25); color: oklch(0.38 0.15 25) }
    .cl-alert-ok   { background: oklch(0.97 0.02 168); border: 1px solid oklch(0.85 0.08 168); color: oklch(0.38 0.15 168) }
    .cl-alert-warn { background: oklch(0.97 0.02 70); border: 1px solid oklch(0.85 0.08 70); color: oklch(0.40 0.10 70) }

    /* Form cambio piano */
    .cl-piani-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 20px;
    }
    .cl-piano-opt {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      padding: 14px 16px;
      border: 2px solid var(--border);
      border-radius: 9px;
      cursor: pointer;
      transition: border-color .12s, background .12s;
      background: var(--surface);
    }
    .cl-piano-opt:has(input:checked) {
      border-color: var(--accent);
      background: oklch(0.97 0.02 168);
    }
    .cl-piano-opt input[type="radio"] {
      margin-top: 2px;
      flex-shrink: 0;
      accent-color: var(--accent);
      width: 16px;
      height: 16px;
    }
    .cl-piano-opt-body strong {
      display: block;
      font-size: 14px;
      font-weight: 700;
      color: var(--ink);
    }
    .cl-piano-opt-price {
      font-size: 12.5px;
      color: var(--accent-dk);
      font-weight: 600;
    }
    .cl-piano-opt-desc {
      font-size: 12.5px;
      color: var(--muted);
      margin-top: 2px;
      line-height: 1.4;
    }
    .cl-piano-opt[data-current] {
      opacity: .6;
      cursor: not-allowed;
    }
    .cl-piano-opt[data-current] input { pointer-events: none }
    .cl-sale-price { font-size: 13px; color: oklch(0.45 0.12 210); font-weight: 600; margin-top: 6px }
    .cl-current-tag {
      display: inline-block;
      font-size: 10.5px;
      font-weight: 700;
      background: var(--border);
      color: var(--muted);
      padding: 1px 7px;
      border-radius: 20px;
      margin-left: 6px;
    }

    .cl-field {
      margin-bottom: 14px;
    }
    .cl-label {
      display: block;
      font-size: 12.5px;
      font-weight: 700;
      color: var(--ink);
      margin-bottom: 5px;
    }
    .cl-label span { color: var(--red) }
    .cl-input, .cl-textarea {
      width: 100%;
      padding: 10px 13px;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      background: var(--bg);
      color: var(--ink);
      font: inherit;
      font-size: 14px;
      outline: none;
      transition: border-color .12s;
    }
    .cl-input:focus, .cl-textarea:focus { border-color: var(--accent) }
    .cl-textarea { resize: vertical; min-height: 80px }

    .cl-submit {
      width: 100%;
      padding: 13px;
      background: var(--accent);
      color: #fff;
      font: inherit;
      font-size: 15px;
      font-weight: 700;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background .12s;
    }
    .cl-submit:hover { background: var(--accent-dk) }

    /* Missing / error states */
    .cl-empty {
      text-align: center;
      padding: 48px 24px;
      color: var(--muted);
    }
    .cl-empty h2 { font-size: 16px; color: var(--ink); margin-bottom: 8px }
    .cl-empty p  { font-size: 14px; line-height: 1.5 }

    /* Rivenditore note */
    .cl-rivend-note {
      font-size: 12.5px;
      color: var(--muted);
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 7px;
      padding: 10px 13px;
      margin-top: 12px;
      line-height: 1.5;
    }

    /* Footer */
    .cl-footer {
      text-align: center;
      font-size: 12.5px;
      color: var(--muted);
      margin-top: 32px;
    }
    .cl-intro h1 { font-size: 24px; letter-spacing: -.02em; margin-bottom: 10px }
    .cl-intro p { color: var(--muted); line-height: 1.65; margin-bottom: 16px }
    .cl-steps { margin: 0 0 16px 20px; display: grid; gap: 8px; line-height: 1.55 }
    .cl-note { font-size: 14px }
    .cl-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px }
    .cl-btn { display: inline-flex; align-items: center; min-height: 44px; padding: 0 18px; border-radius: 12px; background: var(--accent); color: #fff; font-weight: 600; text-decoration: none }
    .cl-btn-ghost { background: var(--surface); color: var(--ink); border: 1px solid #d0d5dd }
  </style>
</head>
<body>
<div class="wrap">

  <header class="cl-header">
    <svg class="cl-logo" viewBox="0 0 42 42" aria-hidden="true">
      <rect width="42" height="42" rx="10" fill="#00c391"/>
      <rect x="7" y="7" width="11" height="12" rx="2.5" fill="rgba(0,0,0,0.28)"/>
      <rect x="24" y="7" width="11" height="12" rx="2.5" fill="rgba(0,0,0,0.28)"/>
      <rect x="7" y="23" width="11" height="12" rx="2.5" fill="rgba(0,0,0,0.28)"/>
      <rect x="24" y="23" width="11" height="12" rx="2.5" fill="rgba(0,0,0,0.72)"/>
    </svg>
    <div class="cl-header-text">
      <strong>GestHall Suite</strong>
      <span>Area clienti</span>
    </div>
  </header>

  <?php if ($paramsMissing): ?>

  <div class="cl-card">
    <div class="cl-intro">
      <h1>Area clienti</h1>
      <p>Qui vedi piano, scadenza e numero di sale della tua installazione e puoi chiedere di cambiare piano. Per proteggere il tuo account l'accesso avviene solo dal gestionale, con un collegamento personale valido un'ora: niente password da ricordare.</p>
      <ol class="cl-steps">
        <li>Accedi a GestHall Suite come <strong>responsabile</strong>.</li>
        <li>Apri <strong>Impostazioni → Piano e licenza</strong>.</li>
        <li>Premi <strong>«Gestisci piano online»</strong>: si apre questa pagina con i dati della tua sala.</li>
      </ol>
      <p class="cl-note">Il pulsante non c'è? Serve la chiave dell'installazione: la trovi nell'email di attivazione o puoi chiederla al supporto.</p>
      <div class="cl-actions">
        <a class="cl-btn" href="/contatti?motivo=assistenza">Scrivi al supporto</a>
        <a class="cl-btn cl-btn-ghost" href="/docs/guida/#impostazioni">Guida all'uso</a>
        <a class="cl-btn cl-btn-ghost" href="/prezzi">Piani e prezzi</a>
      </div>
    </div>
  </div>

  <?php elseif ($error !== ''): ?>

  <div class="cl-alert cl-alert-err"><?= $h($error) ?></div>
  <div class="cl-card">
    <div class="cl-empty">
      <h2>Impossibile procedere</h2>
      <p>Il link potrebbe essere scaduto o non valido.<br>
         Generane uno nuovo dall'app in <strong>Impostazioni → Piano</strong>.</p>
    </div>
  </div>

  <?php elseif ($success !== ''): ?>

  <div class="cl-alert cl-alert-ok"><?= $h($success) ?></div>
  <div class="cl-card">
    <div class="cl-empty">
      <h2>Tutto fatto!</h2>
      <p>La tua richiesta è stata inviata al team GestHall.<br>
         Riceverai conferma non appena il piano sarà aggiornato.</p>
    </div>
  </div>

  <?php elseif ($inst): ?>

  <?php if ($inst['scaduta'] ?? false): ?>
  <div class="cl-alert cl-alert-warn">
    <strong>Abbonamento scaduto.</strong>
    Puoi comunque richiedere il rinnovo selezionando il piano desiderato qui sotto.
  </div>
  <?php endif; ?>

  <!-- Piano attuale -->
  <div class="cl-card">
    <div class="cl-card-title">Installazione</div>
    <div class="cl-piano-attuale">
      <?php
        $pi = $pianiInfo[$inst['piano']] ?? $pianiInfo['pro'];
      ?>
      <span class="cl-piano-badge" style="background:<?= $pi['color'] ?>"><?= $h($pi['label']) ?></span>
      <div class="cl-piano-info">
        <strong><?= $h($inst['nome_sala']) ?></strong>
        <span>Piano <?= $h($pi['label']) ?><?php if (($inst['piano'] ?? '') === 'multisala'): ?> · <?= $saleAttuali ?> sale<?php endif; ?> — <?= isset($inst['prezzo']) ? '€' . number_format((float)$inst['prezzo'], 0, ',', '.') . '/mese' : $pi['price'] ?></span>
      </div>
    </div>
    <div class="cl-scadenza">
      Scadenza:
      <strong class="<?= ($inst['scaduta'] ?? false) ? 'cl-scaduta' : '' ?>">
        <?= $h(date('d/m/Y', strtotime($inst['scadenza']))) ?>
        <?= ($inst['scaduta'] ?? false) ? ' — scaduto' : '' ?>
      </strong>
    </div>
    <?php if ($inst['rivenditore'] ?? null): ?>
    <div class="cl-rivend-note">
      Installazione gestita da <strong><?= $h($inst['rivenditore']) ?></strong>.<br>
      La modifica del piano sarà coordinata con il tuo rivenditore.
    </div>
    <?php endif; ?>
  </div>

  <!-- Form cambio piano -->
  <div class="cl-card">
    <div class="cl-card-title">Modifica piano</div>
    <form method="post">
      <input type="hidden" name="key"   value="<?= $h($key) ?>">
      <input type="hidden" name="ts"    value="<?= $h((string)$ts) ?>">
      <input type="hidden" name="token" value="<?= $h($token) ?>">

      <div class="cl-piani-list">
        <?php foreach ($pianiInfo as $pKey => $p): ?>
        <?php
          $isCurrent = $pKey === $inst['piano'];
          $lock      = $isCurrent && $pKey !== 'multisala'; /* Multi-sala: si può cambiare il numero di sale */
        ?>
        <label class="cl-piano-opt" <?= $lock ? 'data-current' : '' ?>>
          <input type="radio" name="piano" value="<?= $h($pKey) ?>"
                 <?= $isCurrent ? 'checked' : '' ?> <?= $lock ? 'disabled' : '' ?>>
          <div class="cl-piano-opt-body">
            <strong>
              <?= $h($p['label']) ?>
              <span class="cl-piano-opt-price"><?= $p['price'] ?></span>
              <?= $isCurrent ? '<span class="cl-current-tag">piano attivo</span>' : '' ?>
            </strong>
            <div class="cl-piano-opt-desc"><?= $h($p['desc']) ?></div>
          </div>
        </label>
        <?php endforeach; ?>
      </div>

      <div class="cl-field" id="cl-sale-field" <?= ($inst['piano'] ?? '') === 'multisala' ? '' : 'hidden' ?>>
        <label class="cl-label" for="cl-sale">Numero di sale</label>
        <input class="cl-input" type="number" id="cl-sale" name="sale" min="2" max="50"
               value="<?= max(2, $saleAttuali) ?>" style="max-width:120px">
        <div class="cl-sale-price" id="cl-sale-price"></div>
      </div>

      <div class="cl-field">
        <label class="cl-label" for="cl-email">Email di contatto <span>*</span></label>
        <input class="cl-input" type="email" id="cl-email" name="email"
               placeholder="la-tua@email.it" required autocomplete="email">
      </div>

      <div class="cl-field">
        <label class="cl-label" for="cl-note">Note per il team (opzionale)</label>
        <textarea class="cl-textarea" id="cl-note" name="note"
                  placeholder="Motivo del cambio, domande…"></textarea>
      </div>

      <button type="submit" class="cl-submit">Invia richiesta</button>
    </form>
    <script>
    (function () {
      var field = document.getElementById('cl-sale-field'), inp = document.getElementById('cl-sale'), out = document.getElementById('cl-sale-price');
      function upd() {
        var sel = document.querySelector('input[name=piano]:checked');
        field.hidden = !sel || sel.value !== 'multisala';
        var n = Math.max(2, Math.min(50, parseInt(inp.value, 10) || 2));
        out.textContent = '€' + (149 + 49 * (n - 2)) + '/mese per ' + n + ' sale';
      }
      document.querySelectorAll('input[name=piano]').forEach(function (r) { r.addEventListener('change', upd); });
      inp.addEventListener('input', upd);
      upd();
    })();
    </script>
  </div>

  <?php endif; ?>

  <p class="cl-footer">GestHall Suite &middot; Portale riservato ai clienti attivi</p>
</div>
</body>
</html>
