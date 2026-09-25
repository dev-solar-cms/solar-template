/**
 * Created: 2026-09-25 08:30 CEST
 * Role: Unit test for assets/js/header.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the "Collections" mega menu and search overlay wiring against a minimal DOM
 *          fixture matching header.php's real markup, without a real WordPress/browser available.
 *          Run via `npm run test`.
 */

import { beforeEach, describe, expect, it } from 'vitest';
import { initMegaMenu, initSearchOverlay } from '../../assets/js/header.js';

/**
 * Builds the slice of header.php's markup these behaviours attach to.
 *
 * @return {void}
 */
function renderHeaderFixture() {
	document.body.innerHTML = `
		<button id="site-search-toggle" aria-expanded="false" aria-controls="site-search"></button>
		<nav class="site-header__nav">
			<ul class="site-header__menu">
				<li class="site-header__menu-item"><a href="#">Shop</a></li>
				<li class="site-header__menu-item has-mega-menu"><a href="#">Collections</a></li>
			</ul>
		</nav>
		<div class="site-header__mega-menu" id="site-mega-menu">
			<a href="#" id="mega-menu-link">View All Collections</a>
		</div>
		<div class="site-search-overlay" id="site-search">
			<form class="site-search-form">
				<input type="search" class="site-search-form__input" />
			</form>
			<button type="button" id="site-search-close"></button>
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

	describe('initSearchOverlay', () => {
		it('opens the overlay and focuses the search field on toggle click', () => {
			initSearchOverlay();

			const toggle = document.getElementById('site-search-toggle');
			const overlay = document.getElementById('site-search');
			const input = overlay.querySelector('.site-search-form__input');

			toggle.dispatchEvent(new MouseEvent('click', { bubbles: true }));

			expect(overlay.classList.contains('is-open')).toBe(true);
			expect(toggle.getAttribute('aria-expanded')).toBe('true');
			expect(document.activeElement).toBe(input);
		});

		it('toggles closed when the toggle button is clicked again', () => {
			initSearchOverlay();

			const toggle = document.getElementById('site-search-toggle');
			const overlay = document.getElementById('site-search');

			toggle.dispatchEvent(new MouseEvent('click', { bubbles: true }));
			toggle.dispatchEvent(new MouseEvent('click', { bubbles: true }));

			expect(overlay.classList.contains('is-open')).toBe(false);
			expect(toggle.getAttribute('aria-expanded')).toBe('false');
		});

		it('closes and returns focus to the toggle when the close button is clicked', () => {
			initSearchOverlay();

			const toggle = document.getElementById('site-search-toggle');
			const overlay = document.getElementById('site-search');
			const closeButton = document.getElementById('site-search-close');

			toggle.dispatchEvent(new MouseEvent('click', { bubbles: true }));
			closeButton.dispatchEvent(new MouseEvent('click', { bubbles: true }));

			expect(overlay.classList.contains('is-open')).toBe(false);
			expect(document.activeElement).toBe(toggle);
		});

		it('closes on Escape and returns focus to the toggle', () => {
			initSearchOverlay();

			const toggle = document.getElementById('site-search-toggle');
			const overlay = document.getElementById('site-search');

			toggle.dispatchEvent(new MouseEvent('click', { bubbles: true }));
			overlay.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));

			expect(overlay.classList.contains('is-open')).toBe(false);
			expect(document.activeElement).toBe(toggle);
		});

		it('closes when a click happens outside the overlay and the toggle', () => {
			initSearchOverlay();

			const toggle = document.getElementById('site-search-toggle');
			const overlay = document.getElementById('site-search');

			toggle.dispatchEvent(new MouseEvent('click', { bubbles: true }));
			document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }));

			expect(overlay.classList.contains('is-open')).toBe(false);
		});

		it('does not throw when the overlay markup is absent from the page', () => {
			document.getElementById('site-search').remove();

			expect(() => initSearchOverlay()).not.toThrow();
		});
	});
});
