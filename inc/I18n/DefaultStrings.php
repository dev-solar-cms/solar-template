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
			'Add to wishlist'                              => array(
				'fr_FR' => 'Ajouter à la liste de souhaits',
				'en_US' => 'Add to wishlist',
			),
			'Add to cart'                                  => array(
				'fr_FR' => 'Ajouter au panier',
				'en_US' => 'Add to cart',
			),
			'−%d%%'                                        => array(
				'fr_FR' => '−%d %%',
				'en_US' => '−%d%%',
			),
			'Skip to content'                              => array(
				'fr_FR' => 'Aller au contenu',
				'en_US' => 'Skip to content',
			),
			'Instagram'                                    => array(
				'fr_FR' => 'Instagram',
				'en_US' => 'Instagram',
			),
			'Facebook'                                     => array(
				'fr_FR' => 'Facebook',
				'en_US' => 'Facebook',
			),
			'X (Twitter)'                                  => array(
				'fr_FR' => 'X (Twitter)',
				'en_US' => 'X (Twitter)',
			),
			'Search'                                       => array(
				'fr_FR' => 'Rechercher',
				'en_US' => 'Search',
			),
			'My account'                                   => array(
				'fr_FR' => 'Mon compte',
				'en_US' => 'My account',
			),
			'Cart'                                         => array(
				'fr_FR' => 'Panier',
				'en_US' => 'Cart',
			),
			'Primary Navigation'                           => array(
				'fr_FR' => 'Navigation principale',
				'en_US' => 'Primary Navigation',
			),
			'Shop'                                         => array(
				'fr_FR' => 'Boutique',
				'en_US' => 'Shop',
			),
			'New In'                                       => array(
				'fr_FR' => 'Nouveautés',
				'en_US' => 'New In',
			),
			'Collections'                                  => array(
				'fr_FR' => 'Collections',
				'en_US' => 'Collections',
			),
			'Sale'                                         => array(
				'fr_FR' => 'Promotions',
				'en_US' => 'Sale',
			),
			'Blog'                                         => array(
				'fr_FR' => 'Blog',
				'en_US' => 'Blog',
			),
			'Contact'                                      => array(
				'fr_FR' => 'Contact',
				'en_US' => 'Contact',
			),
			'Shop by category'                             => array(
				'fr_FR' => 'Acheter par catégorie',
				'en_US' => 'Shop by category',
			),
			'Best Sellers'                                 => array(
				'fr_FR' => 'Meilleures ventes',
				'en_US' => 'Best Sellers',
			),
			'Limited Edition'                              => array(
				'fr_FR' => 'Édition limitée',
				'en_US' => 'Limited Edition',
			),
			'Featured'                                     => array(
				'fr_FR' => 'En vedette',
				'en_US' => 'Featured',
			),
			'View All Collections'                         => array(
				'fr_FR' => 'Voir toutes les collections',
				'en_US' => 'View All Collections',
			),
			'A modern, configurable WooCommerce theme to showcase your products.' => array(
				'fr_FR' => 'Thème WooCommerce moderne et configurable pour valoriser vos produits.',
				'en_US' => 'A modern, configurable WooCommerce theme to showcase your products.',
			),
			'All Products'                                 => array(
				'fr_FR' => 'Tous les produits',
				'en_US' => 'All Products',
			),
			'Information'                                  => array(
				'fr_FR' => 'Informations',
				'en_US' => 'Information',
			),
			'About'                                        => array(
				'fr_FR' => 'À propos',
				'en_US' => 'About',
			),
			'Legal'                                        => array(
				'fr_FR' => 'Légal',
				'en_US' => 'Legal',
			),
			'Terms & Conditions'                           => array(
				'fr_FR' => 'C.G.V.',
				'en_US' => 'Terms & Conditions',
			),
			'Privacy Policy'                               => array(
				'fr_FR' => 'Confidentialité',
				'en_US' => 'Privacy Policy',
			),
			'Legal Notice'                                 => array(
				'fr_FR' => 'Mentions légales',
				'en_US' => 'Legal Notice',
			),
			'Cookies'                                      => array(
				'fr_FR' => 'Cookies',
				'en_US' => 'Cookies',
			),
			'Newsletter'                                   => array(
				'fr_FR' => 'Newsletter',
				'en_US' => 'Newsletter',
			),
			'Get exclusive offers first.'                  => array(
				'fr_FR' => 'Offres exclusives en avant-première.',
				'en_US' => 'Get exclusive offers first.',
			),
			'Email address'                                => array(
				'fr_FR' => 'Adresse e-mail',
				'en_US' => 'Email address',
			),
			'email@example.com'                            => array(
				'fr_FR' => 'email@exemple.com',
				'en_US' => 'email@example.com',
			),
			'Subscribe'                                    => array(
				'fr_FR' => "S'abonner",
				'en_US' => 'Subscribe',
			),
			'© {year} %s — WordPress WooCommerce theme · All rights reserved' => array(
				'fr_FR' => '© {year} %s — Thème WordPress WooCommerce · Tous droits réservés',
				'en_US' => '© {year} %s — WordPress WooCommerce theme · All rights reserved',
			),
			'Search this site'                             => array(
				'fr_FR' => 'Rechercher sur ce site',
				'en_US' => 'Search this site',
			),
			'Search products, articles…'                   => array(
				'fr_FR' => 'Rechercher des produits, des articles…',
				'en_US' => 'Search products, articles…',
			),
			'Close search'                                 => array(
				'fr_FR' => 'Fermer la recherche',
				'en_US' => 'Close search',
			),
			'Spring · Summer 2025 Collection'              => array(
				'fr_FR' => 'Collection Printemps · Été 2025',
				'en_US' => 'Spring · Summer 2025 Collection',
			),
			'The essentials,'                              => array(
				'fr_FR' => "L'essentiel,",
				'en_US' => 'The essentials,',
			),
			'reimagined'                                   => array(
				'fr_FR' => 'repensé',
				'en_US' => 'reimagined',
			),
			'for you.'                                     => array(
				'fr_FR' => 'pour vous.',
				'en_US' => 'for you.',
			),
			'Discover our premium selection of products, carefully chosen to combine quality, design and durability.' => array(
				'fr_FR' => 'Découvrez notre sélection premium de produits soigneusement choisis pour allier qualité, design et durabilité.',
				'en_US' => 'Discover our premium selection of products, carefully chosen to combine quality, design and durability.',
			),
			'Explore the shop'                             => array(
				'fr_FR' => 'Explorer la boutique',
				'en_US' => 'Explore the shop',
			),
			'See what’s new →'                             => array(
				'fr_FR' => 'Voir les nouveautés →',
				'en_US' => 'See what’s new →',
			),
			'Free shipping from €60'                       => array(
				'fr_FR' => 'Livraison offerte dès 60€',
				'en_US' => 'Free shipping from €60',
			),
			'Returns within 30 days'                       => array(
				'fr_FR' => 'Retours sous 30 jours',
				'en_US' => 'Returns within 30 days',
			),
			'Secure payment'                               => array(
				'fr_FR' => 'Paiement sécurisé',
				'en_US' => 'Secure payment',
			),
			'Favorite pick'                                => array(
				'fr_FR' => 'Coup de cœur',
				'en_US' => 'Favorite pick',
			),
			'Summer 2025 Collection'                       => array(
				'fr_FR' => 'Collection Été 2025',
				'en_US' => 'Summer 2025 Collection',
			),
			'38% already claimed · Limited stock'          => array(
				'fr_FR' => '38% déjà vendus · Stock limité',
				'en_US' => '38% already claimed · Limited stock',
			),
			'Selection'                                    => array(
				'fr_FR' => 'Sélection',
				'en_US' => 'Selection',
			),
			'Favorites'                                    => array(
				'fr_FR' => 'Coups de cœur',
				'en_US' => 'Favorites',
			),
			'View all →'                                   => array(
				'fr_FR' => 'Voir tout →',
				'en_US' => 'View all →',
			),
			'On Sale'                                      => array(
				'fr_FR' => 'Promo',
				'en_US' => 'On Sale',
			),
			'View the full collection'                     => array(
				'fr_FR' => 'Voir toute la collection',
				'en_US' => 'View the full collection',
			),
			'Explore our worlds'                           => array(
				'fr_FR' => 'Explorer nos univers',
				'en_US' => 'Explore our worlds',
			),
			'Discover'                                     => array(
				'fr_FR' => 'Découvrir',
				'en_US' => 'Discover',
			),
			'Our story'                                    => array(
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
			'10+'                                          => array(
				'fr_FR' => '10+',
				'en_US' => '10+',
			),
			'years of expertise'                           => array(
				'fr_FR' => "ans d'expertise",
				'en_US' => 'years of expertise',
			),
			'50K+'                                         => array(
				'fr_FR' => '50K+',
				'en_US' => '50K+',
			),
			'Happy customers'                              => array(
				'fr_FR' => 'Clients satisfaits',
				'en_US' => 'Happy customers',
			),
			'200+'                                         => array(
				'fr_FR' => '200+',
				'en_US' => '200+',
			),
			'Products available'                           => array(
				'fr_FR' => 'Produits disponibles',
				'en_US' => 'Products available',
			),
			'4.9★'                                         => array(
				'fr_FR' => '4,9★',
				'en_US' => '4.9★',
			),
			'Average rating'                               => array(
				'fr_FR' => 'Note moyenne',
				'en_US' => 'Average rating',
			),
			'Learn more'                                   => array(
				'fr_FR' => 'En savoir plus',
				'en_US' => 'Learn more',
			),
			'Testimonials'                                 => array(
				'fr_FR' => 'Témoignages',
				'en_US' => 'Testimonials',
			),
			'What our customers say'                       => array(
				'fr_FR' => 'Ce que disent nos clients',
				'en_US' => 'What our customers say',
			),
			'%d out of 5 stars'                            => array(
				'fr_FR' => '%d étoiles sur 5',
				'en_US' => '%d out of 5 stars',
			),
			'Ultra-fast delivery and the product exactly matched my expectations. Customer service is outstanding. Highly recommend!' => array(
				'fr_FR' => 'Livraison ultra rapide et produit exactement conforme à mes attentes. Le service client est remarquable. Je recommande vivement !',
				'en_US' => 'Ultra-fast delivery and the product exactly matched my expectations. Customer service is outstanding. Highly recommend!',
			),
			'Marie L.'                                     => array(
				'fr_FR' => 'Marie L.',
				'en_US' => 'Marie L.',
			),
			'Customer since 2023'                          => array(
				'fr_FR' => 'Cliente depuis 2023',
				'en_US' => 'Customer since 2023',
			),
			'Exceptional quality for a very reasonable price. A loyal customer for 2 years and never disappointed. Products that last.' => array(
				'fr_FR' => "Qualité exceptionnelle pour un prix très raisonnable. Client fidèle depuis 2 ans et je n'ai jamais été déçu. Des produits qui durent.",
				'en_US' => 'Exceptional quality for a very reasonable price. A loyal customer for 2 years and never disappointed. Products that last.',
			),
			'Thomas R.'                                    => array(
				'fr_FR' => 'Thomas R.',
				'en_US' => 'Thomas R.',
			),
			'Customer since 2022'                          => array(
				'fr_FR' => 'Client depuis 2022',
				'en_US' => 'Customer since 2022',
			),
			'Responsive customer service and impeccable product quality. My purchase far exceeded my expectations. Very satisfied!' => array(
				'fr_FR' => "Service client réactif et produits d'une qualité irréprochable. Mon achat a largement dépassé mes espérances. Très satisfaite !",
				'en_US' => 'Responsive customer service and impeccable product quality. My purchase far exceeded my expectations. Very satisfied!',
			),
			'Sophie M.'                                    => array(
				'fr_FR' => 'Sophie M.',
				'en_US' => 'Sophie M.',
			),
			'Customer since 2024'                          => array(
				'fr_FR' => 'Cliente depuis 2024',
				'en_US' => 'Customer since 2024',
			),
			'News'                                         => array(
				'fr_FR' => 'Actualités',
				'en_US' => 'News',
			),
			'Inspiration & tips'                           => array(
				'fr_FR' => 'Inspirations & conseils',
				'en_US' => 'Inspiration & tips',
			),
			'View all articles →'                          => array(
				'fr_FR' => 'Voir tous les articles →',
				'en_US' => 'View all articles →',
			),
			'%1$s · %2$d min read'                         => array(
				'fr_FR' => '%1$s · %2$d min de lecture',
				'en_US' => '%1$s · %2$d min read',
			),
			'Stay in the loop'                             => array(
				'fr_FR' => 'Restez dans la boucle',
				'en_US' => 'Stay in the loop',
			),
			'Get our new arrivals, exclusive offers and inspiration straight to your inbox.' => array(
				'fr_FR' => 'Recevez nos nouveautés, offres exclusives et inspirations directement dans votre boîte mail.',
				'en_US' => 'Get our new arrivals, exclusive offers and inspiration straight to your inbox.',
			),
			'your@email.com'                               => array(
				'fr_FR' => 'votre@email.com',
				'en_US' => 'your@email.com',
			),
			'Unsubscribe at any time. No spam, ever.'      => array(
				'fr_FR' => 'Désinscription à tout moment. Aucun spam, promis.',
				'en_US' => 'Unsubscribe at any time. No spam, ever.',
			),
			'Thank you for subscribing!'                   => array(
				'fr_FR' => 'Merci pour votre inscription !',
				'en_US' => 'Thank you for subscribing!',
			),
			'This email address is already subscribed.'    => array(
				'fr_FR' => 'Cette adresse e-mail est déjà inscrite.',
				'en_US' => 'This email address is already subscribed.',
			),
			'Please enter a valid email address.'          => array(
				'fr_FR' => 'Merci de saisir une adresse e-mail valide.',
				'en_US' => 'Please enter a valid email address.',
			),
			'Something went wrong. Please try again.'      => array(
				'fr_FR' => "Une erreur s'est produite. Merci de réessayer.",
				'en_US' => 'Something went wrong. Please try again.',
			),
			'Breadcrumb'                                   => array(
				'fr_FR' => "Fil d'Ariane",
				'en_US' => 'Breadcrumb',
			),
			'No products currently match this selection.'  => array(
				'fr_FR' => 'Aucun produit ne correspond actuellement à cette sélection.',
				'en_US' => 'No products currently match this selection.',
			),
			'%d product available'                         => array(
				'fr_FR'  => '%d produit disponible',
				'en_US'  => '%d product available',
				'plural' => array(
					'fr_FR' => '%d produits disponibles',
					'en_US' => '%d products available',
				),
			),
			'Category'                                     => array(
				'fr_FR' => 'Catégorie',
				'en_US' => 'Category',
			),
			'Price'                                        => array(
				'fr_FR' => 'Prix',
				'en_US' => 'Price',
			),
			'Color'                                        => array(
				'fr_FR' => 'Couleur',
				'en_US' => 'Color',
			),
			'Size'                                         => array(
				'fr_FR' => 'Taille',
				'en_US' => 'Size',
			),
			'Rating'                                       => array(
				'fr_FR' => 'Note',
				'en_US' => 'Rating',
			),
			'%1$s (%2$d)'                                  => array(
				'fr_FR' => '%1$s (%2$d)',
				'en_US' => '%1$s (%2$d)',
			),
			'Min'                                          => array(
				'fr_FR' => 'Min',
				'en_US' => 'Min',
			),
			'Max'                                          => array(
				'fr_FR' => 'Max',
				'en_US' => 'Max',
			),
			'%d star'                                      => array(
				'fr_FR'  => '%d étoile',
				'en_US'  => '%d star',
				'plural' => array(
					'fr_FR' => '%d étoiles',
					'en_US' => '%d stars',
				),
			),
			'Active filters:'                              => array(
				'fr_FR' => 'Filtres actifs :',
				'en_US' => 'Active filters:',
			),
			'Remove filter: %s'                            => array(
				'fr_FR' => 'Retirer le filtre : %s',
				'en_US' => 'Remove filter: %s',
			),
			'Clear all'                                    => array(
				'fr_FR' => 'Effacer tout',
				'en_US' => 'Clear all',
			),
			'%1$s – %2$s'                                  => array(
				'fr_FR' => '%1$s – %2$s',
				'en_US' => '%1$s – %2$s',
			),
			'From %s'                                      => array(
				'fr_FR' => 'À partir de %s',
				'en_US' => 'From %s',
			),
			'Up to %s'                                     => array(
				'fr_FR' => "Jusqu'à %s",
				'en_US' => 'Up to %s',
			),
			'Sort by'                                      => array(
				'fr_FR' => 'Trier par',
				'en_US' => 'Sort by',
			),
			'Relevance'                                    => array(
				'fr_FR' => 'Pertinence',
				'en_US' => 'Relevance',
			),
			'Price: low to high'                           => array(
				'fr_FR' => 'Prix croissant',
				'en_US' => 'Price: low to high',
			),
			'Price: high to low'                           => array(
				'fr_FR' => 'Prix décroissant',
				'en_US' => 'Price: high to low',
			),
			'Newest'                                       => array(
				'fr_FR' => 'Nouveautés',
				'en_US' => 'Newest',
			),
			'Best sellers'                                 => array(
				'fr_FR' => 'Meilleures ventes',
				'en_US' => 'Best sellers',
			),
			'Showing %1$d of %2$d products'                => array(
				'fr_FR' => 'Affichage de %1$d sur %2$d produits',
				'en_US' => 'Showing %1$d of %2$d products',
			),
			'Load more products'                           => array(
				'fr_FR' => 'Charger plus de produits',
				'en_US' => 'Load more products',
			),
			'View image %1$d of %2$d'                      => array(
				'fr_FR' => "Voir l'image %1\$d sur %2\$d",
				'en_US' => 'View image %1$d of %2$d',
			),
			'New'                                          => array(
				'fr_FR' => 'Nouveau',
				'en_US' => 'New',
			),
			'Solar Premium'                                => array(
				'fr_FR' => 'Solar Premium',
				'en_US' => 'Solar Premium',
			),
			'In stock'                                     => array(
				'fr_FR' => 'En stock',
				'en_US' => 'In stock',
			),
			'Out of stock'                                 => array(
				'fr_FR' => 'Rupture de stock',
				'en_US' => 'Out of stock',
			),
			'Available on backorder'                       => array(
				'fr_FR' => 'Disponible sur commande',
				'en_US' => 'Available on backorder',
			),
			'%d review →'                                  => array(
				'fr_FR'  => '%d avis →',
				'en_US'  => '%d review →',
				'plural' => array(
					'fr_FR' => '%d avis →',
					'en_US' => '%d reviews →',
				),
			),
			'Free shipping'                                => array(
				'fr_FR' => 'Livraison offerte',
				'en_US' => 'Free shipping',
			),
			'From €60'                                     => array(
				'fr_FR' => 'Dès 60€',
				'en_US' => 'From €60',
			),
			'Free returns'                                 => array(
				'fr_FR' => 'Retour gratuit',
				'en_US' => 'Free returns',
			),
			'Within 30 days'                               => array(
				'fr_FR' => 'Sous 30 jours',
				'en_US' => 'Within 30 days',
			),
			'SSL encrypted'                                => array(
				'fr_FR' => 'Chiffrement SSL',
				'en_US' => 'SSL encrypted',
			),
			'Shipping & Returns'                           => array(
				'fr_FR' => 'Livraison & Retours',
				'en_US' => 'Shipping & Returns',
			),
			'Standard delivery in 3–5 business days (free from €60). Express delivery available. Returns accepted within 30 days in their original packaging.' => array(
				'fr_FR' => "Livraison standard en 3 à 5 jours ouvrés (offerte dès 60€). Livraison express disponible. Retours acceptés sous 30 jours dans leur emballage d'origine.",
				'en_US' => 'Standard delivery in 3–5 business days (free from €60). Express delivery available. Returns accepted within 30 days in their original packaging.',
			),
			'Size guide'                                   => array(
				'fr_FR' => 'Guide des tailles',
				'en_US' => 'Size guide',
			),
			'If in doubt between two sizes, we recommend choosing the larger one. See the product\'s own attributes above for the sizes currently available.' => array(
				'fr_FR' => 'En cas de doute entre deux tailles, nous recommandons de choisir la taille supérieure. Consultez les caractéristiques du produit ci-dessus pour connaître les tailles actuellement disponibles.',
				'en_US' => 'If in doubt between two sizes, we recommend choosing the larger one. See the product\'s own attributes above for the sizes currently available.',
			),
			'Care instructions'                            => array(
				'fr_FR' => 'Entretien',
				'en_US' => 'Care instructions',
			),
			'Hand washing recommended. Do not tumble dry. Iron on a low heat setting if needed.' => array(
				'fr_FR' => 'Lavage à la main recommandé. Ne pas sécher en machine. Repasser à basse température si nécessaire.',
				'en_US' => 'Hand washing recommended. Do not tumble dry. Iron on a low heat setting if needed.',
			),
			'Color: %s'                                    => array(
				'fr_FR' => 'Couleur : %s',
				'en_US' => 'Color: %s',
			),
			'Decrease quantity'                            => array(
				'fr_FR' => 'Diminuer la quantité',
				'en_US' => 'Decrease quantity',
			),
			'Quantity'                                     => array(
				'fr_FR' => 'Quantité',
				'en_US' => 'Quantity',
			),
			'Increase quantity'                            => array(
				'fr_FR' => 'Augmenter la quantité',
				'en_US' => 'Increase quantity',
			),
			'This combination is currently unavailable.'   => array(
				'fr_FR' => "Cette combinaison n'est actuellement pas disponible.",
				'en_US' => 'This combination is currently unavailable.',
			),
			'This combination is currently out of stock.'  => array(
				'fr_FR' => 'Cette combinaison est actuellement en rupture de stock.',
				'en_US' => 'This combination is currently out of stock.',
			),
			'Custom engraving'                             => array(
				'fr_FR' => 'Gravure personnalisée',
				'en_US' => 'Custom engraving',
			),
			'Let customers add custom engraving text to this product, for an extra price.' => array(
				'fr_FR' => 'Permet aux clients d\'ajouter un texte de gravure personnalisé à ce produit, moyennant un supplément.',
				'en_US' => 'Let customers add custom engraving text to this product, for an extra price.',
			),
			'Engraving price (%s)'                         => array(
				'fr_FR' => 'Prix de la gravure (%s)',
				'en_US' => 'Engraving price (%s)',
			),
			'Max. engraving length (characters)'           => array(
				'fr_FR' => 'Longueur maximale de la gravure (caractères)',
				'en_US' => 'Max. engraving length (characters)',
			),
			'Add your text · +%s'                          => array(
				'fr_FR' => 'Ajoutez votre texte · +%s',
				'en_US' => 'Add your text · +%s',
			),
			'Enable custom engraving'                      => array(
				'fr_FR' => 'Activer la gravure personnalisée',
				'en_US' => 'Enable custom engraving',
			),
			'Your engraving text (max. %d characters)'     => array(
				'fr_FR' => 'Votre texte de gravure (max. %d caractères)',
				'en_US' => 'Your engraving text (max. %d characters)',
			),
			'Engraving'                                    => array(
				'fr_FR' => 'Gravure',
				'en_US' => 'Engraving',
			),
			'Description'                                  => array(
				'fr_FR' => 'Description',
				'en_US' => 'Description',
			),
			'Review (%d)'                                  => array(
				'fr_FR'  => 'Avis (%d)',
				'en_US'  => 'Review (%d)',
				'plural' => array(
					'fr_FR' => 'Avis (%d)',
					'en_US' => 'Reviews (%d)',
				),
			),
			'Specifications'                               => array(
				'fr_FR' => 'Caractéristiques',
				'en_US' => 'Specifications',
			),
			'Weight'                                       => array(
				'fr_FR' => 'Poids',
				'en_US' => 'Weight',
			),
			'Dimensions'                                   => array(
				'fr_FR' => 'Dimensions',
				'en_US' => 'Dimensions',
			),
			'Product information'                          => array(
				'fr_FR' => 'Informations produit',
				'en_US' => 'Product information',
			),
			'Based on %d verified review'                  => array(
				'fr_FR'  => 'Basé sur %d avis vérifié',
				'en_US'  => 'Based on %d verified review',
				'plural' => array(
					'fr_FR' => 'Basé sur %d avis vérifiés',
					'en_US' => 'Based on %d verified reviews',
				),
			),
			'%d★'                                          => array(
				'fr_FR' => '%d★',
				'en_US' => '%d★',
			),
			'Verified purchase'                            => array(
				'fr_FR' => 'Achat vérifié',
				'en_US' => 'Verified purchase',
			),
			'There are no reviews yet.'                    => array(
				'fr_FR' => "Il n'y a pas encore d'avis.",
				'en_US' => 'There are no reviews yet.',
			),
			'Leave a review'                               => array(
				'fr_FR' => 'Laisser un avis',
				'en_US' => 'Leave a review',
			),
			'Add a review'                                 => array(
				'fr_FR' => 'Ajouter un avis',
				'en_US' => 'Add a review',
			),
			'Be the first to review "%s"'                  => array(
				'fr_FR' => 'Soyez le premier à donner votre avis sur « %s »',
				'en_US' => 'Be the first to review "%s"',
			),
			'Submit review'                                => array(
				'fr_FR' => "Publier l'avis",
				'en_US' => 'Submit review',
			),
			'You must be %1$slogged in%2$s to post a review.' => array(
				'fr_FR' => 'Vous devez être %1$sconnecté%2$s pour publier un avis.',
				'en_US' => 'You must be %1$slogged in%2$s to post a review.',
			),
			'Your rating'                                  => array(
				'fr_FR' => 'Votre note',
				'en_US' => 'Your rating',
			),
			'Rate…'                                        => array(
				'fr_FR' => 'Noter…',
				'en_US' => 'Rate…',
			),
			'Perfect'                                      => array(
				'fr_FR' => 'Parfait',
				'en_US' => 'Perfect',
			),
			'Good'                                         => array(
				'fr_FR' => 'Bien',
				'en_US' => 'Good',
			),
			'Average'                                      => array(
				'fr_FR' => 'Moyen',
				'en_US' => 'Average',
			),
			'Not that bad'                                 => array(
				'fr_FR' => 'Pas si mal',
				'en_US' => 'Not that bad',
			),
			'Very poor'                                    => array(
				'fr_FR' => 'Très mauvais',
				'en_US' => 'Very poor',
			),
			'Your review'                                  => array(
				'fr_FR' => 'Votre avis',
				'en_US' => 'Your review',
			),
			'Suggestions'                                  => array(
				'fr_FR' => 'Suggestions',
				'en_US' => 'Suggestions',
			),
			'Related products'                             => array(
				'fr_FR' => 'Produits similaires',
				'en_US' => 'Related products',
			),
			'Login'                                        => array(
				'fr_FR' => 'Connexion',
				'en_US' => 'Login',
			),
			'Shipping'                                     => array(
				'fr_FR' => 'Livraison',
				'en_US' => 'Shipping',
			),
			'Payment'                                      => array(
				'fr_FR' => 'Paiement',
				'en_US' => 'Payment',
			),
			'Confirmation'                                 => array(
				'fr_FR' => 'Confirmation',
				'en_US' => 'Confirmation',
			),
			'Checkout progress'                            => array(
				'fr_FR' => 'Progression de la commande',
				'en_US' => 'Checkout progress',
			),
			'100% secure payment'                          => array(
				'fr_FR' => 'Paiement 100% sécurisé',
				'en_US' => '100% secure payment',
			),
			'Secure SSL payment'                           => array(
				'fr_FR' => 'Paiement sécurisé SSL',
				'en_US' => 'Secure SSL payment',
			),
			'My cart'                                      => array(
				'fr_FR' => 'Mon panier',
				'en_US' => 'My cart',
			),
			'%d item'                                      => array(
				'fr_FR'  => '%d article',
				'en_US'  => '%d item',
				'plural' => array(
					'fr_FR' => '%d articles',
					'en_US' => '%d items',
				),
			),
			'Remove'                                       => array(
				'fr_FR' => 'Retirer',
				'en_US' => 'Remove',
			),
			'%1$d × %2$s'                                  => array(
				'fr_FR' => '%1$d × %2$s',
				'en_US' => '%1$d × %2$s',
			),
			'Promo code'                                   => array(
				'fr_FR' => 'Code promotionnel',
				'en_US' => 'Promo code',
			),
			'Apply'                                        => array(
				'fr_FR' => 'Appliquer',
				'en_US' => 'Apply',
			),
			'Continue shopping'                            => array(
				'fr_FR' => 'Continuer mes achats',
				'en_US' => 'Continue shopping',
			),
			'Order summary'                                => array(
				'fr_FR' => 'Récapitulatif',
				'en_US' => 'Order summary',
			),
			'Subtotal (%d item)'                           => array(
				'fr_FR'  => 'Sous-total (%d article)',
				'en_US'  => 'Subtotal (%d item)',
				'plural' => array(
					'fr_FR' => 'Sous-total (%d articles)',
					'en_US' => 'Subtotal (%d items)',
				),
			),
			'Total incl. tax'                              => array(
				'fr_FR' => 'Total TTC',
				'en_US' => 'Total incl. tax',
			),
			'SSL 256-bit secure payment'                   => array(
				'fr_FR' => 'Paiement sécurisé SSL 256-bit',
				'en_US' => 'SSL 256-bit secure payment',
			),
			'Proceed to shipping'                          => array(
				'fr_FR' => 'Procéder à la livraison',
				'en_US' => 'Proceed to shipping',
			),
			'Proceed to login'                             => array(
				'fr_FR' => 'Procéder à la connexion',
				'en_US' => 'Proceed to login',
			),
			'Return to shop'                               => array(
				'fr_FR' => 'Retourner à la boutique',
				'en_US' => 'Return to shop',
			),
			'Coupon:'                                      => array(
				'fr_FR' => 'Code promo :',
				'en_US' => 'Coupon:',
			),
			'Coupon code'                                  => array(
				'fr_FR' => 'Code promo',
				'en_US' => 'Coupon code',
			),
			'Apply coupon'                                 => array(
				'fr_FR' => 'Appliquer le code promo',
				'en_US' => 'Apply coupon',
			),
			'Update cart'                                  => array(
				'fr_FR' => 'Mettre à jour le panier',
				'en_US' => 'Update cart',
			),
			'You must be logged in to checkout.'           => array(
				'fr_FR' => 'Vous devez être connecté pour passer commande.',
				'en_US' => 'You must be logged in to checkout.',
			),
			'Checkout'                                     => array(
				'fr_FR' => 'Paiement',
				'en_US' => 'Checkout',
			),
			'Log in to speed up your order'                => array(
				'fr_FR' => 'Connectez-vous pour accélérer votre commande',
				'en_US' => 'Log in to speed up your order',
			),
			'Password'                                     => array(
				'fr_FR' => 'Mot de passe',
				'en_US' => 'Password',
			),
			'Remember me'                                  => array(
				'fr_FR' => 'Se souvenir de moi',
				'en_US' => 'Remember me',
			),
			'Forgot your password?'                        => array(
				'fr_FR' => 'Mot de passe oublié ?',
				'en_US' => 'Forgot your password?',
			),
			'or continue with'                             => array(
				'fr_FR' => 'ou continuer avec',
				'en_US' => 'or continue with',
			),
			'Not available yet'                            => array(
				'fr_FR' => 'Bientôt disponible',
				'en_US' => 'Not available yet',
			),
			"Don't have an account?"                       => array(
				'fr_FR' => 'Pas de compte ?',
				'en_US' => "Don't have an account?",
			),
			'Create an account'                            => array(
				'fr_FR' => 'Créer un compte',
				'en_US' => 'Create an account',
			),
			'Continue as guest'                            => array(
				'fr_FR' => 'Continuer sans compte',
				'en_US' => 'Continue as guest',
			),
			'Back to cart'                                 => array(
				'fr_FR' => 'Retour au panier',
				'en_US' => 'Back to cart',
			),
			'Your order'                                   => array(
				'fr_FR' => 'Votre commande',
				'en_US' => 'Your order',
			),
			'Shipping address'                             => array(
				'fr_FR' => 'Adresse de livraison',
				'en_US' => 'Shipping address',
			),
			'Create an account?'                           => array(
				'fr_FR' => 'Créer un compte ?',
				'en_US' => 'Create an account?',
			),
			'Ship to a different address?'                 => array(
				'fr_FR' => 'Livrer à une adresse différente ?',
				'en_US' => 'Ship to a different address?',
			),
			'Shipping to %s.'                              => array(
				'fr_FR' => 'Livraison vers %s.',
				'en_US' => 'Shipping to %s.',
			),
			'Change address'                               => array(
				'fr_FR' => "Changer d'adresse",
				'en_US' => 'Change address',
			),
			'Shipping options will be updated during checkout.' => array(
				'fr_FR' => 'Les options de livraison seront mises à jour lors du paiement.',
				'en_US' => 'Shipping options will be updated during checkout.',
			),
			'Shipping costs are calculated during checkout.' => array(
				'fr_FR' => 'Les frais de livraison sont calculés lors du paiement.',
				'en_US' => 'Shipping costs are calculated during checkout.',
			),
			'Enter your address to view shipping options.' => array(
				'fr_FR' => 'Saisissez votre adresse pour voir les options de livraison.',
				'en_US' => 'Enter your address to view shipping options.',
			),
			'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.' => array(
				'fr_FR' => "Aucune option de livraison n'est disponible. Merci de vérifier que votre adresse est correcte, ou de nous contacter si vous avez besoin d'aide.",
				'en_US' => 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.',
			),
			'No shipping options were found for %s.'       => array(
				'fr_FR' => "Aucune option de livraison n'a été trouvée pour %s.",
				'en_US' => 'No shipping options were found for %s.',
			),
			'Enter a different address'                    => array(
				'fr_FR' => 'Saisir une adresse différente',
				'en_US' => 'Enter a different address',
			),
			'Subtotal'                                     => array(
				'fr_FR' => 'Sous-total',
				'en_US' => 'Subtotal',
			),
			'Payment methods'                              => array(
				'fr_FR' => 'Moyens de paiement',
				'en_US' => 'Payment methods',
			),
			'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.' => array(
				'fr_FR' => "Désolé, il semble qu'aucun moyen de paiement ne soit disponible. Merci de nous contacter si vous avez besoin d'aide ou souhaitez un arrangement particulier.",
				'en_US' => 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.',
			),
			'Please fill in your details above to see available payment methods.' => array(
				'fr_FR' => 'Merci de renseigner vos informations ci-dessus pour afficher les moyens de paiement disponibles.',
				'en_US' => 'Please fill in your details above to see available payment methods.',
			),
			'This order requires no payment.'              => array(
				'fr_FR' => 'Cette commande ne nécessite aucun paiement.',
				'en_US' => 'This order requires no payment.',
			),
			'Your data is protected by SSL 256-bit encryption.' => array(
				'fr_FR' => 'Vos données sont protégées par un chiffrement SSL 256 bits.',
				'en_US' => 'Your data is protected by SSL 256-bit encryption.',
			),
			'Back'                                         => array(
				'fr_FR' => 'Retour',
				'en_US' => 'Back',
			),
			'If JavaScript is disabled in your browser, click %1$sUpdate Totals%2$s before placing your order — you may otherwise be charged more than the amount shown above.' => array(
				'fr_FR' => 'Si JavaScript est désactivé dans votre navigateur, cliquez sur %1$sMettre à jour les totaux%2$s avant de passer commande — le montant facturé pourrait sinon être supérieur à celui affiché ci-dessus.',
				'en_US' => 'If JavaScript is disabled in your browser, click %1$sUpdate Totals%2$s before placing your order — you may otherwise be charged more than the amount shown above.',
			),
			'Update totals'                                => array(
				'fr_FR' => 'Mettre à jour les totaux',
				'en_US' => 'Update totals',
			),
			'Proceed to payment'                           => array(
				'fr_FR' => 'Procéder au paiement',
				'en_US' => 'Proceed to payment',
			),
			'🔒 Pay %s'                                     => array(
				'fr_FR' => '🔒 Payer %s',
				'en_US' => '🔒 Pay %s',
			),
			'By placing your order, you accept our %1$sT&Cs%2$s and our %3$sprivacy policy%4$s.' => array(
				'fr_FR' => 'En passant commande, vous acceptez nos %1$sC.G.V.%2$s et notre %3$spolitique de confidentialité%4$s.',
				'en_US' => 'By placing your order, you accept our %1$sT&Cs%2$s and our %3$sprivacy policy%4$s.',
			),
			'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.' => array(
				'fr_FR' => "Malheureusement, votre commande ne peut pas être traitée : la banque ou le commerçant d'origine a refusé la transaction. Merci de réessayer votre achat.",
				'en_US' => 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.',
			),
			'Pay'                                          => array(
				'fr_FR' => 'Payer',
				'en_US' => 'Pay',
			),
			'Order confirmed!'                             => array(
				'fr_FR' => 'Commande confirmée !',
				'en_US' => 'Order confirmed!',
			),
			'Thank you for your purchase, %s! Your order has been placed and is being prepared.' => array(
				'fr_FR' => 'Merci pour votre achat, %s ! Votre commande a bien été enregistrée et est en cours de préparation.',
				'en_US' => 'Thank you for your purchase, %s! Your order has been placed and is being prepared.',
			),
			'valued customer'                              => array(
				'fr_FR' => 'cher client',
				'en_US' => 'valued customer',
			),
			'Order number'                                 => array(
				'fr_FR' => 'Numéro de commande',
				'en_US' => 'Order number',
			),
			'Estimated delivery'                           => array(
				'fr_FR' => 'Livraison estimée',
				'en_US' => 'Estimated delivery',
			),
			'Total paid'                                   => array(
				'fr_FR' => 'Total payé',
				'en_US' => 'Total paid',
			),
			'Items ordered'                                => array(
				'fr_FR' => 'Articles commandés',
				'en_US' => 'Items ordered',
			),
			'A confirmation email has been sent to %s'     => array(
				'fr_FR' => 'Un email de confirmation a été envoyé à %s',
				'en_US' => 'A confirmation email has been sent to %s',
			),
			'Track my order'                               => array(
				'fr_FR' => 'Suivre ma commande',
				'en_US' => 'Track my order',
			),
			'We could not find this order.'                => array(
				'fr_FR' => "Nous n'avons pas trouvé cette commande.",
				'en_US' => 'We could not find this order.',
			),
			'Dashboard'                                    => array(
				'fr_FR' => 'Tableau de bord',
				'en_US' => 'Dashboard',
			),
			'Orders'                                       => array(
				'fr_FR' => 'Mes commandes',
				'en_US' => 'Orders',
			),
			'Wishlist'                                     => array(
				'fr_FR' => 'Liste de souhaits',
				'en_US' => 'Wishlist',
			),
			'Addresses'                                    => array(
				'fr_FR' => 'Mes adresses',
				'en_US' => 'Addresses',
			),
			'Downloads'                                    => array(
				'fr_FR' => 'Téléchargements',
				'en_US' => 'Downloads',
			),
			'Support request'                              => array(
				'fr_FR' => 'Demande S.A.V.',
				'en_US' => 'Support request',
			),
			'Settings'                                     => array(
				'fr_FR' => 'Paramètres',
				'en_US' => 'Settings',
			),
			'Logout'                                       => array(
				'fr_FR' => 'Se déconnecter',
				'en_US' => 'Logout',
			),
			'My account navigation'                        => array(
				'fr_FR' => 'Navigation du compte',
				'en_US' => 'My account navigation',
			),
			'Hello, %s! 👋'                                 => array(
				'fr_FR' => 'Bonjour, %s ! 👋',
				'en_US' => 'Hello, %s! 👋',
			),
			'Welcome to your personal space'               => array(
				'fr_FR' => 'Bienvenue dans votre espace personnel',
				'en_US' => 'Welcome to your personal space',
			),
			'In progress'                                  => array(
				'fr_FR' => 'En cours',
				'en_US' => 'In progress',
			),
			'Total'                                        => array(
				'fr_FR' => 'Total',
				'en_US' => 'Total',
			),
			'orders'                                       => array(
				'fr_FR' => 'commandes',
				'en_US' => 'orders',
			),
			'orders placed'                                => array(
				'fr_FR' => 'commandes passées',
				'en_US' => 'orders placed',
			),
			'saved items'                                  => array(
				'fr_FR' => 'articles sauvegardés',
				'en_US' => 'saved items',
			),
			'Support'                                      => array(
				'fr_FR' => 'S.A.V.',
				'en_US' => 'Support',
			),
			'active requests'                              => array(
				'fr_FR' => 'demandes actives',
				'en_US' => 'active requests',
			),
			'Recent orders'                                => array(
				'fr_FR' => 'Commandes récentes',
				'en_US' => 'Recent orders',
			),
			'You have not placed any order yet.'           => array(
				'fr_FR' => "Vous n'avez pas encore passé de commande.",
				'en_US' => 'You have not placed any order yet.',
			),
			'Order'                                        => array(
				'fr_FR' => 'Commande',
				'en_US' => 'Order',
			),
			'Date'                                         => array(
				'fr_FR' => 'Date',
				'en_US' => 'Date',
			),
			'Items'                                        => array(
				'fr_FR' => 'Articles',
				'en_US' => 'Items',
			),
			'Status'                                       => array(
				'fr_FR' => 'Statut',
				'en_US' => 'Status',
			),
			'Detail'                                       => array(
				'fr_FR' => 'Détail',
				'en_US' => 'Detail',
			),
			'Reorder'                                      => array(
				'fr_FR' => 'Recommander',
				'en_US' => 'Reorder',
			),
			'The items from your past order have been added to your cart.' => array(
				'fr_FR' => 'Les articles de votre commande précédente ont été ajoutés à votre panier.',
				'en_US' => 'The items from your past order have been added to your cart.',
			),
			'Awaiting payment'                             => array(
				'fr_FR' => 'En attente de paiement',
				'en_US' => 'Awaiting payment',
			),
			'On hold'                                      => array(
				'fr_FR' => 'En attente',
				'en_US' => 'On hold',
			),
			'In transit'                                   => array(
				'fr_FR' => 'En transit',
				'en_US' => 'In transit',
			),
			'Delivered'                                    => array(
				'fr_FR' => 'Livré',
				'en_US' => 'Delivered',
			),
			'Cancelled'                                    => array(
				'fr_FR' => 'Annulé',
				'en_US' => 'Cancelled',
			),
			'Refunded'                                     => array(
				'fr_FR' => 'Remboursé',
				'en_US' => 'Refunded',
			),
			'Failed'                                       => array(
				'fr_FR' => 'Échoué',
				'en_US' => 'Failed',
			),
			'All'                                          => array(
				'fr_FR' => 'Toutes',
				'en_US' => 'All',
			),
			'Returns'                                      => array(
				'fr_FR' => 'Retours',
				'en_US' => 'Returns',
			),
			'View detail'                                  => array(
				'fr_FR' => 'Voir le détail',
				'en_US' => 'View detail',
			),
			'+%d item'                                     => array(
				'fr_FR' => '+%d article',
				'en_US' => '+%d item',
			),
			'Previous'                                     => array(
				'fr_FR' => 'Précédent',
				'en_US' => 'Previous',
			),
			'Next'                                         => array(
				'fr_FR' => 'Suivant',
				'en_US' => 'Next',
			),
			'No order matches this selection.'             => array(
				'fr_FR' => 'Aucune commande ne correspond à cette sélection.',
				'en_US' => 'No order matches this selection.',
			),
			'Orders pagination'                            => array(
				'fr_FR' => 'Pagination des commandes',
				'en_US' => 'Orders pagination',
			),
			'← Back to orders'                             => array(
				'fr_FR' => '← Retour aux commandes',
				'en_US' => '← Back to orders',
			),
			'Order #%s'                                    => array(
				'fr_FR' => 'Commande #%s',
				'en_US' => 'Order #%s',
			),
			'Order updates'                                => array(
				'fr_FR' => 'Suivi de la commande',
				'en_US' => 'Order updates',
			),
			'l jS \o\f F Y, h:ia'                          => array(
				'fr_FR' => 'l j F Y à H\hi',
				'en_US' => 'l jS \o\f F Y, h:ia',
			),
			'This product could not be found.'             => array(
				'fr_FR' => "Ce produit n'a pas été trouvé.",
				'en_US' => 'This product could not be found.',
			),
			'You have not saved any product yet.'          => array(
				'fr_FR' => "Vous n'avez encore sauvegardé aucun produit.",
				'en_US' => 'You have not saved any product yet.',
			),
			'My addresses'                                 => array(
				'fr_FR' => 'Mes adresses',
				'en_US' => 'My addresses',
			),
			'Billing address'                              => array(
				'fr_FR' => 'Adresse de facturation',
				'en_US' => 'Billing address',
			),
			'Editing'                                      => array(
				'fr_FR' => 'En cours de modification',
				'en_US' => 'Editing',
			),
			'No address saved yet.'                        => array(
				'fr_FR' => "Aucune adresse enregistrée pour l'instant.",
				'en_US' => 'No address saved yet.',
			),
			'Edit'                                         => array(
				'fr_FR' => 'Modifier',
				'en_US' => 'Edit',
			),
			'Save address'                                 => array(
				'fr_FR' => "Enregistrer l'adresse",
				'en_US' => 'Save address',
			),
			'%d download remaining'                        => array(
				'fr_FR'  => '%d téléchargement restant',
				'en_US'  => '%d download remaining',
				'plural' => array(
					'fr_FR' => '%d téléchargements restants',
					'en_US' => '%d downloads remaining',
				),
			),
			'Unlimited'                                    => array(
				'fr_FR' => 'Illimité',
				'en_US' => 'Unlimited',
			),
			'Download'                                     => array(
				'fr_FR' => 'Télécharger',
				'en_US' => 'Download',
			),
			'No downloads available yet.'                  => array(
				'fr_FR' => 'Aucun téléchargement disponible pour le moment.',
				'en_US' => 'No downloads available yet.',
			),
			'Browse products'                              => array(
				'fr_FR' => 'Parcourir les produits',
				'en_US' => 'Browse products',
			),
			'Defective product'                            => array(
				'fr_FR' => 'Produit défectueux',
				'en_US' => 'Defective product',
			),
			'Delivery issue'                               => array(
				'fr_FR' => 'Problème de livraison',
				'en_US' => 'Delivery issue',
			),
			'Return / Exchange'                            => array(
				'fr_FR' => 'Retour / Échange',
				'en_US' => 'Return / Exchange',
			),
			'Other'                                        => array(
				'fr_FR' => 'Autre',
				'en_US' => 'Other',
			),
			'Your request has been sent. Our team will get back to you shortly.' => array(
				'fr_FR' => 'Votre demande a été envoyée. Notre équipe vous répondra rapidement.',
				'en_US' => 'Your request has been sent. Our team will get back to you shortly.',
			),
			'Please fill in every required field.'         => array(
				'fr_FR' => 'Merci de remplir tous les champs obligatoires.',
				'en_US' => 'Please fill in every required field.',
			),
			'New request'                                  => array(
				'fr_FR' => 'Nouvelle demande',
				'en_US' => 'New request',
			),
			'Related order'                                => array(
				'fr_FR' => 'Commande concernée',
				'en_US' => 'Related order',
			),
			'Select an order'                              => array(
				'fr_FR' => 'Sélectionnez une commande',
				'en_US' => 'Select an order',
			),
			'Request type'                                 => array(
				'fr_FR' => 'Type de demande',
				'en_US' => 'Request type',
			),
			'Select a type'                                => array(
				'fr_FR' => 'Sélectionnez un type',
				'en_US' => 'Select a type',
			),
			'Subject'                                      => array(
				'fr_FR' => 'Sujet',
				'en_US' => 'Subject',
			),
			'Briefly describe your issue'                  => array(
				'fr_FR' => 'Décrivez brièvement votre problème',
				'en_US' => 'Briefly describe your issue',
			),
			'Detailed description'                         => array(
				'fr_FR' => 'Description détaillée',
				'en_US' => 'Detailed description',
			),
			'Explain your situation in detail…'            => array(
				'fr_FR' => 'Expliquez votre situation en détail…',
				'en_US' => 'Explain your situation in detail…',
			),
			'Photos / documents'                           => array(
				'fr_FR' => 'Photos / documents',
				'en_US' => 'Photos / documents',
			),
			'optional'                                     => array(
				'fr_FR' => 'facultatif',
				'en_US' => 'optional',
			),
			'PNG, JPG, PDF — max. 10 MB'                   => array(
				'fr_FR' => 'PNG, JPG, PDF — max. 10 Mo',
				'en_US' => 'PNG, JPG, PDF — max. 10 MB',
			),
			'Send request'                                 => array(
				'fr_FR' => 'Envoyer la demande',
				'en_US' => 'Send request',
			),
			'You have no active support request at the moment.' => array(
				'fr_FR' => "Vous n'avez aucune demande S.A.V. active pour le moment.",
				'en_US' => 'You have no active support request at the moment.',
			),
			'Your requests'                                => array(
				'fr_FR' => 'Vos demandes',
				'en_US' => 'Your requests',
			),
			'Open'                                         => array(
				'fr_FR' => 'Ouverte',
				'en_US' => 'Open',
			),
			'Closed'                                       => array(
				'fr_FR' => 'Fermée',
				'en_US' => 'Closed',
			),
			'None'                                         => array(
				'fr_FR' => 'Aucune',
				'en_US' => 'None',
			),
			'New support request: %s'                      => array(
				'fr_FR' => 'Nouvelle demande S.A.V. : %s',
				'en_US' => 'New support request: %s',
			),
			"New support request from %1\$s (%2\$s)\nType: %3\$s\nOrder: %4\$s\n\n%5\$s" => array(
				'fr_FR' => "Nouvelle demande S.A.V. de %1\$s (%2\$s)\nType : %3\$s\nCommande : %4\$s\n\n%5\$s",
				'en_US' => "New support request from %1\$s (%2\$s)\nType: %3\$s\nOrder: %4\$s\n\n%5\$s",
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
