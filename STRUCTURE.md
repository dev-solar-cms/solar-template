# STRUCTURE.md

Organisation du dépôt et sa place dans l'écosystème **dev-solar-cms**.

## Ce dépôt

`solar-template` est un dépôt git **indépendant**, public, sous l'organisation GitHub `dev-solar-cms`. Il a été généré par `make new-theme name=solar-template` depuis le repo parent `wordpress-dev-env`, qui gère l'environnement Docker WordPress local.

Ce dépôt n'a aucun lien d'historique git avec le repo parent : il vit dans `wp-content/themes/solar-template/` de ce dernier, mais possède son propre `.git`, indépendant (pas de submodule). Ouvrir la racine du projet parent dans un éditeur affichera donc plusieurs panneaux Source Control distincts — c'est un comportement attendu.

## Fichiers

```
solar-template/
├── style.css        # En-tête du thème (nom, version, text-domain) — pas de styles réels encore
├── functions.php     # Support title-tag/post-thumbnails, menus, hooks, aides header (réseaux, compte, panier, nav)
├── header.php         # En-tête sticky global (barre supérieure + navigation principale + overlay de recherche)
├── footer.php         # Pied de page global (colonnes, newsletter, copyright, icônes de paiement)
├── searchform.php     # Formulaire de recherche natif personnalisé, chargé par get_search_form()
├── front-page.php     # Page d'accueil, assemblée section par section (template-parts/front-page/*)
├── archive-product.php # Catalogue produits (boutique + archives de catégorie/étiquette) : fil d'Ariane, titre, barre de filtres et résultats
├── single-product.php  # Fiche produit complète : fil d'Ariane, galerie, panneau, onglets, produits similaires (template-parts/single-product/*)
├── checkout.php        # Gabarit du tunnel de vente (panier/connexion/livraison/paiement/confirmation), servi via template_include
├── header-checkout.php # En-tête minimal du tunnel de vente (marque centrée + mention paiement sécurisé, pas de nav)
├── footer-checkout.php # Pied de page minimal du tunnel de vente (liens légaux, copyright, mention SSL)
├── index.php          # Gabarit de repli ; appelle get_header()/get_footer()
├── screenshot.png     # 1200x900, requis par WordPress pour l'aperçu du thème
├── composer.json      # Dépendances PHP + autoload PSR-4 (Solar_Template\)
├── phpcs.xml.dist      # Norme de code PHP (WordPress-Extra), utilisée par `composer lint`/`format`
├── .env.example        # Réglages non sensibles du thème (version, mode assets, TTL cache) — versionné
├── .env                 # Copie locale de .env.example, ignorée par git
├── package.json       # Pipeline de build des assets + tests JS (Vite, Vitest, Prettier)
├── inc/                # Classes PHP du thème, autoloadées en PSR-4 sous `Solar_Template\`
│   ├── Contracts/       # Interfaces consommées par le code métier (jamais une lib tierce directement)
│   ├── Support/         # Implémentations par défaut des interfaces génériques
│   ├── I18n/            # Système de traduction multilingue (catalogue + compilation .mo)
│   ├── Database/        # Schéma et installation des tables `wp_solar_template_*`
│   ├── Newsletter/      # Stockage des inscrits à la newsletter (SubscriberRepository)
│   ├── Catalog/         # Options/filtres/pagination/contrôleurs du catalogue produits
│   ├── Product/         # Logique de la fiche produit (galerie, panneau, variations, onglets, produits similaires)
│   └── Checkout/        # Tunnel de vente : routage (template_include), état de l'indicateur d'étapes, vue du panier
├── template-parts/      # Fragments de gabarit réutilisables (`get_template_part()`)
│   ├── product-card.php # Carte produit (image, badge, wishlist, overlay panier, prix, swatches)
│   ├── blog-card.php    # Carte article de blog (image 16:10, badge catégorie, meta, titre, extrait, auteur)
│   ├── cart-badge.php   # Badge du nombre d'articles au panier, utilisé par l'en-tête
│   ├── mega-menu.php    # Panneau du mega menu « Collections », contenu factice pour l'instant
│   ├── catalog-filters.php # Barre de filtres du catalogue (Catégorie/Prix/Couleur/Taille/Note) + tri, formulaire natif progressivement amélioré en AJAX
│   ├── catalog-results.php # Compteur/grille/chargement progressif du catalogue, partagé entre le chargement de page et le ré-affichage AJAX
│   ├── catalog-cards.php # Boucle des cartes produit seule (sans grille), réutilisée par le mode « append » du chargement progressif
│   ├── catalog-load-more.php # Statut « Affichage de N sur M », barre de progression et bouton « Charger plus » du catalogue
│   ├── catalog-active-filters.php # Rangée de chips des filtres actifs (retrait individuel + « Effacer tout »)
│   ├── single-product/  # Fragments de la fiche produit, un par section
│   │   ├── gallery.php  # Image principale 4:5 + bande de miniatures (changement au clic, sans rechargement)
│   │   ├── panel.php    # Colonne droite sticky : badges, titre, notation, statut de stock, description courte, réassurance, accordéon
│   │   ├── tabs.php     # Onglets Description/Avis/Caractéristiques (motif ARIA tabs, avis WooCommerce natifs)
│   │   └── related-products.php # Grille masonry des produits similaires WooCommerce réels (même carte produit)
│   ├── checkout/        # Fragments du tunnel de vente
│   │   └── step-indicator.php # Indicateur des 5 étapes (cercles/labels/ligne de progression), partagé par tout le tunnel
│   └── front-page/      # Sections de la page d'accueil, une par fichier
│       ├── hero.php     # Hero plein écran (accroche, titre 3 lignes, CTA, trust badges, carte flottante)
│       ├── featured-products.php # Grille masonry des produits WooCommerce marqués « en vedette »
│       ├── categories.php # Grille asymétrique des catégories produit WooCommerce
│       ├── brand-story.php # Section « notre histoire » (image, carte stat flottante, texte, stats, CTA)
│       ├── testimonials.php # Grille de 3 témoignages, carte du milieu en style inversé
│       ├── blog-preview.php # Grille des 3 derniers articles de blog réels
│       └── newsletter.php # Formulaire d'inscription newsletter (traité en AJAX)
├── woocommerce/         # Surcharges de gabarits natifs WooCommerce (voir « Tunnel de vente » ci-dessous)
│   └── cart/            # Page panier : articles, code promo, récapitulatif
│       ├── cart.php     # Liste des articles réels du panier + formulaire de code promo
│       ├── cart-totals.php # Récapitulatif sticky (sous-total, remise, livraison, total, bouton de paiement)
│       ├── cart-item-data.php # Métadonnées d'un article (variante/gravure) sur une seule ligne, plutôt que la liste de définitions par défaut
│       ├── cart-empty.php # État panier vide, stylé
│       └── proceed-to-checkout-button.php # Bouton nommant explicitement la prochaine étape (connexion ou livraison)
├── languages/           # Fichiers `.mo` compilés (générés, ignorés par git sauf `.gitkeep`)
├── vite.config.js       # Configuration du pipeline de build des assets
├── .prettierrc.json     # Norme de formatage JS/SCSS, utilisée par `npm run format`
├── assets/
│   ├── scss/main.scss    # Point d'entrée SCSS (sources non compilées)
│   ├── scss/_tokens.scss # Design tokens (couleurs, typographie, espacements, géométrie) + mixins d'échelle typo
│   ├── scss/_buttons.scss # Composant boutons (.btn + variantes primaire/dark/outline)
│   ├── scss/_badges.scss  # Composant badges (.badge + variantes new/exclusive/sale/outline/discount)
│   ├── scss/_pills.scss   # Composant pills/chips de filtre (.pill + état actif, bouton de suppression)
│   ├── scss/_product-card.scss # Composant carte produit (media, badge, wishlist, overlay panier, prix, swatches), hauteurs de texte fixes pour garder les cartes alignées entre elles quelle que soit la longueur du nom du produit
│   ├── scss/_blog-card.scss # Composant carte article de blog (media 16:10, badge, meta, titre, extrait, auteur)
│   ├── scss/_header.scss # En-tête sticky (barre supérieure, actions, navigation, mega menu)
│   ├── scss/_footer.scss # Pied de page (colonnes, newsletter, copyright, icônes de paiement)
│   ├── scss/_hero.scss   # Section hero de la page d'accueil (titre, CTA, trust badges, carte flottante)
│   ├── scss/_featured-products.scss # Grille masonry des produits vedettes de la page d'accueil
│   ├── scss/_home-categories.scss # Grille asymétrique des catégories produit de la page d'accueil
│   ├── scss/_brand-story.scss # Section « notre histoire » de la page d'accueil
│   ├── scss/_testimonials.scss # Grille de témoignages de la page d'accueil
│   ├── scss/_blog-preview.scss # Grille de l'aperçu du blog de la page d'accueil
│   ├── scss/_home-newsletter.scss # Section newsletter de la page d'accueil
│   ├── scss/_catalog.scss # Catalogue produits (fil d'Ariane, titre/compteur, grille masonry, pagination)
│   ├── scss/_product-page.scss # Fiche produit complète (fil d'Ariane, galerie, panneau, onglets, produits similaires)
│   ├── scss/_checkout.scss # Tunnel de vente : en-tête/pied de page minimaux, indicateur d'étapes, page panier, récapitulatif
│   ├── js/main.js        # Point d'entrée JS (importe le SCSS, initialise les modules de comportement)
│   ├── js/header.js      # Comportement de l'en-tête (mega menu, bascule de recherche)
│   ├── js/newsletter.js  # Soumission AJAX des formulaires newsletter (`.js-newsletter-form`)
│   ├── js/catalog.js     # Barre de filtres du catalogue : panneaux, état actif, soumission AJAX
│   ├── js/product.js     # Fiche produit : changement d'image de la galerie au clic sur une miniature
	├── js/checkout.js    # Panier : stepper de quantité "−"/"+" à côté du champ natif WooCommerce
│   └── dist/             # Sortie compilée (générée par `npm run build`/`dev`, ignorée par git)
├── phpunit.xml.dist     # Configuration PHPUnit, utilisée par `composer test`
├── tests/php/           # Tests unitaires PHP (bootstrap minimal, pas une installation WordPress complète)
├── tests/js/            # Tests unitaires JS (Vitest, environnement jsdom), utilisés par `npm run test`
├── .github/workflows/ci.yml  # CI : lint + tests PHP/JS à chaque push, release GitHub automatique sur `main`
├── doc/                # Documentation du projet (site statique HTML/JS, FR + EN)
│   ├── en/, fr/         # Pages par langue (index.html, infrastructure.html, ...)
│   └── assets/          # CSS/JS partagés par la documentation
├── LICENSE             # GPL-2.0-or-later
├── README.md
├── RELEASE.md
└── .gitignore
```

### Interfaces PHP (`inc/Contracts/`)

- `EnvironmentLoaderInterface` — lecture de la configuration `.env` du thème (implémentation par
  défaut : `Support\DotenvEnvironmentLoader`, basée sur `vlucas/phpdotenv`).
- `CacheInterface` — cache court terme (implémentation par défaut : `Support\TransientCache`,
  basée sur l'API des transients WordPress, aucune dépendance externe).
- `TranslatorInterface` — gestion des langues et du catalogue de traductions (implémentation par
  défaut : `I18n\DatabaseTranslator`, basée sur les tables `wp_solar_template_languages`/
  `wp_solar_template_translations`).
- `Support\WooCommerceStatus::is_active()` — détection centralisée de WooCommerce (`class_exists('WooCommerce')`),
  utilisée pour dégrader gracieusement (notice d'admin + support WooCommerce conditionnel) plutôt
  que de fataliser en son absence.
- `MoCompilerInterface` — compilation d'un catalogue en fichier `.mo` (implémentation par défaut :
  `I18n\GettextMoCompiler`, basée sur `gettext/gettext`).

### Système de traduction (`inc/I18n/`)

- Les gabarits du thème continuent d'appeler `__()`/`_e()`/`_n()` avec le text-domain
  `solar-template` : le catalogue en base ne fait que fournir une source éditable, compilée en
  `.mo` standards que WordPress charge lui-même (`load_theme_textdomain()`, câblé dans
  `functions.php`).
- `LanguageCatalog` liste les langues qu'un futur écran d'administration pourra proposer
  d'ajouter (avec leur règle de pluriel), indépendamment des langues réellement installées.
- Important : les `.mo` du dossier `languages/` propre au thème doivent être nommés `{locale}.mo`
  (sans préfixe de domaine) — c'est ce que produit `DatabaseTranslator::compile()`.

### Tables et activation (`inc/Database/Installer.php`)

- `wp_solar_template_languages` / `wp_solar_template_translations` : voir ci-dessus.
- `wp_solar_template_settings` : réglages génériques clé/valeur, alimentés au fil des futurs écrans
  d'administration (onglets) ; valeurs minimales par défaut : version du thème, couleur primaire
  (`#c9a227`), couleur secondaire (`#0b0b0c`), logo/favicon vides.
- `Installer::install()` est appelée au hook `after_switch_theme` (câblé dans `functions.php`) :
  crée les trois tables (`dbDelta`, idempotent), seed les valeurs par défaut si absentes, puis
  compile le catalogue de traduction existant en `.mo`.

### En-tête du thème (`header.php`, `assets/scss/_header.scss`)

- Barre supérieure (réseaux sociaux, marque du site, icônes recherche/compte/panier) et barre de
  navigation principale sticky, conformes à la maquette.
- La navigation principale utilise `wp_nav_menu()` sur l'emplacement `primary` (assignable depuis
  Apparence > Menus) ; tant qu'aucun menu n'y est assigné, `solar_template_primary_nav_fallback()`
  affiche des éléments par défaut (Boutique/Nouveautés/Collections/Promotions/Blog/Contact)
  pointant vers l'équivalent le plus proche déjà disponible (page boutique/panier/compte
  WooCommerce, page des articles, page « contact » si elle existe) plutôt que vers des vues
  dédiées qui restent à construire (étapes suivantes de la feuille de route).
- Les icônes compte/panier pointent vers les pages WooCommerce réelles quand WooCommerce est actif
  (dégradation gracieuse vers la connexion WordPress / l'accueil sinon).
- Le badge du panier (`template-parts/cart-badge.php`) est toujours présent dans le DOM (masqué en
  CSS si le compte est à 0) pour pouvoir être ciblé par un futur rafraîchissement AJAX
  (`woocommerce_add_to_cart_fragments`), sans nouvelle logique JS à cette étape.
- Les URLs des réseaux sociaux sont un filtre (`solar_template_social_links`), pas encore une
  option d'administration — celle-ci arrive avec un futur écran d'administration.
- Le mega menu de l'item « Collections » (`template-parts/mega-menu.php`,
  `assets/js/header.js`) s'ouvre/ferme au survol, au focus clavier et se ferme à l'échappement ou
  au clic extérieur. Son contenu (colonnes de liens) est du texte de remplacement, exposé via le
  filtre `solar_template_mega_menu_columns` — le branchement aux vraies catégories WooCommerce est
  hors périmètre de cette étape. Deux filtres (`nav_menu_css_class`/`nav_menu_link_attributes`)
  garantissent qu'un menu réel assigné à l'emplacement `primary` reçoit les mêmes classes que la
  navigation de secours, pour un rendu/comportement identique dans les deux cas.
- L'icône de recherche ouvre un overlay plein écran (`#site-search`) qui enveloppe
  `searchform.php` — un formulaire de recherche WordPress natif (`?s=`), sans aucun branchement
  spécifique à WooCommerce : WordPress inclut déjà tous les types de contenu publics et
  interrogeables (dont les produits, une fois WooCommerce actif) dans les résultats d'une
  recherche native, sans réglage supplémentaire. Ouverture/fermeture (bouton, Échap, clic
  extérieur) gérées par `initSearchOverlay()` dans `assets/js/header.js`, avec déplacement du
  focus vers le champ à l'ouverture et retour au bouton déclencheur à la fermeture.
- Le badge du panier se met à jour dynamiquement, sans rechargement de page, via le mécanisme de
  fragments AJAX natif de WooCommerce : `solar_template_cart_fragments()` (filtre
  `woocommerce_add_to_cart_fragments`) renvoie le même balisage que
  `template-parts/cart-badge.php`, ciblé par le sélecteur `span.site-header__cart-count` déjà
  présent dans le DOM depuis l'en-tête (voir 02.01). Aucun JS propre au thème n'est nécessaire
  pour le rafraîchissement lui-même : le script `wc-cart-fragments` de WooCommerce le fait déjà —
  mais ce script n'est auto-chargé par WooCommerce que par son propre widget « Panier », que le
  thème n'utilise pas ; il est donc mis en file explicitement
  (`solar_template_enqueue_cart_fragments()`) pour que le mécanisme fonctionne avec le badge
  personnalisé de l'en-tête.

### Catalogue produits (`inc/Catalog/`)

- La logique du catalogue (grille, barre de filtres, chips actifs, tri, chargement progressif —
  `archive-product.php` et `template-parts/catalog-*.php`) vit dans quatre classes, suivant la même
  convention MVC que le reste du thème (`inc/Support/WooCommerceStatus`, `inc/Database/Installer`) :
  méthodes toutes statiques, aucune n'ayant de dépendance à injecter.
  - `CatalogOptions` — données descriptives en lecture seule, indépendantes de la sélection en
    cours (colonnes de la grille, produits par page, slugs d'attribut Couleur/Taille, listes
    d'options catégorie/attribut/note/tri, bornes de prix, position de la barre de filtres,
    libellé du compteur de résultats).
  - `CatalogFilters` — validation d'une sélection de filtres/tri brute, lecture depuis l'URL de
    requête, construction des URLs de filtre/suppression/effacement et des chips actifs,
    conversion en arguments `WP_Query`.
  - `CatalogPagination` — calcule l'état « charger plus » (progression, page suivante) à partir
    d'une `WP_Query` déjà exécutée.
  - `CatalogController` — les « contrôleurs » au sens de l'architecture MVC du projet : le handler
    `pre_get_posts`, le handler AJAX de l'action `solar_template_catalog_filter`, et l'enqueue du
    script de la barre de filtres. Ne contient aucune logique métier propre — délègue aux trois
    classes ci-dessus et rend les vues `template-parts/catalog-*.php` existantes, inchangées.
  - `functions.php` ne garde que de fins wrappers procéduraux (appelés par nom depuis un gabarit,
    ou enregistrés comme callback de hook), chacun repliant sur la valeur par défaut documentée de
    la fonction d'origine si le loader Composer n'est pas disponible — réorganisation interne sans
    changement de comportement.

### Fiche produit (`single-product.php`, `inc/Product/`, `template-parts/single-product/`)

**Complète** — construite section par section, même convention que `front-page.php`.

- `Solar_Template\Product\ProductGallery::images()` lit l'image mise en avant puis la galerie
  WooCommerce du produit (`WC_Product::get_image_id()`/`get_gallery_image_ids()`), dédoublonnées,
  et renvoie une liste plate `{id, full, alt}` — `single-product.php` calcule cette liste (avec
  repli sur l'image de remplacement WooCommerce si le produit n'a aucune image) et la transmet à
  `template-parts/single-product/gallery.php`, qui ne fait que l'afficher (même convention que
  `Catalog\ProductCardMapper`/`template-parts/product-card.php`).
- La bande de miniatures ne s'affiche que si le produit a plus d'une image ; cliquer une miniature
  change l'image principale sans rechargement (`assets/js/product.js`,
  `initProductGallery()`) — aucune dépendance à la galerie zoom/lightbox de la maquette, dont le
  comportement n'est pas spécifié au-delà de la maquette elle-même (même choix qu'`archive-
  product.php` pour la bascule grille/liste).
- Le panneau produit (colonne droite, sticky `top:120px`) affiche des badges calculés depuis de
  vraies données WooCommerce (`Product\ProductBadges::for_product()` : « Nouveau » si le produit a
  été publié il y a moins de `solar_template_product_new_badge_days` jours — 14 par défaut,
  filtrable —, « Solar Premium » si `WC_Product::is_featured()`), le titre, la notation (étoiles +
  moyenne + lien vers les avis, `Product\ProductPanel::rating_summary()`), le statut de stock
  (`Product\ProductStock::for_product()`, indépendant des chaînes du cœur WooCommerce — traduit via
  le catalogue propre au thème), la description courte du produit, un bloc de réassurance et un
  accordéon (livraison/guide des tailles/entretien) — ces deux derniers en contenu éditorial
  filtrable (`Product\ProductPanel::trust_badges()`/`accordion_sections()`), même convention que
  les aides de la page d'accueil, prêt pour le futur onglet d'administration « Produits ».
- Pour un produit variable, le panneau affiche des sélecteurs Couleur/Taille
  (`Product\ProductVariations::attribute_groups()`, réutilisant les mêmes slugs d'attribut
  filtrables que la barre de filtres du catalogue — `Catalog\CatalogOptions::color_attribute_slug()`/
  `size_attribute_slug()`, configurés une seule fois pour tout le thème) : cercles de couleur
  (`Product\ColorSwatch::hex_for_term()`, une meta de terme optionnelle puis une table de
  correspondance nom/slug → hex filtrable, en attendant un futur écran d'administration) et
  boutons de taille, chacun grisé/désactivé s'il n'existe aucune variation en stock pour cette
  valeur, toutes autres dimensions confondues (même traitement statique que la maquette pour sa
  taille XXL). Le prix affiché et le bouton « Ajouter au panier » restent des éléments séparés
  (plutôt que le texte de bouton combiné « Ajouter · {prix} » de la maquette), pour n'avoir qu'un
  seul emplacement de prix à recalculer.
- `assets/js/product.js` (`initProductVariations()`) résout la sélection Couleur/Taille courante
  contre la vraie liste de variations WooCommerce localisée par
  `Product\ProductController::enqueue_script()` (`window.solarTemplateProduct`), et met à jour en
  conséquence : le prix affiché (reformaté selon les réglages réels d'affichage des prix de la
  boutique — décimales, séparateurs, position du symbole —, pas une valeur codée en dur), le champ
  caché `variation_id` du formulaire d'ajout au panier, et l'état désactivé du bouton. Le
  formulaire lui-même reste un `<form>` natif WooCommerce standard (champs `attribute_*` cachés
  synchronisés par ce script plutôt que des `<select>` visibles) — l'ajout au panier fonctionne
  donc par un chargement de page classique, sans AJAX, vérifié de bout en bout en simulant la
  requête réelle que soumettrait le formulaire.
- Option de personnalisation (gravure) : native, sans ACF ni WooCommerce Product Add-ons
  (DECISIONS.md §2). `Product\ProductEngraving` lit trois metas produit natives
  (`_solar_template_engraving_enabled`/`_price`/`_max_length`, défauts 25€/20 caractères) ;
  `Product\EngravingAdminFields` ajoute un toggle + deux champs dans l'onglet « Général » de
  l'écran d'édition produit WooCommerce (`woocommerce_wp_checkbox()`/`woocommerce_wp_text_input()`
  natifs, aucun framework de meta box tiers) et les enregistre au hook
  `woocommerce_process_product_meta`. Quand activée, le panneau affiche un interrupteur + un champ
  texte (dans le formulaire d'ajout au panier, pour que leur valeur soit soumise avec) ; activer
  l'interrupteur ajoute le supplément au prix affiché (même mécanisme de recalcul que les
  variations, `assets/js/product.js`) et rend le champ requis.
- `Product\EngravingCart` (le « contrôleur » de ce cycle de vie panier/commande) capture la
  sélection soumise dans la donnée de l'article du panier
  (`woocommerce_add_cart_item_data`, tronquée à la longueur maximale configurée), ajoute le
  supplément au prix de cet article à chaque recalcul des totaux
  (`woocommerce_before_calculate_totals`, recalculé depuis le prix réel du produit/de la variante à
  chaque fois plutôt que cumulé sur le prix déjà ajusté, pour ne jamais composer le supplément sur
  lui-même), l'affiche comme ligne supplémentaire au panier/paiement
  (`woocommerce_get_item_data`) et la persiste comme meta de la ligne de commande
  (`woocommerce_checkout_create_order_line_item`) — la valeur saisie se retrouve donc bien dans la
  commande, pas seulement dans le panier.
- Sous le panneau, des onglets Description/Avis/Caractéristiques
  (`template-parts/single-product/tabs.php`), suivant le motif ARIA « tabs » (navigation clavier
  flèches/Home/End en plus du clic) — un onglet ne s'affiche que s'il a un contenu réel, le
  produit entier ne s'affiche pas si aucun des trois n'en a. `Product\ProductDescription::content()`
  lit le vrai contenu de l'article produit (comme le ferait `the_content()`), avec en second
  colonne la deuxième image de la galerie du produit (pas de texte éditorial factice, contrairement
  au hero/à la page « notre histoire » : hors du périmètre de cette étape). `Product\ProductReviews`
  réimplémente `woocommerce/templates/single-product-reviews.php` (résumé de note + distribution
  par étoile depuis `WC_Product::get_rating_counts()`, liste des avis approuvés avec badge « Achat
  vérifié » via `wc_review_is_from_verified_owner()`, formulaire de soumission natif via
  `comment_form()`) avec les chaînes du catalogue du thème plutôt que le domaine de traduction de
  WooCommerce (même convention que `ProductStock`). `Product\ProductSpecifications::rows()`
  reproduit les règles de sélection de `wc_display_product_attributes()` (poids, dimensions, puis
  chaque attribut visible) pour renvoyer des lignes brutes plutôt que d'échapper à un template
  WooCommerce complet. Le lien existant du panneau vers `#reviews` active directement l'onglet Avis
  au chargement (`assets/js/product.js`, `initProductTabs()`).
- Une section « Produits similaires » (`template-parts/single-product/related-products.php`,
  `Product\ProductRelated`) réutilisant `wc_get_related_products()` (même correspondance
  catégorie/étiquette que les templates natifs de WooCommerce) et le composant carte produit
  existant (`Catalog\ProductCardMapper`) — ne s'affiche pas du tout sans aucun produit similaire.
  Grille à décalages « masonry » identiques à celle des produits vedettes de la page d'accueil : le
  correctif de hauteur fixe des cartes, déjà apporté au catalogue produits, vit dans le composant carte produit partagé,
  donc cette section en hérite automatiquement sans code supplémentaire.

### Tunnel de vente (`checkout.php`, `header-checkout.php`, `footer-checkout.php`, `inc/Checkout/`)

**En cours de construction**, page par page.

- Les pages panier/paiement/confirmation restent de vraies pages WordPress WooCommerce
  (`/cart/`, `/checkout/`, `/checkout/order-received/...`) : plutôt que de créer des gabarits
  `page-{slug}.php` fragiles (le slug de ces pages est configurable dans les réglages WooCommerce),
  `Checkout\CheckoutController::template_include()` (hook `template_include`) sert le propre
  gabarit du thème, `checkout.php`, dès que `is_cart()`/`is_checkout()` est vrai — même mécanisme de
  contrôleur que `Catalog\CatalogController::apply_filters_to_main_query()`.
- `checkout.php` ouvre le document avec `get_header('checkout')`/`get_footer('checkout')` (chargeant
  `header-checkout.php`/`footer-checkout.php` plutôt que les gabarits globaux) : le tunnel n'a
  volontairement ni navigation principale, ni mega menu, ni recherche, pour ne pas distraire le
  client en cours d'achat — seulement la marque (lien vers l'accueil) et une mention « Paiement
  100% sécurisé » en en-tête, des liens légaux/copyright en pied de page.
- L'indicateur des 5 étapes (Panier/Connexion/Livraison/Paiement/Confirmation) est calculé par
  `Checkout\StepIndicator::steps()` (étape en cours → `done`/`active`/`future` pour chacune) et
  rendu par `template-parts/checkout/step-indicator.php`, partagé par toutes les pages du tunnel.
  Les étapes Connexion/Livraison/Paiement vivent en réalité sur la même page réelle WooCommerce
  (`/checkout/`, formulaire natif unique) : `Checkout\CheckoutController::current_step()` ne
  détermine que la sous-étape visible au premier chargement (Connexion pour un invité, Livraison
  directement pour un client déjà connecté) — se déplacer entre ces trois sous-étapes ensuite se
  fait côté client (assets/js/checkout.js, étapes suivantes de ce même chantier), sans
  jamais soumettre le formulaire avant le clic final sur « Payer ».
- La page panier (`/cart/`) affiche les vrais articles du panier
  (`Checkout\CartView::items()` : image, nom, métadonnées de variante/gravure sur une seule
  ligne — `woocommerce/cart/cart-item-data.php` réimplémente la sortie par défaut de WooCommerce en
  ligne unique plutôt qu'une liste de définitions, même contenu réel puisque toujours alimentée par
  `wc_get_formatted_cart_item_data()` — stepper de quantité, prix, lien de retrait), un formulaire de
  code promo, et un récapitulatif sticky (sous-total, remises, livraison, total, bouton vers l'étape
  suivante). Chaque champ/action garde le nom, l'action et le nonce natifs de WooCommerce
  (`cart[{clé}][qty]`, `wc_get_cart_remove_url()`, `coupon_code`/`apply_coupon`,
  `woocommerce-cart-nonce`) : modifier une quantité, retirer un article et appliquer un code promo
  fonctionnent par une soumission de formulaire classique, sans JavaScript requis — le stepper
  « −»/« + » (`assets/js/checkout.js`, `initCartQuantitySteppers()`) ne fait qu'ajuster la valeur du
  même champ natif avant cette soumission, même convention que le stepper de quantité de la fiche
  produit.
- Décision technique : le bouton « Procéder à... » (`woocommerce/cart/proceed-to-checkout-button.php`)
  nomme explicitement la prochaine étape que le client verra réellement sur la page de paiement
  (« Procéder à la connexion » pour un invité, « Procéder à la livraison » pour un client déjà
  connecté) plutôt que le texte générique de WooCommerce, cohérent avec l'indicateur d'étapes.
- **Bug détecté et corrigé pendant la vérification en Docker réel** : les pages Panier et Paiement de
  l'installation WooCommerce utilisaient par défaut les blocs Gutenberg natifs (`<!-- wp:woocommerce/
  cart -->`/`<!-- wp:woocommerce/checkout -->`), qui ne passent jamais par les gabarits PHP classiques
  que ce thème surcharge (`woocommerce/cart/cart.php`...) — leur contenu réel n'est hydraté que côté
  client par l'API Store, invisible à toute vérification serveur. Un thème classique/non-FSE comme
  celui-ci doit surcharger les gabarits classiques, donc corrigé en réassignant le contenu de ces deux
  pages sur les shortcodes classiques (`[woocommerce_cart]`/`[woocommerce_checkout]`) — vérifié de
  bout en bout ensuite : ajout au panier, modification de quantité, retrait d'article et application
  d'un code promo de test (créé puis supprimé après vérification) confirmés fonctionnels dans
  l'environnement Docker réel, sans avertissement ni erreur PHP.

### Pied de page du thème (`footer.php`, `assets/scss/_footer.scss`)

- Grille 5 colonnes (Marque, Boutique, Informations, Légal, Newsletter) + barre de bas de page
  (copyright, icônes de méthodes de paiement), conforme à la maquette.
- Contenu piloté par `solar_template_footer_config()` (marque, colonnes de liens, newsletter),
  `solar_template_footer_payment_icons()` et `solar_template_footer_copyright()` — chacune
  filtrable, en prévision d'un futur écran d'administration (pas encore construit).
- Les liens Boutique/Informations pointent vers l'équivalent le plus proche déjà disponible (page
  boutique WooCommerce, page des articles) ; les liens Légal (C.G.V., confidentialité, mentions
  légales, cookies) et « À propos » résolvent vers une page du même slug si elle existe
  (`solar_template_page_url_by_slug()`, partagée avec la navigation), sinon vers l'accueil — ces
  pages légales elles-mêmes restent à construire.
- Le formulaire newsletter n'est que du balisage : aucune logique d'inscription n'est câblée à
  cette étape (hors périmètre, voir la fiche d'étape correspondante).
- Le texte de copyright utilise un espace réservé littéral `{year}`, remplacé par l'année en cours
  au rendu plutôt qu'à la traduction, pour ne jamais devenir obsolète.

### À noter
- `template-claude-code.html` (s'il est présent à la racine) est une maquette HTML exportée, ignorée par git — à utiliser comme référence visuelle/structurelle pour construire les vrais gabarits, jamais comme code à exécuter ou copier tel quel.
- `.claude/` et `CLAUDE.md` sont exclus du dépôt via `.gitignore` (configuration locale de l'assistant, non versionnée).
- `composer.json` définit les dépendances PHP et l'autoload PSR-4 (`composer install` requis après
  chaque clone). `package.json` définit le pipeline de build des assets (`npm install` puis
  `npm run build`, ou `npm run dev` pour une recompilation continue) — voir « Pipeline d'assets »
  ci-dessous.

### Pipeline d'assets (`npm run build` / `npm run dev`)

- `npm run build` compile une fois (mode production, sans sourcemap) ; `npm run dev` recompile à
  chaque changement (mode développement, avec sourcemap).
- Le thème charge `assets/dist/main.css`/`main.js` s'ils existent (anti-cache basé sur la date de
  modification du fichier) ; sinon, une notice d'administration invite à lancer `npm run build`,
  sans bloquer le reste du site.

### Design system (`assets/scss/_tokens.scss`)

- Toutes les valeurs de couleur, typographie, espacement et géométrie du design sont définies une
  seule fois dans ce fichier (variables SCSS), plus un jeu de mixins (`type-display`, `type-h1`,
  `type-h2`, `type-h3`, `type-body`, `type-small`, `type-caption`, `type-price`) qui regroupent
  chaque ligne de l'échelle typographique. Aucun autre fichier SCSS ne doit redéfinir une valeur
  déjà couverte ici.
- La police **Plus Jakarta Sans** (poids 300 à 800) est chargée depuis Google Fonts
  (`solar_template_enqueue_assets()` dans `functions.php`), avec le numéro de version du thème comme
  version de ressource pour l'invalidation de cache.
- Composants de base construits à partir de ces tokens : boutons (`_buttons.scss`), badges
  (`_badges.scss`), pills/chips de filtre (`_pills.scss`). Aucun de ces partials ne code en dur une
  valeur déjà couverte par `_tokens.scss` ; les quelques valeurs propres au « chrome » d'un
  composant (ex. taille de police d'un bouton, 13.5px) sont déclarées en variable locale en tête du
  partial concerné plutôt que dans le fichier de tokens global.
- `tests/js/design-system.test.js` compile `assets/scss/main.scss` directement (indépendamment de
  `npm run build`) et vérifie, par expression régulière sur le CSS généré, que chaque variante de
  composant reproduit exactement les valeurs de la maquette (couleurs, rayons de bordure, états
  hover/actif) — la vérification visuelle « pixel » n'étant pas outillable dans cet environnement
  sans navigateur, cette régression structurelle en est le meilleur équivalent automatisé.

### Tests (`composer test` / `npm run test`)

- PHP : PHPUnit, avec un bootstrap (`tests/php/bootstrap.php`) qui ne définit que les quelques
  fonctions/constantes WordPress réellement utilisées en dehors d'une installation complète
  (transients en mémoire, `ARRAY_A`...) — pas une bibliothèque de stubs du cœur WordPress. Zéro
  avertissement/dépréciation toléré (`failOnWarning`/`failOnDeprecation` dans `phpunit.xml.dist`).
- JS : Vitest (environnement jsdom), configuré dans `vite.config.js`.
- Cette suite automatisée ne remplace pas la vérification fonctionnelle manuelle dans
  l'environnement Docker réel (activation du thème, base de données, WooCommerce...), effectuée à
  chaque étape et documentée dans `RELEASE.md`.

### Intégration continue (`.github/workflows/ci.yml`)

- À chaque push/pull request : norme de code PHP, tests PHP (PHP `8.1` et `8.3`), build de
  production des assets, tests JS.
- Sur un push réussi vers `main` : création automatique d'une release GitHub taguée `vX.Y.Z`
  (version lue dans `composer.json`), avec pour description la section correspondante de
  `RELEASE.md` — sauf si cette version a déjà été publiée.
- Le `.env` de ce thème (`Solar_Template\Support\DotenvEnvironmentLoader`, lu via la fonction
  `solar_template_env()`) est indépendant du `.env` du dépôt Docker parent : aucun nom de variable
  en commun, et ce thème ne possède ni ne lit jamais les identifiants de connexion à la base de
  données (gérés exclusivement par le `.env` parent).

## Conventions de code

- PHP `>=8.1` (voir `composer.json` et l'en-tête `style.css`).
- Toutes les chaînes traduisibles utilisent le text domain `solar-template`.
- Chaque fichier PHP de premier niveau commence par un garde `ABSPATH` (voir `functions.php`).

## Relation avec le repo parent

Le repo parent `wordpress-dev-env` (privé) documente le fonctionnement du polyrepo dans son propre `STRUCTURE.md`/`CLAUDE.md` : il versionne le cœur WordPress (`wp-admin/`, `wp-includes/`, fichiers PHP racine, Docker, Makefile) et ignore volontairement `wp-content/themes/*`/`wp-content/plugins/*` personnalisés au profit de ces dépôts indépendants. Une mise à jour du cœur WordPress ne touche jamais l'intérieur de ce dépôt.
