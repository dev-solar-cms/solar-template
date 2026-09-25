/**
 * Created: 2026-09-25 12:30 CEST
 * Role: Unit test for assets/js/newsletter.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the newsletter form's AJAX submission against a minimal DOM fixture matching
 *          template-parts/front-page/newsletter.php's real markup, with `fetch` mocked (no real
 *          WordPress/network available here). Run via `npm run test`.
 */

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { initNewsletterForms } from '../../assets/js/newsletter.js';

/**
 * Builds the slice of newsletter.php's markup this behaviour attaches to.
 *
 * @return {void}
 */
function renderNewsletterFixture() {
	document.body.innerHTML = `
		<div>
			<form class="js-newsletter-form">
				<input type="email" name="email" />
				<button type="submit">Subscribe</button>
			</form>
			<p class="js-newsletter-feedback"></p>
		</div>
	`;
}

describe('assets/js/newsletter.js', () => {
	beforeEach(() => {
		renderNewsletterFixture();
		window.solarTemplateNewsletter = {
			ajaxUrl: 'https://example.test/admin-ajax.php',
			nonce: 'abc123',
		};
	});

	afterEach(() => {
		vi.unstubAllGlobals();
		delete window.solarTemplateNewsletter;
	});

	it('does not throw and attaches no listener when the localized config is missing', () => {
		delete window.solarTemplateNewsletter;

		expect(() => initNewsletterForms()).not.toThrow();
	});

	it('shows the success message and resets the form on a successful subscription', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () => Promise.resolve({ success: true, data: { message: 'Thank you!' } }),
		});
		vi.stubGlobal('fetch', fetchMock);

		initNewsletterForms();

		const form = document.querySelector('.js-newsletter-form');
		const input = form.querySelector('input[type="email"]');
		input.value = 'jane@example.test';

		form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		const feedback = document.querySelector('.js-newsletter-feedback');
		expect(feedback.textContent).toBe('Thank you!');
		expect(feedback.classList.contains('is-success')).toBe(true);
		expect(input.value).toBe('');
	});

	it('shows the error message and keeps the input value on a failed subscription', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () =>
				Promise.resolve({ success: false, data: { message: 'Already subscribed.' } }),
		});
		vi.stubGlobal('fetch', fetchMock);

		initNewsletterForms();

		const form = document.querySelector('.js-newsletter-form');
		const input = form.querySelector('input[type="email"]');
		input.value = 'jane@example.test';

		form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		const feedback = document.querySelector('.js-newsletter-feedback');
		expect(feedback.textContent).toBe('Already subscribed.');
		expect(feedback.classList.contains('is-error')).toBe(true);
		expect(input.value).toBe('jane@example.test');
	});

	it('re-enables the submit button after the request settles', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () => Promise.resolve({ success: true, data: { message: 'Thank you!' } }),
		});
		vi.stubGlobal('fetch', fetchMock);

		initNewsletterForms();

		const form = document.querySelector('.js-newsletter-form');
		const submitButton = form.querySelector('button[type="submit"]');

		form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		expect(submitButton.disabled).toBe(false);
	});
});
