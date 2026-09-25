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
			'Add to wishlist'                             => array(
				'fr_FR' => 'Ajouter à la liste de souhaits',
				'en_US' => 'Add to wishlist',
			),
			'Add to cart'                                 => array(
				'fr_FR' => 'Ajouter au panier',
				'en_US' => 'Add to cart',
			),
			'−%d%%'                                       => array(
				'fr_FR' => '−%d %%',
				'en_US' => '−%d%%',
			),
			'Skip to content'                             => array(
				'fr_FR' => 'Aller au contenu',
				'en_US' => 'Skip to content',
			),
			'Instagram'                                   => array(
				'fr_FR' => 'Instagram',
				'en_US' => 'Instagram',
			),
			'Facebook'                                    => array(
				'fr_FR' => 'Facebook',
				'en_US' => 'Facebook',
			),
			'X (Twitter)'                                 => array(
				'fr_FR' => 'X (Twitter)',
				'en_US' => 'X (Twitter)',
			),
			'Search'                                      => array(
				'fr_FR' => 'Rechercher',
				'en_US' => 'Search',
			),
			'My account'                                  => array(
				'fr_FR' => 'Mon compte',
				'en_US' => 'My account',
			),
			'Cart'                                        => array(
				'fr_FR' => 'Panier',
				'en_US' => 'Cart',
			),
			'Primary Navigation'                          => array(
				'fr_FR' => 'Navigation principale',
				'en_US' => 'Primary Navigation',
			),
			'Shop'                                        => array(
				'fr_FR' => 'Boutique',
				'en_US' => 'Shop',
			),
			'New In'                                      => array(
				'fr_FR' => 'Nouveautés',
				'en_US' => 'New In',
			),
			'Collections'                                 => array(
				'fr_FR' => 'Collections',
				'en_US' => 'Collections',
			),
			'Sale'                                        => array(
				'fr_FR' => 'Promotions',
				'en_US' => 'Sale',
			),
			'Blog'                                        => array(
				'fr_FR' => 'Blog',
				'en_US' => 'Blog',
			),
			'Contact'                                     => array(
				'fr_FR' => 'Contact',
				'en_US' => 'Contact',
			),
			'Shop by category'                            => array(
				'fr_FR' => 'Acheter par catégorie',
				'en_US' => 'Shop by category',
			),
			'Best Sellers'                                => array(
				'fr_FR' => 'Meilleures ventes',
				'en_US' => 'Best Sellers',
			),
			'Limited Edition'                             => array(
				'fr_FR' => 'Édition limitée',
				'en_US' => 'Limited Edition',
			),
			'Featured'                                    => array(
				'fr_FR' => 'En vedette',
				'en_US' => 'Featured',
			),
			'View All Collections'                        => array(
				'fr_FR' => 'Voir toutes les collections',
				'en_US' => 'View All Collections',
			),
			'A modern, configurable WooCommerce theme to showcase your products.' => array(
				'fr_FR' => 'Thème WooCommerce moderne et configurable pour valoriser vos produits.',
				'en_US' => 'A modern, configurable WooCommerce theme to showcase your products.',
			),
			'All Products'                                => array(
				'fr_FR' => 'Tous les produits',
				'en_US' => 'All Products',
			),
			'Information'                                 => array(
				'fr_FR' => 'Informations',
				'en_US' => 'Information',
			),
			'About'                                       => array(
				'fr_FR' => 'À propos',
				'en_US' => 'About',
			),
			'Legal'                                       => array(
				'fr_FR' => 'Légal',
				'en_US' => 'Legal',
			),
			'Terms & Conditions'                          => array(
				'fr_FR' => 'C.G.V.',
				'en_US' => 'Terms & Conditions',
			),
			'Privacy Policy'                              => array(
				'fr_FR' => 'Confidentialité',
				'en_US' => 'Privacy Policy',
			),
			'Legal Notice'                                => array(
				'fr_FR' => 'Mentions légales',
				'en_US' => 'Legal Notice',
			),
			'Cookies'                                     => array(
				'fr_FR' => 'Cookies',
				'en_US' => 'Cookies',
			),
			'Newsletter'                                  => array(
				'fr_FR' => 'Newsletter',
				'en_US' => 'Newsletter',
			),
			'Get exclusive offers first.'                 => array(
				'fr_FR' => 'Offres exclusives en avant-première.',
				'en_US' => 'Get exclusive offers first.',
			),
			'Email address'                               => array(
				'fr_FR' => 'Adresse e-mail',
				'en_US' => 'Email address',
			),
			'email@example.com'                           => array(
				'fr_FR' => 'email@exemple.com',
				'en_US' => 'email@example.com',
			),
			'Subscribe'                                   => array(
				'fr_FR' => "S'abonner",
				'en_US' => 'Subscribe',
			),
			'© {year} %s — WordPress WooCommerce theme · All rights reserved' => array(
				'fr_FR' => '© {year} %s — Thème WordPress WooCommerce · Tous droits réservés',
				'en_US' => '© {year} %s — WordPress WooCommerce theme · All rights reserved',
			),
			'Search this site'                            => array(
				'fr_FR' => 'Rechercher sur ce site',
				'en_US' => 'Search this site',
			),
			'Search products, articles…'                  => array(
				'fr_FR' => 'Rechercher des produits, des articles…',
				'en_US' => 'Search products, articles…',
			),
			'Close search'                                => array(
				'fr_FR' => 'Fermer la recherche',
				'en_US' => 'Close search',
			),
			'Spring · Summer 2025 Collection'             => array(
				'fr_FR' => 'Collection Printemps · Été 2025',
				'en_US' => 'Spring · Summer 2025 Collection',
			),
			'The essentials,'                             => array(
				'fr_FR' => "L'essentiel,",
				'en_US' => 'The essentials,',
			),
			'reimagined'                                  => array(
				'fr_FR' => 'repensé',
				'en_US' => 'reimagined',
			),
			'for you.'                                    => array(
				'fr_FR' => 'pour vous.',
				'en_US' => 'for you.',
			),
			'Discover our premium selection of products, carefully chosen to combine quality, design and durability.' => array(
				'fr_FR' => 'Découvrez notre sélection premium de produits soigneusement choisis pour allier qualité, design et durabilité.',
				'en_US' => 'Discover our premium selection of products, carefully chosen to combine quality, design and durability.',
			),
			'Explore the shop'                            => array(
				'fr_FR' => 'Explorer la boutique',
				'en_US' => 'Explore the shop',
			),
			'See what’s new →'                            => array(
				'fr_FR' => 'Voir les nouveautés →',
				'en_US' => 'See what’s new →',
			),
			'Free shipping from €60'                      => array(
				'fr_FR' => 'Livraison offerte dès 60€',
				'en_US' => 'Free shipping from €60',
			),
			'Returns within 30 days'                      => array(
				'fr_FR' => 'Retours sous 30 jours',
				'en_US' => 'Returns within 30 days',
			),
			'Secure payment'                              => array(
				'fr_FR' => 'Paiement sécurisé',
				'en_US' => 'Secure payment',
			),
			'Favorite pick'                               => array(
				'fr_FR' => 'Coup de cœur',
				'en_US' => 'Favorite pick',
			),
			'Summer 2025 Collection'                      => array(
				'fr_FR' => 'Collection Été 2025',
				'en_US' => 'Summer 2025 Collection',
			),
			'38% already claimed · Limited stock'         => array(
				'fr_FR' => '38% déjà vendus · Stock limité',
				'en_US' => '38% already claimed · Limited stock',
			),
			'Selection'                                   => array(
				'fr_FR' => 'Sélection',
				'en_US' => 'Selection',
			),
			'Favorites'                                   => array(
				'fr_FR' => 'Coups de cœur',
				'en_US' => 'Favorites',
			),
			'View all →'                                  => array(
				'fr_FR' => 'Voir tout →',
				'en_US' => 'View all →',
			),
			'On Sale'                                     => array(
				'fr_FR' => 'Promo',
				'en_US' => 'On Sale',
			),
			'View the full collection'                    => array(
				'fr_FR' => 'Voir toute la collection',
				'en_US' => 'View the full collection',
			),
			'Explore our worlds'                          => array(
				'fr_FR' => 'Explorer nos univers',
				'en_US' => 'Explore our worlds',
			),
			'Discover'                                    => array(
				'fr_FR' => 'Découvrir',
				'en_US' => 'Discover',
			),
			'Our story'                                   => array(
				'fr_FR' => 'Notre histoire',
				'en_US' => 'Our story',
			),
			'Craftsmanship, quality and contemporary design.' => array(
				'fr_FR' => 'Savoir-faire, qualité et design contemporain.',
				'en_US' => 'Craftsmanship, quality and contemporary design.',
			),
			'For over a decade, we have been selecting and offering products that combine artisanal quality, durability and modern aesthetics.' => array(
				'fr_FR' => "Depuis plus d'une décennie, nous sélectionnons et proposons des produits qui allient qualité artisanale, durabilité et esthétique moderne.",
				'en_US' => 'For over a decade, we have been selecting and offering products that combine artisanal quality, durability and modern aesthetics.',
			),
			'Every item is chosen with care, paying close attention to materials, manufacturing and environmental impact.' => array(
				'fr_FR' => "Chaque article est choisi avec soin, en accordant une attention particulière aux matériaux, à la fabrication et à l'impact environnemental.",
				'en_US' => 'Every item is chosen with care, paying close attention to materials, manufacturing and environmental impact.',
			),
			'10+'                                         => array(
				'fr_FR' => '10+',
				'en_US' => '10+',
			),
			'years of expertise'                          => array(
				'fr_FR' => "ans d'expertise",
				'en_US' => 'years of expertise',
			),
			'50K+'                                        => array(
				'fr_FR' => '50K+',
				'en_US' => '50K+',
			),
			'Happy customers'                             => array(
				'fr_FR' => 'Clients satisfaits',
				'en_US' => 'Happy customers',
			),
			'200+'                                        => array(
				'fr_FR' => '200+',
				'en_US' => '200+',
			),
			'Products available'                          => array(
				'fr_FR' => 'Produits disponibles',
				'en_US' => 'Products available',
			),
			'4.9★'                                        => array(
				'fr_FR' => '4,9★',
				'en_US' => '4.9★',
			),
			'Average rating'                              => array(
				'fr_FR' => 'Note moyenne',
				'en_US' => 'Average rating',
			),
			'Learn more'                                  => array(
				'fr_FR' => 'En savoir plus',
				'en_US' => 'Learn more',
			),
			'Testimonials'                                => array(
				'fr_FR' => 'Témoignages',
				'en_US' => 'Testimonials',
			),
			'What our customers say'                      => array(
				'fr_FR' => 'Ce que disent nos clients',
				'en_US' => 'What our customers say',
			),
			'%d out of 5 stars'                           => array(
				'fr_FR' => '%d étoiles sur 5',
				'en_US' => '%d out of 5 stars',
			),
			'Ultra-fast delivery and the product exactly matched my expectations. Customer service is outstanding. Highly recommend!' => array(
				'fr_FR' => 'Livraison ultra rapide et produit exactement conforme à mes attentes. Le service client est remarquable. Je recommande vivement !',
				'en_US' => 'Ultra-fast delivery and the product exactly matched my expectations. Customer service is outstanding. Highly recommend!',
			),
			'Marie L.'                                    => array(
				'fr_FR' => 'Marie L.',
				'en_US' => 'Marie L.',
			),
			'Customer since 2023'                         => array(
				'fr_FR' => 'Cliente depuis 2023',
				'en_US' => 'Customer since 2023',
			),
			'Exceptional quality for a very reasonable price. A loyal customer for 2 years and never disappointed. Products that last.' => array(
				'fr_FR' => "Qualité exceptionnelle pour un prix très raisonnable. Client fidèle depuis 2 ans et je n'ai jamais été déçu. Des produits qui durent.",
				'en_US' => 'Exceptional quality for a very reasonable price. A loyal customer for 2 years and never disappointed. Products that last.',
			),
			'Thomas R.'                                   => array(
				'fr_FR' => 'Thomas R.',
				'en_US' => 'Thomas R.',
			),
			'Customer since 2022'                         => array(
				'fr_FR' => 'Client depuis 2022',
				'en_US' => 'Customer since 2022',
			),
			'Responsive customer service and impeccable product quality. My purchase far exceeded my expectations. Very satisfied!' => array(
				'fr_FR' => "Service client réactif et produits d'une qualité irréprochable. Mon achat a largement dépassé mes espérances. Très satisfaite !",
				'en_US' => 'Responsive customer service and impeccable product quality. My purchase far exceeded my expectations. Very satisfied!',
			),
			'Sophie M.'                                   => array(
				'fr_FR' => 'Sophie M.',
				'en_US' => 'Sophie M.',
			),
			'Customer since 2024'                         => array(
				'fr_FR' => 'Cliente depuis 2024',
				'en_US' => 'Customer since 2024',
			),
			'News'                                        => array(
				'fr_FR' => 'Actualités',
				'en_US' => 'News',
			),
			'Inspiration & tips'                          => array(
				'fr_FR' => 'Inspirations & conseils',
				'en_US' => 'Inspiration & tips',
			),
			'View all articles →'                         => array(
				'fr_FR' => 'Voir tous les articles →',
				'en_US' => 'View all articles →',
			),
			'%1$s · %2$d min read'                        => array(
				'fr_FR' => '%1$s · %2$d min de lecture',
				'en_US' => '%1$s · %2$d min read',
			),
			'Stay in the loop'                            => array(
				'fr_FR' => 'Restez dans la boucle',
				'en_US' => 'Stay in the loop',
			),
			'Get our new arrivals, exclusive offers and inspiration straight to your inbox.' => array(
				'fr_FR' => 'Recevez nos nouveautés, offres exclusives et inspirations directement dans votre boîte mail.',
				'en_US' => 'Get our new arrivals, exclusive offers and inspiration straight to your inbox.',
			),
			'your@email.com'                              => array(
				'fr_FR' => 'votre@email.com',
				'en_US' => 'your@email.com',
			),
			'Unsubscribe at any time. No spam, ever.'     => array(
				'fr_FR' => 'Désinscription à tout moment. Aucun spam, promis.',
				'en_US' => 'Unsubscribe at any time. No spam, ever.',
			),
			'Thank you for subscribing!'                  => array(
				'fr_FR' => 'Merci pour votre inscription !',
				'en_US' => 'Thank you for subscribing!',
			),
			'This email address is already subscribed.'   => array(
				'fr_FR' => 'Cette adresse e-mail est déjà inscrite.',
				'en_US' => 'This email address is already subscribed.',
			),
			'Please enter a valid email address.'         => array(
				'fr_FR' => 'Merci de saisir une adresse e-mail valide.',
				'en_US' => 'Please enter a valid email address.',
			),
			'Something went wrong. Please try again.'     => array(
				'fr_FR' => "Une erreur s'est produite. Merci de réessayer.",
				'en_US' => 'Something went wrong. Please try again.',
			),
			'Breadcrumb'                                  => array(
				'fr_FR' => "Fil d'Ariane",
				'en_US' => 'Breadcrumb',
			),
			'No products currently match this selection.' => array(
				'fr_FR' => 'Aucun produit ne correspond actuellement à cette sélection.',
				'en_US' => 'No products currently match this selection.',
			),
			'%d product available'                        => array(
				'fr_FR'  => '%d produit disponible',
				'en_US'  => '%d product available',
				'plural' => array(
					'fr_FR' => '%d produits disponibles',
					'en_US' => '%d products available',
				),
			),
			'Category'                                    => array(
				'fr_FR' => 'Catégorie',
				'en_US' => 'Category',
			),
			'Price'                                       => array(
				'fr_FR' => 'Prix',
				'en_US' => 'Price',
			),
			'Color'                                       => array(
				'fr_FR' => 'Couleur',
				'en_US' => 'Color',
			),
			'Size'                                        => array(
				'fr_FR' => 'Taille',
				'en_US' => 'Size',
			),
			'Rating'                                      => array(
				'fr_FR' => 'Note',
				'en_US' => 'Rating',
			),
			'%1$s (%2$d)'                                 => array(
				'fr_FR' => '%1$s (%2$d)',
				'en_US' => '%1$s (%2$d)',
			),
			'Min'                                         => array(
				'fr_FR' => 'Min',
				'en_US' => 'Min',
			),
			'Max'                                         => array(
				'fr_FR' => 'Max',
				'en_US' => 'Max',
			),
			'%d star'                                     => array(
				'fr_FR'  => '%d étoile',
				'en_US'  => '%d star',
				'plural' => array(
					'fr_FR' => '%d étoiles',
					'en_US' => '%d stars',
				),
			),
			'Active filters:'                             => array(
				'fr_FR' => 'Filtres actifs :',
				'en_US' => 'Active filters:',
			),
			'Remove filter: %s'                           => array(
				'fr_FR' => 'Retirer le filtre : %s',
				'en_US' => 'Remove filter: %s',
			),
			'Clear all'                                   => array(
				'fr_FR' => 'Effacer tout',
				'en_US' => 'Clear all',
			),
			'%1$s – %2$s'                                 => array(
				'fr_FR' => '%1$s – %2$s',
				'en_US' => '%1$s – %2$s',
			),
			'From %s'                                     => array(
				'fr_FR' => 'À partir de %s',
				'en_US' => 'From %s',
			),
			'Up to %s'                                    => array(
				'fr_FR' => "Jusqu'à %s",
				'en_US' => 'Up to %s',
			),
			'Sort by'                                     => array(
				'fr_FR' => 'Trier par',
				'en_US' => 'Sort by',
			),
			'Relevance'                                   => array(
				'fr_FR' => 'Pertinence',
				'en_US' => 'Relevance',
			),
			'Price: low to high'                          => array(
				'fr_FR' => 'Prix croissant',
				'en_US' => 'Price: low to high',
			),
			'Price: high to low'                          => array(
				'fr_FR' => 'Prix décroissant',
				'en_US' => 'Price: high to low',
			),
			'Newest'                                      => array(
				'fr_FR' => 'Nouveautés',
				'en_US' => 'Newest',
			),
			'Best sellers'                                => array(
				'fr_FR' => 'Meilleures ventes',
				'en_US' => 'Best sellers',
			),
			'Showing %1$d of %2$d products'               => array(
				'fr_FR' => 'Affichage de %1$d sur %2$d produits',
				'en_US' => 'Showing %1$d of %2$d products',
			),
			'Load more products'                          => array(
				'fr_FR' => 'Charger plus de produits',
				'en_US' => 'Load more products',
			),
			'View image %1$d of %2$d'                     => array(
				'fr_FR' => "Voir l'image %1\$d sur %2\$d",
				'en_US' => 'View image %1$d of %2$d',
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
