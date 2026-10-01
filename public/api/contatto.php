<?php
/* Modulo contatti e richiesta demo del sito.
   Invia un'email a info@gesthallsuite.it. Protezioni: campo trappola, tempo minimo
   di compilazione, limite di 5 invii all'ora per indirizzo IP. */
declare(strict_types=1);
date_default_timezone_set('Europe/Rome');

const GH_TO   = 'info@gesthallsuite.it';
const GH_FROM = 'noreply@gesthallsuite.it';
const MOTIVI  = ['demo' => 'Richiesta demo', 'info' => 'Informazioni', 'rivenditore' => 'Programma rivenditori', 'assistenza' => 'Assistenza clienti', 'altro' => 'Altro'];

$json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

function esci(bool $ok, string $msg, int $code = 200): never {
    global $json;
    if ($json) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => $ok, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: /contatti?' . ($ok ? 'inviato=1' : 'errore=' . rawurlencode($msg)) . '#modulo', true, 303);
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') esci(false, 'Richiesta non valida.', 405);

$campo = fn(string $k, int $max) => mb_substr(trim(str_replace(["\r", "\0"], '', (string)($_POST[$k] ?? ''))), 0, $max);

if ($campo('sito_web', 200) !== '') esci(true, 'Messaggio inviato.');
$t = (int)($_POST['t'] ?? 0);
if ($t > 0 && time() - intdiv($t, 1000) < 3) esci(true, 'Messaggio inviato.');

$nome     = preg_replace('/\s+/', ' ', $campo('nome', 120));
$email    = $campo('email', 160);
$telefono = preg_replace('/[^\d +().\/-]/', '', $campo('telefono', 40));
$sala     = preg_replace('/\s+/', ' ', $campo('sala', 160));
$motivo   = array_key_exists($_POST['motivo'] ?? '', MOTIVI) ? $_POST['motivo'] : 'altro';
$piano    = preg_match('/^(essenziale|pro|suite|multisala)$/', (string)($_POST['piano'] ?? '')) ? $_POST['piano'] : '';
$messaggio = $campo('messaggio', 5000);

if ($nome === '' || $messaggio === '') esci(false, 'Indica nome e messaggio.', 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) esci(false, 'L\'indirizzo email non sembra corretto.', 422);
if (empty($_POST['privacy'])) esci(false, 'Serve il consenso al trattamento dei dati per risponderti.', 422);

$ip  = $_SERVER['REMOTE_ADDR'] ?? '0';
$dir = sys_get_temp_dir() . '/gh_contatti';
if (!is_dir($dir)) @mkdir($dir, 0700, true);
$file = $dir . '/' . hash('sha256', $ip . '|gh') . '.json';
$ora  = time();
$log  = array_values(array_filter((array)json_decode((string)@file_get_contents($file), true), fn($x) => is_int($x) && $x > $ora - 3600));
if (count($log) >= 5) esci(false, 'Troppi invii in poco tempo: riprova tra un\'ora o scrivi a ' . GH_TO . '.', 429);
$log[] = $ora;
@file_put_contents($file, json_encode($log), LOCK_EX);

$righe = [
    'Motivo'    => MOTIVI[$motivo] . ($piano ? " (piano $piano)" : ''),
    'Nome'      => $nome,
    'Email'     => $email,
    'Telefono'  => $telefono ?: '—',
    'Sala / azienda' => $sala ?: '—',
];
$corpo = '';
foreach ($righe as $k => $v) $corpo .= str_pad($k . ':', 16) . $v . "\n";
$corpo .= "\n" . $messaggio . "\n\n—\nInviato dal sito il " . date('d/m/Y H:i') . ' · IP ' . $ip . "\n";

$oggetto = '=?UTF-8?B?' . base64_encode('[Sito] ' . MOTIVI[$motivo] . ' — ' . $nome . ($sala ? " ($sala)" : '')) . '?=';
$headers = implode("\r\n", [
    'From: GestHall Suite <' . GH_FROM . '>',
    'Reply-To: ' . '=?UTF-8?B?' . base64_encode($nome) . '?= <' . $email . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: gesthallsuite.it',
]);

if (!@mail(GH_TO, $oggetto, $corpo, $headers, '-f' . GH_FROM)) {
    error_log('contatto.php: invio non riuscito per ' . $email);
    esci(false, 'Non siamo riusciti a inviare il messaggio. Scrivi direttamente a ' . GH_TO . '.', 500);
}
esci(true, 'Messaggio inviato. Ti rispondiamo entro un giorno lavorativo.');
