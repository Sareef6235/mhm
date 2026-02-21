module.exports = {
  content: ['./app/**/*.{js,jsx}', './components/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: { glass: 'rgba(255,255,255,0.15)' },
      boxShadow: { glow: '0 20px 40px rgba(0,0,0,0.15)' }
    }
  },
  darkMode: 'class',
  plugins: []
};
