/**
 * Created: 2026-09-27 09:40 CEST
 * Role: Behaviour for the documentation site's root landing page (doc/index.html).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Redirect the visitor straight to the French or English documentation home page based
 *          on their browser language, without JavaScript being required to reach either one (the
 *          landing page itself always shows a manual choice for both languages).
 */

/**
 * Resolves which documentation language subdirectory to redirect to, based on the browser's
 * reported language(s). Defaults to English when no French preference is detected.
 *
 * @param {ReadonlyArray<string>} languages The browser's preferred languages (most preferred first).
 * @return {string} Either "fr" or "en".
 */
function solarTemplateResolveDocLanguage( languages ) {
	var hasFrenchPreference = languages.some( function ( language ) {
		return language.toLowerCase().indexOf( 'fr' ) === 0;
	} );

	return hasFrenchPreference ? 'fr' : 'en';
}

var preferredLanguages = navigator.languages && navigator.languages.length
	? navigator.languages
	: [ navigator.language || 'en' ];

window.location.replace( solarTemplateResolveDocLanguage( preferredLanguages ) + '/index.html' );
