/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Regression test for the theme's SCSS design system components.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compile assets/scss/main.scss directly (not the built dist/ artifact, so this test
 *          does not depend on `npm run build` having already run) and assert that every button,
 *          badge and pill variant renders with the exact values from the design handoff. This is
 *          the automated stand-in for a visual QA pass: a real WordPress page to eyeball these
 *          components on does not exist yet (no header.php/footer.php/page templates), so
 *          conformance is instead verified structurally against the compiled CSS.
 */

import { resolve } from 'node:path';
import * as sass from 'sass';
import { describe, expect, it } from 'vitest';

const { css } = sass.compile(resolve(process.cwd(), 'assets/scss/main.scss'), {
	style: 'expanded',
});

describe('design system: buttons', () => {
	it('renders the primary CTA in gold with dark text and the button radius', () => {
		expect(css).toMatch(/\.btn--primary\s*{[^}]*background:\s*#c9a96e/);
		expect(css).toMatch(/\.btn--primary\s*{[^}]*color:\s*#0d0d0d/);
		expect(css).toMatch(/\.btn\s*{[^}]*border-radius:\s*5px/);
	});

	it('renders the dark full-width CTA with its hover state', () => {
		expect(css).toMatch(/\.btn--dark\s*{[^}]*background:\s*#0d0d0d/);
		expect(css).toMatch(/\.btn--dark:hover\s*{[^}]*background:\s*#1a1a1a/);
	});

	it('renders the outline variant for a dark hero background', () => {
		expect(css).toMatch(
			/\.btn--outline-on-dark\s*{[^}]*border-color:\s*rgba\(255,\s*255,\s*255,\s*0\.15\)/,
		);
	});
});

describe('design system: badges', () => {
	it('renders the New/Exclusive/Sale corner badges with the badge radius', () => {
		expect(css).toMatch(/\.badge\s*{[^}]*border-radius:\s*3px/);
		expect(css).toMatch(/\.badge--new\s*{[^}]*background:\s*#0d0d0d/);
		expect(css).toMatch(/\.badge--exclusive\s*{[^}]*background:\s*#c9a96e/);
		expect(css).toMatch(/\.badge--sale\s*{[^}]*background:\s*#c05a2a/);
	});

	it('renders the discount badge as a tinted sale color, not a solid fill', () => {
		expect(css).toMatch(/\.badge--discount\s*{[^}]*color:\s*#c05a2a/);
		expect(css).toMatch(
			/\.badge--discount\s*{[^}]*background:\s*rgba\(192,\s*90,\s*42,\s*0\.1\)/,
		);
	});
});

describe('design system: pills', () => {
	it('renders the pill radius and the active/inactive states', () => {
		expect(css).toMatch(/\.pill\s*{[^}]*border-radius:\s*99px/);
		expect(css).toMatch(/\.pill\.is-active\s*{[^}]*background:\s*#0d0d0d/);
	});
});

describe('design system: product card', () => {
	it('gives the media block the card radius and a 3:4 aspect ratio', () => {
		expect(css).toMatch(/\.product-card__media\s*{[^}]*border-radius:\s*6px/);
		expect(css).toMatch(/\.product-card__media\s*{[^}]*aspect-ratio:\s*3\/4/);
	});

	it('lifts the card on hover', () => {
		expect(css).toMatch(/\.product-card:hover\s*{[^}]*transform:\s*translateY\(-4px\)/);
	});

	it('keeps the "add to cart" overlay hidden until hover/focus', () => {
		expect(css).toMatch(/\.product-card__cta\s*{[^}]*opacity:\s*0/);
		expect(css).toMatch(/\.product-card:hover \.product-card__cta[^{]*{[^}]*opacity:\s*1/);
	});

	it('sizes the wishlist button as a 34px circle', () => {
		expect(css).toMatch(/\.product-card__wishlist\s*{[^}]*width:\s*34px/);
		expect(css).toMatch(/\.product-card__wishlist\s*{[^}]*border-radius:\s*50%/);
	});
});
