// Prezzi e contenuto dei piani: unica fonte per home, prezzi e rivenditori.
// Deve restare allineato a piano_features() della suite (includes/lib/parte-2.php)
// e a hub_piano_features() / hub_prezzo() dell'hub.

export type PianoId = 'essenziale' | 'pro' | 'suite' | 'multisala';

export interface Piano {
  id: PianoId;
  nome: string;
  mese: number;
  anno: number | null;
  per: string;
  nota: string;
  evidenza?: boolean;
  include: string[];
}

export const PROVA_GIORNI = 14;
export const SALA_EXTRA = 49;

export const piani: Piano[] = [
  {
    id: 'essenziale', nome: 'Essenziale', mese: 39, anno: 390,
    per: 'La sala singola che vuole chiudere la cassa senza fogli Excel.',
    nota: 'Fino a 4 utenti attivi',
    include: ['Cassa giornaliera per turni', 'Scassettamenti VLT e refill AWP', 'Calendario turni', 'Report settimanali, mensili e annuali', 'Bet/Win e archivio borderò', 'Export per il commercialista', 'Funziona anche offline', 'Backup e aggiornamenti automatici'],
  },
  {
    id: 'pro', nome: 'Pro', mese: 69, anno: 690, evidenza: true,
    per: 'La sala con bar, clienti abituali e più operatori.',
    nota: 'Utenti illimitati',
    include: ['Tutto Essenziale', 'Firma digitale del versamento', 'Bar: banco, magazzino e report', 'Ticket assistenza macchine', 'Clienti, prestiti e rientri', 'Documenti e contatti della sala', 'Notifiche push e avvisi automatici', 'Confronto tra periodi'],
  },
  {
    id: 'suite', nome: 'Suite', mese: 99, anno: 990,
    per: 'La sala strutturata che vuole il proprio marchio e meno carta.',
    nota: 'Utenti illimitati',
    include: ['Tutto Pro', 'Il tuo logo e i tuoi colori', 'Studio di stile completo per ognuno', 'Chat interna con foto e vocali', 'LUL e buste paga digitali', 'Passaggio consegne tra turni', 'Layout avanzato del giornaliero', 'Musica: Web Radio e SONOS', 'Supporto prioritario'],
  },
  {
    id: 'multisala', nome: 'Multi-sala', mese: 149, anno: null,
    per: 'Chi gestisce due o più sale e vuole un solo accesso.',
    nota: `2 sale incluse · +€${SALA_EXTRA}/mese per sala`,
    include: ['Tutto Suite', 'Panoramica di tutte le sale', 'Cambio sala con un tocco', 'Operatori abilitati per sala', 'Impostazioni, turni e macchine per sala', 'Un solo abbonamento, un solo accesso'],
  },
];

export const inclusi = [
  'Installazione e configurazione iniziale',
  'Migrazione dei dati da Excel o da un altro gestionale',
  'Aggiornamenti e nuove funzionalità',
  'Assistenza in italiano via email',
  'Disdetta quando vuoi, senza penali',
  `${PROVA_GIORNI} giorni di prova, senza carta di credito`,
];

type Cella = boolean | string;
export interface Riga { voce: string; v: [Cella, Cella, Cella, Cella] }
export interface Gruppo { titolo: string; righe: Riga[] }

export const confronto: Gruppo[] = [
  { titolo: 'Cassa e incassi', righe: [
    { voce: 'Cassa giornaliera con turni flessibili', v: [true, true, true, true] },
    { voce: 'Scassettamenti VLT, refill AWP, ticket e bancomat', v: [true, true, true, true] },
    { voce: 'Turno di chiusura e versamento calcolati', v: [true, true, true, true] },
    { voce: 'Bet/Win dichiarato e archivio borderò', v: [true, true, true, true] },
    { voce: 'Firma digitale del versamento', v: [false, true, true, true] },
    { voce: 'Layout avanzato del giornaliero', v: [false, false, true, true] },
  ]},
  { titolo: 'Report', righe: [
    { voce: 'Dashboard, settimanale, mensile, annuale', v: [true, true, true, true] },
    { voce: 'Export Excel e per il commercialista', v: [true, true, true, true] },
    { voce: 'Storico per macchina', v: [true, true, true, true] },
    { voce: 'Confronto tra periodi', v: [false, true, true, true] },
  ]},
  { titolo: 'Personale', righe: [
    { voce: 'Calendario turni e utenti con ruoli', v: [true, true, true, true] },
    { voce: 'Utenti attivi', v: ['4', 'Illimitati', 'Illimitati', 'Illimitati'] },
    { voce: 'Chat interna', v: [false, false, true, true] },
    { voce: 'LUL e buste paga con presa visione', v: [false, false, true, true] },
    { voce: 'Passaggio consegne con checklist', v: [false, false, true, true] },
  ]},
  { titolo: 'Sala', righe: [
    { voce: 'Ticket assistenza macchine con QR', v: [false, true, true, true] },
    { voce: 'Bar: banco, magazzino, menu, report', v: [false, true, true, true] },
    { voce: 'Clienti, prestiti e rientri', v: [false, true, true, true] },
    { voce: 'Documenti e contatti', v: [false, true, true, true] },
    { voce: 'Web Radio e SONOS', v: [false, false, true, true] },
  ]},
  { titolo: 'Sistema', righe: [
    { voce: 'App installabile su telefono, anche offline', v: [true, true, true, true] },
    { voce: 'Backup settimanale e aggiornamenti con un clic', v: [true, true, true, true] },
    { voce: 'Verifica in due passaggi e registro attività', v: [true, true, true, true] },
    { voce: 'Notifiche push e avvisi automatici', v: [false, true, true, true] },
    { voce: 'Logo e colori della sala', v: [false, false, true, true] },
    { voce: 'Studio di stile completo per ogni persona (colori, copertine, foto, sfondi)', v: [false, false, true, true] },
    { voce: 'Stile della sala e pagina di accesso personalizzata', v: [false, false, true, true] },
    { voce: 'Più sale con un solo accesso', v: [false, false, false, true] },
    { voce: 'Supporto prioritario', v: [false, false, true, true] },
  ]},
];

export const eur = (n: number) => '€' + n.toLocaleString('it-IT');
