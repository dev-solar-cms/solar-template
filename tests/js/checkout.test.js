/**
 * Created: 2026-09-26 11:00 CEST
 * Role: Unit test for assets/js/checkout.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the cart page's quantity stepper against a minimal DOM fixture matching
 *          woocommerce/cart/cart.php's real markup (a native WooCommerce quantity input wrapped by
 *          the theme's own "−"/"+" buttons), without a real WordPress/browser available.
 */

import { beforeEach, describe, expect, it } from 'vitest';
import { initCartQuantitySteppers } from '../../assets/js/checkout.js';

/**
 * Builds two cart rows' worth of quantity stepper markup, one with a max quantity set.
 *
 * @return {void}
 */
function renderCartFixture() {
	document.body.innerHTML = `
		<div class="cart-page__qty">
			<button type="button" class="cart-page__qty-decrease"></button>
			<input type="number" class="qty" value="1" min="0" />
			<button type="button" class="cart-page__qty-increase"></button>
		</div>
		<div class="cart-page__qty">
			<button type="button" class="cart-page__qty-decrease"></button>
			<input type="number" class="qty" value="2" min="1" max="3" />
			<button type="button" class="cart-page__qty-increase"></button>
		</div>
	`;
}

describe('assets/js/checkout.js', () => {
	beforeEach(() => {
		renderCartFixture();
	});

	describe('initCartQuantitySteppers', () => {
		it('increments and decrements the row it belongs to', () => {
			initCartQuantitySteppers();

			const [firstRow, secondRow] = document.querySelectorAll('.cart-page__qty');
			const firstInput = firstRow.querySelector('input.qty');
			const secondInput = secondRow.querySelector('input.qty');

			firstRow.querySelector('.cart-page__qty-increase').click();
			expect(firstInput.value).toBe('2');

			secondRow.querySelector('.cart-page__qty-decrease').click();
			expect(secondInput.value).toBe('1');
			// The second row's own quantity is untouched by the first row's stepper.
			expect(firstInput.value).toBe('2');
		});

		it("never decreases below the input's own min value", () => {
			initCartQuantitySteppers();

			const [firstRow] = document.querySelectorAll('.cart-page__qty');
			const input = firstRow.querySelector('input.qty');
			input.value = '0';

			firstRow.querySelector('.cart-page__qty-decrease').click();

			expect(input.value).toBe('0');
		});

		it("never increases above the input's own max value", () => {
			initCartQuantitySteppers();

			const [, secondRow] = document.querySelectorAll('.cart-page__qty');
			const input = secondRow.querySelector('input.qty');
			input.value = '3';

			secondRow.querySelector('.cart-page__qty-increase').click();

			expect(input.value).toBe('3');
		});
	});
});
