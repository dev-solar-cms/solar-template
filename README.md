# Solar Template

[![CI](https://github.com/dev-solar-cms/solar-template/actions/workflows/ci.yml/badge.svg)](https://github.com/dev-solar-cms/solar-template/actions/workflows/ci.yml)

Thème WordPress classique (non block/FSE) destiné à l'écosystème **dev-solar-cms**.

> État actuel : système de design commun en place (tokens, boutons/badges/pills, cartes produit/article), structure commune du thème (`header.php`, `footer.php`, mega menu) et page d'accueil (`front-page.php`) en cours de construction section par section — hero et produits vedettes (données WooCommerce réelles) sont en place. Voir [STRUCTURE.md](./STRUCTURE.md) pour le détail des fichiers.

## Prérequis

- PHP >= 8.1
- Une installation WordPress pour l'exécuter (ce repo ne contient pas WordPress lui-même)

En développement local, ce thème est prévu pour tourner dans l'environnement Docker du repo `wordpress-dev-env` (`make start` → http://localhost:8000).

## Installation

1. Copier (ou symlink) ce dossier dans `wp-content/themes/` d'une installation WordPress.
2. Lancer `composer install` et `npm install && npm run build` à la racine du thème (sinon le
   thème s'active quand même, avec une notice d'administration invitant à le faire).
3. Activer **Solar Template** depuis Apparence > Thèmes (Text Domain `solar-template`).

## Développement

L'outillage (Composer, pipeline d'assets, tests, CI) est mis en place progressivement — voir
[RELEASE.md](./RELEASE.md) pour l'état d'avancement exact. Toute vérification fonctionnelle réelle
(activation du thème, base de données, traductions) s'exécute à l'intérieur du conteneur Docker du
dépôt parent, via `wp-cli`/Composer récupérés à la demande (voir la documentation dans
[doc/fr/infrastructure.html](./doc/fr/infrastructure.html) /
[doc/en/infrastructure.html](./doc/en/infrastructure.html)).

Suite de tests : `composer test` (PHP) et `npm run test` (JS). Une CI GitHub Actions exécute cette
suite à chaque push et crée automatiquement une release à partir de `main` si elle passe.

Voir [STRUCTURE.md](./STRUCTURE.md) pour l'organisation des fichiers et [RELEASE.md](./RELEASE.md) pour la procédure de versionnage/publication.

## Licence

GPL-2.0-or-later. Voir [LICENSE](./LICENSE).
