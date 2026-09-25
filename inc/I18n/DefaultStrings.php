<?php
/**
 * Created: 2026-09-25 05:39 CEST
 * Role: Seed data for the theme's translation catalog (Solar_Template\I18n).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: List every user-visible string the theme's PHP code currently calls `__()`/`_e()`/
 *          `_n()` on, with its French and English translation, so the catalog stored in
 *          `wp_solar_template_translations` (and the `.mo` files compiled from it) always has a
 *          real entry for each one — not just a text-domain call with nothing behind it. Grows by
 *          one entry every time a step introduces a new visible string (see
 *          `./.claude/etapes/actions/apres_i18n-compatibilite.md`); a future translation editor
 *          screen edits the same rows this seeds.
 *
 * @package Solar_Template
 */

namespace Solar_Template\I18n;

use Solar_Template\Contracts\TranslatorInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seeds the translation catalog with every string currently used by the theme's PHP code.
 */
final class DefaultStrings {

	/**
	 * Every catalog entry, keyed by the string as used for `msgid` (the English source text).
	 *
	 * @return array<string, array{fr_FR: string, en_US: string, plural?: array{fr_FR: string, en_US: string}}>
	 */
	public static function all(): array {
		return array(
			'Solar Template: run "composer install" in the theme directory to enable its PHP dependencies.' => array(
				'fr_FR' => 'Solar Template : lancez « composer install » dans le dossier du thème pour activer ses dépendances PHP.',
				'en_US' => 'Solar Template: run "composer install" in the theme directory to enable its PHP dependencies.',
			),
			'Solar Template: run "npm run build" in the theme directory to compile its CSS/JS assets.' => array(
				'fr_FR' => 'Solar Template : lancez « npm run build » dans le dossier du thème pour compiler ses assets CSS/JS.',
				'en_US' => 'Solar Template: run "npm run build" in the theme directory to compile its CSS/JS assets.',
			),
			'Solar Template is designed for WooCommerce. Please <a href="%s">install and activate WooCommerce</a> to enable the storefront features.' => array(
				'fr_FR' => "Solar Template est conçu pour WooCommerce. Merci d'<a href=\"%s\">installer et d'activer WooCommerce</a> pour activer les fonctionnalités boutique.",
				'en_US' => 'Solar Template is designed for WooCommerce. Please <a href="%s">install and activate WooCommerce</a> to enable the storefront features.',
			),
			'Add to wishlist'             => array(
				'fr_FR' => 'Ajouter à la liste de souhaits',
				'en_US' => 'Add to wishlist',
			),
			'Add to cart'                 => array(
				'fr_FR' => 'Ajouter au panier',
				'en_US' => 'Add to cart',
			),
			'−%d%%'                       => array(
				'fr_FR' => '−%d %%',
				'en_US' => '−%d%%',
			),
			'Skip to content'             => array(
				'fr_FR' => 'Aller au contenu',
				'en_US' => 'Skip to content',
			),
			'Instagram'                   => array(
				'fr_FR' => 'Instagram',
				'en_US' => 'Instagram',
			),
			'Facebook'                    => array(
				'fr_FR' => 'Facebook',
				'en_US' => 'Facebook',
			),
			'X (Twitter)'                 => array(
				'fr_FR' => 'X (Twitter)',
				'en_US' => 'X (Twitter)',
			),
			'Search'                      => array(
				'fr_FR' => 'Rechercher',
				'en_US' => 'Search',
			),
			'My account'                  => array(
				'fr_FR' => 'Mon compte',
				'en_US' => 'My account',
			),
			'Cart'                        => array(
				'fr_FR' => 'Panier',
				'en_US' => 'Cart',
			),
			'Primary Navigation'          => array(
				'fr_FR' => 'Navigation principale',
				'en_US' => 'Primary Navigation',
			),
			'Shop'                        => array(
				'fr_FR' => 'Boutique',
				'en_US' => 'Shop',
			),
			'New In'                      => array(
				'fr_FR' => 'Nouveautés',
				'en_US' => 'New In',
			),
			'Collections'                 => array(
				'fr_FR' => 'Collections',
				'en_US' => 'Collections',
			),
			'Sale'                        => array(
				'fr_FR' => 'Promotions',
				'en_US' => 'Sale',
			),
			'Blog'                        => array(
				'fr_FR' => 'Blog',
				'en_US' => 'Blog',
			),
			'Contact'                     => array(
				'fr_FR' => 'Contact',
				'en_US' => 'Contact',
			),
			'Shop by category'            => array(
				'fr_FR' => 'Acheter par catégorie',
				'en_US' => 'Shop by category',
			),
			'Best Sellers'                => array(
				'fr_FR' => 'Meilleures ventes',
				'en_US' => 'Best Sellers',
			),
			'Limited Edition'             => array(
				'fr_FR' => 'Édition limitée',
				'en_US' => 'Limited Edition',
			),
			'Featured'                    => array(
				'fr_FR' => 'En vedette',
				'en_US' => 'Featured',
			),
			'View All Collections'        => array(
				'fr_FR' => 'Voir toutes les collections',
				'en_US' => 'View All Collections',
			),
			'A modern, configurable WooCommerce theme to showcase your products.' => array(
				'fr_FR' => 'Thème WooCommerce moderne et configurable pour valoriser vos produits.',
				'en_US' => 'A modern, configurable WooCommerce theme to showcase your products.',
			),
			'All Products'                => array(
				'fr_FR' => 'Tous les produits',
				'en_US' => 'All Products',
			),
			'Information'                 => array(
				'fr_FR' => 'Informations',
				'en_US' => 'Information',
			),
			'About'                       => array(
				'fr_FR' => 'À propos',
				'en_US' => 'About',
			),
			'Legal'                       => array(
				'fr_FR' => 'Légal',
				'en_US' => 'Legal',
			),
			'Terms & Conditions'          => array(
				'fr_FR' => 'C.G.V.',
				'en_US' => 'Terms & Conditions',
			),
			'Privacy Policy'              => array(
				'fr_FR' => 'Confidentialité',
				'en_US' => 'Privacy Policy',
			),
			'Legal Notice'                => array(
				'fr_FR' => 'Mentions légales',
				'en_US' => 'Legal Notice',
			),
			'Cookies'                     => array(
				'fr_FR' => 'Cookies',
				'en_US' => 'Cookies',
			),
			'Newsletter'                  => array(
				'fr_FR' => 'Newsletter',
				'en_US' => 'Newsletter',
			),
			'Get exclusive offers first.' => array(
				'fr_FR' => 'Offres exclusives en avant-première.',
				'en_US' => 'Get exclusive offers first.',
			),
			'Email address'               => array(
				'fr_FR' => 'Adresse e-mail',
				'en_US' => 'Email address',
			),
			'email@example.com'           => array(
				'fr_FR' => 'email@exemple.com',
				'en_US' => 'email@example.com',
			),
			'Subscribe'                   => array(
				'fr_FR' => "S'abonner",
				'en_US' => 'Subscribe',
			),
			'© {year} %s — WordPress WooCommerce theme · All rights reserved' => array(
				'fr_FR' => '© {year} %s — Thème WordPress WooCommerce · Tous droits réservés',
				'en_US' => '© {year} %s — WordPress WooCommerce theme · All rights reserved',
			),
		);
	}

	/**
	 * Registers every entry from self::all() for `fr_FR` and `en_US`, overwriting any existing
	 * row for the same key (this is the seed data, always the source of truth for these strings
	 * until edited from the future translation editor screen).
	 *
	 * @param TranslatorInterface $translator Translator to write the catalog through.
	 * @return void
	 */
	public static function seed( TranslatorInterface $translator ): void {
		foreach ( self::all() as $key => $translations ) {
			foreach ( array( 'fr_FR', 'en_US' ) as $locale ) {
				if ( ! isset( $translations[ $locale ] ) ) {
					continue;
				}

				$plural = $translations['plural'][ $locale ] ?? null;

				$translator->set_string( $locale, $key, $translations[ $locale ], $plural );
			}
		}
	}
}
