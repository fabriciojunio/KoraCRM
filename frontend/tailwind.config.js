/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{js,ts,jsx,tsx}',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['DM Sans', 'Inter', 'system-ui', 'sans-serif'],
      },
      // Identidade visual do KoraCRM. Para trocar a cor do sistema,
      // ajuste só esta escala (ou as variáveis em src/index.css).
      colors: {
        brand: {
          50: '#f0faf6',
          100: '#d4f0e3',
          200: '#a9e1c8',
          400: '#34b88f',
          500: '#199b76',
          600: '#0f7d60',
          700: '#0b6149',
          800: '#0a4e3b',
        },
        primary: {
          50: '#f0faf6',
          100: '#d4f0e3',
          500: '#199b76',
          600: '#0f7d60',
          700: '#0b6149',
        },
      },
      borderRadius: {
        card: '14px',
      },
      boxShadow: {
        card: '0 1px 2px rgba(16, 24, 40, 0.04), 0 1px 3px rgba(16, 24, 40, 0.06)',
        pop: '0 8px 24px rgba(16, 24, 40, 0.12)',
      },
      animation: {
        'slide-in': 'slideIn 0.2s ease-out',
      },
      keyframes: {
        slideIn: {
          from: { transform: 'translateY(-8px)', opacity: 0 },
          to: { transform: 'translateY(0)', opacity: 1 },
        },
      },
    },
  },
  plugins: [],
}
