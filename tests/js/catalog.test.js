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
					<label><input type="checkbox" name="filter_category[]" value="watches" checked /></label>
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
			<select name="catalog_orderby">
				<option value="menu_order">Relevance</option>
				<option value="price-asc">Price: low to high</option>
			</select>
		</form>
		<div id="catalog-results">
			<p>9 products available</p>
			<div class="catalog__grid">
				<div class="product-card">Existing product</div>
			</div>
			<div class="catalog__load-more" id="catalog-load-more">
				<a href="https://example.test/shop/?paged=2" data-load-more data-page="2">Load more products</a>
			</div>
		</div>
		<div id="catalog-active-filters">
			<span class="catalog-active-filters__chip">
				Watches
				<a class="catalog-active-filters__remove" href="https://example.test/shop/?min_price=200">×</a>
			</span>
			<a class="catalog-active-filters__clear" href="https://example.test/shop/">Clear all</a>
		</div>
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

	it('applies a chip removal link onto the form and submits via AJAX instead of navigating', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () =>
				Promise.resolve({
					success: true,
					data: {
						html: '<div id="catalog-results"><p>4 products available</p></div>',
						activeFiltersHtml: '<div id="catalog-active-filters"></div>',
					},
				}),
		});
		vi.stubGlobal('fetch', fetchMock);

		initCatalogFilters();

		const removeLink = document.querySelector('.catalog-active-filters__remove');
		const clickEvent = new MouseEvent('click', { bubbles: true, cancelable: true });
		removeLink.dispatchEvent(clickEvent);

		expect(clickEvent.defaultPrevented).toBe(true);
		expect(document.querySelector('input[value="watches"]').checked).toBe(false);
		expect(document.querySelector('input[name="min_price"]').value).toBe('200');

		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		const [, requestInit] = fetchMock.mock.calls[0];
		expect(String(requestInit.body)).not.toContain('filter_category');
		expect(String(requestInit.body)).toContain('min_price=200');
		expect(document.getElementById('catalog-active-filters').outerHTML).toBe(
			'<div id="catalog-active-filters"></div>',
		);
	});

	it('applies "Clear all" by resetting every form control and submitting via AJAX', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () =>
				Promise.resolve({
					success: true,
					data: { html: '<div id="catalog-results"><p>9 products available</p></div>' },
				}),
		});
		vi.stubGlobal('fetch', fetchMock);

		initCatalogFilters();

		document
			.querySelector('.catalog-active-filters__clear')
			.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		expect(document.querySelector('input[value="watches"]').checked).toBe(false);

		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
	});

	it('submits the form via AJAX when the sort dropdown changes', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () =>
				Promise.resolve({
					success: true,
					data: { html: '<div id="catalog-results"><p>9 products available</p></div>' },
				}),
		});
		vi.stubGlobal('fetch', fetchMock);

		initCatalogFilters();

		const select = document.querySelector('select[name="catalog_orderby"]');
		select.value = 'price-asc';
		select.dispatchEvent(new Event('change', { bubbles: true }));

		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		const [, requestInit] = fetchMock.mock.calls[0];
		expect(String(requestInit.body)).toContain('catalog_orderby=price-asc');
	});

	it('syncs the sort dropdown when applying a chip removal URL that carries an orderby value', () => {
		vi.stubGlobal(
			'fetch',
			vi.fn().mockResolvedValue({ json: () => Promise.resolve({ success: false }) }),
		);

		document
			.querySelector('.catalog-active-filters__remove')
			.setAttribute('href', 'https://example.test/shop/?catalog_orderby=price-asc');

		initCatalogFilters();

		document
			.querySelector('.catalog-active-filters__remove')
			.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		expect(document.querySelector('select[name="catalog_orderby"]').value).toBe('price-asc');
	});

	it('appends the returned cards to the grid and replaces the load-more block on click', async () => {
		const fetchMock = vi.fn().mockResolvedValue({
			json: () =>
				Promise.resolve({
					success: true,
					data: {
						cardsHtml: '<div class="product-card">New product</div>',
						loadMoreHtml:
							'<div class="catalog__load-more" id="catalog-load-more"><a href="?paged=3" data-load-more data-page="3">Load more products</a></div>',
					},
				}),
		});
		vi.stubGlobal('fetch', fetchMock);

		initCatalogFilters();

		document
			.querySelector('[data-load-more]')
			.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));

		await vi.waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(1));
		await new Promise((resolve) => setTimeout(resolve, 0));

		const [, requestInit] = fetchMock.mock.calls[0];
		expect(String(requestInit.body)).toContain('mode=append');
		expect(String(requestInit.body)).toContain('paged=2');

		const cards = document.querySelectorAll('.catalog__grid .product-card');
		expect(cards).toHaveLength(2);
		expect(cards[1].textContent).toBe('New product');
		expect(document.querySelector('[data-load-more]').dataset.page).toBe('3');
	});

	it('does not throw when clicking load more without a grid on the page', () => {
		document.querySelector('.catalog__grid').remove();

		initCatalogFilters();

		expect(() =>
			document
				.querySelector('[data-load-more]')
				.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true })),
		).not.toThrow();
	});
});
