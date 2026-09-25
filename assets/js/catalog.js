/**
 * Created: 2026-09-25 10:20 CEST
 * Role: Front-end behaviour for the product catalog's native filter bar (assets/js/catalog.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Open/close each filter group's dropdown panel (one at a time, closed on outside
 *          click/Escape, same pattern as the header's mega menu), reflect the active
 *          selection on its toggle button, and submit the whole `#catalog-filters` form via AJAX
 *          to `solar_template_catalog_filter` (see functions.php) whenever a filter control
 *          changes, swapping the returned markup into `#catalog-results` and
 *          `#catalog-active-filters` without a full page reload. A click on an "Active filters"
 *          chip's remove link or "Clear all" (template-parts/catalog-active-filters.php) is
 *          intercepted the same way: its `href` already encodes the resulting filter state (a
 *          plain link works without JavaScript), which is applied back onto the form's own
 *          controls before submitting, so the two stay in sync either way. Reads the AJAX
 *          endpoint/nonce from `window.solarTemplateCatalog`, localized by `wp_localize_script()`.
 *          Does nothing (and throws no error) on a page without the filter bar, e.g. because
 *          WooCommerce is inactive.
 */

// Tracks the two listeners bound directly to `document` (rather than to an element scoped to this
// page's own filter bar), so a repeated call re-wires them instead of accumulating duplicates —
// production only ever calls this once (on `DOMContentLoaded`), but each call still fully rewires
// as if it were the first, which the test suite relies on against a fresh fixture per test.
let outsideClickHandler = null;
let chipsClickHandler = null;

/**
 * Wires up every filter group's dropdown panel, the "Active filters" chip row, and change
 * handling on `#catalog-filters`.
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

	if (outsideClickHandler) {
		document.removeEventListener('click', outsideClickHandler);
	}

	outsideClickHandler = (event) => {
		if (!form.contains(event.target)) {
			closeAllPanels(groups);
		}
	};
	document.addEventListener('click', outsideClickHandler);

	form.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeAllPanels(groups);
		}
	});

	form.addEventListener('submit', (event) => {
		event.preventDefault();
		submitFilters(form, config);
	});

	wireActiveFilterChips(form, groups, config);
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
 * Intercepts clicks on the "Active filters" chip row (a chip's remove link, or "Clear all"),
 * applying the link's target `href` query string onto the filter form's own controls instead of
 * following it, then submitting via AJAX — the chip row itself is replaced wholesale on every
 * response, so its links are (re)found by delegating from a stable ancestor rather than bound
 * once at page load.
 *
 * @param {HTMLFormElement}                  form   The `#catalog-filters` form.
 * @param {Element[]}                        groups Every filter group on the page.
 * @param {{ajaxUrl: string, nonce: string}} config Localized AJAX endpoint/nonce.
 * @return {void}
 */
function wireActiveFilterChips(form, groups, config) {
	if (chipsClickHandler) {
		document.removeEventListener('click', chipsClickHandler);
	}

	chipsClickHandler = (event) => {
		const link = event.target.closest(
			'#catalog-active-filters .catalog-active-filters__remove, #catalog-active-filters .catalog-active-filters__clear',
		);

		if (!link) {
			return;
		}

		event.preventDefault();
		applyUrlToForm(form, link.href);
		groups.forEach((group) => updateGroupActiveState(group));
		submitFilters(form, config);
	};
	document.addEventListener('click', chipsClickHandler);
}

/**
 * Applies a target URL's filter query string onto the form's own controls (checkboxes checked to
 * match, price inputs set/cleared), so submitting the form afterwards reproduces that exact
 * state.
 *
 * @param {HTMLFormElement} form Target URL that already encodes the desired filter state (as
 *                                 built by solar_template_catalog_filters_url() in functions.php).
 * @param {string}          url  URL to read filter values from.
 * @return {void}
 */
function applyUrlToForm(form, url) {
	const params = new URL(url, window.location.href).searchParams;

	form.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
		const baseName = checkbox.name.replace(/\[\]$/, '');
		checkbox.checked = getArrayParamValues(params, baseName).includes(checkbox.value);
	});

	form.querySelectorAll('input[type="number"]').forEach((input) => {
		input.value = params.get(input.name) || '';
	});
}

/**
 * Reads every value submitted for an array-shaped query parameter, regardless of whether it was
 * encoded as `name[]=a&name[]=b` or `name[0]=a&name[1]=b` (both are valid PHP array-parsing
 * conventions, and `add_query_arg()` in functions.php produces the latter).
 *
 * @param {URLSearchParams} params   Parsed query string.
 * @param {string}          baseName Parameter name without any `[...]` suffix.
 * @return {string[]}
 */
function getArrayParamValues(params, baseName) {
	return Array.from(params.entries())
		.filter(([key]) => key === `${baseName}[]` || key.startsWith(`${baseName}[`))
		.map(([, value]) => value);
}

/**
 * Submits the filter bar's current state via AJAX and swaps the returned markup into
 * `#catalog-results`/`#catalog-active-filters`, looked up fresh each call since the previous
 * elements are replaced wholesale.
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
		params.set('pageUrl', window.location.href);

		const response = await fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: params,
		});

		const result = await response.json();

		if (result.success && result.data && typeof result.data.html === 'string') {
			resultsContainer.outerHTML = result.data.html;

			const activeFiltersContainer = document.getElementById('catalog-active-filters');

			if (activeFiltersContainer && typeof result.data.activeFiltersHtml === 'string') {
				activeFiltersContainer.outerHTML = result.data.activeFiltersHtml;
			}
		} else {
			resultsContainer.classList.remove('is-loading');
		}
	} catch {
		resultsContainer.classList.remove('is-loading');
	}
}
