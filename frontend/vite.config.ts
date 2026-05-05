import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: process.env['VITE_API_BASE_URL'] ?? 'https://127.0.0.1:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
