# CLAUDE.md — Sito gesthallsuite.it

Sito Astro statico (vedi README.md). Tono e pubblico in PRODUCT.md.

## Regola operativa

Ogni modifica è tracciata:

- **Novità del prodotto** → `src/data/changelog.json` (fonte unica: pagina `/changelog`, feed `/changelog.json` letto dalla suite, riquadro della Documentazione). Una release per ogni versione della suite (`GH_VERSION` in `suite/includes/lib.php`), testo per i clienti, 2-5 `highlights` brevi.
- **Funzionalità nuove o cambiate** → pagine `src/pages/funzionalita/*`, `src/data/features.ts`, guida `public/docs/guida/index.html`, prezzi in `index.astro` se cambia un piano.
- **Modifiche al sito stesso** → `CHANGELOG.md`.
- Dopo le modifiche: `npm run build` senza errori.
