# RELEASE.md

Procédure de versionnage et de publication du thème.

## Versionnage

Le thème suit [Semantic Versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`). Le numéro de version doit être identique et mis à jour simultanément à trois endroits :

- `style.css` — champ `Version:` de l'en-tête du thème (c'est la valeur que WordPress affiche et utilise pour le cache des assets)
- `composer.json` — champ `version`
- `package.json` — champ `version`

Version actuelle : `0.2.7`.

## Avant une release

1. Vérifier que le thème s'active sans erreur dans l'environnement Docker du repo parent (`make start`, puis Apparence > Thèmes).
2. Mettre à jour les trois fichiers de version ci-dessus.
3. Mettre à jour `README.md`/`STRUCTURE.md` si la structure du thème a changé depuis la dernière release.
4. Vérifier `.gitignore` : aucun fichier de config locale (`.claude/`, `CLAUDE.md`, `.env`, `node_modules/`, `vendor/`) ne doit être commité.

## Publication

Ce dépôt est public (`github.com/dev-solar-cms/solar-template`) et versionné indépendamment du repo parent `wordpress-dev-env` — voir [STRUCTURE.md](./STRUCTURE.md).

Un push sur `main` déclenche la CI GitHub Actions (`.github/workflows/ci.yml`) : tests PHP et JS,
puis, si tout passe, création automatique d'une release GitHub taguée `vX.Y.Z` (à partir du champ
`version` de `composer.json`), avec pour description la section correspondante de ce fichier —
aucune étape manuelle de tag/release n'est donc nécessaire tant que la version est bien mise à jour
avant de pousser.

## Changelog

### 0.2.7 — recherche en overlay

- Ajout de l'overlay de recherche plein écran, ouvert depuis l'icône de la barre supérieure,
  enveloppant un formulaire de recherche WordPress natif personnalisé (`searchform.php`).
- Décision technique : la recherche reste native (`?s=`), sans branchement spécifique à
  WooCommerce — WordPress inclut déjà les produits dans les résultats d'une recherche native une
  fois WooCommerce actif, sans réglage supplémentaire ; une recherche avancée type SearchWP est
  explicitement hors périmètre.
- Ouverture/fermeture au clic, à la touche Échap et au clic extérieur, avec déplacement du focus
  dans le champ à l'ouverture et retour au bouton déclencheur à la fermeture — même schéma
  d'accessibilité que le mega menu déjà en place.
- Vérifié de bout en bout dans l'environnement Docker réel : un article et un produit WooCommerce
  de test apparaissent tous les deux dans les résultats d'une même recherche.

### 0.2.6 — pied de page global

- Ajout de `footer.php` : grille 5 colonnes (Marque, Boutique, Informations, Légal, Newsletter) et
  barre de bas de page (copyright avec année dynamique, icônes de méthodes de paiement), affichée
  sur toutes les pages qui appellent `get_footer()`.
- Décision technique : le contenu (colonnes de liens, icônes de paiement, modèle de copyright) est
  exposé via des filtres WordPress plutôt que codé en dur, en prévision du futur écran
  d'administration qui le pilotera.
- Décision technique : extraction d'une aide partagée (résolution d'une page par son slug, avec
  repli vers l'accueil) entre la navigation et le pied de page, pour éviter de dupliquer cette
  logique entre les deux.
- Le formulaire d'inscription à la newsletter reste volontairement du balisage sans logique
  d'envoi, hors périmètre de cette version.
- Vérifié de bout en bout dans l'environnement Docker réel : le pied de page s'affiche sans
  avertissement ni erreur sur plusieurs pages, avec le nom du site et l'année courante
  correctement interpolés.

### 0.2.5 — mega menu de navigation

- Ajout du mega menu déroulant sur l'item « Collections » de la navigation principale
  (`template-parts/mega-menu.php`, `assets/js/header.js`) : ouverture/fermeture au survol de la
  souris, au focus clavier, à la touche Échap et au clic extérieur, sans erreur en console dans
  aucun de ces cas.
- Décision technique : le contenu du panneau (colonnes de liens) reste un texte de remplacement
  exposé par un filtre WordPress (`solar_template_mega_menu_columns`) — le branchement à de
  vraies catégories WooCommerce est hors périmètre de cette étape.
- Décision technique : deux filtres WordPress (`nav_menu_css_class`/`nav_menu_link_attributes`)
  garantissent qu'un menu réel assigné par un administrateur depuis Apparence > Menus reçoit les
  mêmes classes que la navigation de secours (même rendu, même comportement) — vérifié dans
  l'environnement Docker réel avec un menu de test.
- Bug détecté et corrigé pendant les tests automatisés avant qu'il n'affecte un utilisateur réel :
  fermer le mega menu à la touche Échap redonnait le focus au lien déclencheur, ce qui déclenchait
  immédiatement son propre gestionnaire d'ouverture au focus et rouvrait aussitôt le panneau.

### 0.2.4 — en-tête sticky global

- Ajout de `header.php` : le thème dispose désormais d'un en-tête global (barre supérieure avec
  réseaux sociaux/marque/icônes recherche-compte-panier, barre de navigation principale collante),
  affiché sur toutes les pages qui appellent `get_header()`.
- La navigation principale utilise `wp_nav_menu()` (emplacement assignable depuis Apparence >
  Menus) avec des éléments par défaut tant qu'aucun menu n'est assigné, chacun résolu vers
  l'équivalent le plus proche déjà disponible dans une installation WordPress/WooCommerce standard
  plutôt qu'une vue dédiée qui reste à construire.
- Les icônes compte/panier résolvent vers les pages WooCommerce réelles quand WooCommerce est
  actif, avec une dégradation gracieuse sinon — vérifié de bout en bout dans l'environnement Docker
  réel avec WooCommerce actif (aucun avertissement ni erreur liés à l'en-tête).
- Décision technique : les URLs de réseaux sociaux et les éléments de navigation par défaut sont
  exposés via des filtres WordPress plutôt que codés en dur, en prévision du futur écran
  d'administration qui les pilotera — sans construire cet écran maintenant.
- Décision technique : le badge du nombre d'articles au panier est toujours présent dans le DOM
  (masqué en CSS à zéro article plutôt qu'omis du balisage), pour qu'un futur rafraîchissement
  AJAX du panier puisse cibler cet élément sans avoir à l'insérer lui-même.
- Bug évité avant qu'il ne se produise : un nom de variable de boucle réutilisait un nom de
  variable globale historique de WordPress, ce que la norme de code du projet interdit
  précisément pour ce risque ; corrigé avant la mise en production.

### 0.2.3 — composant carte article de blog

- Ajout d'un composant réutilisable « carte article » (image 16:10, badge de catégorie, ligne
  date/durée de lecture, titre, extrait limité à trois lignes, auteur), affiché avec des données
  factices — le branchement aux articles réels est prévu pour une étape ultérieure.
- Décision technique : la ligne date/durée de lecture est acceptée comme texte déjà formaté plutôt
  que par champs séparés, pour éviter d'introduire une chaîne traduisible/pluralisable sans donnée
  réelle pour la piloter ; ce composant n'introduit d'ailleurs aucune chaîne propre (tout le texte
  affiché est fourni par le gabarit appelant).
- Bug évité avant qu'il ne se produise : une variable interne du composant portait le même nom
  qu'une variable globale de WordPress, ce que la norme de code du projet interdit précisément pour
  ce risque ; corrigé avant la mise en production.
- **Le système de design commun du thème (tokens, boutons/badges/pills, carte produit, carte
  article) est désormais complet** et prêt à être utilisé par les prochaines pages du thème.

### 0.2.2 — composant carte produit

- Ajout d'un composant réutilisable « carte produit » (image, badge en coin, bouton liste de
  souhaits, overlay « ajouter au panier » au survol, catégorie, nom, prix avec prix barré/badge de
  réduction optionnels, swatches de couleur), affiché avec des données factices pour l'instant — le
  branchement aux données réelles WooCommerce est prévu pour une étape ultérieure.
- Décision technique : la structure des données attendues par ce composant est volontairement
  calquée sur celle de WooCommerce, avec un formatage de prix minimal marqué comme temporaire (à
  remplacer par la fonction native de WooCommerce une fois branché sur de vrais produits).
- Mise en place d'un mécanisme qui enregistre automatiquement, à l'activation du thème, la
  traduction française et anglaise de chaque chaîne visible introduite par le thème — vérifié de
  bout en bout dans l'environnement réel (catalogue → fichier de traduction compilé → affichage
  traduit).
- Bug évité avant mise en production : la fonction utilitaire de formatage de prix du composant
  aurait provoqué une erreur fatale à la deuxième carte affichée sur une même page (grille de
  plusieurs produits) ; corrigé avant que cette situation ne se présente.
- Correction de confidentialité : quelques commentaires de code déjà publiés faisaient
  indirectement référence à l'organisation interne du projet en étapes ; reformulés en langage
  neutre.

### 0.2.1 — boutons, badges et pills

- Ajout des composants de base réutilisables : boutons (variantes primaire dorée, sombre pleine
  largeur, contour clair/sombre), badges (Nouveau/Exclusif/Promo/contour doré/réduction) et
  pills/chips de filtre (état actif, bouton de suppression), tous construits à partir des tokens de
  design existants.
- Décision technique : pas encore de partial PHP de rendu pour ces composants — aucune page réelle
  du thème ne les consomme à ce stade (l'en-tête/pied de page et les gabarits de page restent à
  construire) ; les classes CSS sont prêtes à être utilisées par les prochaines étapes.
- Conformité à la maquette vérifiée par un test automatisé qui compile le SCSS et vérifie chaque
  couleur/rayon/état généré ; une vérification visuelle en navigateur n'étant pas outillable dans
  cet environnement, ce point est signalé explicitement plutôt que supposé.

### 0.2.0 — fondations du système de design

- Ajout des tokens de design (couleurs, typographie, espacements, géométrie) en variables SCSS
  (`assets/scss/_tokens.scss`), traduits directement depuis la charte graphique du projet, avec des
  mixins regroupant chaque niveau de l'échelle typographique.
- Chargement de la police Plus Jakarta Sans (graisses 300 à 800) depuis Google Fonts.
- Décision technique : les plages de taille typographique (ex. « 56–80px ») sont reproduites en
  `clamp()` fluide plutôt qu'en valeur fixe unique, pour un texte réellement responsive ; les
  plages de rayon de bordure applicables à deux composants différents (cartes de contenu vs.
  modales) sont scindées en deux tokens distincts plutôt qu'en une seule valeur fluide.
- Bug corrigé (environnement local, sans rapport avec le contenu fonctionnel de cette version) :
  dépendances Node obsolètes/incomplètes empêchant la compilation des assets, résolu par une
  réinstallation propre et une resynchronisation du fichier de verrouillage des dépendances avec le
  numéro de version du projet.

### 0.1.9 — intégration continue (CI)

- Mise en place d'un workflow GitHub Actions exécutant, à chaque push et pull request : la norme de
  code PHP, les tests PHP (sur deux versions de PHP, `8.1` et `8.3`), le build de production des
  assets et les tests JS.
- Sur un push réussi vers `main`, une release GitHub est créée automatiquement pour la version
  courante de `composer.json` (si elle n'existe pas déjà), avec pour description la section
  correspondante de ce fichier.

### 0.1.8 — squelette de tests automatisés (PHP + JS)

- PHPUnit (`composer test`), avec un bootstrap qui ne définit que la poignée de fonctions/constantes
  WordPress réellement nécessaires en dehors d'une vraie installation (transients en mémoire,
  `ARRAY_A`, etc.) — pas une bibliothèque de stubs complète du cœur WordPress. `failOnWarning`/
  `failOnDeprecation`/`failOnNotice` activés : aucun avertissement toléré.
- Vitest (`npm run test`, environnement jsdom), configuré directement dans `vite.config.js`.
- Premiers tests couvrant : le cache court terme, le compilateur `.mo` (avec un test dédié pour une
  règle de pluriel française et une anglaise), le catalogue de traductions (via un double de test
  en mémoire pour `$wpdb`), les instructions SQL de création des tables, la synchronisation des
  numéros de version entre `composer.json`/`style.css`/`.env.example`, et un test trivial côté JS.
- La vérification fonctionnelle réelle (activation du thème, base de données, WooCommerce) continue
  de se faire manuellement dans l'environnement Docker à chaque étape, en complément de cette suite
  automatisée — cette dernière ne remplace pas la première.

### 0.1.7 — dégradation gracieuse sans WooCommerce

- Le thème détecte l'absence/inactivité de WooCommerce et affiche une notice d'administration
  claire invitant à l'installer/l'activer, au lieu de fataliser ou de laisser des fonctionnalités
  boutique silencieusement cassées.
- Le support WooCommerce du thème (`add_theme_support('woocommerce')`) n'est déclaré que lorsque le
  plugin est réellement actif.
- Toutes les chaînes d'administration introduites depuis le début de cette série de travaux
  (notices Composer, build des assets, WooCommerce) sont désormais enregistrées dans le catalogue
  de traduction et traduites en français et en anglais.

### 0.1.6 — tables de configuration et activation du thème

- Ajout d'une table de réglages génériques (`wp_solar_template_settings`, clé → valeur), initialisée
  avec des valeurs minimales par défaut (version du thème, couleurs de base, logo/favicon vides).
- Toutes les tables du thème (langues, traductions, réglages) sont désormais créées et initialisées
  automatiquement à l'activation du thème (`after_switch_theme`), de façon idempotente : réactiver
  le thème, ou l'activer sur une base vierge, ne duplique jamais les données par défaut.
- L'activation compile aussi immédiatement les fichiers `.mo` du catalogue de traduction existant,
  pour que le mécanisme complet (table → catalogue → fichier compilé → chargement WordPress) soit
  exercé dès le premier chargement du thème.

### 0.1.5 — pipeline de compilation des assets (SCSS/JS)

- Mise en place d'un outillage Node (Vite) compilant `assets/scss/*` et `assets/js/*` vers
  `assets/dist/main.css`/`assets/dist/main.js`, avec un point d'entrée JS unique qui importe le
  SCSS pour une compilation unifiée en une seule étape.
- Le thème charge automatiquement ces fichiers compilés (avec anti-cache basé sur leur date de
  modification) s'ils existent, et affiche une notice d'administration invitant à lancer
  `npm run build` sinon, plutôt que de bloquer le site.
- Adoption de Prettier comme outil de formatage JS/SCSS du projet (`npm run format`).
- `npm audit` : 0 vulnérabilité (résolu en fixant Vite sur sa branche majeure la plus récente au
  moment de cette étape).

### 0.1.4 — configuration propre au thème

- Ajout d'un fichier `.env` propre au thème (déjà exclu par `.gitignore`, avec un `.env.example`
  versionné) : uniquement des réglages non sensibles (version, mode des assets, TTL du cache) —
  jamais d'identifiants de connexion, la base de données restant gérée par le `.env` du dépôt
  Docker parent, un fichier distinct dans un répertoire distinct.
- Le numéro de version est désormais lisible depuis `.env`, `composer.json` et l'en-tête de
  `style.css`, avec la même valeur dans les trois.

### 0.1.3 — fondations du système de traduction multilingue

- Ajout de deux tables dédiées : les langues installées (code, libellé, drapeau, active/défaut) et
  le catalogue de traductions (clé → singulier/pluriel par langue), avec `fr_FR` et `en_US`
  pré-remplies.
- Le thème continue d'appeler les fonctions de traduction natives de WordPress (`__()`/`_e()`/`_n()`)
  partout : ce catalogue ne fait que fournir une source éditable, compilée en fichiers `.mo`
  standards que WordPress charge lui-même.
- Ajout d'une liste statique de langues proposables (au-delà des deux fournies par défaut), prête
  à alimenter le futur menu d'ajout de langue.
- **Bug rencontré et corrigé** : les fichiers `.mo` compilés dans le dossier de langues propre au
  thème doivent être nommés `{locale}.mo` (sans le préfixe du domaine de traduction) — le
  chargement « juste à temps » des traductions de WordPress attend ce format précis pour le
  dossier `languages/` d'un thème, contrairement au dossier de langues global où le préfixe est
  nécessaire. Vérifié par un test de bout en bout dans l'environnement Docker réel (chaîne avec
  espace réservé et pluriel, résolue correctement en français et en anglais).

### 0.1.2 — dépendances PHP et interfaces de base

- Mise en place de Composer (autoload PSR-4, espace de noms `Solar_Template\`) avec `vlucas/phpdotenv`
  comme première dépendance, consommée uniquement via une interface du thème
  (`Solar_Template\Contracts\EnvironmentLoaderInterface`), jamais appelée directement ailleurs.
- Ajout d'une seconde interface de cache court terme (`CacheInterface`), avec une implémentation
  par défaut basée sur les transients WordPress (aucune dépendance externe).
- Mise en place de la norme de code PHP du projet (WordPress Coding Standards via
  PHP_CodeSniffer) : `composer lint` / `composer format`.
- Le thème continue de s'activer sans erreur si `composer install` n'a pas encore été lancé
  (notice d'administration au lieu d'une erreur fatale).

### 0.1.1 — vérification de l'environnement de développement

- Confirmation que le thème peut être testé fonctionnellement dans l'environnement Docker réel
  (`wp-cli` et Composer sont récupérés à la demande sous forme de `.phar`, sans modifier l'image
  du conteneur ni aucun fichier hors de ce thème).
- Ajout d'un site de documentation statique (FR + EN) dans `doc/`, avec une première page
  « Infrastructure » décrivant cette méthode de vérification.

### 0.1.0 — scaffold initial

- Génération du thème via `make new-theme name=solar-template`.
- En-tête `style.css`, `functions.php` (support `title-tag`, `post-thumbnails`), `index.php` minimal.
- `screenshot.png` (1200x900).
