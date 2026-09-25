/**
 * Created: 2026-09-25 12:30 CEST
 * Role: Front-end behaviour for newsletter sign-up forms (assets/js/newsletter.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Submit any `.js-newsletter-form` on the page via AJAX to
 *          `solar_template_newsletter_subscribe` (see functions.php), disabling the submit button
 *          while the request is in flight and showing the returned feedback message in the
 *          form's sibling `.js-newsletter-feedback` element. Reads the AJAX endpoint/nonce from
 *          `window.solarTemplateNewsletter`, localized by `wp_localize_script()`.
 */

/**
 * Wires up every `.js-newsletter-form` on the page (there may be more than one, e.g. the front
 * page section and the footer's own form). Does nothing when the required localized data is
 * missing (script not enqueued, or `wp_localize_script()` failed against an unregistered handle).
 *
 * @return {void}
 */
export function initNewsletterForms() {
	const config = window.solarTemplateNewsletter;

	if (!config) {
		return;
	}

	document.querySelectorAll('.js-newsletter-form').forEach((form) => {
		form.addEventListener('submit', (event) => {
			event.preventDefault();
			handleSubmit(form, config);
		});
	});
}

/**
 * Submits one newsletter form via AJAX and reports the result in its feedback element.
 *
 * @param {HTMLFormElement} form   The submitted form.
 * @param {{ajaxUrl: string, nonce: string}} config Localized AJAX endpoint/nonce.
 * @return {Promise<void>}
 */
async function handleSubmit(form, config) {
	const emailInput = form.querySelector('input[type="email"]');
	const submitButton = form.querySelector('button[type="submit"]');
	const feedback = form.parentElement
		? form.parentElement.querySelector('.js-newsletter-feedback')
		: null;

	if (!emailInput) {
		return;
	}

	if (submitButton) {
		submitButton.disabled = true;
	}

	try {
		const body = new URLSearchParams({
			action: 'solar_template_newsletter_subscribe',
			nonce: config.nonce,
			email: emailInput.value,
		});

		const response = await fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body,
		});

		const result = await response.json();

		if (feedback) {
			feedback.textContent = result.data && result.data.message ? result.data.message : '';
			feedback.classList.toggle('is-success', result.success === true);
			feedback.classList.toggle('is-error', result.success !== true);
		}

		if (result.success) {
			form.reset();
		}
	} catch {
		if (feedback) {
			feedback.textContent = '';
			feedback.classList.remove('is-success');
			feedback.classList.add('is-error');
		}
	} finally {
		if (submitButton) {
			submitButton.disabled = false;
		}
	}
}
