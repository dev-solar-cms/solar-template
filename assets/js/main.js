/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Entry point of the theme's compiled front-end script (assets/dist/main.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Placeholder entry point for the JS build pipeline. Real front-end behaviour is added
 *          in later steps; this file only proves the build pipeline itself works end to end. It
 *          imports the SCSS entry point so Vite bundles both into a single build step, emitting
 *          `assets/dist/main.js` and `assets/dist/main.css`.
 */

import '../scss/main.scss';

document.addEventListener('DOMContentLoaded', () => {
	document.documentElement.classList.add('solar-template-ready');
});
