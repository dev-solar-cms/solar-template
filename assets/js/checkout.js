/**
 * Created: 2026-09-26 10:55 CEST
 * Role: Checkout flow front-end behaviour (assets/js/checkout.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Progressive enhancement for the cart page's quantity steppers — the "−"/"+" buttons
 *          only adjust the same native `<input type="number">` WooCommerce's own
 *          `woocommerce_quantity_input()` already renders (min/max already enforced by that
 *          helper); the real update still happens through the plain "Update cart" form submission,
 *          same convention as assets/js/product.js's own quantity stepper. Also drives the checkout
 *          page's step wizard (woocommerce/checkout/form-checkout.php's `[data-step-panel]`
 *          sections): showing one panel at a time and keeping the step indicator
 *          (template-parts/checkout/step-indicator.php) in sync, without ever submitting the
 *          underlying checkout form before the customer's final "place order" click.
 */

/**
 * Step keys that live on the single checkout page and can be switched between client-side, in
 * wizard order. "payment" is reserved for a future step that splits it out of "shipping" into its
 * own styled panel.
 *
 * @type {string[]}
 */
const IN_PAGE_STEPS = ['login', 'shipping', 'payment'];

/** @type {((event: MouseEvent) => void)|null} */
let checkoutStepsClickHandler = null;

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

/**
 * Updates the step indicator's done/active/future state for the login/shipping/payment steps to
 * match the newly active in-page step (the "cart"/"confirmation" steps are real separate pages, so
 * their own state stays exactly as the server rendered it).
 *
 * @param {string} activeStep One of IN_PAGE_STEPS.
 * @return {void}
 */
function updateStepIndicator(activeStep) {
	const activeIndex = IN_PAGE_STEPS.indexOf(activeStep);

	document.querySelectorAll('.checkout-steps__item[data-step]').forEach((item) => {
		const stepIndex = IN_PAGE_STEPS.indexOf(item.dataset.step);

		if (-1 === stepIndex) {
			return;
		}

		const state =
			stepIndex < activeIndex ? 'done' : stepIndex === activeIndex ? 'active' : 'future';

		item.classList.remove(
			'checkout-steps__item--done',
			'checkout-steps__item--active',
			'checkout-steps__item--future',
		);
		item.classList.add(`checkout-steps__item--${state}`);

		const circle = item.querySelector('.checkout-steps__circle');
		if (!circle) {
			return;
		}

		const symbol = circle.querySelector('[aria-hidden="true"]') || circle;
		symbol.textContent = 'done' === state ? '✓' : item.dataset.stepNumber;

		if ('BUTTON' === circle.tagName) {
			circle.disabled = 'future' === state;
		}
	});
}

/**
 * Shows the given step's panel and hides every other `[data-step-panel]` section, then syncs the
 * step indicator to match. Does nothing if no panel matches (e.g. the "payment" step, not split
 * into its own panel yet).
 *
 * @param {string} step Step key to show.
 * @return {void}
 */
function showCheckoutStep(step) {
	const panels = document.querySelectorAll('[data-step-panel]');
	const matchingPanel = Array.from(panels).find((panel) => panel.dataset.stepPanel === step);

	if (!matchingPanel) {
		return;
	}

	panels.forEach((panel) => {
		panel.hidden = panel !== matchingPanel;
	});

	updateStepIndicator(step);
}

/**
 * Wires up the checkout page's step wizard: clicking a step indicator circle (once reached) or one
 * of the login panel's own "Continue as guest"/"Create an account" buttons switches the visible
 * `[data-step-panel]` section, without ever touching the underlying checkout form's own fields or
 * submitting it.
 *
 * @return {void}
 */
export function initCheckoutSteps() {
	if (!document.querySelector('[data-step-panel]')) {
		return;
	}

	if (checkoutStepsClickHandler) {
		document.removeEventListener('click', checkoutStepsClickHandler);
	}

	checkoutStepsClickHandler = (event) => {
		const trigger = event.target.closest('[data-goto-step]');

		if (!trigger || trigger.disabled || !IN_PAGE_STEPS.includes(trigger.dataset.gotoStep)) {
			return;
		}

		if (trigger.dataset.checkCreateAccount) {
			const createAccountCheckbox = document.getElementById('createaccount');

			if (createAccountCheckbox && !createAccountCheckbox.checked) {
				createAccountCheckbox.checked = true;
				createAccountCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
			}
		}

		event.preventDefault();
		showCheckoutStep(trigger.dataset.gotoStep);
	};

	document.addEventListener('click', checkoutStepsClickHandler);
}
