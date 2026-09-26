/**
 * Created: 2026-09-26 11:00 CEST
 * Role: Unit test for assets/js/checkout.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the cart page's quantity stepper against a minimal DOM fixture matching
 *          woocommerce/cart/cart.php's real markup (a native WooCommerce quantity input wrapped by
 *          the theme's own "−"/"+" buttons), without a real WordPress/browser available.
 */

import { beforeEach, describe, expect, it } from 'vitest';
import { initCartQuantitySteppers, initCheckoutSteps } from '../../assets/js/checkout.js';

/**
 * Builds the slice of the checkout page's real markup this behaviour attaches to: the step
 * indicator (template-parts/checkout/step-indicator.php) for the login/shipping/payment steps, plus
 * a login panel and a hidden shipping panel (woocommerce/checkout/form-checkout.php), matching a
 * guest visitor's first page load.
 *
 * @return {void}
 */
function renderCheckoutStepsFixture() {
	document.body.innerHTML = `
		<ol class="checkout-steps__list">
			<li class="checkout-steps__item checkout-steps__item--active" data-step="login" data-step-number="2">
				<button type="button" class="checkout-steps__circle"><span aria-hidden="true">2</span></button>
			</li>
			<li class="checkout-steps__item checkout-steps__item--future" data-step="shipping" data-step-number="3">
				<button type="button" class="checkout-steps__circle" disabled><span aria-hidden="true">3</span></button>
			</li>
			<li class="checkout-steps__item checkout-steps__item--future" data-step="payment" data-step-number="4">
				<button type="button" class="checkout-steps__circle" disabled><span aria-hidden="true">4</span></button>
			</li>
		</ol>
		<div class="checkout-step" data-step-panel="login">
			<button type="button" class="checkout-login-panel__create-account-link" data-goto-step="shipping" data-check-create-account="1">Create an account</button>
			<button type="button" class="checkout-login-panel__guest" data-goto-step="shipping">Continue as guest</button>
		</div>
		<form>
			<div class="checkout-step" data-step-panel="shipping" hidden>
				<p class="create-account">
					<input type="checkbox" id="createaccount" name="createaccount" value="1" />
				</p>
			</div>
		</form>
	`;
}

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

	describe('initCheckoutSteps', () => {
		beforeEach(() => {
			renderCheckoutStepsFixture();
		});

		it('switches from the login panel to the shipping panel and updates the indicator', () => {
			initCheckoutSteps();

			document.querySelector('.checkout-login-panel__guest').click();

			expect(document.querySelector('[data-step-panel="login"]').hidden).toBe(true);
			expect(document.querySelector('[data-step-panel="shipping"]').hidden).toBe(false);

			const loginItem = document.querySelector('.checkout-steps__item[data-step="login"]');
			const shippingItem = document.querySelector(
				'.checkout-steps__item[data-step="shipping"]',
			);

			expect(loginItem.classList.contains('checkout-steps__item--done')).toBe(true);
			expect(loginItem.querySelector('[aria-hidden="true"]').textContent).toBe('✓');
			expect(shippingItem.classList.contains('checkout-steps__item--active')).toBe(true);
			expect(shippingItem.querySelector('.checkout-steps__circle').disabled).toBe(false);
		});

		it('checks the create-account checkbox when its own link is clicked', () => {
			initCheckoutSteps();

			document.querySelector('.checkout-login-panel__create-account-link').click();

			expect(document.getElementById('createaccount').checked).toBe(true);
			expect(document.querySelector('[data-step-panel="shipping"]').hidden).toBe(false);
		});

		it('ignores a click on a disabled (future) step circle', () => {
			initCheckoutSteps();

			document.querySelector('[data-step="payment"] .checkout-steps__circle').click();

			expect(document.querySelector('[data-step-panel="login"]').hidden).toBe(false);
		});

		it('does nothing outside the checkout page (no step panel present)', () => {
			document.body.innerHTML = '<div>plain page</div>';

			expect(() => initCheckoutSteps()).not.toThrow();
		});
	});
});
