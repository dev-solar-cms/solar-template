# DEV.md

Notes de développement détaillées, complémentaires au `README.md` (qui reste volontairement
court) : état d'avancement du projet, choix d'architecture (thème classique, non block/FSE) et
conventions de l'écosystème **dev-solar-cms**.

## 0. État actuel du projet

### Design system

Tokens SCSS, boutons/badges/pills, cartes produit/article.

### Structure commune du thème

`header.php`, `footer.php`, mega menu.

### Page d'accueil

`front-page.php` complète : hero, produits vedettes, catégories, notre histoire, témoignages,
aperçu du blog, newsletter.

### Catalogue produits

`archive-product.php` complet : grille de produits réels avec fil d'Ariane, titre, compteur, une
barre de filtres qui met à jour la grille en AJAX sans rechargement de page, filtres actifs, tri,
chargement progressif.

### Fiche produit

`single-product.php` complète : galerie d'images avec bande de miniatures, panneau produit
(badges, titre, notation, statut de stock, description, réassurance, accordéon) ; pour les
produits variables, des sélecteurs couleur/taille branchés sur les vraies variations WooCommerce
avec recalcul du prix affiché en JS ; une option de personnalisation (gravure) native configurable
depuis l'admin produit ; des onglets Description/Avis/Caractéristiques sous le panneau produit
(avis WooCommerce natifs, avec formulaire de soumission réel) ; une section produits similaires
réutilisant la carte produit existante. La fiche produit est désormais complète.

### Tunnel de vente

Complet : panier et paiement réels sur les propres gabarits WooCommerce du thème, indicateur des 5
étapes, connexion/livraison/paiement organisés en panneaux basculés côté client sur la même page
de paiement, moyens de paiement réellement configurés par la boutique affichés en cartes radio, et
une page de confirmation reprenant les vraies données de la commande passée.

### Espace client (« Mon compte »)

Complet : sidebar/tableau de bord, commandes (liste à onglets + détail), liste de souhaits native,
gestion des adresses de facturation/livraison, téléchargements, demande de service après-vente
native, et des paramètres de compte (informations personnelles, téléphone/date de naissance,
changement de mot de passe, suppression de compte).

### Blog

Complet : un index (hero, filtre catégories, article à la une, grille, pagination) et des
archives catégorie/étiquette, plus une page article complète (image à la une, contenu riche, tags,
carte auteur, articles connexes).

### Pages statiques

En place : un template de page légale sélectionnable (onglets C.G.V./Confidentialité/Mentions
légales/Cookies + sommaire généré automatiquement depuis les titres de la page), une page de
contact (`page-contact.php`) avec un formulaire natif (protection anti-spam nonce/honeypot, envoi
par email, état de succès) et un panneau de coordonnées, et une FAQ en accordéon natif.

### Administration du thème

En place : menu admin « Solar Template », onglets Général/Apparence/En-tête/Pied de
page/Page d'accueil/Produits/Blog/Traductions — nom boutique/logo/favicon/devise/mode maintenance,
couleurs/polices/arrondi appliqués au front sans reconstruction des assets, disposition/mega
menu/barre de promo/réseaux sociaux/transparence du header, colonnes/newsletter/icônes de
paiement/copyright du footer, sections de la page d'accueil réordonnables par glisser-déposer,
colonnes/pagination/position de la barre de filtres (haut ou sidebar) et widgets de
sidebar/produits similaires/gravure personnalisée du catalogue, pagination/à la une/colonnes/
boutons de partage/articles connexes du blog, langue par défaut/détection automatique et import de
traductions, et un bouton de régénération du cache et des assets — chaque réglage modifie
réellement le rendu du site.

### Multilingue

Complet : une page **Langues** pour rechercher/ajouter une langue standard (code + drapeau),
définir la langue par défaut ou en supprimer une, et un **Éditeur de traduction** pour modifier
directement, langue par langue, chaque chaîne du thème (espaces réservés et pluriel gérés
explicitement) — la sauvegarde recompile immédiatement le `.mo` de la langue concernée.

Voir [STRUCTURE.md](./STRUCTURE.md) pour le détail des fichiers et [RELEASE.md](./RELEASE.md) pour
l'historique version par version.

## 1. Ce que « classique (non block/FSE) » veut dire concrètement

Ce thème est un thème WordPress **PHP traditionnel**, pas un thème *Full Site Editing* (FSE) :

- Pas de `theme.json`, pas de dossier `templates/`/`parts/` en HTML de blocs. Toute la mise en
  page passe par la **hiérarchie de gabarits PHP classique** : `header.php`/`footer.php`,
  `front-page.php`, `archive-product.php`, `single-product.php`, `single.php`, `page.php`,
  `page-contact.php`, `page-templates/legal-page.php`, etc. — exactement le mécanisme de résolution
  de gabarit que WordPress utilise depuis toujours (`template-loader.php` → hiérarchie standard).
- Les fragments réutilisables sont de vrais `get_template_part()` PHP
  (`template-parts/product-card.php`, `template-parts/blog-card.php`, `template-parts/front-page/*`,
  `template-parts/checkout/*`, `template-parts/account/*`…), pas des *patterns*/*block templates*
  gérés par l'éditeur de site.
- La navigation utilise `wp_nav_menu()` sur une position enregistrée (`primary`), avec un filtre de
  repli (`solar_template_primary_nav_items`) si aucun menu n'est assigné — pas le bloc Navigation.
- Aucune dépendance à l'éditeur de site (« Apparence > Éditeur ») : ce menu n'a simplement aucun
  effet ici, puisque le thème ne déclare pas le support `block-templates`.
- Le réglage du thème (couleurs, polices, disposition du header, sections de la page d'accueil…) ne
  passe **ni par le Customizer ni par le Site Editor**, mais par un écran d'administration propre au
  thème (`inc/Admin/SettingsPage.php`, menu « Solar Template », onglets Général/Apparence/En-tête/
  Pied de page/Page d'accueil/Produits/Blog/Traductions) — voir §5.

En résumé : si une doc WordPress commence par « dans l'éditeur de site, ouvrez le panneau
Styles… », elle ne s'applique pas à ce thème. Le point d'entrée est toujours un fichier PHP ou un
réglage de l'écran d'administration du thème, jamais l'éditeur de blocs pleine page.

## 2. Pourquoi ce choix (plutôt qu'un thème block/FSE) ?

- **Fidélité pixel-perfect à la maquette.** Le design handoff (`./.claude/design/
  design_handoff_solar_woocommerce/`, branding or-sur-noir « ST ») est un système très structuré
  (grilles asymétriques, cartes avec décalages « masonry », composants très spécifiques comme
  l'indicateur d'étapes du tunnel de vente ou le badge de stock progressif) — bien plus simple à
  reproduire fidèlement avec du HTML/SCSS/JS entièrement maîtrisé qu'avec la boîte à outils (limitée
  en placement/alignement fin) d'un thème de blocs.
- **Compatibilité WooCommerce.** WooCommerce reste, dans son usage le plus robuste et le plus
  documenté, un système de surcharge de gabarits PHP classiques (`woocommerce/*.php` copiés et
  adaptés dans le thème — cart, checkout, myaccount, single-product…). Un thème block/FSE
  fonctionne avec WooCommerce, mais avec plus de frictions et une doc/communauté plus réduite pour
  les cas avancés que ce projet couvre (gravure personnalisée, variations avec recalcul de prix en
  JS, tunnel de vente en plusieurs panneaux sur une seule page...).
- **Convention d'équipe.** Le projet impose une architecture MVC stricte en PHP (voir §4) —
  logique dans des classes sous `inc/`, jamais dans un template. Cette discipline est celle d'une
  équipe habituée au PHP orienté objet classique, pas au workflow React/JSON des thèmes de blocs.
- **Système de réglages sur-mesure.** Le projet voulait un panneau d'administration entièrement
  intégré au thème (pas un plugin séparé) avec des besoins très spécifiques (mode maintenance,
  gravure personnalisée site-wide, système multilingue maison...) qui dépassent largement ce que
  `theme.json`/Site Editor permettent de piloter nativement.

## 3. Ce que « écosystème dev-solar-cms » veut dire

Ce thème n'est **pas un projet isolé** : il fait partie d'un **polyrepo**, documenté en détail dans
`STRUCTURE.md` du repo parent (`wordpress-dev-env`, privé) :

- Le repo parent (`dev-solar-cms/wordpress-dev-env`, privé) contient l'environnement Docker complet
  et le cœur WordPress (`wp-admin/`, `wp-includes/`, fichiers racine PHP) — jamais le code de ce
  thème.
- **Chaque thème/plugin custom vit dans son propre dépôt public indépendant**, sous l'organisation
  GitHub `dev-solar-cms` — ce dépôt (`dev-solar-cms/solar-template`) en est un exemple. Il a été créé
  via `make new-theme name=solar-template` depuis le repo parent, qui scaffolde la structure
  standard (composer.json/package.json, `.gitignore`, `LICENSE`, `README.md`) et publie
  immédiatement le nouveau dépôt public.
- Ces dépôts imbriqués sous `wp-content/themes/*`/`wp-content/plugins/*` sont **invisibles au repo
  racine** (ignorés par son propre `.gitignore`) — pas de sous-modules Git, chaque `.git` reste
  totalement indépendant. Ouvrir le dossier racine dans un éditeur affiche donc plusieurs panneaux
  Source Control distincts : c'est le fonctionnement attendu, pas un état Git cassé.
- Une mise à jour du cœur WordPress (dans le repo parent) ne touche jamais l'intérieur de
  `wp-content/plugins/*`/`wp-content/themes/*` au-delà de leur activation — aucune interaction avec
  ce dépôt.

Concrètement, pour développer sur ce thème : le code de ce thème (celui-ci) est autonome et se
publie/versionne indépendamment (voir `RELEASE.md`), mais il ne **tourne** que branché dans
l'environnement Docker du repo parent (`make start` → http://localhost:8000, `wp-cli`/Composer
récupérés à la demande en `.phar` dans le conteneur — jamais installés dans l'image elle-même).

## 4. Conventions héritées de cet écosystème (rappel rapide)

- **MVC strict.** Toute logique métier/requête/mapping de données vit dans une classe PHP sous
  `inc/` (namespace `Solar_Template\*`), jamais dans un template ni dans `functions.php` — celui-ci
  reste un pur bootstrap (~20 lignes : autoloader Composer + `Solar_Template\Theme::boot()`). Tout
  hook WordPress est enregistré dans `Theme::boot()` (`inc/Theme.php`), jamais via une fonction
  wrapper nommée.
- **Dépendance par interface.** Toute classe qui s'appuie sur une bibliothèque tierce le fait via
  une interface `Solar_Template\Contracts\*`, jamais directement sur la classe de la lib.
- **i18n obligatoire dès la conception.** Aucun texte visible codé en dur — toujours `__()`/`_e()`/
  `_n()` avec le text-domain `solar-template`, chaque nouvelle chaîne enregistrée dans le catalogue
  de traduction maison (`Solar_Template\I18n\*`, tables `wp_solar_template_languages`/
  `wp_solar_template_translations`) et traduite fr_FR **et** en_US avant de considérer un
  changement terminé.
- **Rien en dehors du thème, aucune dépendance à un plugin tiers** au-delà de WooCommerce — pas de
  plugin de formulaire, de wishlist, de traduction (WPML/Polylang restent compatibles mais ne sont
  jamais une dépendance), etc. Tout ce qui ressemble à une fonctionnalité de plugin est réimplémenté
  nativement dans `inc/`.
- **Tests obligatoires**, sans warning ni code déprécié : `composer test` (PHPUnit, bootstrap
  minimal sans faux noyau WordPress complet) et `npm run test` (Vitest/jsdom).

## 5. Où regarder pour chaque type de changement

| Je veux…                                          | Je regarde…                                                              |
|----------------------------------------------------|---------------------------------------------------------------------------|
| Changer la structure d'une page (nouveau gabarit)  | Hiérarchie de gabarits WordPress standard, fichier PHP à la racine ou `page-templates/` |
| Changer un fragment réutilisable                    | `template-parts/*`                                                        |
| Ajouter/modifier de la logique métier               | Une classe sous `inc/<Domaine>/`, jamais le template ni `functions.php`   |
| Ajouter un réglage admin                            | Un onglet `Solar_Template\Admin\*SettingsTabInterface`, jamais le Customizer |
| Ajouter une chaîne visible                          | `__()`/`_e()`/`_n()` + entrée dans `Solar_Template\I18n\DefaultStrings`   |
| Changer une couleur/police/style global              | Tokens SCSS (`assets/scss/_tokens.scss`) + onglet Apparence, jamais `theme.json` |
| Ajouter un nouveau thème/plugin custom au projet     | `make new-theme`/`make new-plugin` depuis le repo parent, jamais à la main |

## 6. Pièges à éviter (spécifiques à ce choix d'architecture)

- Ne pas chercher un `theme.json` ou un dossier `templates/` en blocs — ils n'existent pas et
  n'ont pas leur place ici.
- Ne pas ajouter de bloc Gutenberg custom pour une fonctionnalité de rendu front — tout passe par
  un `template-part`/une classe `inc/`.
- Ne pas piloter un réglage visuel via le Customizer (`customize_register`) — l'écran de réglages du
  thème (Settings API + table `wp_solar_template_settings`) est le seul mécanisme de configuration
  admin voulu par le projet.
- Se rappeler que les pages Panier/Paiement WooCommerce, si elles sont un jour réinitialisées dans
  wp-admin, reviennent par défaut aux **blocs** natifs de WooCommerce (`[woocommerce_cart]`/
  `[woocommerce_checkout]` sont les shortcodes classiques attendus) — un bug déjà rencontré une fois
  sur ce projet (voir `RELEASE.md`, version 0.5.1) : les gabarits PHP de ce thème ne sont jamais
  appelés tant que ces pages utilisent les blocs.
