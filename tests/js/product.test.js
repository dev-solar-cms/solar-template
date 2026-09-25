/**
 * Created: 2026-09-25 16:14 CEST
 * Role: Unit test for assets/js/product.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the product gallery's thumbnail-click behaviour and the variation
 *          selection/price-recalculation logic against minimal DOM fixtures matching
 *          template-parts/single-product/gallery.php/panel.php's real markup, without a real
 *          WordPress/browser available. Run via `npm run test`.
 */

import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { formatPrice, initProductGallery, initProductVariations } from '../../assets/js/product.js';

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

/**
 * Builds the slice of panel.php's markup a variable product's cart form/selectors attach to, with
 * one available and one unavailable size option (matching the design handoff's static "this size
 * is out of stock" treatment) and a single available color.
 *
 * @return {void}
 */
function renderVariationsFixture() {
	document.body.innerHTML = `
		<span class="product-panel__price-amount" data-product-price>20,00&nbsp;€ – 50,00&nbsp;€</span>
		<div class="product-panel__variation-group" data-attribute="pa_color" data-type="color">
			<span class="product-panel__variation-value" data-selected-label></span>
			<button type="button" class="product-panel__swatch" data-value="black" data-label="Black"></button>
			<button type="button" class="product-panel__swatch" data-value="gold" data-label="Gold"></button>
		</div>
		<div class="product-panel__variation-group" data-attribute="pa_size" data-type="size">
			<button type="button" class="product-panel__size-option" data-value="m">M</button>
			<button type="button" class="product-panel__size-option is-unavailable" data-value="xxl" disabled>XXL</button>
		</div>
		<p data-variation-message hidden></p>
		<form class="product-panel__cart-form">
			<input type="hidden" name="attribute_pa_color" value="" />
			<input type="hidden" name="attribute_pa_size" value="" />
			<input type="hidden" class="product-panel__variation-id" name="variation_id" value="0" />
			<div class="product-panel__quantity">
				<button type="button" class="product-panel__qty-decrease"></button>
				<input type="number" class="product-panel__qty-input" name="quantity" value="1" min="1" />
				<button type="button" class="product-panel__qty-increase"></button>
			</div>
			<button type="submit" class="product-panel__add-to-cart" disabled>Add to cart</button>
		</form>
	`;

	window.solarTemplateProduct = {
		priceFormat: {
			decimals: 2,
			decimalSeparator: ',',
			thousandSeparator: ' ',
			format: '%2$s&nbsp;%1$s',
			currencySymbol: '€',
		},
		variations: [
			{
				variation_id: 101,
				attributes: { pa_color: 'black', pa_size: 'm' },
				is_in_stock: true,
				display_price: 42,
			},
			{
				variation_id: 102,
				attributes: { pa_color: 'black', pa_size: 'xxl' },
				is_in_stock: false,
				display_price: 42,
			},
			{
				variation_id: 103,
				attributes: { pa_color: 'gold', pa_size: 'm' },
				is_in_stock: false,
				display_price: 62,
			},
		],
		i18n: {
			unavailable: 'This combination is currently unavailable.',
			outOfStock: 'This combination is currently out of stock.',
		},
	};
}

describe('assets/js/product.js', () => {
	afterEach(() => {
		delete window.solarTemplateProduct;
	});

	describe('initProductVariations', () => {
		beforeEach(() => {
			renderVariationsFixture();
		});

		it('leaves "Add to cart" disabled until every group has a selection', () => {
			initProductVariations();

			document
				.querySelector('.product-panel__swatch')
				.dispatchEvent(new Event('click', { bubbles: true }));

			const addToCartButton = document.querySelector('.product-panel__add-to-cart');

			expect(addToCartButton.disabled).toBe(true);
		});

		it('resolves a valid selection: updates price, variation_id, and enables "Add to cart"', () => {
			initProductVariations();

			document
				.querySelector('.product-panel__swatch')
				.dispatchEvent(new Event('click', { bubbles: true }));
			document
				.querySelector('.product-panel__size-option[data-value="m"]')
				.dispatchEvent(new Event('click', { bubbles: true }));

			const addToCartButton = document.querySelector('.product-panel__add-to-cart');
			const variationIdInput = document.querySelector('.product-panel__variation-id');
			const priceElement = document.querySelector('[data-product-price]');
			const message = document.querySelector('[data-variation-message]');

			expect(addToCartButton.disabled).toBe(false);
			expect(variationIdInput.value).toBe('101');
			expect(priceElement.textContent).toContain('42,00');
			expect(message.hidden).toBe(true);
		});

		it('disables "Add to cart" and shows a message for an out-of-stock combination', () => {
			initProductVariations();

			document
				.querySelector('.product-panel__swatch[data-value="gold"]')
				.dispatchEvent(new Event('click', { bubbles: true }));
			document
				.querySelector('.product-panel__size-option[data-value="m"]')
				.dispatchEvent(new Event('click', { bubbles: true }));

			const addToCartButton = document.querySelector('.product-panel__add-to-cart');
			const message = document.querySelector('[data-variation-message]');

			expect(addToCartButton.disabled).toBe(true);
			expect(message.hidden).toBe(false);
			expect(message.textContent).toBe('This combination is currently out of stock.');
		});

		it('ignores a click on a disabled (unavailable) option', () => {
			initProductVariations();

			document
				.querySelector('.product-panel__size-option[data-value="xxl"]')
				.dispatchEvent(new Event('click', { bubbles: true }));

			expect(
				document
					.querySelector('.product-panel__size-option[data-value="xxl"]')
					.classList.contains('is-active'),
			).toBe(false);
		});

		it('increases and decreases the quantity input, clamped to a minimum of 1', () => {
			initProductVariations();

			const input = document.querySelector('.product-panel__qty-input');
			const decreaseButton = document.querySelector('.product-panel__qty-decrease');
			const increaseButton = document.querySelector('.product-panel__qty-increase');

			increaseButton.dispatchEvent(new Event('click', { bubbles: true }));
			expect(input.value).toBe('2');

			decreaseButton.dispatchEvent(new Event('click', { bubbles: true }));
			decreaseButton.dispatchEvent(new Event('click', { bubbles: true }));
			expect(input.value).toBe('1');
		});

		it('does nothing on a page without a cart form', () => {
			document.body.innerHTML = '<p>No product panel here</p>';

			expect(() => initProductVariations()).not.toThrow();
		});
	});

	describe('initProductVariations with custom engraving', () => {
		beforeEach(() => {
			document.body.innerHTML = `
				<span class="product-panel__price-amount" data-product-price>30,00&nbsp;€</span>
				<form class="product-panel__cart-form">
					<div class="product-panel__engraving">
						<input type="checkbox" class="product-panel__engraving-toggle" data-surcharge="25" />
						<div class="product-panel__engraving-field">
							<input type="text" class="product-panel__engraving-input" />
						</div>
					</div>
					<div class="product-panel__quantity">
						<button type="button" class="product-panel__qty-decrease"></button>
						<input type="number" class="product-panel__qty-input" name="quantity" value="1" min="1" />
						<button type="button" class="product-panel__qty-increase"></button>
					</div>
					<button type="submit" class="product-panel__add-to-cart">Add to cart</button>
				</form>
			`;

			window.solarTemplateProduct = {
				priceFormat: {
					decimals: 2,
					decimalSeparator: ',',
					thousandSeparator: ' ',
					format: '%2$s&nbsp;%1$s',
					currencySymbol: '€',
				},
				basePrice: 30,
				variations: [],
			};
		});

		it('adds the surcharge to the displayed price once toggled on, and removes it once off', () => {
			initProductVariations();

			const toggle = document.querySelector('.product-panel__engraving-toggle');
			const priceElement = document.querySelector('[data-product-price]');
			const field = document.querySelector('.product-panel__engraving-field');
			const input = document.querySelector('.product-panel__engraving-input');

			toggle.checked = true;
			toggle.dispatchEvent(new Event('change', { bubbles: true }));

			expect(priceElement.textContent).toContain('55,00');
			expect(field.classList.contains('is-open')).toBe(true);
			expect(input.required).toBe(true);

			toggle.checked = false;
			toggle.dispatchEvent(new Event('change', { bubbles: true }));

			expect(priceElement.textContent).toContain('30,00');
			expect(field.classList.contains('is-open')).toBe(false);
			expect(input.required).toBe(false);
		});
	});

	describe('formatPrice', () => {
		it('formats an amount using the store price display settings', () => {
			const format = {
				decimals: 2,
				decimalSeparator: ',',
				thousandSeparator: ' ',
				format: '%2$s&nbsp;%1$s',
				currencySymbol: '€',
			};

			expect(formatPrice(1234.5, format)).toBe('1 234,50&nbsp;€');
		});
	});
});
