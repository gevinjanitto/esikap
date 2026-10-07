module.exports = {
  content: ['./resources/views/**/*.blade.php', './app/**/*.php', './config/*.php', './public/js/*.js'],
  theme: { extend: {
      fontFamily: { sans: ['Onest', 'system-ui', 'sans-serif'], display: ['Unbounded', 'Onest', 'sans-serif'] },
      colors: { brand: { 50: '#FFF1F2', 100: '#FFE1E3', 200: '#FFC7CC', 400: '#F7525F', 500: '#EE2B3B', 600: '#E11D2E', 700: '#B3121F', 900: '#5F0A12' }, ink: '#0B0F19' },
    } },
};
