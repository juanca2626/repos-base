import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import * as sass from 'sass'; // Usa la sintaxis de importación
import svgLoader from 'vite-svg-loader';
import VueDevTools from 'vite-plugin-vue-devtools';
import path from 'path';

export default defineConfig({
  plugins: [vue(), svgLoader(), process.env.NODE_ENV !== 'production' && VueDevTools()].filter(
    Boolean
  ),

  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '@views': fileURLToPath(new URL('./src/views', import.meta.url)),
      '@routes': fileURLToPath(new URL('./src/router', import.meta.url)),
      '@controllers': fileURLToPath(new URL('./src/controllers', import.meta.url)),
      '@store': fileURLToPath(new URL('./src/stores', import.meta.url)),
      '@service': fileURLToPath(new URL('./src/services', import.meta.url)),
      '@operations': fileURLToPath(new URL('./src/modules/operations', import.meta.url)),
      '@ordercontrol': fileURLToPath(new URL('./src/modules/order-control', import.meta.url)),
    },
  },

  css: {
    preprocessorOptions: {
      less: {
        modifyVars: {},
        javascriptEnabled: true,
      },
      scss: {
        implementation: sass,
      },
    },
  },

  build: {
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'index.html'), // tu app principal
        login: path.resolve(__dirname, 'src/widgets/login.js'),
      },
      output: {
        entryFileNames: '[name].js',
        globals: {
          vue: 'Vue',
        },
      },
    },
    lib: false, // desactiva modo librería
    outDir: 'dist',
    sourcemap: true,
  },
});
