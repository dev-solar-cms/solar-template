/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Shared behaviour for the Solar Template project documentation site (doc/en, doc/fr).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Highlight the current page in the sidebar navigation.
 */

/**
 * Marks the sidebar link matching the current page as active.
 *
 * @param {Document} doc The document to operate on.
 * @return {void}
 */
function solarTemplateHighlightActiveDocLink( doc ) {
	var currentPath = doc.location.pathname.split( '/' ).pop() || 'index.html';
	var links = doc.querySelectorAll( '.doc-sidebar nav a' );

	links.forEach( function ( link ) {
		var linkPath = link.getAttribute( 'href' ).split( '/' ).pop();
		if ( linkPath === currentPath ) {
			link.classList.add( 'is-active' );
		}
	} );
}

solarTemplateHighlightActiveDocLink( document );
