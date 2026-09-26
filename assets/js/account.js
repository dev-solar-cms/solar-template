/**
 * Created: 2026-09-26 17:25 CEST
 * Role: Front-end behaviour for the My Account area (assets/js/account.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Toggle a product card's wishlist button (`.product-card__wishlist`) via AJAX to
 *          `solar_template_wishlist_toggle` (see inc/Account/WishlistController.php), delegated on
 *          `document` so it also covers cards rendered later (e.g. the catalog's AJAX filter/load
 *          more results). Reads the AJAX endpoint/nonce/login state from
 *          `window.solarTemplateWishlist`, localized by `wp_localize_script()`. A logged-out
 *          visitor is sent to the login page instead of the request being silently rejected.
 *          Also wires up the settings page's account-deletion form (`.account-danger-zone__form`)
 *          with a native `confirm()` dialog as a second, JS-only confirmation layer on top of that
 *          form's own required checkbox (see woocommerce/myaccount/form-edit-account.php).
 */

let wishlistClickHandler = null;
let accountDeletionSubmitHandler = null;

/**
 * Wires up every `.product-card__wishlist` button on the page, current and future. Does nothing
 * when the required localized data is missing (script not enqueued, or `wp_localize_script()`
 * failed against an unregistered handle).
 *
 * @return {void}
 */
export function initWishlistToggle() {
	const config = window.solarTemplateWishlist;

	if (!config) {
		return;
	}

	if (wishlistClickHandler) {
		document.removeEventListener('click', wishlistClickHandler);
	}

	wishlistClickHandler = (event) => {
		const button = event.target.closest('.product-card__wishlist');

		if (!button) {
			return;
		}

		event.preventDefault();
		handleToggle(button, config);
	};

	document.addEventListener('click', wishlistClickHandler);
}

/**
 * Toggles one wishlist button's state via AJAX, or redirects a logged-out visitor to the login
 * page.
 *
 * @param {HTMLButtonElement} button Wishlist button that was clicked.
 * @param {{ajaxUrl: string, nonce: string, isLoggedIn: boolean, loginUrl: string}} config Localized config.
 * @return {Promise<void>}
 */
async function handleToggle(button, config) {
	if (!config.isLoggedIn) {
		window.location.href = config.loginUrl;
		return;
	}

	const productId = button.dataset.productId;

	if (!productId) {
		return;
	}

	button.disabled = true;

	try {
		const body = new URLSearchParams({
			action: 'solar_template_wishlist_toggle',
			nonce: config.nonce,
			product_id: productId,
		});

		const response = await fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body,
		});

		const result = await response.json();

		if (!result.success) {
			if (result.data && result.data.redirect) {
				window.location.href = result.data.redirect;
			}
			return;
		}

		const wishlisted = result.data.wishlisted === true;
		button.classList.toggle('is-active', wishlisted);
		button.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');
	} finally {
		button.disabled = false;
	}
}

/**
 * Wires up the account-deletion form: a native `confirm()` dialog must be accepted before the form
 * (already gated server-side by its own required checkbox) is allowed to submit. Delegated on
 * `document` and defensive against repeated calls, same convention as the catalog filters' own
 * listeners.
 *
 * @return {void}
 */
export function initAccountDeletionConfirm() {
	if (accountDeletionSubmitHandler) {
		document.removeEventListener('submit', accountDeletionSubmitHandler);
	}

	accountDeletionSubmitHandler = (event) => {
		const form = event.target.closest('.account-danger-zone__form');

		if (!form) {
			return;
		}

		if (!window.confirm(form.dataset.confirm || '')) {
			event.preventDefault();
		}
	};

	document.addEventListener('submit', accountDeletionSubmitHandler);
}
