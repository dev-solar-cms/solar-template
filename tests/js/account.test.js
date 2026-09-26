/**
 * Created: 2026-09-26 17:55 CEST
 * Role: Unit test for assets/js/account.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the wishlist toggle button's AJAX behaviour against a minimal DOM fixture
 *          matching template-parts/product-card.php's real markup, with `fetch` mocked (no real
 *          WordPress/network available here). Run via `npm run test`.
 */

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { initWishlistToggle } from '../../assets/js/account.js';

/**
 * Builds the slice of product-card.php's markup this behaviour attaches to.
 *
 * @return {void}
 */
function renderProductCardFixture() {
	document.body.innerHTML = `
		<div class="product-card">
			<button
				type="button"
				class="product-card__wishlist"
				aria-pressed="false"
				data-product-id="41"
			></button>
		</div>
	`;
}

describe('assets/js/account.js', () => {
	beforeEach(() => {
		renderProductCardFixture();
		window.solarTemplateWishlist = {
			ajaxUrl: 'https://example.test/admin-ajax.php',
			nonce: 'abc123',
			isLoggedIn: true,
			loginUrl: 'https://example.test/my-account/',
		};
	});

	afterEach(() => {
		vi.unstubAllGlobals();
		delete window.solarTemplateWishlist;
	});

	it('does not throw and attaches no listener when the localized config is missing', () => {
		delete window.solarTemplateWishlist;

		expect(() => initWishlistToggle()).not.toThrow();
	});

	it('marks the button active when the toggle response reports it is now wishlisted', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () => Promise.resolve({ success: true, data: { wishlisted: true } }),
		});
		vi.stubGlobal('fetch', fetchMock);

		initWishlistToggle();

		const button = document.querySelector('.product-card__wishlist');
		button.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		expect(button.classList.contains('is-active')).toBe(true);
		expect(button.getAttribute('aria-pressed')).toBe('true');
		expect(button.disabled).toBe(false);

		const [, requestInit] = fetchMock.mock.calls[0];
		const body = new URLSearchParams(requestInit.body);
		expect(body.get('action')).toBe('solar_template_wishlist_toggle');
		expect(body.get('product_id')).toBe('41');
	});

	it('unmarks the button when the toggle response reports it was removed', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () => Promise.resolve({ success: true, data: { wishlisted: false } }),
		});
		vi.stubGlobal('fetch', fetchMock);

		initWishlistToggle();

		const button = document.querySelector('.product-card__wishlist');
		button.classList.add('is-active');
		button.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		expect(button.classList.contains('is-active')).toBe(false);
		expect(button.getAttribute('aria-pressed')).toBe('false');
	});

	it('redirects to the login page instead of calling the API when logged out', () => {
		window.solarTemplateWishlist.isLoggedIn = false;
		const fetchMock = vi.fn();
		vi.stubGlobal('fetch', fetchMock);

		delete window.location;
		window.location = { href: '' };

		initWishlistToggle();

		const button = document.querySelector('.product-card__wishlist');
		button.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		expect(fetchMock).not.toHaveBeenCalled();
		expect(window.location.href).toBe('https://example.test/my-account/');
	});

	it('redirects when the API reports the visitor is not authenticated', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () =>
				Promise.resolve({
					success: false,
					data: { redirect: 'https://example.test/my-account/' },
				}),
		});
		vi.stubGlobal('fetch', fetchMock);

		delete window.location;
		window.location = { href: '' };

		initWishlistToggle();

		const button = document.querySelector('.product-card__wishlist');
		button.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		expect(window.location.href).toBe('https://example.test/my-account/');
	});
});
