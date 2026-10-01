import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

// Vecchi indirizzi delle pagine «funzionalità» → nuove pagine prodotto
const vecchie = {
  'cassa-giornaliera': 'cassa-e-turni', 'scassettamenti': 'cassa-e-turni', 'firma-digitale': 'cassa-e-turni',
  'offline': 'cassa-e-turni', 'turni': 'sala', 'ticket-assistenza': 'sala', 'chat-operatori': 'sala',
  'lul-buste-paga': 'sala', 'report-analisi': 'report', 'betwin-bordero': 'report', 'bar': 'bar-e-clienti',
  'giocatori-prestiti': 'bar-e-clienti', 'multi-sala': 'multi-sala',
};
const redirects = { '/funzionalita': '/prodotto' };
for (const [da, a] of Object.entries(vecchie)) redirects[`/funzionalita/${da}`] = `/prodotto/${a}`;
redirects['/funzionalita/app-mobile'] = '/prodotto';
redirects['/funzionalita/white-label'] = '/rivenditori';
redirects['/funzionalita/sicurezza-aggiornamenti'] = '/sicurezza';

export default defineConfig({
  site: 'https://gesthallsuite.it',
  integrations: [sitemap({ filter: (p) => !p.includes('/funzionalita') && !p.includes('/interno') })],
  trailingSlash: 'ignore',
  redirects,
});
