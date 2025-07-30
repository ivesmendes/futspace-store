import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue     from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/js/app.js',  // apenas seu código Vue/axios/etc.
      ],
      refresh: true,
    }),
    vue(),
  ],
  resolve: {
    alias: {
      // se quiser importar @heroicons mais facilmente
      '@heroicons': '/node_modules/@heroicons/vue/24/outline',
    },
  },
})
