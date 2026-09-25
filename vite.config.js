/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Build configuration for the theme's front-end assets (Solar Template).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compile `assets/js/main.js` (which itself imports `assets/scss/main.scss`) into a
 *          single predictable pair of output files, `assets/dist/main.js` / `assets/dist/main.css`,
 *          enqueued by functions.php with a filemtime-based cache-busting version. Also configures
 *          Vitest (`npm run test`), which reads its settings from this same file's `test` key.
 */

import { defineConfig } from 'vite';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const themeRoot = fileURLToPath(new URL('.', import.meta.url));

export default defineConfig(({ mode }) => ({
	root: themeRoot,
	build: {
		outDir: resolve(themeRoot, 'assets/dist'),
		emptyOutDir: true,
		sourcemap: mode !== 'production',
		rollupOptions: {
			input: resolve(themeRoot, 'assets/js/main.js'),
			output: {
				entryFileNames: 'main.js',
				assetFileNames: 'main[extname]',
			},
		},
	},
	test: {
		environment: 'jsdom',
		include: ['test/js/**/*.test.js'],
	},
}));
