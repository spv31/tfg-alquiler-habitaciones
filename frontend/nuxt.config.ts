import Aura from "@primeuix/themes/aura";
export default defineNuxtConfig({
  ssr: false,
  typescript: {
    typeCheck: false,
  },
  modules: [
    "nuxt-auth-sanctum",
    "@pinia/nuxt",
    "@nuxtjs/google-fonts",
    "@nuxtjs/i18n",
    "@nuxtjs/color-mode",
    "@nuxtjs/tailwindcss",
    "nuxt-tiptap-editor",
    "@primevue/nuxt-module",
    "pinia-plugin-persistedstate/nuxt",
  ],
  colorMode: {
    preference: "light",
    fallback: "light",
    classSuffix: "",
    classPrefix: "",
  },
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  css: [
    "~/assets/css/main.css",
    "~/assets/css/tailwind.css",
    "primeicons/primeicons.css",
  ],
  vite: {
    server: {
      hmr: true,
    },
  },
  components: [
    {
      path: "~/components",
      pathPrefix: false,
    },
  ],
  i18n: {
    locales: ["es", "en"],
    defaultLocale: 'es',
    strategy: "prefix",
    vueI18n: "./i18n.config.ts",
    detectBrowserLanguage: {
      useCookie: true,
      alwaysRedirect: true,
      redirectOn: "root",
    },
  },
  googleFonts: {
    families: {
      Montserrat: [100, 200, 300, 400, 500, 600, 700, 800, 900],
    },
    display: "swap",
  },
  tiptap: { prefix: "Tiptap" },
  primevue: {
    options: {
      theme: {
        preset: Aura,
        options: {
          darkModeSelector: ".dark",
        },
      },
    },
  },
  runtimeConfig: {
    app: {
      apiBaseURL: "http://localhost:8000/api",
    },
    public: {
      sanctum: {
        baseUrl: "http://localhost:8000",
        mode: "cookie",
        endpoints: {
          csrf: "/sanctum/csrf-cookie",
          register: "/api/register",
          login: "/api/login",
          logout: "/api/logout",
          user: "/api/user",
        },
      },
    },
  },
  build: {
    transpile: ["primevue", "@primeuix/themes"],
  },
  imports: {
    autoImport: true,
  },
});
