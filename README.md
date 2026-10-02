# GestHall Suite — Sito

Sito di presentazione e vendita di GestHall Suite (`gesthallsuite.it`). **Astro 7**, output statico; un solo file PHP per il modulo contatti. Tono, pubblico e principi in [PRODUCT.md](PRODUCT.md).

## Sviluppo

```bash
npm install
npm run dev      # http://localhost:4321
npm run build    # sito statico in dist/
```

Per provare anche il modulo contatti: `cd dist && php -S 127.0.0.1:4400`.

## Struttura

```
src/
├── styles/site.css          Sistema grafico: token della suite (chiaro/scuro), pulsanti, schede,
│                            schermate, tabelle, FAQ, moduli, prosa
├── layouts/
│   ├── Layout.astro         <head>, meta e Open Graph, tema senza sfarfallio, nav, footer, comparsa
│   └── AreaLayout.astro     Pagina di un'area del prodotto (intestazione, schermata, altre aree, CTA)
├── components/
│   ├── SiteNav.astro        Menu (Prodotto a tendina, tema chiaro/scuro, foglio su telefono)
│   ├── SiteFooter.astro     Piè di pagina
│   ├── Logo.astro           Marchio
│   ├── Shot.astro           Schermata del computer in cornice del browser (public/img/prodotto/*.webp)
│   ├── Phone.astro          Schermata del telefono in cornice
│   ├── PricingCards.astro   Schede dei piani con selettore mensile/annuale
│   └── Cta.astro            Fascia finale «Richiedi una demo»
├── data/
│   ├── piani.ts             Prezzi, contenuto dei piani, tabella di confronto, inclusi — FONTE UNICA dei prezzi
│   ├── aree.ts              Le 5 aree del prodotto (menu, panoramica, pagine) e le icone
│   └── changelog.json       Novità per versione — fonte di /changelog, /changelog.json e Risorse
└── pages/
    ├── index.astro          Home
    ├── prodotto/            index (panoramica), cassa-e-turni, report, sala, bar-e-clienti, multi-sala
    ├── sicurezza.astro      Dati, backup, accessi, aggiornamenti, requisiti tecnici
    ├── prezzi.astro         Piani, inclusi, confronto, domande frequenti
    ├── rivenditori.astro    Programma rivenditori e pannello
    ├── contatti.astro       Modulo demo/contatti (?motivo=demo|info|rivenditore|assistenza, &piano=)
    ├── docs.astro           Risorse: guida, novità, area clienti, area riservata
    ├── changelog.astro      Novità per versione
    ├── changelog.json.ts    Feed letto dalla suite (riquadro Novità)
    └── privacy, termini, 404
public/
├── api/contatto.php         Invio del modulo (vedi sotto)
├── img/prodotto/*.webp      Schermate reali dalla suite (sala dimostrativa)
├── og.png                   Anteprima social 1200×630
├── .htaccess                Redirect 301 dei vecchi indirizzi /funzionalita/*
├── cliente/index.php        Area clienti (piano e cambio piano, link firmato dall'app; senza link mostra come entrare)
├── docs/guida/              Guida all'uso (HTML statico)
└── interno/                 Area riservata (accesso con password): index, docs (tecnica), manutenzione
                             (aggiornamenti e backup), vendita (vendita, pagamenti, documenti legali), business-plan
php/interno                  Accesso all'area interna: si carica a parte in /interno/
```

## Sistema grafico

Stessi token di `suite/assets/css/ui.css`: sfondo `#f4f5f7`, superfici bianche, bordi `#e4e7ec`, testo `#101828`, accento `#00c391` (testo accento `#00785a`), raggi 16/12/10 px. Tema scuro con `data-theme="dark"` su `<html>` (preferenza del sistema o pulsante nel menu, salvata in `localStorage.gh-site-theme`). Font di sistema: il sito non carica nulla da terzi.

Componenti in `site.css`: `.btn` (`-primary`, `-soft`, `-ghost`, `-light`), `.eyebrow`, `.lead`, `.section(-alt|-tight)`, `.section-head`, `.page-hero` + `.crumbs`, `.card` + `.icon`, `.grid .g2/.g3/.g4`, `.split(.rev)` (testo + schermata), `.facts`, `.checks`, `.badge`, `.table-wrap .table`, `.faq` (`<details>`), `.cta`, `.form .field`, `.prose`, `.reveal` (comparsa allo scorrimento). Gli stili di pagina che toccano `.shot` o `.phone` (componenti) vanno scritti con `:global()`.

## Schermate

Le immagini in `public/img/prodotto/` vengono da una sala dimostrativa con dati realistici (3 sale, 200 giorni di cassa, turni, bar, assistenze), fotografata con Playwright a 1440×900 e 390×844 e convertita in WebP (1600 px computer, 640 px telefono). Quando l'interfaccia della suite cambia in modo visibile, rifare le schermate interessate.

## Modulo contatti

`public/api/contatto.php` riceve il modulo di `/contatti` (anche senza JavaScript: redirect a `/contatti?inviato=1`), e invia un'email di testo a `info@gesthallsuite.it` con `Reply-To` del cliente. Protezioni: campo trappola `sito_web`, tempo minimo di compilazione, 5 invii all'ora per IP (file in `sys_get_temp_dir()`), consenso privacy obbligatorio. Mittente `noreply@gesthallsuite.it`: deve essere un indirizzo del dominio sull'hosting perché la posta non finisca nello spam.

## Prezzi e piani

Tutto in `src/data/piani.ts`: prezzi mensili/annuali, `SALA_EXTRA` (Multi-sala), elenco per piano, tabella di confronto, inclusi, giorni di prova. Deve restare allineato a `piano_features()` della suite (`includes/lib/parte-2.php`) e a `hub_piano_features()` / `hub_prezzo()` dell'hub.

## Redirect

I vecchi indirizzi `/funzionalita/*` portano alle nuove pagine: `redirects` in `astro.config.mjs` (pagine con meta refresh, valide ovunque) e `public/.htaccess` (301 su Apache/SiteGround). La sitemap esclude `/funzionalita` e `/interno`.

## Deploy

```bash
npm run build
# caricare il contenuto di dist/ (compresi .htaccess e api/) nella cartella pubblica del dominio
```

L'area clienti è compresa nel build (`dist/cliente/index.php`); l'accesso all'area interna (`php/interno/`) resta nella sua cartella sul server.

## Regola operativa

Vedi [CLAUDE.md](CLAUDE.md): novità del prodotto in `changelog.json`, funzioni nuove o cambiate nelle pagine `prodotto/*` (e `piani.ts` se cambia un piano), modifiche al sito in `CHANGELOG.md`, `npm run build` senza errori.
