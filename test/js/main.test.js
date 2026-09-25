/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Unit test for assets/js/main.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Trivial test proving the JS test runner itself works end to end (jsdom environment,
 *          ES module import of the theme's real entry point). Run via `npm run test`.
 */

import { beforeEach, describe, expect, it } from 'vitest';

describe('assets/js/main.js', () => {
	beforeEach(() => {
		document.documentElement.className = '';
	});

	it('adds the "solar-template-ready" class once the DOM is ready', async () => {
		await import('../../assets/js/main.js');

		document.dispatchEvent(new Event('DOMContentLoaded'));

		expect(document.documentElement.classList.contains('solar-template-ready')).toBe(true);
	});
});
