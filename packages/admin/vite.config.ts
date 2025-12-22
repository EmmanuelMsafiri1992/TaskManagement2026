import { defineConfig } from 'vite'
import { resolve } from 'path'
import eslintPlugin from 'vite-plugin-eslint'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'

const config = {
  root: 'resources',
  server: {
    port: 5173,
    strictPort: false,
    cors: true,
    origin: 'http://localhost:5173',
    hmr: {
      host: 'localhost',
    },
  },
  plugins: [
    vue(),
    vueJsx(),
    eslintPlugin({
      failOnWarning: false
    })
  ],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
      Component: resolve(__dirname, 'resources/js/components'),
      spack: resolve(__dirname, 'resources/js/spack'),
      Store: resolve(__dirname, 'resources/js/stores'),
      thetheme: resolve(__dirname, 'resources/js/thetheme'),
      Use: resolve(__dirname, 'resources/js/composables'),
      View: resolve(__dirname, 'resources/js/views')
    },
  },
  build: {
    chunkSizeWarningLimit: 800,
    manifest: '.vite/manifest.json',
    emptyOutDir: true,
    outDir: '../../../public/vendor/admin',
    rollupOptions: {
      input: resolve(__dirname, 'resources/js/main.ts'),
    },
  },
}

const configProduction = {
  esbuild: {
    drop: ['console', 'debugger'],
  },
}

export default defineConfig(({ command }) => {
  if (command === 'build') {
    return Object.assign(config, configProduction)
  }

  return config
})
