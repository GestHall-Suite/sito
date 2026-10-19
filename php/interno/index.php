<?php
declare(strict_types=1);

/* Area interna del sito (/interno/): accesso controllato sul server.
   - password (hash in config.php, fuori da git) e, se configurato, codice dell'app di autenticazione (TOTP);
   - massimo 5 tentativi falliti per IP in 15 minuti, poi attese crescenti; tentativi registrati in dati/accessi.log;
   - sessione httponly, Secure, SameSite=Strict, 2 ore di inattività e 8 ore al massimo;
   - le pagine stanno in pagine/ (vietata al web da .htaccess) e si leggono solo da qui dopo l'accesso.
   Prima le pagine erano HTML pubblici con un controllo della password in JavaScript: chiunque poteva leggerle. */

const INTERNO_PAGINE = [
    'indice' => 'indice.html', 'stato' => 'stato.php', 'docs' => 'docs.html',
    'manutenzione' => 'manutenzione.html', 'vendita' => 'vendita.html', 'business-plan' => 'business-plan.html',
    'spazio' => 'spazio.html',
];
const INTERNO_MAX = 5;
const INTERNO_FINESTRA = 900;

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$cfgFile = __DIR__ . '/config.php';
$cfg = is_file($cfgFile) ? (array)require $cfgFile : [];
$hashPw = trim((string)($cfg['password_hash'] ?? ''));
/* Errori di configurazione comuni: hash incollato tra virgolette doppie (i $ vengono «mangiati» da PHP) o creato
   con la shell che ha interpretato i caratteri speciali della password. Il messaggio non rivela nulla del contenuto. */
$cfgErrore = '';
if (!is_file($cfgFile)) $cfgErrore = 'Area non configurata: manca interno/config.php (si crea da config.esempio.php o con la pubblicazione automatica).';
elseif ($hashPw === '') $cfgErrore = 'In interno/config.php manca password_hash.';
elseif (password_get_info($hashPw)['algo'] === null) $cfgErrore = 'Il valore di password_hash in interno/config.php non è un hash valido: rigeneralo (vedi config.esempio.php) e incollalo tra apici singoli \' \'.';
$totp = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', (string)($cfg['totp_segreto'] ?? '')));

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
session_name('gh_interno');
session_set_cookie_params(['lifetime' => 0, 'path' => '/interno', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
session_start(['use_strict_mode' => true, 'use_only_cookies' => true]);
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$h = fn(mixed $v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

function interno_tentativi(string $ip, bool $aggiungi = false): array {
    $dir = __DIR__ . '/dati';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $f = $dir . '/tentativi.json';
    $fp = @fopen($f, 'c+');
    if (!$fp) return [0, 0];
    flock($fp, LOCK_EX);
    $d = json_decode((string)stream_get_contents($fp), true) ?: [];
    foreach ($d as $k => $l) { $d[$k] = array_values(array_filter((array)$l, fn($t) => $t > time() - INTERNO_FINESTRA)); if (!$d[$k]) unset($d[$k]); }
    if ($aggiungi) $d[$ip][] = time();
    ftruncate($fp, 0); rewind($fp); fwrite($fp, json_encode($d)); flock($fp, LOCK_UN); fclose($fp);
    $l = $d[$ip] ?? [];
    return [count($l), $l ? max($l) : 0];
}

function interno_log(string $evento): void {
    @file_put_contents(__DIR__ . '/dati/accessi.log', date('c') . ' ' . ($_SERVER['REMOTE_ADDR'] ?? '-') . ' ' . $evento . "\n", FILE_APPEND | LOCK_EX);
}

function interno_totp_ok(string $segreto, string $codice): bool {
    $codice = preg_replace('/\D/', '', $codice);
    if (strlen($codice) !== 6 || $segreto === '') return false;
    $a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $bits = ''; $k = '';
    foreach (str_split($segreto) as $c) $bits .= str_pad(decbin((int)strpos($a, $c)), 5, '0', STR_PAD_LEFT);
    foreach (str_split($bits, 8) as $g) if (strlen($g) === 8) $k .= chr(bindec($g));
    $t = intdiv(time(), 30);
    for ($d = -1; $d <= 1; $d++) {
        if (($_SESSION['totp_ultimo'] ?? 0) >= $t + $d) continue;
        $hm = hash_hmac('sha1', pack('J', $t + $d), $k, true);
        $o = ord($hm[19]) & 15;
        $n = ((ord($hm[$o]) & 0x7f) << 24) | (ord($hm[$o + 1]) << 16) | (ord($hm[$o + 2]) << 8) | ord($hm[$o + 3]);
        if (hash_equals(str_pad((string)($n % 1000000), 6, '0', STR_PAD_LEFT), $codice)) return true;
    }
    return false;
}

/* Uscita */
if (($_GET['az'] ?? '') === 'logout') {
    $_SESSION = [];
    session_regenerate_id(true);
    header('Location: /interno/');
    exit;
}

/* Sessione scaduta: 2 ore senza attività o 8 ore dall'accesso */
if (!empty($_SESSION['ok']) && (time() - (int)($_SESSION['ultimo'] ?? 0) > 7200 || time() - (int)($_SESSION['dal'] ?? 0) > 28800)) {
    $_SESSION = ['csrf' => bin2hex(random_bytes(16))];
}

$errore = '';
if (empty($_SESSION['ok']) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    [$n, $ultimo] = interno_tentativi($ip);
    $attesa = $n >= 3 ? $ultimo + min(2 ** ($n - 2), 300) - time() : 0;
    if (!hash_equals((string)$_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $errore = 'La pagina era scaduta: riprova.';
    } elseif ($n >= INTERNO_MAX) {
        $errore = 'Troppi tentativi falliti. Riprova tra 15 minuti.';
    } elseif ($attesa > 0) {
        $errore = 'Troppi tentativi ravvicinati: riprova tra ' . $attesa . ' secondi.';
    } elseif ($cfgErrore === '' && password_verify((string)($_POST['password'] ?? ''), $hashPw)
              && ($totp === '' || interno_totp_ok($totp, (string)($_POST['codice'] ?? '')))) {
        session_regenerate_id(true);
        $_SESSION = ['ok' => true, 'dal' => time(), 'ultimo' => time(), 'csrf' => bin2hex(random_bytes(16)), 'totp_ultimo' => intdiv(time(), 30) + 1];
        interno_log('accesso');
        header('Location: /interno/');
        exit;
    } else {
        interno_tentativi($ip, true);
        interno_log('accesso_fallito');
        $errore = 'Password o codice non corretti.';
    }
}

if (!empty($_SESSION['ok'])) {
    $_SESSION['ultimo'] = time();
    $p = (string)($_GET['p'] ?? 'indice');
    $file = INTERNO_PAGINE[$p] ?? INTERNO_PAGINE['indice'];
    $esci = '<a href="/interno/?az=logout" style="position:fixed;right:14px;bottom:14px;z-index:99;padding:8px 14px;border-radius:99px;background:#101828;color:#fff;font:600 13px system-ui;text-decoration:none">Esci</a>';
    $avviso = $totp === '' ? '<div style="position:fixed;left:14px;bottom:14px;right:90px;z-index:99;padding:10px 14px;border-radius:12px;background:#fef3c7;color:#78350f;font:13px system-ui">Manca il codice dell\'app: in <code>interno/config.php</code> aggiungi <code>\'totp_segreto\' => \'' . $h(interno_b32()) . '\'</code> e inseriscilo nell\'app di autenticazione.</div>' : '';
    if (str_ends_with($file, '.php')) {
        define('INTERNO_OK', true);
        ob_start();
        require __DIR__ . '/pagine/' . $file;
        $html = (string)ob_get_clean();
    } else {
        $html = (string)file_get_contents(__DIR__ . '/pagine/' . $file);
    }
    echo str_replace('</body>', $esci . $avviso . '</body>', $html);
    exit;
}

function interno_b32(): string {
    $a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $s = '';
    foreach (str_split(random_bytes(20)) as $c) $s .= $a[ord($c) & 31];
    return $s;
}
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Area interna · GestHall Suite</title>
<meta name="robots" content="noindex, nofollow">
<style>
  *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f5f7;font:15px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif;color:#101828}
  form{width:min(360px,calc(100% - 32px));background:#fff;border:1px solid #e4e7ec;border-radius:16px;padding:28px;box-shadow:0 20px 40px -16px rgba(16,24,40,.18)}
  h1{font-size:20px;margin:0 0 4px}p{margin:0 0 18px;color:#5d6679;font-size:13.5px}
  label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#5d6679;margin:14px 0 6px}
  input{width:100%;padding:11px 12px;border:1px solid #d0d5dd;border-radius:10px;font:inherit}
  input:focus{outline:2px solid #00c391;border-color:transparent}
  button{width:100%;margin-top:20px;padding:12px;border:0;border-radius:10px;background:#00c391;color:#062a20;font:700 15px system-ui;cursor:pointer}
  .err{background:#fef2f2;color:#b91c1c;border-radius:10px;padding:10px 12px;font-size:13px;margin-bottom:6px}
  @media (prefers-color-scheme:dark){body{background:#0b0f15;color:#e5e7eb}form{background:#111827;border-color:#1f2937}input{background:#0b0f15;color:#e5e7eb;border-color:#374151}p,label{color:#9ca3af}}
</style>
</head>
<body>
<form method="post" autocomplete="on">
  <h1>Area interna</h1>
  <p>Documenti riservati di GestHall Suite.</p>
  <?php if ($cfgErrore !== ''): ?><div class="err"><?= $h($cfgErrore) ?></div><?php endif; ?>
  <?php if ($errore !== ''): ?><div class="err"><?= $h($errore) ?></div><?php endif; ?>
  <input type="hidden" name="csrf" value="<?= $h($_SESSION['csrf']) ?>">
  <label for="pw">Password</label>
  <input id="pw" type="password" name="password" required autocomplete="current-password" autofocus>
  <?php if ($totp !== ''): ?>
  <label for="cd">Codice dell'app</label>
  <input id="cd" name="codice" inputmode="numeric" autocomplete="one-time-code" maxlength="7" required placeholder="000000">
  <?php endif; ?>
  <button type="submit">Entra</button>
</form>
</body>
</html>
