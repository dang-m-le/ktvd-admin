import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [vue()],

    publicDir: 'public',

    resolve: {
        alias: {
        '@': path.resolve(__dirname, './src'),
        },
    },
    
    server: {
        proxy: {
            '^.*\.php$': { // must begins with the caret(^) to capture the entire path
                target: 'http://localhost:8080',
                changeOrigin: true,
                rewrite: (path) => path.replace(/^/, '/admin')
            }
        }
    }
})
