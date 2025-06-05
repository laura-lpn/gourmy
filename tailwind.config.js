/** @type {import('tailwindcss').Config} */
const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  content: [
    "./assets/**/*.js",
    "./assets/**/*.jsx",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
        colors: {
          'black': '#050505',
          'white': '#FFFDF8',
          'blue': '#098C8C',
          'orange': '#ED8E06',
          'paleBlue': '#F2F7F2',
          DEFAULT: '#050505'
        },  
        fontFamily: {
          'main': ['Poppins', 'sans-serif'],
          'second': ['Kodchasan', 'sans-serif'],
          'sans': ['Poppins', 'sans-serif',
          defaultTheme.fontFamily.sans]
        },
        borderRadius: {
          'main': '25px',
          'mobile': '20px',
          'input': '16px',
        },
        backgroundImage: {
          'hero-home': "url('/images/pages/home.jpg')",
          'roadtrips': "url('/images/pages/roadtrips.jpg')",
          'restaurants': "url('/images/pages/restaurants.jpg')",
        },
        boxShadow: {
          'main': '0px 0px 15px #00000029',
        },
        fontSize: {
          xxs: '.5rem',
          xs: '.75rem',
          sm: '1rem',
          base: '1.2rem',
          lg: '1.3rem',
          xl: '1.5rem',
          '2xl': '1.8rem',
          '3xl': '2rem',
          '4xl': '3rem',
          '5xl': '4rem',
          '6xl': '4.5rem',
        },
    },
  },
  future: {
    hoverOnlyWhenSupported: true,
  },
  plugins: [],
}
