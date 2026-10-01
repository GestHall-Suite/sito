export type Plan = 'essenziale' | 'pro' | 'suite' | 'multisala';

export interface Feature {
  id: string;
  slug: string;
  name: string;
  desc: string;
  plan: Plan;
  icon: string;
}

export const features: Feature[] = [
  {
    id: 'cassa-giornaliera',
    slug: '/funzionalita/cassa-giornaliera',
    name: 'Cassa giornaliera',
    desc: 'Una scheda per ogni turno del giorno, turno di chiusura e versamento calcolati in tempo reale',
    plan: 'essenziale',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="16" height="12" rx="2"/><path d="M2 8h16M6 12h2M10 12h4"/></svg>`,
  },
  {
    id: 'scassettamenti',
    slug: '/funzionalita/scassettamenti',
    name: 'Scassettamenti',
    desc: 'VLT e AWP macchina per macchina: gli incassi reali su cui si basano tutte le statistiche',
    plan: 'essenziale',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="2" width="14" height="10" rx="2"/><path d="M3 7h14M7 2v5M13 2v5M3 16h14M6 16v2M14 16v2"/></svg>`,
  },
  {
    id: 'turni',
    slug: '/funzionalita/turni',
    name: 'Turni e operatori',
    desc: 'Calendario flessibile: 2, 3 o più turni a seconda del giorno, senza fogli paga',
    plan: 'essenziale',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="16" height="15" rx="2"/><path d="M14 2v3M6 2v3M2 9h16M6 13h2M10 13h4M6 16h2"/></svg>`,
  },
  {
    id: 'report-analisi',
    slug: '/funzionalita/report-analisi',
    name: 'Report e analisi',
    desc: 'Statistiche sugli incassi reali del turno di chiusura, dal settimanale all\'annuale',
    plan: 'essenziale',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 14l4-4 4 2 4-6M2 18h16"/></svg>`,
  },
  {
    id: 'offline',
    slug: '/funzionalita/offline',
    name: 'Funziona offline',
    desc: 'Giornaliero e turni anche senza connessione, con sincronizzazione automatica',
    plan: 'essenziale',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 7.5a12 12 0 0 1 16 0M5 10.5a8 8 0 0 1 10 0M8 13.5a4 4 0 0 1 4 0"/><circle cx="10" cy="16.5" r="1"/><path d="M3 3l14 14"/></svg>`,
  },
  {
    id: 'sicurezza-aggiornamenti',
    slug: '/funzionalita/sicurezza-aggiornamenti',
    name: 'Backup e aggiornamenti',
    desc: 'Aggiornamenti con un clic, backup settimanale del database, accesso in due passaggi e avvisi automatici',
    plan: 'essenziale',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 2l6 2.5v4.5c0 4-2.6 7.2-6 8.5-3.4-1.3-6-4.5-6-8.5V4.5z"/><path d="M7 10l2 2 4-4"/></svg>`,
  },
  {
    id: 'betwin-bordero',
    slug: '/funzionalita/betwin-bordero',
    name: 'Bet/Win e borderò',
    desc: 'Dati dichiarati dai fornitori con import Excel e archivio PDF dei borderò',
    plan: 'essenziale',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/><polyline points="12 2 12 7 17 7"/><path d="M7 11h6M7 14h4"/></svg>`,
  },
  {
    id: 'firma-digitale',
    slug: '/funzionalita/firma-digitale',
    name: 'Firma digitale',
    desc: 'Il revisore firma ogni versamento su canvas touch',
    plan: 'pro',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 14 C6 8, 9 16, 12 12 C14 10, 16 13, 18 11"/><path d="M3 17h14"/></svg>`,
  },
  {
    id: 'app-mobile',
    slug: '/funzionalita/app-mobile',
    name: 'App mobile & push',
    desc: 'PWA installabile su iOS e Android: si usa come un\'app nativa, con notifiche push',
    plan: 'pro',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="10" height="16" rx="2"/><path d="M9 15h2"/></svg>`,
  },
  {
    id: 'ticket-assistenza',
    slug: '/funzionalita/ticket-assistenza',
    name: 'Ticket assistenza',
    desc: 'Guasti VLT e AWP tracciati con email automatica al tecnico',
    plan: 'pro',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 5a4 4 0 0 1 0 4v1a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V5a4 4 0 0 1 0-4V3a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1z"/><path d="M7 10V8a2 2 0 0 1 4 0"/></svg>`,
  },
  {
    id: 'giocatori-prestiti',
    slug: '/funzionalita/giocatori-prestiti',
    name: 'Clienti e prestiti',
    desc: 'Anagrafica clienti (sala e bar) con documento d\'identità, indirizzi e badge scadenza. Gestione prestiti con saldo live.',
    plan: 'pro',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="6" r="3"/><path d="M2 18c0-3.3 2.7-6 6-6M13 14l2 2 4-4"/></svg>`,
  },
  {
    id: 'bar',
    slug: '/funzionalita/bar',
    name: 'Bar e magazzino',
    desc: 'Banco, omaggi al cliente, scontrinato e fatturato, magazzino premium e banco',
    plan: 'pro',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 3h9v8a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4z"/><path d="M13 5h2a2 2 0 0 1 0 4h-2M3 18h12"/></svg>`,
  },
  {
    id: 'lul-buste-paga',
    slug: '/funzionalita/lul-buste-paga',
    name: 'LUL e buste paga',
    desc: 'Distribuzione digitale delle buste paga con presa visione tracciata',
    plan: 'suite',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/><polyline points="12 2 12 7 17 7"/><line x1="13" y1="11" x2="7" y2="11"/><line x1="13" y1="14" x2="7" y2="14"/></svg>`,
  },
  {
    id: 'chat-operatori',
    slug: '/funzionalita/chat-operatori',
    name: 'Chat interna',
    desc: 'Messaggistica in tempo reale con vocali, immagini ed emoji. Condivisione contenuti (turni, documenti, ticket) e stato online degli operatori.',
    plan: 'suite',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6l-4 3V4z"/></svg>`,
  },
  {
    id: 'white-label',
    slug: '/funzionalita/white-label',
    name: 'White-label',
    desc: 'Logo, colori e nome personalizzati per ogni cliente',
    plan: 'suite',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="10" cy="10" r="3"/><path d="M10 3v2M10 15v2M3 10h2M15 10h2M5.2 5.2l1.4 1.4M13.4 13.4l1.4 1.4M5.2 14.8l1.4-1.4M13.4 6.6l1.4-1.4"/></svg>`,
  },
  {
    id: 'multi-sala',
    slug: '/funzionalita/multi-sala',
    name: 'Multi-sala',
    desc: 'Più sale in un unico account: selettore sala, panoramica e utenti per sala',
    plan: 'multisala',
    icon: `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 17V8l4-3 4 3v9M10 17V6l4-3 4 3v11M1 17h18"/><path d="M5 12h2M13 10h2M13 13h2"/></svg>`,
  },
];

export const byPlan = {
  essenziale: features.filter(f => f.plan === 'essenziale'),
  pro: features.filter(f => f.plan === 'pro'),
  suite: features.filter(f => f.plan === 'suite'),
  multisala: features.filter(f => f.plan === 'multisala'),
};

export const planLabel: Record<Plan, string> = {
  essenziale: 'Essenziale',
  pro: 'Pro',
  suite: 'Suite',
  multisala: 'Multi-sala',
};

export const planColor: Record<Plan, string> = {
  essenziale: 'oklch(0.72 0.16 168)',
  pro: 'oklch(0.65 0.18 250)',
  suite: 'oklch(0.65 0.18 300)',
  multisala: 'oklch(0.60 0.12 210)',
};
