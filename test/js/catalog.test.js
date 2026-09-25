/**
 * Created: 2026-09-25 10:20 CEST
 * Role: Unit test for assets/js/catalog.js.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Exercise the catalog filter bar's panel toggling, active-state reflection and AJAX
 *          submission against a minimal DOM fixture matching
 *          template-parts/catalog-filters.php/template-parts/catalog-results.php's real markup,
 *          with `fetch` mocked (no real WordPress/network available here). Run via `npm run test`.
 */

import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { initCatalogFilters } from '../../assets/js/catalog.js';

/**
 * Builds the slice of the catalog's real markup this behaviour attaches to: two filter groups
 * (one checkbox-based, one price range) and the results container they update.
 *
 * @return {void}
 */
function renderCatalogFixture() {
	document.body.innerHTML = `
		<form id="catalog-filters">
			<div data-filter-group="category">
				<button type="button" class="catalog-filters__toggle" aria-expanded="false">
					Category
					<span class="catalog-filters__toggle-count" hidden></span>
				</button>
				<div class="catalog-filters__panel" hidden>
					<label><input type="checkbox" name="filter_category[]" value="watches" /></label>
					<label><input type="checkbox" name="filter_category[]" value="jewelry" /></label>
				</div>
			</div>
			<div data-filter-group="price">
				<button type="button" class="catalog-filters__toggle" aria-expanded="false">
					Price
					<span class="catalog-filters__toggle-count" hidden></span>
				</button>
				<div class="catalog-filters__panel" hidden>
					<input type="number" name="min_price" value="" />
					<input type="number" name="max_price" value="" />
				</div>
			</div>
		</form>
		<div id="catalog-results"><p>9 products available</p></div>
	`;
}

describe('assets/js/catalog.js', () => {
	beforeEach(() => {
		renderCatalogFixture();
		window.solarTemplateCatalog = {
			ajaxUrl: 'https://example.test/admin-ajax.php',
			nonce: 'abc123',
		};
	});

	afterEach(() => {
		vi.unstubAllGlobals();
		delete window.solarTemplateCatalog;
	});

	it('does not throw and attaches no listener when the localized config is missing', () => {
		delete window.solarTemplateCatalog;

		expect(() => initCatalogFilters()).not.toThrow();
	});

	it('does not throw when the results container is absent from the page', () => {
		document.getElementById('catalog-results').remove();

		expect(() => initCatalogFilters()).not.toThrow();
	});

	it('opens a group panel on toggle click and closes the other group', () => {
		initCatalogFilters();

		const groups = document.querySelectorAll('[data-filter-group]');
		const [categoryGroup, priceGroup] = groups;

		categoryGroup
			.querySelector('.catalog-filters__toggle')
			.dispatchEvent(new MouseEvent('click', { bubbles: true }));
		expect(categoryGroup.querySelector('.catalog-filters__panel').hidden).toBe(false);

		priceGroup
			.querySelector('.catalog-filters__toggle')
			.dispatchEvent(new MouseEvent('click', { bubbles: true }));
		expect(priceGroup.querySelector('.catalog-filters__panel').hidden).toBe(false);
		expect(categoryGroup.querySelector('.catalog-filters__panel').hidden).toBe(true);
	});

	it('closes every open panel on Escape', () => {
		initCatalogFilters();

		const group = document.querySelector('[data-filter-group="category"]');
		group
			.querySelector('.catalog-filters__toggle')
			.dispatchEvent(new MouseEvent('click', { bubbles: true }));

		document
			.getElementById('catalog-filters')
			.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));

		expect(group.querySelector('.catalog-filters__panel').hidden).toBe(true);
	});

	it('marks a checkbox group active with a count once a checkbox is checked', async () => {
		vi.stubGlobal(
			'fetch',
			vi.fn().mockResolvedValue({
				json: () =>
					Promise.resolve({
						success: true,
						data: { html: '<div id="catalog-results"></div>' },
					}),
			}),
		);

		initCatalogFilters();

		const group = document.querySelector('[data-filter-group="category"]');
		const checkbox = group.querySelector('input[value="watches"]');
		checkbox.checked = true;
		checkbox.dispatchEvent(new Event('change', { bubbles: true }));

		const toggle = group.querySelector('.catalog-filters__toggle');
		const count = group.querySelector('.catalog-filters__toggle-count');

		expect(toggle.classList.contains('is-active')).toBe(true);
		expect(count.hidden).toBe(false);
		expect(count.textContent).toBe('· 1');
	});

	it('submits the form via AJAX on change and swaps in the returned results markup', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () =>
				Promise.resolve({
					success: true,
					data: { html: '<div id="catalog-results"><p>1 product available</p></div>' },
				}),
		});
		vi.stubGlobal('fetch', fetchMock);

		initCatalogFilters();

		const checkbox = document.querySelector('input[value="watches"]');
		checkbox.checked = true;
		checkbox.dispatchEvent(new Event('change', { bubbles: true }));

		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		const [, requestInit] = fetchMock.mock.calls[0];
		expect(String(requestInit.body)).toContain('filter_category%5B%5D=watches');
		expect(document.getElementById('catalog-results').textContent).toBe('1 product available');
	});

	it('removes the loading state when the request fails', async () => {
		vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('network error')));

		initCatalogFilters();

		const checkbox = document.querySelector('input[value="watches"]');
		checkbox.checked = true;
		checkbox.dispatchEvent(new Event('change', { bubbles: true }));

		await new Promise((resolve) => setTimeout(resolve, 0));

		expect(document.getElementById('catalog-results').classList.contains('is-loading')).toBe(
			false,
		);
	});
});
