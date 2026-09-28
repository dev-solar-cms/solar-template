/**
 * Created: 2026-09-25 08:30 CEST
 * Role: Unit test for assets/js/header.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the "Collections" mega menu and search overlay wiring against a minimal DOM
 *          fixture matching header.php's real markup, without a real WordPress/browser available.
 *          Run via `npm run test`.
 */

import { beforeEach, describe, expect, it, vi } from 'vitest';
import { initMegaMenu, initSearchOverlay, initTransparentHeader } from '../../assets/js/header.js';

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

		it('does not attach a duplicate outside-click listener when called twice', () => {
			initMegaMenu();
			initMegaMenu();

			const nav = document.querySelector('.site-header__nav');
			const trigger = document.querySelector('.has-mega-menu');

			trigger.dispatchEvent(new Event('mouseenter'));
			expect(nav.classList.contains('is-mega-menu-open')).toBe(true);

			const removeSpy = vi.spyOn(nav.classList, 'remove');

			document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }));

			expect(removeSpy).toHaveBeenCalledTimes(1);
			expect(nav.classList.contains('is-mega-menu-open')).toBe(false);
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

		it('does not attach a duplicate outside-click listener when called twice', () => {
			initSearchOverlay();
			initSearchOverlay();

			const toggle = document.getElementById('site-search-toggle');
			const overlay = document.getElementById('site-search');

			// Opened directly (bypassing the toggle button) so this test isolates the outside-click
			// listener under test here — the toggle's own click listener is element-scoped, not
			// document/window, so it's a separate concern (out of scope) and still accumulates on a
			// repeated call like this test's.
			overlay.classList.add('is-open');

			const removeSpy = vi.spyOn(overlay.classList, 'remove');

			document.body.dispatchEvent(new MouseEvent('click', { bubbles: true }));

			expect(removeSpy).toHaveBeenCalledTimes(1);
			expect(overlay.classList.contains('is-open')).toBe(false);
			expect(toggle.getAttribute('aria-expanded')).toBe('false');
		});
	});

	describe('initTransparentHeader', () => {
		/**
		 * Builds a transparent-on-home header fixture.
		 *
		 * @return {void}
		 */
		function renderTransparentHeaderFixture() {
			document.body.innerHTML = `
				<header id="site-header" class="site-header--transparent-home"></header>
			`;
		}

		/**
		 * jsdom's `window.scrollY` is a read-only getter — overridden per test via
		 * `Object.defineProperty` (same approach as tests/js/blog.test.js does for
		 * `navigator.share`/`navigator.clipboard`).
		 *
		 * @param {number} value Scroll position to simulate.
		 * @return {void}
		 */
		function setScrollY(value) {
			Object.defineProperty(window, 'scrollY', { value, configurable: true });
		}

		it('adds the scrolled class past the threshold and removes it above it', () => {
			renderTransparentHeaderFixture();
			initTransparentHeader();

			const header = document.getElementById('site-header');

			setScrollY(100);
			window.dispatchEvent(new Event('scroll'));
			expect(header.classList.contains('site-header--scrolled')).toBe(true);

			setScrollY(0);
			window.dispatchEvent(new Event('scroll'));
			expect(header.classList.contains('site-header--scrolled')).toBe(false);
		});

		it('does nothing when the header lacks the transparent-on-home modifier', () => {
			document.body.innerHTML = '<header id="site-header"></header>';

			expect(() => initTransparentHeader()).not.toThrow();

			setScrollY(100);
			window.dispatchEvent(new Event('scroll'));

			expect(
				document.getElementById('site-header').classList.contains('site-header--scrolled'),
			).toBe(false);
		});

		it('does not attach a duplicate scroll listener when called twice', () => {
			renderTransparentHeaderFixture();
			initTransparentHeader();
			initTransparentHeader();

			const header = document.getElementById('site-header');
			const toggleSpy = vi.spyOn(header.classList, 'toggle');

			setScrollY(100);
			window.dispatchEvent(new Event('scroll'));

			expect(toggleSpy).toHaveBeenCalledTimes(1);
		});
	});
});
