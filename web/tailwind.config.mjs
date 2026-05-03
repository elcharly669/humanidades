/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,ts,tsx}'],
  theme: {
    extend: {
      fontFamily: {
        serif: ['"EB Garamond"', 'Georgia', 'serif'],
        sans:  ['"EB Garamond"', 'Georgia', 'serif'], // todo Garamond
      },
      fontSize: {
        // Escala editorial clásica
        'xs':   ['0.78rem',  { lineHeight: '1.5' }],
        'sm':   ['0.9rem',   { lineHeight: '1.6' }],
        'base': ['1rem',     { lineHeight: '1.75' }],
        'lg':   ['1.2rem',   { lineHeight: '1.6' }],
        'xl':   ['1.4rem',   { lineHeight: '1.4' }],
        '2xl':  ['1.75rem',  { lineHeight: '1.25' }],
        '3xl':  ['2.2rem',   { lineHeight: '1.15' }],
        '4xl':  ['2.8rem',   { lineHeight: '1.1' }],
      },
      colors: {
        // ── Paleta editorial de tinta y papel ───────────────────────
        // Sin blanco ni negro puros; todo ligeramente templado.
        ink: {
          DEFAULT: '#1c1917', // casi negro con matiz sepia
          soft:    '#3c3632',
          muted:   '#6b6460',
          faint:   '#9a9491',
        },
        paper: {
          DEFAULT: '#f7f4ef', // pergamino cálido — fondo de cuerpo principal
          warm:    '#f0ece4', // ligeramente más oscuro; secciones alternas
          border:  '#e2ddd6', // bordes sutiles
        },
        // ── Identidad institucional (navy + dorado) ──────────────────
        // Actualizarse con los hex reales de la facultad antes del lanzamiento.
        brand: {
          DEFAULT: '#1c3557', // azul marino académico
          light:   '#2a5080', // variante más clara para hovers
          dark:    '#122340', // para fondos muy oscuros (footer)
          accent:  '#b89a3e', // dorado templado; evita el amarillo puro
        },
        // Alias para código semántico
        accent: {
          DEFAULT: '#b89a3e',
          hover:   '#a08530',
        },
      },
      borderColor: {
        DEFAULT: '#e2ddd6', // alineado con paper.border
      },
      maxWidth: {
        prose:  '68ch',
        layout: '72rem',
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
};
