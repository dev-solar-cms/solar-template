/**
 * Created: 2026-09-25 16:14 CEST
 * Role: Front-end behaviour for the product page (assets/js/product.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Swap the main product image when a thumbnail is clicked (template-parts/single-product/
 *          gallery.php), no page reload. Does nothing (and throws no error) on a page without a
 *          product gallery.
 */

/**
 * Wires up every product gallery's thumbnail strip.
 *
 * @return {void}
 */
export function initProductGallery() {
	document.querySelectorAll('.product-gallery').forEach((gallery) => {
		const mainImage = gallery.querySelector('.product-gallery__main-image');
		const thumbnails = Array.from(gallery.querySelectorAll('.product-gallery__thumbnail'));

		if (!mainImage || !thumbnails.length) {
			return;
		}

		thumbnails.forEach((thumbnail) => {
			thumbnail.addEventListener('click', () => {
				const fullSrc = thumbnail.dataset.full;

				if (!fullSrc) {
					return;
				}

				mainImage.src = fullSrc;
				mainImage.alt = thumbnail.dataset.alt || '';

				thumbnails.forEach((otherThumbnail) =>
					otherThumbnail.classList.remove('is-active'),
				);
				thumbnail.classList.add('is-active');
			});
		});
	});
}
