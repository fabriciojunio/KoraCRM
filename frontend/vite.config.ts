/// <reference types="vitest" />
import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const rootDir = dirname(fileURLToPath(import.meta.url))

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': resolve(rootDir, './src'),
    },
  },
  build: {
    sourcemap: false,
    minify: 'oxc',
    rolldownOptions: {
      output: {
        minify: {
          compress: { dropConsole: true, dropDebugger: true },
        },
        codeSplitting: {
          groups: [
            { name: 'vendor-react', test: /node_modules[\\/]react(?:-dom)?[\\/]/, priority: 20 },
            { name: 'vendor-router', test: /node_modules[\\/]react-router(?:-dom)?[\\/]/, priority: 10 },
            { name: 'vendor-query', test: /node_modules[\\/]@tanstack[\\/]react-query[\\/]/, priority: 10 },
          ],
        },
      },
    },
  },
  server: {
    host: '0.0.0.0',
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://nginx:80',
        changeOrigin: true,
      },
    },
  },
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.ts'],
    // Só o que está em src. Sem isto o Vitest tenta rodar os arquivos de
    // e2e/, que são do Playwright e usam outro test.beforeEach: a bateria
    // quebra com um erro que não tem nada a ver com o código.
    include: ['src/**/*.{test,spec}.{ts,tsx}'],
    exclude: ['node_modules/**', 'dist/**', 'e2e/**'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'lcov'],
      exclude: ['node_modules/', 'src/test/', 'e2e/'],
    },
  },
})
