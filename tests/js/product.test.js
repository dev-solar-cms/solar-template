/**
 * Created: 2026-09-25 16:14 CEST
 * Role: Unit test for assets/js/product.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the product gallery's thumbnail-click behaviour against a minimal DOM fixture
 *          matching template-parts/single-product/gallery.php's real markup, without a real
 *          WordPress/browser available. Run via `npm run test`.
 */

import { beforeEach, describe, expect, it } from 'vitest';
import { initProductGallery } from '../../assets/js/product.js';

/**
 * Builds the slice of gallery.php's markup this behaviour attaches to.
 *
 * @return {void}
 */
function renderGalleryFixture() {
	document.body.innerHTML = `
		<div class="product-gallery">
			<img class="product-gallery__main-image" src="https://example.test/one.jpg" alt="One" />
			<div class="product-gallery__thumbnails">
				<button type="button" class="product-gallery__thumbnail is-active" data-full="https://example.test/one.jpg" data-alt="One"></button>
				<button type="button" class="product-gallery__thumbnail" data-full="https://example.test/two.jpg" data-alt="Two"></button>
			</div>
		</div>
	`;
}

describe('assets/js/product.js', () => {
	beforeEach(() => {
		renderGalleryFixture();
	});

	describe('initProductGallery', () => {
		it('swaps the main image and its active thumbnail on click', () => {
			initProductGallery();

			const mainImage = document.querySelector('.product-gallery__main-image');
			const thumbnails = document.querySelectorAll('.product-gallery__thumbnail');

			thumbnails[1].dispatchEvent(new Event('click', { bubbles: true }));

			expect(mainImage.src).toBe('https://example.test/two.jpg');
			expect(mainImage.alt).toBe('Two');
			expect(thumbnails[0].classList.contains('is-active')).toBe(false);
			expect(thumbnails[1].classList.contains('is-active')).toBe(true);
		});

		it('does nothing on a page without a product gallery', () => {
			document.body.innerHTML = '<p>No gallery here</p>';

			expect(() => initProductGallery()).not.toThrow();
		});
	});
});
