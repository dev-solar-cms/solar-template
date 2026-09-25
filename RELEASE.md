# RELEASE.md

Procédure de versionnage et de publication du thème.

## Versionnage

Le thème suit [Semantic Versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`). Le numéro de version doit être identique et mis à jour simultanément à trois endroits :

- `style.css` — champ `Version:` de l'en-tête du thème (c'est la valeur que WordPress affiche et utilise pour le cache des assets)
- `composer.json` — champ `version`
- `package.json` — champ `version`

Version actuelle : `0.1.4`.

## Avant une release

1. Vérifier que le thème s'active sans erreur dans l'environnement Docker du repo parent (`make start`, puis Apparence > Thèmes).
2. Mettre à jour les trois fichiers de version ci-dessus.
3. Mettre à jour `README.md`/`STRUCTURE.md` si la structure du thème a changé depuis la dernière release.
4. Vérifier `.gitignore` : aucun fichier de config locale (`.claude/`, `CLAUDE.md`, `.env`, `node_modules/`, `vendor/`) ne doit être commité.

## Publication

Ce dépôt est public (`github.com/dev-solar-cms/solar-template`) et versionné indépendamment du repo parent `wordpress-dev-env` — voir [STRUCTURE.md](./STRUCTURE.md).

1. Commit des changements sur `main`.
2. Tag git correspondant à la version : `git tag vX.Y.Z && git push origin vX.Y.Z`.
3. Créer une release GitHub à partir du tag, avec un changelog résumant les changements.

## Changelog

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
