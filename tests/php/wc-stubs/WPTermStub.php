<?php
/**
 * Created: 2026-09-28 14:10 CEST
 * Role: Minimal WP_Term stand-in for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Same spirit as WCProductStub.php — a plain, constructible stand-in covering only the
 *          properties actually read by the classes under test (never a full WordPress install here).
 *
 * @package Solar_Template
 */

if ( class_exists( 'WP_Term', false ) ) {
	return;
}

/**
 * Minimal stand-in for WordPress' own WP_Term.
 */
class WP_Term {

	/**
	 * @var int
	 */
	public int $term_id;

	/**
	 * @var string
	 */
	public string $name;

	/**
	 * @var string
	 */
	public string $slug;

	/**
	 * @var string
	 */
	public string $taxonomy;

	/**
	 * @param int    $term_id  Term ID.
	 * @param string $name     Term name.
	 * @param string $slug     Term slug, defaults to $name when omitted.
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function __construct( int $term_id, string $name = '', string $slug = '', string $taxonomy = '' ) {
		$this->term_id  = $term_id;
		$this->name     = $name;
		$this->slug     = '' !== $slug ? $slug : $name;
		$this->taxonomy = $taxonomy;
	}
}
