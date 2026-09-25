/**
 * Created: 2026-09-25 16:14 CEST
 * Role: Front-end behaviour for the product page (assets/js/product.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Swap the main product image when a thumbnail is clicked (template-parts/single-product/
 *          gallery.php), and, for a variable product, resolve a Color/Size selection against its
 *          real WooCommerce variations (window.solarTemplateProduct, localized by
 *          Solar_Template\Product\ProductController): recompute the displayed price, the hidden
 *          `variation_id` submitted with the "Add to cart" form, and that button's disabled state.
 *          Does nothing (and throws no error) on a page without the relevant markup.
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

/**
 * Wires up the product panel's quantity stepper and, for a variable product, its Color/Size
 * selectors: resolving a full selection against the real variation payload updates the displayed
 * price, the "Add to cart" form's hidden `variation_id`, and that button's disabled state.
 *
 * @return {void}
 */
export function initProductVariations() {
	const form = document.querySelector('.product-panel__cart-form');

	if (!form) {
		return;
	}

	wireQuantityStepper(form);

	const config = window.solarTemplateProduct;
	const groups = Array.from(document.querySelectorAll('.product-panel__variation-group'));

	if (!groups.length || !config || !Array.isArray(config.variations)) {
		return;
	}

	const variationIdInput = form.querySelector('.product-panel__variation-id');
	const priceElement = document.querySelector('[data-product-price]');
	const addToCartButton = form.querySelector('.product-panel__add-to-cart');
	const messageElement = document.querySelector('[data-variation-message]');
	const selection = {};

	/**
	 * Resolves the current selection (one value per group) against config.variations, updating the
	 * price/hidden input/button/message accordingly.
	 *
	 * @return {void}
	 */
	function resolveSelection() {
		if (Object.keys(selection).length < groups.length) {
			setUnresolved();
			return;
		}

		const match = config.variations.find((variation) =>
			groups.every((group) => {
				const value = variation.attributes[group.dataset.attribute];

				return '' === value || value === selection[group.dataset.attribute];
			}),
		);

		if (!match) {
			setUnavailable();
			return;
		}

		if (!match.is_in_stock) {
			setOutOfStock(match);
			return;
		}

		setResolved(match);
	}

	/**
	 * @return {void}
	 */
	function setUnresolved() {
		if (variationIdInput) {
			variationIdInput.value = '0';
		}

		if (addToCartButton) {
			addToCartButton.disabled = true;
		}

		hideMessage();
	}

	/**
	 * @return {void}
	 */
	function setUnavailable() {
		if (variationIdInput) {
			variationIdInput.value = '0';
		}

		if (addToCartButton) {
			addToCartButton.disabled = true;
		}

		showMessage(config.i18n && config.i18n.unavailable);
	}

	/**
	 * @param {{variation_id: number, display_price: number}} match Matched, out-of-stock variation.
	 * @return {void}
	 */
	function setOutOfStock(match) {
		if (variationIdInput) {
			variationIdInput.value = String(match.variation_id);
		}

		if (addToCartButton) {
			addToCartButton.disabled = true;
		}

		updatePrice(match.display_price);
		showMessage(config.i18n && config.i18n.outOfStock);
	}

	/**
	 * @param {{variation_id: number, display_price: number}} match Matched, in-stock variation.
	 * @return {void}
	 */
	function setResolved(match) {
		if (variationIdInput) {
			variationIdInput.value = String(match.variation_id);
		}

		if (addToCartButton) {
			addToCartButton.disabled = false;
		}

		updatePrice(match.display_price);
		hideMessage();
	}

	/**
	 * @param {string|undefined} text Message to show, if any.
	 * @return {void}
	 */
	function showMessage(text) {
		if (!messageElement || !text) {
			return;
		}

		messageElement.textContent = text;
		messageElement.hidden = false;
	}

	/**
	 * @return {void}
	 */
	function hideMessage() {
		if (!messageElement) {
			return;
		}

		messageElement.hidden = true;
		messageElement.textContent = '';
	}

	/**
	 * @param {number} amount Amount to format and display.
	 * @return {void}
	 */
	function updatePrice(amount) {
		if (!priceElement || !config.priceFormat) {
			return;
		}

		priceElement.textContent = formatPrice(amount, config.priceFormat);
	}

	groups.forEach((group) => {
		const taxonomy = group.dataset.attribute;
		const hiddenInput = form.querySelector(`input[name="attribute_${taxonomy}"]`);
		const selectedLabel = group.querySelector('[data-selected-label]');
		const buttons = Array.from(group.querySelectorAll('[data-value]'));

		buttons.forEach((button) => {
			button.addEventListener('click', () => {
				if (button.disabled) {
					return;
				}

				buttons.forEach((otherButton) => otherButton.classList.remove('is-active'));
				button.classList.add('is-active');
				selection[taxonomy] = button.dataset.value;

				if (hiddenInput) {
					hiddenInput.value = button.dataset.value;
				}

				if (selectedLabel) {
					selectedLabel.textContent = button.dataset.label || '';
				}

				resolveSelection();
			});
		});
	});
}

/**
 * Wires up a "−"/"+" quantity stepper next to its number input, clamped to a minimum of 1.
 *
 * @param {HTMLFormElement} form The cart form containing the stepper.
 * @return {void}
 */
function wireQuantityStepper(form) {
	const input = form.querySelector('.product-panel__qty-input');
	const decreaseButton = form.querySelector('.product-panel__qty-decrease');
	const increaseButton = form.querySelector('.product-panel__qty-increase');

	if (!input) {
		return;
	}

	if (decreaseButton) {
		decreaseButton.addEventListener('click', () => {
			input.value = String(Math.max(1, (parseInt(input.value, 10) || 1) - 1));
		});
	}

	if (increaseButton) {
		increaseButton.addEventListener('click', () => {
			input.value = String((parseInt(input.value, 10) || 1) + 1);
		});
	}
}

/**
 * Formats a price the same way WooCommerce's own `wc_price()` would, using the store's real
 * decimal/thousand separators, decimal count and currency symbol position (localized by
 * Solar_Template\Product\ProductController).
 *
 * @param {number} amount Amount to format.
 * @param {{decimals: number, decimalSeparator: string, thousandSeparator: string, format: string, currencySymbol: string}} format Store price display settings.
 * @return {string} Formatted price, e.g. "89,00 €".
 */
export function formatPrice(amount, format) {
	const decimals = typeof format.decimals === 'number' ? format.decimals : 2;
	const decimalSeparator = format.decimalSeparator || '.';
	const thousandSeparator = format.thousandSeparator || ',';
	const [integerPart, decimalPart] = amount.toFixed(decimals).split('.');
	const withThousands = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
	const number =
		undefined === decimalPart
			? withThousands
			: `${withThousands}${decimalSeparator}${decimalPart}`;
	const template = format.format || '%1$s%2$s';

	return template.replace('%1$s', format.currencySymbol || '').replace('%2$s', number);
}
