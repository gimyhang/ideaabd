module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './Modules/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/css/**/*.css',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#f5f7ff',
          100: '#e6edff',
          200: '#c7d6ff',
          300: '#98b3ff',
          400: '#6b8cff',
          500: '#3d66ff',
          600: '#3153e6',
          700: '#2742b4',
          800: '#1d2f82',
          900: '#10184d',
        },
        accent: '#ffb020',
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Helvetica', 'Arial'],
      },
    },
  },
  plugins: [require('@tailwindcss/typography')],
};
