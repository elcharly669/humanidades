/** @type {import('tailwindcss').Config} */
export default {
  content: ['./src/**/*.{astro,html,js,jsx,ts,tsx,md,mdx}'],
  theme: {
    extend: {
      fontFamily: {
        // Inter para la interfaz; stack del sistema como fallback para carga sin CLS
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      colors: {
        // Paleta de marca universitaria — actualizar con los valores hex reales de la institución
        brand: {
          DEFAULT: '#1a3a5c', // azul marino profundo
          light: '#2d5f8a',
          accent: '#c8a951', // dorado cálido
        },
      },
    },
  },
  plugins: [],
};
