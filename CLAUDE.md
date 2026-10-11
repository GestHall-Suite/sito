# CLAUDE.md — Sito gesthallsuite.it

Sito Astro statico (vedi README.md). Tono e pubblico in PRODUCT.md.

## Regola operativa

Ogni modifica è tracciata:

- **Novità del prodotto** → `src/data/changelog.json` (fonte unica: pagina `/changelog`, feed `/changelog.json` letto dalla suite, riquadro della Documentazione). Una release per ogni versione della suite (`GH_VERSION` in `suite/includes/lib.php`), testo per i clienti, 2-5 `highlights` brevi.
- **Funzionalità nuove o cambiate** → pagine `src/pages/prodotto/*` (aree in `src/data/aree.ts`), `src/pages/sicurezza.astro` se tocca dati/accessi/aggiornamenti, guida `public/docs/guida/index.html`; prezzi, piani e tabella di confronto solo in `src/data/piani.ts`.
- **Schermate** → `public/img/prodotto/*.webp` (reali, dalla sala dimostrativa): rifarle quando l'interfaccia cambia in modo visibile.
- **Modifiche al sito stesso** → `CHANGELOG.md`.
- Dopo le modifiche: `npm run build` senza errori, nessuno scorrimento orizzontale a 390 px, tema chiaro e scuro.

## Lingue (.it / .com)

- Il sito si scrive in **italiano**. `npm run build` esegue anche `scripts/lingue.mjs`: `dist/` → gesthallsuite.it (inglese in `/en/`), `dist-com/` → gesthallsuite.com (italiano in `/it/`). Pubblicare le due cartelle sui due domini.
- Inglese = dizionario `i18n/en.json` («testo italiano»: «traduzione», `{1}` per le parti variabili). **Testo nuovo o cambiato → aggiungerlo al dizionario**: `node scripts/lingue.mjs --mancanti` (dopo la build) elenca ciò che manca e deve restare vuoto (tranne email/indirizzi).
- Contenuti diversi per lingua: elementi con `data-solo="it"` / `data-solo="en"` (es. changelog). Release del changelog: campo `en` con `title`, `highlights`, `groups` (lo legge anche la suite per il riquadro Novità in inglese).
- Privacy e termini: testo solo in italiano (versione vincolante) con nota in inglese.

## Stile

- Grafica: token e componenti in `src/styles/site.css` (stessi colori e raggi della suite). Niente font o script esterni. Nuovi blocchi = classi esistenti (`.split`, `.card`, `.grid`, `.facts`, `.faq`, `.cta`…) prima di scrivere CSS di pagina.
- Testi: italiano professionale e concreto, dal punto di vista del gestore. Niente testimonianze, numeri di clienti o statistiche inventati; ogni funzione descritta deve esistere nella suite e nel piano indicato.
