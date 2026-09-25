/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Entry point of the theme's compiled front-end script (assets/dist/main.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Entry point for the theme's compiled front-end script. Imports the SCSS entry point so
 *          Vite bundles both into a single build step (`assets/dist/main.js`/`main.css`), then
 *          wires up every front-end behaviour module (see assets/js/header.js) once the DOM is
 *          ready.
 */

import '../scss/main.scss';
import { initMegaMenu, initSearchOverlay } from './header.js';
import { initNewsletterForms } from './newsletter.js';

document.addEventListener('DOMContentLoaded', () => {
	document.documentElement.classList.add('solar-template-ready');
	initMegaMenu();
	initSearchOverlay();
	initNewsletterForms();
});
