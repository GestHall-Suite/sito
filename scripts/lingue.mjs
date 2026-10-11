// Sito in due lingue (dopo `astro build`). Il sito si scrive in italiano; questo script crea:
//   dist/      → gesthallsuite.it : italiano alla radice, inglese in /en/
//   dist-com/  → gesthallsuite.com: inglese alla radice, italiano in /it/
// L'inglese si ottiene traducendo l'HTML con il dizionario i18n/en.json («testo italiano»: «traduzione»;
// chiavi con {1}, {2}… = parti variabili): nodi di testo interi, attributi visibili, meta di descrizione e
// le stringhe identiche nei <script>. Aggiunge hreflang, canonical e il selettore di lingua (data-lang-sw).
// `node scripts/lingue.mjs --mancanti` elenca i testi delle pagine senza traduzione.
import fs from 'node:fs';
import path from 'node:path';

const DIST = 'dist', COM = 'dist-com';
const DOMINI = { it: 'https://gesthallsuite.it', en: 'https://gesthallsuite.com' };
const ESCLUSI = /^(interno|cliente|api|docs\/guida|_astro|img)\//; // pagine non tradotte (restano solo in italiano)
const dizionario = JSON.parse(fs.readFileSync('i18n/en.json', 'utf8'));
const E = new Map(), P = [];
for (const [k0, v] of Object.entries(dizionario)) {
  const k = norm(k0);
  if (k.includes('{1}')) P.push([new RegExp('^' + k.replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace(/\\\{(\d)\\\}/g, '(.+?)') + '$'), v, k.replace(/\{\d\}/g, '').length]);
  else E.set(k, v);
}
P.sort((a, b) => b[2] - a[2]);
function norm(s) { return String(s).replace(/\s+/g, ' ').trim(); }
const dec = s => s.replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(+n)).replace(/&#x([0-9a-f]+);/gi, (_, n) => String.fromCodePoint(parseInt(n, 16)))
  .replace(/&quot;/g, '"').replace(/&#39;|&apos;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&');
const encT = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const encA = s => encT(s).replace(/"/g, '&quot;');
const mancanti = new Set();
function testo(s, conta = true) {
  const k = norm(s);
  if (!k || !/[A-Za-zÀ-ÿ]/.test(k)) return null;
  if (E.has(k)) return E.get(k);
  for (const [re, tr] of P) { const m = re.exec(k); if (m) return tr.replace(/\{(\d)\}/g, (_, n) => testo(m[+n] || '', false) ?? m[+n] ?? ''); }
  if (conta) mancanti.add(k);
  return null;
}
/* blocchi di una sola lingua: <tag data-solo="it|en">…</tag> (con i tag annidati dello stesso tipo) */
function blocchi(html, lang) {
  const out = [], re = new RegExp(`<(\\w+)\\b[^>]*\\sdata-solo="${lang}"[^>]*>`, 'g');
  let m;
  while ((m = re.exec(html))) {
    const tag = m[1], apri = new RegExp(`<${tag}\\b|</${tag}\\s*>`, 'gi');
    apri.lastIndex = m.index + m[0].length;
    let d = 1, x;
    while (d && (x = apri.exec(html))) d += x[0][1] === '/' ? -1 : 1;
    const fine = x ? x.index + x[0].length : html.length;
    out.push([m.index, fine]); re.lastIndex = fine;
  }
  return out;
}
function togli(html, lang) {
  let r = html;
  for (const [a, b] of blocchi(html, lang).reverse()) r = r.slice(0, a) + r.slice(b);
  return r;
}
function traduci(html0) {
  /* i blocchi già in inglese non passano dal dizionario */
  const html = togli(html0, 'it'), salvati = [];
  let base = html;
  for (const [a, b] of blocchi(html, 'en').reverse()) { salvati.unshift(html.slice(a, b)); base = base.slice(0, a) + `<!--solo-en-${salvati.length - 1}-->` + base.slice(b); }
  return traduciTesto(base).replace(/<!--solo-en-(\d+)-->/g, (_, i) => salvati[salvati.length - 1 - +i]);
}
function traduciTesto(html) {
  const parti = html.split(/(<(script|style|textarea|pre|code)\b[\s\S]*?<\/\2\s*>)/i);
  let out = '';
  for (let i = 0; i < parti.length; i++) {
    let seg = parti[i];
    if (i % 3 === 2) continue;
    if (i % 3 === 1) {
      if (/^<script/i.test(seg) && !/application\/ld\+json/i.test(seg))
        seg = seg.replace(/(['"`])((?:(?!\1)[^\\\n]|\\.)*)\1/g, (m, q, s) => { const t = E.get(norm(s)); return t !== undefined && s === norm(s) ? q + t.replace(new RegExp(q, 'g'), '\\' + q) + q : m; });
      out += seg; continue;
    }
    seg = seg.replace(/>([^<>]+)</g, (m, raw) => {
      if (!/[A-Za-zÀ-ÿ]/.test(raw)) return m;
      const tr = testo(dec(raw)); if (tr === null) return m;
      return '>' + /^\s*/.exec(raw)[0] + encT(tr) + /\s*$/.exec(raw)[0] + '<';
    });
    seg = seg.replace(/\b(title|alt|aria-label|placeholder|data-tip)="([^"]*)"/g, (m, a, v) => { const tr = testo(dec(v)); return tr === null ? m : `${a}="${encA(tr)}"`; });
    seg = seg.replace(/(<meta\s+(?:name|property)="(?:description|og:title|og:description|twitter:title|twitter:description)"\s+content=")([^"]*)"/g, (m, a, v) => { const tr = testo(dec(v)); return tr === null ? m : a + encA(tr) + '"'; });
    out += seg;
  }
  return out.replace('<html lang="it"', '<html lang="en"').replace('content="it_IT"', 'content="en_GB"');
}

/* pagine del sito (percorsi senza .html e senza index) */
function pagine(dir, base = '') {
  let r = [];
  for (const f of fs.readdirSync(path.join(dir, base), { withFileTypes: true })) {
    const rel = base ? `${base}/${f.name}` : f.name;
    if (f.isDirectory()) { if (!ESCLUSI.test(rel + '/') && rel !== 'en' && rel !== 'it') r = r.concat(pagine(dir, rel)); }
    else if (f.name.endsWith('.html') && !ESCLUSI.test(rel)) r.push(rel);
  }
  return r;
}
const url = rel => ('/' + rel.replace(/(^|\/)index\.html$/, '$1').replace(/\.html$/, '')).replace(/(.)\/$/, '$1');
function percorsi(html, prefisso, elenco) {
  /* i collegamenti alle pagine tradotte restano nella stessa lingua */
  return html.replace(/(href|action)="(\/[^"#?]*)([^"]*)"/g, (m, a, p, resto) => {
    const pulito = p.replace(/\/$/, '') || '/';
    return elenco.has(pulito) ? `${a}="${prefisso}${pulito === '/' ? '/' : pulito}${resto}"` : m;
  });
}
function testa(html, lang, pag, prefissoIt, prefissoEn, dominio) {
  const altIt = DOMINI.it + pag, altEn = DOMINI.en + pag;
  const link = `<link rel="alternate" hreflang="it" href="${altIt}"><link rel="alternate" hreflang="en" href="${altEn}"><link rel="alternate" hreflang="x-default" href="${altEn}">`;
  html = html.replace(/<link rel="canonical" href="[^"]*"\s*\/?>/, `<link rel="canonical" href="${lang === 'it' ? altIt : altEn}">` + link)
    .replace(/(<meta property="og:url" content=")[^"]*"/, `$1${lang === 'it' ? altIt : altEn}"`);
  /* selettore di lingua: porta alla stessa pagina nell'altra lingua, sullo stesso dominio */
  const altro = lang === 'it' ? prefissoEn + (pag === '/' ? '/' : pag) : prefissoIt + (pag === '/' ? '/' : pag);
  return html.replace(/<a\b[^>]*\bdata-lang-sw\b[^>]*>[^<]*</g, tag => tag
    .replace(/href="[^"]*"/, `href="${altro}"`)
    .replace(/hreflang="[^"]*"/, `hreflang="${lang === 'it' ? 'en' : 'it'}"`)
    .replace(/>[^<]*<$/, `>${lang === 'it' ? 'English' : 'Italiano'}<`));
}
function copia(src, dst, filtro = () => true) {
  fs.mkdirSync(dst, { recursive: true });
  for (const f of fs.readdirSync(src, { withFileTypes: true })) {
    const s = path.join(src, f.name), d = path.join(dst, f.name);
    if (!filtro(s)) continue;
    if (f.isDirectory()) copia(s, d, filtro); else fs.copyFileSync(s, d);
  }
}

const elenco = pagine(DIST);
const URLS = new Set(elenco.map(url));
/* documenti legali: il testo resta in italiano (versione vincolante), tradotti solo menu e piè di pagina */
const SOLO_IT = new Set(['/privacy', '/termini']);
if (process.argv.includes('--mancanti')) {
  for (const rel of elenco.filter(r => !SOLO_IT.has(url(r)))) traduci(fs.readFileSync(path.join(DIST, rel), 'utf8'));
  console.log([...mancanti].sort().join('\n'));
  console.error(`${mancanti.size} testi senza traduzione`);
  process.exit(0);
}
fs.rmSync(COM, { recursive: true, force: true });
/* .com: tutto il sito (risorse, api, guida…) poi le pagine in inglese alla radice */
copia(DIST, COM, s => !/\/(en|it)$/.test(s));
const originali = new Map(elenco.map(rel => [rel, fs.readFileSync(path.join(DIST, rel), 'utf8')]));
for (const [rel, html] of originali) {
  const pag = url(rel), en = traduci(html);
  const scrivi = (dir, contenuto) => { const f = path.join(dir, rel); fs.mkdirSync(path.dirname(f), { recursive: true }); fs.writeFileSync(f, contenuto); };
  scrivi(DIST, testa(togli(html, 'en'), 'it', pag, '', '/en', DOMINI.it));                       // .it  italiano
  scrivi(path.join(DIST, 'en'), testa(percorsi(en, '/en', URLS), 'en', pag, '', '/en', DOMINI.it)); // .it/en
  scrivi(COM, testa(en, 'en', pag, '/it', '', DOMINI.en));                          // .com inglese
  scrivi(path.join(COM, 'it'), testa(percorsi(togli(html, 'en'), '/it', URLS), 'it', pag, '/it', '', DOMINI.en)); // .com/it
}
/* sitemap e robots del .com */
const sm = [...URLS].map(u => `<url><loc>${DOMINI.en}${u}</loc></url>`).join('');
fs.writeFileSync(path.join(COM, 'sitemap-index.xml'), `<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${sm}</urlset>`);
for (const f of fs.readdirSync(COM)) if (/^sitemap-\d+\.xml$/.test(f)) fs.rmSync(path.join(COM, f));
if (fs.existsSync(path.join(COM, 'robots.txt'))) fs.writeFileSync(path.join(COM, 'robots.txt'), fs.readFileSync(path.join(COM, 'robots.txt'), 'utf8').replaceAll(DOMINI.it, DOMINI.en));
console.log(`Lingue: ${elenco.length} pagine · dist/ (it + /en/) · dist-com/ (en + /it/) · ${mancanti.size} testi ancora in italiano`);
