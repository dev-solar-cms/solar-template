/**
 * Created: 2026-09-26 10:55 CEST
 * Role: Checkout flow front-end behaviour (assets/js/checkout.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Progressive enhancement for the cart page's quantity steppers — the "−"/"+" buttons
 *          only adjust the same native `<input type="number">` WooCommerce's own
 *          `woocommerce_quantity_input()` already renders (min/max already enforced by that
 *          helper); the real update still happens through the plain "Update cart" form submission,
 *          same convention as assets/js/product.js's own quantity stepper.
 */

/**
 * Wires up every cart row's "−"/"+" quantity stepper next to its native number input.
 *
 * @return {void}
 */
export function initCartQuantitySteppers() {
	document.querySelectorAll('.cart-page__qty').forEach((wrapper) => {
		const input = wrapper.querySelector('input.qty');
		const decreaseButton = wrapper.querySelector('.cart-page__qty-decrease');
		const increaseButton = wrapper.querySelector('.cart-page__qty-increase');

		if (!input) {
			return;
		}

		const min = parseInt(input.min, 10) || 0;
		const max = input.max ? parseInt(input.max, 10) : null;

		if (decreaseButton) {
			decreaseButton.addEventListener('click', () => {
				input.value = String(Math.max(min, (parseInt(input.value, 10) || min) - 1));
			});
		}

		if (increaseButton) {
			increaseButton.addEventListener('click', () => {
				const next = (parseInt(input.value, 10) || min) + 1;
				input.value = String(null !== max ? Math.min(max, next) : next);
			});
		}
	});
}
