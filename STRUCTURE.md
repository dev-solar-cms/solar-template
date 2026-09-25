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
├── header.php         # En-tête sticky global (barre supérieure + navigation principale)
├── index.php          # Seul gabarit de contenu présent ; appelle get_header()/get_footer()
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
│   └── Database/        # Schéma et installation des tables `wp_solar_template_*`
├── template-parts/      # Fragments de gabarit réutilisables (`get_template_part()`)
│   ├── product-card.php # Carte produit (image, badge, wishlist, overlay panier, prix, swatches)
│   ├── blog-card.php    # Carte article de blog (image 16:10, badge catégorie, meta, titre, extrait, auteur)
│   ├── cart-badge.php   # Badge du nombre d'articles au panier, utilisé par l'en-tête
│   └── mega-menu.php    # Panneau du mega menu « Collections », contenu factice pour l'instant
├── languages/           # Fichiers `.mo` compilés (générés, ignorés par git sauf `.gitkeep`)
├── vite.config.js       # Configuration du pipeline de build des assets
├── .prettierrc.json     # Norme de formatage JS/SCSS, utilisée par `npm run format`
├── assets/
│   ├── scss/main.scss    # Point d'entrée SCSS (sources non compilées)
│   ├── scss/_tokens.scss # Design tokens (couleurs, typographie, espacements, géométrie) + mixins d'échelle typo
│   ├── scss/_buttons.scss # Composant boutons (.btn + variantes primaire/dark/outline)
│   ├── scss/_badges.scss  # Composant badges (.badge + variantes new/exclusive/sale/outline/discount)
│   ├── scss/_pills.scss   # Composant pills/chips de filtre (.pill + état actif, bouton de suppression)
│   ├── scss/_product-card.scss # Composant carte produit (media, badge, wishlist, overlay panier, prix, swatches)
│   ├── scss/_blog-card.scss # Composant carte article de blog (media 16:10, badge, meta, titre, extrait, auteur)
│   ├── scss/_header.scss # En-tête sticky (barre supérieure, actions, navigation, mega menu)
│   ├── js/main.js        # Point d'entrée JS (importe le SCSS, initialise les modules de comportement)
│   ├── js/header.js      # Comportement de l'en-tête (mega menu, bascule de recherche)
│   └── dist/             # Sortie compilée (générée par `npm run build`/`dev`, ignorée par git)
├── phpunit.xml.dist     # Configuration PHPUnit, utilisée par `composer test`
├── tests/php/           # Tests unitaires PHP (bootstrap minimal, pas une installation WordPress complète)
├── test/js/             # Tests unitaires JS (Vitest, environnement jsdom), utilisés par `npm run test`
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
  option d'administration — celle-ci arrive avec l'onglet « En-tête » du Groupe 10.
- Le mega menu de l'item « Collections » (`template-parts/mega-menu.php`,
  `assets/js/header.js`) s'ouvre/ferme au survol, au focus clavier et se ferme à l'échappement ou
  au clic extérieur. Son contenu (colonnes de liens) est du texte de remplacement, exposé via le
  filtre `solar_template_mega_menu_columns` — le branchement aux vraies catégories WooCommerce est
  hors périmètre de cette étape. Deux filtres (`nav_menu_css_class`/`nav_menu_link_attributes`)
  garantissent qu'un menu réel assigné à l'emplacement `primary` reçoit les mêmes classes que la
  navigation de secours, pour un rendu/comportement identique dans les deux cas.

### À noter
- `footer.php` **n'existe pas encore** : `index.php` l'appelle déjà, le thème produira donc un
  avertissement de dépréciation tant qu'il n'est pas créé (`header.php`, lui, existe désormais).
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
- `test/js/design-system.test.js` compile `assets/scss/main.scss` directement (indépendamment de
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
