/**
 * Created: 2026-09-26 22:40 CEST
 * Role: Unit test for assets/js/blog.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the article share button against a minimal DOM fixture matching
 *          template-parts/single-article/header.php's own markup, with the Web Share API/clipboard
 *          mocked (no real browser capability available here). Run via `npm run test`.
 */

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { initArticleShare } from '../../assets/js/blog.js';

/**
 * Builds the share button fixture (header.php's own markup).
 *
 * @return {void}
 */
function renderShareButtonFixture() {
	document.body.innerHTML = `
		<button
			type="button"
			class="article-share-button btn-link"
			data-share-title="Guide"
			data-share-url="https://example.test/guide/"
			data-copied-label="Link copied!"
		>Share</button>
	`;
}

describe('assets/js/blog.js', () => {
	beforeEach(() => {
		renderShareButtonFixture();
	});

	afterEach(() => {
		vi.unstubAllGlobals();
		delete navigator.share;
		delete navigator.clipboard;
	});

	it('calls the native Web Share API when available', async () => {
		const shareMock = vi.fn().mockResolvedValue(undefined);
		Object.defineProperty(navigator, 'share', { value: shareMock, configurable: true });

		initArticleShare();

		const button = document.querySelector('.article-share-button');
		button.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		await vi.waitFor(() => expect(shareMock).toHaveBeenCalledTimes(1));
		expect(shareMock).toHaveBeenCalledWith({ title: 'Guide', url: 'https://example.test/guide/' });
	});

	it('falls back to copying the link when the Web Share API is unavailable', async () => {
		const writeTextMock = vi.fn().mockResolvedValue(undefined);
		Object.defineProperty(navigator, 'clipboard', {
			value: { writeText: writeTextMock },
			configurable: true,
		});

		initArticleShare();

		const button = document.querySelector('.article-share-button');
		button.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		await vi.waitFor(() => expect(writeTextMock).toHaveBeenCalledWith('https://example.test/guide/'));
		expect(button.textContent).toBe('Link copied!');
	});

	it('does not attach a duplicate listener when called more than once', () => {
		const shareMock = vi.fn().mockResolvedValue(undefined);
		Object.defineProperty(navigator, 'share', { value: shareMock, configurable: true });

		initArticleShare();
		initArticleShare();

		const button = document.querySelector('.article-share-button');
		button.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		expect(shareMock).toHaveBeenCalledTimes(1);
	});
});
