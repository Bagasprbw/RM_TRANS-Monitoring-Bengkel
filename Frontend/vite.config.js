  import { defineConfig } from 'vite'
  import vue from '@vitejs/plugin-vue2'
  import { fileURLToPath, URL } from 'url'

  export default defineConfig({
    plugins: [
      vue(),
      // //ini untuk mendukung browser lama seperti IE11 di windows xp, tapi ini sedikit menambah ukuran bundle
      // legacy({
      //   targets: ['defaults', 'ie >= 11']
      // })
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },
    server: {
      host: '0.0.0.0',
      port: 8086,
      proxy: {
        '/api': {
          target: 'http://localhost:8000',
          changeOrigin: true,
        },
      },
    },
  })
