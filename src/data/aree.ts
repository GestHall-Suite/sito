// Le cinque aree del prodotto: menu, pagina /prodotto e pagine di dettaglio.

export interface Area {
  slug: string;
  nome: string;
  breve: string;
  titolo: string;
  sintesi: string;
  icon: string;
  shot: string;
}

const ic = (d: string) => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;

export const ICONE = {
  cassa: ic('<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3 10h18M7 15h3M13 15h4"/>'),
  report: ic('<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>'),
  sala: ic('<path d="M3 21V9l9-6 9 6v12"/><path d="M9 21v-6h6v6"/>'),
  bar: ic('<path d="M5 8h12v6a6 6 0 0 1-12 0z"/><path d="M17 10h1.5a2.5 2.5 0 0 1 0 5H17M8 2v3M12 2v3"/>'),
  multi: ic('<rect x="2" y="9" width="8" height="12" rx="1.5"/><rect x="14" y="3" width="8" height="18" rx="1.5"/><path d="M5 13h2M5 17h2M17 7h2M17 11h2M17 15h2"/>'),
  scudo: ic('<path d="M12 3l8 3v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>'),
  firma: ic('<path d="M3 17c3-1 4-6 6-6s1 5 3 5 3-3 4-3 2 2 5 2"/><path d="M3 21h18"/>'),
  turni: ic('<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M16 2v4M8 2v4M3 10h18M8 14h2M14 14h2M8 17h2"/>'),
  telefono: ic('<rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/>'),
  offline: ic('<path d="M2 8.5a15 15 0 0 1 20 0M5.5 12a10 10 0 0 1 13 0M9 15.5a5 5 0 0 1 6 0"/><circle cx="12" cy="19" r="1"/>'),
  ticket: ic('<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/>'),
  chat: ic('<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-4.6A8 8 0 1 1 21 12z"/>'),
  doc: ic('<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>'),
  utenti: ic('<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6.5 6.5 0 0 1 3.5 6"/>'),
  backup: ic('<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>'),
  aggiorna: ic('<path d="M21 12a9 9 0 1 1-2.6-6.4"/><path d="M21 4v5h-5"/>'),
  chiave: ic('<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M17 6l3 3M15 8l2 2"/>'),
  occhio: ic('<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>'),
  campana: ic('<path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4z"/><path d="M10 21h4"/>'),
  euro: ic('<path d="M17 6.5A7 7 0 1 0 17 17.5"/><path d="M4 10h9M4 14h9"/>'),
  marchio: ic('<circle cx="12" cy="12" r="9"/><path d="M12 3a9 9 0 0 0 0 18M3.5 9h17M3.5 15h17"/>'),
  server: ic('<rect x="3" y="4" width="18" height="7" rx="2"/><rect x="3" y="13" width="18" height="7" rx="2"/><path d="M7 7.5h.01M7 16.5h.01"/>'),
  freccia: ic('<path d="M5 12h14M13 6l6 6-6 6"/>'),
};

export const aree: Area[] = [
  {
    slug: 'cassa-e-turni', nome: 'Cassa e turni', breve: 'Giornaliero, scassettamenti, versamento firmato',
    titolo: 'La cassa di ogni turno, chiusa senza sorprese.',
    sintesi: 'Scassettamenti VLT, refill AWP, ticket, bancomat e contanti per taglio: il giornaliero calcola cassetto, versamento e scostamento mentre scrivi, turno per turno.',
    icon: ICONE.cassa, shot: 'giornaliero',
  },
  {
    slug: 'report', nome: 'Report e numeri', breve: 'Dashboard, mensile, commercialista',
    titolo: 'I numeri veri della sala, senza rifare i conti.',
    sintesi: 'Incassi per giorno, macchina e piattaforma calcolati sul turno di chiusura. Dashboard in tempo reale, report fino all\'anno, export pronto per il commercialista.',
    icon: ICONE.report, shot: 'mensile',
  },
  {
    slug: 'sala', nome: 'Sala e personale', breve: 'Turni, assistenze, chat, documenti, LUL',
    titolo: 'Il lavoro di tutti, organizzato in un posto solo.',
    sintesi: 'Calendario turni, ticket assistenza per macchina, chat della sala, documenti, buste paga digitali e passaggio consegne: quello che oggi passa da WhatsApp e bacheche.',
    icon: ICONE.sala, shot: 'turni',
  },
  {
    slug: 'bar-e-clienti', nome: 'Bar e clienti', breve: 'Banco, magazzino, menu, prestiti',
    titolo: 'Il bar con il suo magazzino, i clienti con il loro storico.',
    sintesi: 'Vendite al banco in due tocchi, giacenze e scorte, menu stampabile, chiusura del registratore. E l\'anagrafica dei clienti abituali con prestiti e rientri.',
    icon: ICONE.bar, shot: 'bar',
  },
  {
    slug: 'multi-sala', nome: 'Multi-sala', breve: 'Più sale, un solo accesso',
    titolo: 'Tutte le tue sale, da un solo accesso.',
    sintesi: 'Ogni sala ha cassa, turni, macchine e impostazioni propri; tu le vedi insieme, passi dall\'una all\'altra con un tocco e decidi chi lavora dove.',
    icon: ICONE.multi, shot: 'sale',
  },
];
