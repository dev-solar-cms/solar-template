/**
 * Created: 2026-09-25 08:30 CEST
 * Role: Front-end behaviour for the sticky site header (assets/js/header.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Open/close the "Collections" mega menu (hover, keyboard focus, Escape, outside click),
 *          the full-screen search overlay (toggle button, close button, Escape, outside click,
 *          moving focus into the search field on open and back to the toggle on close), and the
 *          transparent-on-home header's scroll-based solidify behaviour (Header administration
 *          tab).
 */

/**
 * Scroll threshold (px) past which the transparent-on-home header (`.site-header--transparent-home`,
 * see Solar_Template\Admin\HeaderSettings::is_transparent_on_home()) switches to its normal
 * sticky/opaque appearance, by toggling `.site-header--scrolled`.
 *
 * @type {number}
 */
const TRANSPARENT_HEADER_SCROLL_THRESHOLD = 40;

/**
 * Wires up the transparent-on-home header: adds/removes `.site-header--scrolled` as the page
 * scrolls past TRANSPARENT_HEADER_SCROLL_THRESHOLD, in either direction. A no-op when the header
 * doesn't have the `.site-header--transparent-home` modifier class (every other page/setting).
 *
 * @return {void}
 */
export function initTransparentHeader() {
	const header = document.getElementById('site-header');

	if (!header || !header.classList.contains('site-header--transparent-home')) {
		return;
	}

	const updateScrolledState = () => {
		header.classList.toggle(
			'site-header--scrolled',
			window.scrollY > TRANSPARENT_HEADER_SCROLL_THRESHOLD,
		);
	};

	updateScrolledState();
	window.addEventListener('scroll', updateScrolledState, { passive: true });
}

/**
 * Wires up the "Collections" mega menu panel rendered by template-parts/mega-menu.php.
 *
 * Only one trigger/panel pair exists in the current markup (`.has-mega-menu`, `#site-mega-menu`);
 * every element is looked up once and the function returns early (no listener attached, no error
 * thrown) when either is missing — e.g. when the mega menu is disabled via
 * `solar_template_header_mega_menu_enabled`.
 *
 * @return {void}
 */
export function initMegaMenu() {
	const nav = document.querySelector('.site-header__nav');
	const panel = document.getElementById('site-mega-menu');
	const trigger = nav ? nav.querySelector('.site-header__menu-item.has-mega-menu') : null;
	const link = trigger ? trigger.querySelector('a') : null;

	if (!nav || !panel || !trigger || !link) {
		return;
	}

	link.setAttribute('aria-haspopup', 'true');
	link.setAttribute('aria-expanded', 'false');
	link.setAttribute('aria-controls', 'site-mega-menu');

	// Guards the programmatic `link.focus()` call below from re-triggering the "focus opens the
	// menu" listener: without it, closing on Escape would immediately reopen the menu it just
	// closed, since giving the link focus back fires the very listener that opens it.
	let suppressOpenOnFocus = false;

	const open = () => {
		nav.classList.add('is-mega-menu-open');
		link.setAttribute('aria-expanded', 'true');
	};

	const close = () => {
		nav.classList.remove('is-mega-menu-open');
		link.setAttribute('aria-expanded', 'false');
	};

	const closeUnlessFocusStaysInside = (event) => {
		if (!trigger.contains(event.relatedTarget) && !panel.contains(event.relatedTarget)) {
			close();
		}
	};

	trigger.addEventListener('mouseenter', open);
	trigger.addEventListener('mouseleave', close);
	link.addEventListener('focus', () => {
		if (!suppressOpenOnFocus) {
			open();
		}
	});
	trigger.addEventListener('focusout', closeUnlessFocusStaysInside);
	panel.addEventListener('focusout', closeUnlessFocusStaysInside);

	nav.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && nav.classList.contains('is-mega-menu-open')) {
			close();
			suppressOpenOnFocus = true;
			link.focus();
			suppressOpenOnFocus = false;
		}
	});

	document.addEventListener('click', (event) => {
		if (!nav.contains(event.target)) {
			close();
		}
	});
}

/**
 * Wires up the header's full-screen search overlay: the top bar's search icon opens it and moves
 * focus into the search field, the overlay's own close button, Escape, or a click outside it
 * close it and return focus to the toggle button.
 *
 * Returns early (no listener attached, no error thrown) when the toggle button or the overlay
 * markup is missing from the page.
 *
 * @return {void}
 */
export function initSearchOverlay() {
	const toggle = document.getElementById('site-search-toggle');
	const overlay = document.getElementById('site-search');

	if (!toggle || !overlay) {
		return;
	}

	const input = overlay.querySelector('.site-search-form__input');
	const closeButton = document.getElementById('site-search-close');

	const open = () => {
		overlay.classList.add('is-open');
		toggle.setAttribute('aria-expanded', 'true');

		if (input) {
			input.focus();
		}
	};

	const close = () => {
		overlay.classList.remove('is-open');
		toggle.setAttribute('aria-expanded', 'false');
	};

	toggle.addEventListener('click', () => {
		if (overlay.classList.contains('is-open')) {
			close();
		} else {
			open();
		}
	});

	if (closeButton) {
		closeButton.addEventListener('click', () => {
			close();
			toggle.focus();
		});
	}

	overlay.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			close();
			toggle.focus();
		}
	});

	document.addEventListener('click', (event) => {
		if (
			overlay.classList.contains('is-open') &&
			!overlay.contains(event.target) &&
			!toggle.contains(event.target)
		) {
			close();
		}
	});
}
