/** @type {import('tailwindcss').Config} */

// As cores e as fontes vivem em src/index.css, como variáveis. Aqui elas só
// viram classe do Tailwind. Para mudar o visual, mexa lá.
const cor = (nome) => `rgb(var(--cor-${nome}) / <alpha-value>)`

export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        aco: cor('aco'),
        'aco-fundo': cor('aco-fundo'),
        ficha: cor('ficha'),
        tinta: cor('tinta'),
        'tinta-suave': cor('tinta-suave'),
        grafite: cor('grafite'),
        pauta: cor('pauta'),
        borda: cor('borda'),
        'borda-forte': cor('borda-forte'),
        gaveta: cor('gaveta'),
        'gaveta-clara': cor('gaveta-clara'),
        caneta: cor('caneta'),
        'caneta-clara': cor('caneta-clara'),
        carimbo: cor('carimbo'),
        'carimbo-clara': cor('carimbo-clara'),
        aprovado: cor('aprovado'),
        'aprovado-clara': cor('aprovado-clara'),
        mostarda: cor('mostarda'),
        'mostarda-clara': cor('mostarda-clara'),
      },
      fontFamily: {
        titulo: ['Familjen Grotesk', 'system-ui', 'sans-serif'],
        corpo: ['Instrument Sans', 'system-ui', 'sans-serif'],
        mono: ['IBM Plex Mono', 'ui-monospace', 'monospace'],
      },
      borderRadius: {
        DEFAULT: '2px',
        ficha: '2px',
      },
      maxWidth: {
        leitura: '68ch',
      },
    },
  },
  plugins: [],
}
