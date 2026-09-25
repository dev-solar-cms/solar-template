/**
 * Created: 2026-09-25 10:20 CEST
 * Role: Front-end behaviour for the product catalog's native filter bar (assets/js/catalog.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Open/close each filter group's dropdown panel (one at a time, closed on outside
 *          click/Escape, same pattern as the header's mega menu), reflect the active
 *          selection on its toggle button, and submit the whole `#catalog-filters` form via AJAX
 *          to `solar_template_catalog_filter` (see functions.php) whenever a filter control
 *          changes, swapping the returned markup into `#catalog-results` without a full page
 *          reload. Reads the AJAX endpoint/nonce from `window.solarTemplateCatalog`, localized by
 *          `wp_localize_script()`. Does nothing (and throws no error) on a page without the
 *          filter bar, e.g. because WooCommerce is inactive.
 */

/**
 * Wires up every filter group's dropdown panel and change handling on `#catalog-filters`.
 *
 * @return {void}
 */
export function initCatalogFilters() {
	const form = document.getElementById('catalog-filters');
	const config = window.solarTemplateCatalog;

	if (!form || !document.getElementById('catalog-results') || !config) {
		return;
	}

	const groups = Array.from(form.querySelectorAll('[data-filter-group]'));

	groups.forEach((group) => {
		wireGroupToggle(group, groups);
		updateGroupActiveState(group);

		group.querySelectorAll('input').forEach((input) => {
			input.addEventListener('change', () => {
				updateGroupActiveState(group);
				submitFilters(form, config);
			});
		});
	});

	document.addEventListener('click', (event) => {
		if (!form.contains(event.target)) {
			closeAllPanels(groups);
		}
	});

	form.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeAllPanels(groups);
		}
	});

	form.addEventListener('submit', (event) => {
		event.preventDefault();
		submitFilters(form, config);
	});
}

/**
 * Wires up one filter group's toggle button: opens its panel (closing every other group's panel
 * first, accordion-style) or closes it back when already open.
 *
 * @param {Element}   group      One `[data-filter-group]` element.
 * @param {Element[]} allGroups  Every filter group on the page, to close the others on open.
 * @return {void}
 */
function wireGroupToggle(group, allGroups) {
	const toggle = group.querySelector('.catalog-filters__toggle');
	const panel = group.querySelector('.catalog-filters__panel');

	if (!toggle || !panel) {
		return;
	}

	toggle.addEventListener('click', () => {
		const isOpen = !panel.hidden;

		closeAllPanels(allGroups);

		if (!isOpen) {
			panel.hidden = false;
			toggle.setAttribute('aria-expanded', 'true');
		}
	});
}

/**
 * Closes every filter group's dropdown panel.
 *
 * @param {Element[]} groups Every filter group on the page.
 * @return {void}
 */
function closeAllPanels(groups) {
	groups.forEach((group) => {
		const toggle = group.querySelector('.catalog-filters__toggle');
		const panel = group.querySelector('.catalog-filters__panel');

		if (toggle && panel) {
			panel.hidden = true;
			toggle.setAttribute('aria-expanded', 'false');
		}
	});
}

/**
 * Reflects one filter group's current selection on its toggle button: an active style once at
 * least one of its controls has a value, plus a "· N" count for checkbox-based groups.
 *
 * @param {Element} group One `[data-filter-group]` element.
 * @return {void}
 */
function updateGroupActiveState(group) {
	const toggle = group.querySelector('.catalog-filters__toggle');
	const countElement = group.querySelector('.catalog-filters__toggle-count');
	const inputs = Array.from(group.querySelectorAll('input'));
	const checkboxes = inputs.filter((input) => input.type === 'checkbox');
	const activeCount = inputs.filter((input) =>
		input.type === 'checkbox' ? input.checked : input.value !== '',
	).length;

	if (!toggle) {
		return;
	}

	toggle.classList.toggle('is-active', activeCount > 0);

	if (!countElement) {
		return;
	}

	if (checkboxes.length > 0 && activeCount > 0) {
		countElement.hidden = false;
		countElement.textContent = `· ${activeCount}`;
	} else {
		countElement.hidden = true;
		countElement.textContent = '';
	}
}

/**
 * Submits the filter bar's current state via AJAX and swaps the returned markup into
 * `#catalog-results`, looked up fresh each call since the previous element is replaced wholesale.
 *
 * @param {HTMLFormElement}                  form   The `#catalog-filters` form.
 * @param {{ajaxUrl: string, nonce: string}} config Localized AJAX endpoint/nonce.
 * @return {Promise<void>}
 */
async function submitFilters(form, config) {
	const resultsContainer = document.getElementById('catalog-results');

	if (!resultsContainer) {
		return;
	}

	resultsContainer.classList.add('is-loading');

	try {
		const params = new URLSearchParams(new FormData(form));
		params.set('action', 'solar_template_catalog_filter');
		params.set('nonce', config.nonce);

		const response = await fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: params,
		});

		const result = await response.json();

		if (result.success && result.data && typeof result.data.html === 'string') {
			resultsContainer.outerHTML = result.data.html;
		} else {
			resultsContainer.classList.remove('is-loading');
		}
	} catch {
		resultsContainer.classList.remove('is-loading');
	}
}
