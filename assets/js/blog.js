/**
 * Created: 2026-09-26 22:30 CEST
 * Role: Front-end behaviour for the blog article page (assets/js/blog.js).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Wire up the article page's "Share" button (`.article-share-button`): the native Web
 *          Share API where the browser supports it, a "copy link to clipboard" fallback otherwise.
 *          Delegated on `document` and defensive against repeated calls, same convention as the
 *          account area's own listeners (assets/js/account.js).
 */

let articleShareClickHandler = null;

/**
 * Wires up the article page's share button.
 *
 * @return {void}
 */
export function initArticleShare() {
	if (articleShareClickHandler) {
		document.removeEventListener('click', articleShareClickHandler);
	}

	articleShareClickHandler = (event) => {
		const button = event.target.closest('.article-share-button');

		if (!button) {
			return;
		}

		handleShare(button);
	};

	document.addEventListener('click', articleShareClickHandler);
}

/**
 * Shares (or copies) the article's URL, restoring the button's original label afterwards.
 *
 * @param {HTMLButtonElement} button Share button that was clicked.
 * @return {Promise<void>}
 */
async function handleShare(button) {
	const url = button.dataset.shareUrl;
	const title = button.dataset.shareTitle;

	if (!url) {
		return;
	}

	if (navigator.share) {
		try {
			await navigator.share({ title, url });
		} catch {
			// The visitor dismissed the native share sheet — nothing to do.
		}
		return;
	}

	if (navigator.clipboard) {
		await navigator.clipboard.writeText(url);
		const originalLabel = button.textContent;
		button.textContent = button.dataset.copiedLabel || originalLabel;
		setTimeout(() => {
			button.textContent = originalLabel;
		}, 2000);
	}
}
