import tailwindcss from "@tailwindcss/vite";

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr: false,
  modules: [
    'nuxt-auth-sanctum',
    '@pinia/nuxt',
    '@nuxtjs/google-fonts',
    '@nuxtjs/i18n'
  ],
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  css: ["~/assets/css/main.css"],
  vite: {
    plugins: [tailwindcss()],
  },
  components: [
    {
      path: '~/components',
      pathPrefix: false,
    }
  ],
  i18n: {
    locales: ['es', 'en'],
    vueI18n: './i18n.config.ts',
    detectBrowserLanguage: {
      useCookie: true,
      redirectOn: 'root',
    }
  },
  googleFonts: {
    families: {
      Montserrat: [100, 200, 300, 400, 500, 600, 700, 800, 900],
    },
    display: 'swap',
  },
  runtimeConfig: {
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
        redirect: {
          onAuthOnly: '/login',
          onGuestOnly: '/dashboard',
          onLogin: "/dashboard",
          onLogout: "/", // Landing Page / Home Page
        },
      },
    }
  },
  imports: {
    autoImport: true,
  }
});
