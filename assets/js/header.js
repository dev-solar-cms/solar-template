/**
 * Created: 2026-09-25 08:30 CEST
 * Role: Front-end behaviour for the sticky site header (assets/js/header.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Open/close the "Collections" mega menu (hover, keyboard focus, Escape, outside click)
 *          and toggle the search button's `aria-expanded` state — no visible search overlay yet,
 *          that panel is added by a later step which is expected to extend initSearchToggle().
 */

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
 * Wires up the header's search toggle button so it reflects its own open/closed state.
 *
 * There is no search overlay to show/hide yet: this only flips `aria-expanded` so assistive
 * technology already reports the correct state, ready for a later step to attach the overlay
 * panel to the same toggle without changing this wiring.
 *
 * @return {void}
 */
export function initSearchToggle() {
	const toggle = document.getElementById('site-search-toggle');

	if (!toggle) {
		return;
	}

	toggle.addEventListener('click', () => {
		const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
		toggle.setAttribute('aria-expanded', String(!isExpanded));
	});
}
