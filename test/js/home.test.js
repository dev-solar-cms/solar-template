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
