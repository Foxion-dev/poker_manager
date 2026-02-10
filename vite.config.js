import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

const devHost = process.env.VITE_DEV_SERVER_HOST || 'localhost';
const devPort = Number(process.env.VITE_DEV_SERVER_PORT || 5173);

export default defineConfig({
	plugins: [
		laravel({
			input: ['resources/css/app.css', 'resources/js/app.js'],
			refresh: true,
			buildDirectory: 'build',
		}),
		vue({
			template: {
				transformAssetUrls: {
					base: null,
					includeAbsolute: false,
				},
			},
		}),
		tailwindcss(),
	],
	build: {
		emptyOutDir: true,
		manifest: true,
		outDir: 'public/build',
		rollupOptions: {
			output: {
				manualChunks: undefined,
			},
		},
	},
	server: {
		host: '0.0.0.0',
		port: devPort,
		hmr: {
			host: devHost,
			port: devPort,
		},
		watch: {
			ignored: ['**/storage/framework/views/**'],
		},
	},
});
