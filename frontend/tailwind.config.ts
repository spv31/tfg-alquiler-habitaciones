/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class', 
  content: [
    './components/**/*.{vue,js,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './app.vue',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#ECF5FF',
        secondary: '#DDF7FC',
        info: '#0046DE',
        info_dark: '#002A85',
        success: '#DFF1ED',
        danger: '#FEE5E5',
        accent: '#FF9F43',
        purple: '#EAD8F3',
        warning: '#F4D03F', 
        neutral: '#F5F5F5', 
        dark: '#2C2C2C',
      }
    },
  },
  plugins: [],
};
