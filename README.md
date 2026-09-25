# Solar Template

Thème WordPress classique (non block/FSE) destiné à l'écosystème **dev-solar-cms**.

> État actuel : scaffold initial. Le thème n'a pas encore de gabarits (`header.php`/`footer.php` manquants) ni de styles réels — voir [STRUCTURE.md](./STRUCTURE.md) pour le détail des fichiers.

## Prérequis

- PHP >= 8.1
- Une installation WordPress pour l'exécuter (ce repo ne contient pas WordPress lui-même)

En développement local, ce thème est prévu pour tourner dans l'environnement Docker du repo `wordpress-dev-env` (`make start` → http://localhost:8000).

## Installation

1. Copier (ou symlink) ce dossier dans `wp-content/themes/` d'une installation WordPress.
2. Activer **Solar Template** depuis Apparence > Thèmes (Text Domain `solar-template`).

## Développement

Aucun outil de build, linter ou suite de tests n'est configuré pour l'instant (`composer.json`/`package.json` sont des métadonnées placeholder). Ne pas supposer l'existence de scripts `npm run build` ou `composer install` tant que ces fichiers n'ont pas été complétés.

Voir [STRUCTURE.md](./STRUCTURE.md) pour l'organisation des fichiers et [RELEASE.md](./RELEASE.md) pour la procédure de versionnage/publication.

## Licence

GPL-2.0-or-later. Voir [LICENSE](./LICENSE).
