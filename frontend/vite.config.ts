import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [sveltekit()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/fogvpn/backend',
        changeOrigin: true,
        secure: false
      }
    }
  },
  build: {
    target: 'es2018',
    sourcemap: true
  }
});
