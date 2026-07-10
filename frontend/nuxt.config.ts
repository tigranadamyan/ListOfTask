export default defineNuxtConfig({
  compatibilityDate: '2026-07-10',

  devtools: { enabled: true },

  typescript: {
    strict: true,
    typeCheck: false,
  },

  components: [
    { path: '~/components/ui', prefix: '' },
    { path: '~/components/task', prefix: '' },
  ],

  css: [],

  modules: [
    '@pinia/nuxt',
    '@nuxtjs/tailwindcss',
    '@vueuse/nuxt',
    'vue-sonner/nuxt',
  ],

  app: {
    head: {
title: 'Список задач',
        meta: [
          { name: 'description', content: 'Управление задачами — современный список дел' },
        ],
    },
  },

  runtimeConfig: {
    // Server-only: used by SSR to reach the API inside Docker network
    apiBaseUrl: 'http://localhost:8000/api',
    public: {
      // Client-only: relative URL, goes through Nginx reverse proxy
      apiBaseUrl: 'http://localhost:8000/api',
    },
  },

  devServer: {
    port: 3000,
  },
})
