/**
 * Created: 2026-09-25 09:30 CEST
 * Role: Regression test for the theme's front page section styles.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compile assets/scss/main.scss directly (same approach as design-system.test.js) and
 *          assert that the front page's sections render with the exact values from the design
 *          handoff. Grows by one `describe` block per section as the home page is built out.
 */

import { resolve } from 'node:path';
import * as sass from 'sass';
import { describe, expect, it } from 'vitest';

const { css } = sass.compile(resolve(process.cwd(), 'assets/scss/main.scss'), {
	style: 'expanded',
});

describe('front page: hero', () => {
	it('renders full-viewport on a dark background, as a two-column grid', () => {
		expect(css).toMatch(/\.hero\s*{[^}]*background:\s*#0d0d0d/);
		expect(css).toMatch(/\.hero\s*{[^}]*grid-template-columns:\s*1fr 1fr/);
	});

	it('accents the middle heading line in gold italics', () => {
		expect(css).toMatch(/\.hero__heading-line--accent\s*{[^}]*color:\s*#c9a96e/);
		expect(css).toMatch(/\.hero__heading-line--accent\s*{[^}]*font-style:\s*italic/);
	});

	it('gives the floating highlight card the modal radius and a blurred light background', () => {
		expect(css).toMatch(/\.hero__highlight\s*{[^}]*border-radius:\s*10px/);
		expect(css).toMatch(/\.hero__highlight\s*{[^}]*backdrop-filter:\s*blur\(12px\)/);
	});

	it('fills the highlight progress bar in gold', () => {
		expect(css).toMatch(/\.hero__highlight-progress-bar\s*{[^}]*background:\s*#c9a96e/);
	});
});

describe('front page: featured products', () => {
	it('lays out a 4-column grid with the shared product grid gap', () => {
		expect(css).toMatch(
			/\.featured-products__grid\s*{[^}]*grid-template-columns:\s*repeat\(4,\s*1fr\)/,
		);
		expect(css).toMatch(/\.featured-products__grid\s*{[^}]*gap:\s*24px/);
	});

	it('offsets the 2nd and 4th cards for the masonry effect', () => {
		expect(css).toMatch(/\.featured-products__item:nth-child\(2\)\s*{[^}]*margin-top:\s*48px/);
		expect(css).toMatch(/\.featured-products__item:nth-child\(4\)\s*{[^}]*margin-top:\s*28px/);
	});
});

describe('front page: categories', () => {
	it('lays out the asymmetric 1.4fr/1fr/1fr grid on a dark background', () => {
		expect(css).toMatch(/\.home-categories\s*{[^}]*background:\s*#0d0d0d/);
		expect(css).toMatch(
			/\.home-categories__grid\s*{[^}]*grid-template-columns:\s*1\.4fr 1fr 1fr/,
		);
	});

	it('spans the primary category across both rows', () => {
		expect(css).toMatch(/\.home-categories__item--primary\s*{[^}]*grid-row:\s*span 2/);
	});

	it('shows the gold outline hover state', () => {
		expect(css).toMatch(
			/\.home-categories__item:hover\s*{[^}]*outline-color:\s*rgba\(201,\s*169,\s*110,\s*0\.3\)/,
		);
	});
});

describe('front page: brand story', () => {
	it('gives the image the content card radius and a 4:5 aspect ratio', () => {
		expect(css).toMatch(/\.brand-story__image\s*{[^}]*border-radius:\s*8px/);
		expect(css).toMatch(/\.brand-story__image\s*{[^}]*aspect-ratio:\s*4\/5/);
	});

	it('renders the floating stat highlight in gold on a dark card', () => {
		expect(css).toMatch(/\.brand-story__highlight\s*{[^}]*background:\s*#0d0d0d/);
		expect(css).toMatch(/\.brand-story__highlight-value\s*{[^}]*color:\s*#c9a96e/);
	});

	it('lays out the three stats in an even grid', () => {
		expect(css).toMatch(
			/\.brand-story__stats\s*{[^}]*grid-template-columns:\s*repeat\(3,\s*1fr\)/,
		);
	});
});

describe('front page: testimonials', () => {
	it('lays out a 3-column grid with the modal radius on each card', () => {
		expect(css).toMatch(
			/\.testimonials__grid\s*{[^}]*grid-template-columns:\s*repeat\(3,\s*1fr\)/,
		);
		expect(css).toMatch(/\.testimonials__item\s*{[^}]*border-radius:\s*10px/);
	});

	it('inverts the middle card to a dark background with light text', () => {
		expect(css).toMatch(/\.testimonials__item--inverted\s*{[^}]*background:\s*#0d0d0d/);
		expect(css).toMatch(
			/\.testimonials__item--inverted \.testimonials__author-name\s*{[^}]*color:\s*#ffffff/,
		);
	});

	it('colors the star rating and quote mark in gold', () => {
		expect(css).toMatch(/\.testimonials__rating\s*{[^}]*color:\s*#c9a96e/);
		expect(css).toMatch(/\.testimonials__quote-mark\s*{[^}]*color:\s*#c9a96e/);
	});
});

describe('front page: blog preview', () => {
	it('lays out a 3-column grid with the shared blog grid gap', () => {
		expect(css).toMatch(
			/\.blog-preview__grid\s*{[^}]*grid-template-columns:\s*repeat\(3,\s*1fr\)/,
		);
	});

	it('underlines the "view all" link in gold on hover', () => {
		expect(css).toMatch(
			/\.blog-preview__view-all:hover\s*{[^}]*color:\s*#c9a96e[^}]*border-color:\s*#c9a96e/,
		);
	});
});

describe('front page: newsletter', () => {
	it('renders centered on a dark background', () => {
		expect(css).toMatch(/\.home-newsletter\s*{[^}]*background:\s*#0d0d0d/);
		expect(css).toMatch(/\.home-newsletter__inner\s*{[^}]*text-align:\s*center/);
	});

	it('fills the submit button in gold with a darker hover state', () => {
		expect(css).toMatch(/\.home-newsletter__submit\s*{[^}]*background:\s*#c9a96e/);
		expect(css).toMatch(/\.home-newsletter__submit:hover\s*{[^}]*background:\s*#a8803e/);
	});

	it('colors the feedback message by success/error state', () => {
		expect(css).toMatch(/\.home-newsletter__feedback\.is-success\s*{[^}]*color:\s*#3aa85a/);
		expect(css).toMatch(/\.home-newsletter__feedback\.is-error\s*{[^}]*color:\s*#d64545/);
	});
});
