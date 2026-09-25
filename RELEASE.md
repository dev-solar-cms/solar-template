# RELEASE.md

Procédure de versionnage et de publication du thème.

## Versionnage

Le thème suit [Semantic Versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`). Le numéro de version doit être identique et mis à jour simultanément à trois endroits :

- `style.css` — champ `Version:` de l'en-tête du thème (c'est la valeur que WordPress affiche et utilise pour le cache des assets)
- `composer.json` — champ `version`
- `package.json` — champ `version`

Version actuelle : `0.1.1`.

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
