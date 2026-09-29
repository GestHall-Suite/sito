# GestHall Suite — Sito marketing

Sito istituzionale e di vendita per GestHall Suite, deployato su `gesthallsuite.it`. Costruito con **Astro 7** — output statico puro, zero JS nel bundle (a parte gli script inline per animazioni e interazioni leggere).

---

## Stack

| Componente | Tecnologia |
|---|---|
| Framework | [Astro 7](https://astro.build) — static site generation |
| Stili | CSS vanilla inline (no framework) |
| Font | System stack (`-apple-system`, `Segoe UI`, …) + Google Fonts via `@font-face` data URI |
| Sitemap | `@astrojs/sitemap` (generato automaticamente al build) |
| Deploy | Hosting statico qualsiasi (Netlify, Cloudflare Pages, SiteGround, …) |

---

## Struttura

```
sito/
│
├── src/
│   ├── pages/
│   │   ├── index.astro       Home: hero, features bento, pricing, resellers, CTA
│   │   ├── contatti.astro    Pagina contatti / richiesta demo
│   │   ├── privacy.astro     Privacy policy
│   │   └── 404.astro         Pagina 404 custom
│   │
│   ├── components/
│   │   ├── Nav.astro          Navbar top con links e CTA
│   │   └── Footer.astro       Footer con links e copyright
│   │
│   └── layouts/
│       └── Base.astro         Layout base: `<head>`, meta, OG tags, font
│
├── public/
│   ├── favicon.svg
│   └── og-image.png           Immagine Open Graph (1200×630)
│
├── astro.config.mjs           Config Astro: site URL + sitemap
├── package.json
└── tsconfig.json
```

---

## Sviluppo

```bash
cd sito
npm install
npm run dev        # dev server → http://localhost:4321
npm run build      # build statico in dist/
npm run preview    # anteprima del build
```

---

## Pagine

### `index.astro` — Home

| Sezione | Descrizione |
|---|---|
| **Hero** | Headline + sub + CTA + mock dashboard animato |
| **Funzionalità** | Bento grid con preview interattive: Cassa giornaliera, Firma digitale, App mobile, White-label, Chat + Radio (Suite) |
| **Prezzi** | Quattro piani (Essenziale €39 / Pro €69 / Suite €99 / Multi-sala €149 con 2 sale, +€49 per sala) con feature list aggiornate |
| **Rivenditori** | Proposta B2B + mock pannello Hub |
| **CTA finale** | Trial gratuito |

### `contatti.astro`

Form di contatto / richiesta demo con campi: nome, email, telefono, messaggio, piano di interesse. Invia via `mailto:` o endpoint custom (configurabile).

### `privacy.astro`

Privacy policy GDPR-compliant. Aggiornare con i dati del titolare prima del deploy in produzione.

---

## Design system

Il sito usa variabili CSS inline in `:root` (definite nel layout `Base.astro`):

```css
--bg:         #080e18       /* sfondo body */
--surface:    #0d1320       /* card/panel */
--border-sub: #1a2332       /* bordi sottili */
--text:       #e8edf5       /* testo primario */
--muted:      #4a5568       /* testo secondario */
--accent:     oklch(0.72 0.16 168)   /* teal brand */
--font-head:  'Cabinet Grotesk', system-ui
```

Il sito è **dark-only** by design: il prodotto è usato in sale scure e la palette dark comunica autorevolezza operativa.

---

## Animazioni

Le `.reveal` usano `IntersectionObserver` (script inline in `Base.astro`) per fade-in + slide-up al primo scroll. Delay via classi `.d1`, `.d2`, `.d3`. Rispetta `prefers-reduced-motion: reduce`.

---

## Piano / Feature aggiornate

Le feature elencate nelle card prezzi devono restare sincronizzate con `suite/includes/lib.php` (fonte di verità per i gate). Aggiornare `index.astro → sezione #prezzi` ogni volta che si aggiunge una feature a un tier.

| Piano | Feature chiave mostrate sul sito |
|---|---|
| Essenziale | Cassa, turni flessibili, VLT/AWP, report sugli incassi reali, bet/win e borderò, offline, PWA, export XLS, max 4 operatori |
| Pro | + Anagrafica giocatori, prestiti, documenti, ticket assistenza, bar e magazzino, notifiche push, firma digitale, confronto periodi, operatori illimitati |
| Suite | + Chat interna, Web Radio, white-label, passaggio consegne, SONOS, LUL, supporto prioritario |
| Multi-sala | + più sale nello stesso account (2 incluse, +€49/sala), panoramica sale, utenti e impostazioni per sala |

Le pagine funzionalità sono in `src/pages/funzionalita/` e l'elenco (nome, descrizione, piano, icona) è in `src/data/features.ts` — il tipo `Plan` include `multisala`. Pagine aggiunte in v3.0: `offline`, `betwin-bordero`, `bar`, `multi-sala`.

Il portale clienti (`php/cliente/index.php`) gestisce anche il piano Multi-sala: campo «Numero di sale» (min 2) con prezzo calcolato, inviato all'hub come `sale` nella richiesta.

---

## Deploy

### Netlify / Cloudflare Pages

```bash
npm run build
# Upload dist/ o connetti il repo con build command "npm run build" e publish dir "dist"
```

### SiteGround / hosting cPanel

```bash
npm run build
# Carica il contenuto di dist/ nella cartella pubblica via SFTP
```

### Redirects

Aggiungere `public/_redirects` (Netlify) o regole equivalenti per:
- `/contatti` → `/contatti` (no trailing slash — già configurato in `astro.config.mjs`)
- `404` → `/404` (gestito da `404.astro`)

---

## Aggiornare i contenuti

### Aggiungere una feature a un piano

1. Apri `src/pages/index.astro`
2. Trova `<!-- ── PRICING ──` (circa riga 262)
3. Modifica la `<ul class="plan-features">` del piano corretto
4. Verifica che la feature sia effettivamente attiva in `suite/includes/lib.php → piano_features()`

### Aggiornare i prezzi

I prezzi appaiono in tre punti: `plan-price`, `plan-annual` (sconto annuale) e il testo della nota in fondo (`pricing-note`). Aggiornare tutti e tre per coerenza.

### Aggiungere una pagina

1. Crea `src/pages/nome-pagina.astro`
2. Importa il layout: `import Base from '../layouts/Base.astro'`
3. Aggiungi il link nella navbar (`src/components/Nav.astro`) e nel footer se necessario
4. Astro genera automaticamente `/nome-pagina` nel build
