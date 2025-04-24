/** @type {import('tailwindcss').Config} */
export default {
  darkMode: "class",
  content: [
    "./components/**/*.{vue,js,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./app.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: "#ECF5FF",
        secondary: "#DDF7FC",
        info: "#0046DE",
        info_dark: "#002A85",
        success: "#DFF1ED",
        danger: "#FEE5E5",
        accent: "#FF9F43",
        purple: "#EAD8F3",
        warning: "#F4D03F",
        neutral: "#F5F5F5",
        dark: "#2C2C2C",
        background: "#FAFAFA",
      },
      typography: {
        DEFAULT: {
          css: {
            color: "#000000",
            h1: { color: "#000000" },
            h2: { color: "#000000" },
            h3: { color: "#000000" },
            h4: { color: "#000000" },
            h5: { color: "#000000" },
            h6: { color: "#000000" },
            strong: { color: "#000000" },
            em: { color: "#000000" },
            a: {
              color: "#1155CC",
              textDecoration: "underline",
              "&:hover": { color: "#0b43a1" },
            },
            blockquote: { color: "#333333", borderLeftColor: "#cccccc" },
            code: { color: "#000000", backgroundColor: "#f3f3f3" },
            pre: { backgroundColor: "#f3f3f3", color: "#000000" },
            hr: { borderColor: "#d1d5db" },
            ul: { color: "#000000" },
            ol: { color: "#000000" },
            li: { color: "#000000" },
            th: { color: "#000000" },
            td: { color: "#000000" },
            "ul > li::marker": {
              color: "#000000",
            },
            "ol > li::marker": {
              color: "#000000",
            },
          },
        },
      },
    },
  },
  plugins: [
    require("@tailwindcss/line-clamp"),
    require("@tailwindcss/typography"),
  ],
};
