import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';

// https://astro.build/config
export default defineConfig({
  // Salida estática — sin JS en tiempo de ejecución por defecto, ideal para Cloudflare Pages
  output: 'static',

  integrations: [
    tailwind({
      // Una sola hoja de estilos global; el @apply por componente se maneja ahí
      applyBaseStyles: true,
    }),
  ],

  // Inyectado en tiempo de compilación desde la variable de entorno;
  // se sobreescribe por ambiente en CF Pages
  // La barra final se elimina para que lib/wp.ts nunca duplique barras en las URLs
  site: process.env.SITE_URL ?? 'https://humanidades.example.edu',
});
