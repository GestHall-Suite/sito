import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://gesthallsuite.it',
  integrations: [sitemap()],
  trailingSlash: 'ignore',
});
