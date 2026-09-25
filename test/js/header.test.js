/**
 * Created: 2026-09-25 08:30 CEST
 * Role: Unit test for assets/js/header.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the "Collections" mega menu and search toggle wiring against a minimal DOM
 *          fixture matching header.php's real markup, without a real WordPress/browser available.
 *          Run via `npm run test`.
 */

import { beforeEach, describe, expect, it } from 'vitest';
import { initMegaMenu, initSearchToggle } from '../../assets/js/header.js';

/**
 * Builds the slice of header.php's markup these behaviours attach to.
 *
 * @return {void}
 */
function renderHeaderFixture() {
	document.body.innerHTML = `
		<button id="site-search-toggle" aria-expanded="false"></button>
		<nav class="site-header__nav">
			<ul class="site-header__menu">
				<li class="site-header__menu-item"><a href="#">Shop</a></li>
				<li class="site-header__menu-item has-mega-menu"><a href="#">Collections</a></li>
			</ul>
		</nav>
		<div class="site-header__mega-menu" id="site-mega-menu">
			<a href="#" id="mega-menu-link">View All Collections</a>
		</div>
	`;
}

describe('assets/js/header.js', () => {
	beforeEach(() => {
		renderHeaderFixture();
	});

	describe('initMegaMenu', () => {
		it('opens on mouseenter and closes on mouseleave', () => {
			initMegaMenu();

			const nav = document.querySelector('.site-header__nav');
			const trigger = document.querySelector('.has-mega-menu');
			const link = trigger.querySelector('a');

			trigger.dispatchEvent(new Event('mouseenter'));
			expect(nav.classList.contains('is-mega-menu-open')).toBe(true);
			expect(link.getAttribute('aria-expanded')).toBe('true');

			trigger.dispatchEvent(new Event('mouseleave'));
			expect(nav.classList.contains('is-mega-menu-open')).toBe(false);
			expect(link.getAttribute('aria-expanded')).toBe('false');
		});

		it('opens when the trigger link receives keyboard focus', () => {
			initMegaMenu();

			const nav = document.querySelector('.site-header__nav');
			const link = document.querySelector('.has-mega-menu a');

			link.dispatchEvent(new Event('focus'));

			expect(nav.classList.contains('is-mega-menu-open')).toBe(true);
		});

		it('closes on Escape and returns focus to the trigger link', () => {
			initMegaMenu();

			const nav = document.querySelector('.site-header__nav');
			const trigger = document.querySelector('.has-mega-menu');
			const link = trigger.querySelector('a');

			trigger.dispatchEvent(new Event('mouseenter'));
			nav.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));

			expect(nav.classList.contains('is-mega-menu-open')).toBe(false);
			expect(document.activeElement).toBe(link);
		});

		it('closes when a click happens outside the nav', () => {
			initMegaMenu();

			const nav = document.querySelector('.site-header__nav');
			const trigger = document.querySelector('.has-mega-menu');

			trigger.dispatchEvent(new Event('mouseenter'));
			document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }));

			expect(nav.classList.contains('is-mega-menu-open')).toBe(false);
		});

		it('does not throw when the mega menu panel is absent from the page', () => {
			document.getElementById('site-mega-menu').remove();

			expect(() => initMegaMenu()).not.toThrow();
		});
	});

	describe('initSearchToggle', () => {
		it('flips aria-expanded on each click', () => {
			initSearchToggle();

			const toggle = document.getElementById('site-search-toggle');

			toggle.dispatchEvent(new MouseEvent('click', { bubbles: true }));
			expect(toggle.getAttribute('aria-expanded')).toBe('true');

			toggle.dispatchEvent(new MouseEvent('click', { bubbles: true }));
			expect(toggle.getAttribute('aria-expanded')).toBe('false');
		});

		it('does not throw when the toggle button is absent from the page', () => {
			document.getElementById('site-search-toggle').remove();

			expect(() => initSearchToggle()).not.toThrow();
		});
	});
});
