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
 */

let wishlistClickHandler = null;

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
