# STRUCTURE.md

Organisation du dépôt et sa place dans l'écosystème **dev-solar-cms**.

## Ce dépôt

`solar-template` est un dépôt git **indépendant**, public, sous l'organisation GitHub `dev-solar-cms`. Il a été généré par `make new-theme name=solar-template` depuis le repo parent `wordpress-dev-env`, qui gère l'environnement Docker WordPress local.

Ce dépôt n'a aucun lien d'historique git avec le repo parent : il vit dans `wp-content/themes/solar-template/` de ce dernier, mais possède son propre `.git`, indépendant (pas de submodule). Ouvrir la racine du projet parent dans un éditeur affichera donc plusieurs panneaux Source Control distincts — c'est un comportement attendu.

## Fichiers

```
solar-template/
├── style.css        # En-tête du thème (nom, version, text-domain) — pas de styles réels encore
├── functions.php     # Déclare le support title-tag et post-thumbnails
├── index.php          # Seul gabarit présent ; appelle get_header()/get_footer()
├── screenshot.png     # 1200x900, requis par WordPress pour l'aperçu du thème
├── composer.json      # Dépendances PHP + autoload PSR-4 (Solar_Template\)
├── phpcs.xml.dist      # Norme de code PHP (WordPress-Extra), utilisée par `composer lint`/`format`
├── package.json       # Métadonnées placeholder (aucun script/dépendance)
├── inc/                # Classes PHP du thème, autoloadées en PSR-4 sous `Solar_Template\`
│   ├── Contracts/       # Interfaces consommées par le code métier (jamais une lib tierce directement)
│   └── Support/         # Implémentations par défaut de ces interfaces
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

### À noter

- `header.php` et `footer.php` **n'existent pas encore** : `index.php` les appelle déjà, le thème produira donc une erreur/warning tant qu'ils ne sont pas créés.
- `template-claude-code.html` (s'il est présent à la racine) est une maquette HTML exportée, ignorée par git — à utiliser comme référence visuelle/structurelle pour construire les vrais gabarits, jamais comme code à exécuter ou copier tel quel.
- `.claude/` et `CLAUDE.md` sont exclus du dépôt via `.gitignore` (configuration locale de l'assistant, non versionnée).
- `composer.json` définit les dépendances PHP et l'autoload PSR-4 (`composer install` requis après
  chaque clone). `package.json` ne définit encore ni dépendances ni scripts : ne pas supposer qu'un
  build JS/SCSS existe avant la mise en place du pipeline d'assets.

## Conventions de code

- PHP `>=8.1` (voir `composer.json` et l'en-tête `style.css`).
- Toutes les chaînes traduisibles utilisent le text domain `solar-template`.
- Chaque fichier PHP de premier niveau commence par un garde `ABSPATH` (voir `functions.php`).

## Relation avec le repo parent

Le repo parent `wordpress-dev-env` (privé) documente le fonctionnement du polyrepo dans son propre `STRUCTURE.md`/`CLAUDE.md` : il versionne le cœur WordPress (`wp-admin/`, `wp-includes/`, fichiers PHP racine, Docker, Makefile) et ignore volontairement `wp-content/themes/*`/`wp-content/plugins/*` personnalisés au profit de ces dépôts indépendants. Une mise à jour du cœur WordPress ne touche jamais l'intérieur de ce dépôt.
