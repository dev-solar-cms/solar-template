# RELEASE.md

Procédure de versionnage et de publication du thème.

## Versionnage

Le thème suit [Semantic Versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`). Le numéro de version doit être identique et mis à jour simultanément à trois endroits :

- `style.css` — champ `Version:` de l'en-tête du thème (c'est la valeur que WordPress affiche et utilise pour le cache des assets)
- `composer.json` — champ `version`
- `package.json` — champ `version`

Version actuelle : `0.7.0`.

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

### 0.7.0 — blog complet (index, archives, article)

- Le blog est désormais entièrement fonctionnel : un index (`home.php`) avec un hero éditorial, une
  rangée de pills de catégories réelles (liens simples, sans JavaScript), l'article épinglé
  natif WordPress affiché en tête (grille 50/50), puis une grille 3 colonnes des vrais articles et
  une pagination numérotée native restylée.
- Les archives de catégorie/étiquette (`archive.php`) partagent la même structure, avec un hero
  utilisant les vraies données du terme affiché plutôt qu'un contenu éditorial.
- La page d'un article (`single.php`) affiche un fil d'Ariane, une image à la une pleine largeur,
  la méta (catégorie/date/temps de lecture réel), un bouton « Partager » natif (API Web Share du
  navigateur avec repli sur la copie du lien), le contenu réel de l'article avec une mise en forme
  riche (citations, figures légendées, listes), les vrais tags, une carte auteur (biographie réelle
  du profil WordPress) et une section d'articles connexes.
- **Décision/bug rencontré** : aucune « page des articles » distincte n'était configurée dans
  Réglages > Lecture, rendant le blog inaccessible par une vraie URL — corrigé en créant une page
  d'accueil dédiée et une page « Blog », puis en configurant Réglages > Lecture en conséquence ;
  vérifié que la page d'accueil réelle du thème continue de s'afficher normalement après ce
  changement.
- Aucune fonctionnalité inventée au-delà de la maquette : le compteur de vues et le bouton
  « Sauvegarder » d'un article, sans spécification ailleurs dans le projet, sont volontairement
  omis.
- Vérifié de bout en bout dans l'environnement Docker réel : articles de test répartis sur
  plusieurs catégories (dont un épinglé, avec tags et mise en forme riche) confirment l'index,
  les archives, la pagination sur 2 pages et la page article complète. Données de test supprimées
  après vérification.

### 0.6.6 — paramètres du compte et suppression de compte

- La page « Paramètres » de l'espace client propose désormais trois sections : informations
  personnelles (prénom, nom, email, téléphone, date de naissance optionnelle), changement de mot de
  passe, et une zone de danger pour supprimer définitivement son compte.
- Les champs natifs (prénom, nom, email, mot de passe) continuent d'être traités par le mécanisme
  natif de WooCommerce ; téléphone et date de naissance sont deux nouveaux champs propres au thème,
  enregistrés comme méta utilisateur simples.
- La suppression de compte est entièrement native (aucune dépendance à un plugin RGPD/suppression
  tiers) : les commandes réelles du client sont supprimées définitivement, puis le compte lui-même,
  après une confirmation obligatoire (case à cocher, doublée d'une boîte de dialogue de
  confirmation côté navigateur).
- **Bug rencontré et corrigé** : le champ « Nom d'affichage » natif de WooCommerce, absent de la
  maquette, est requis par son propre contrôle de champs obligatoires — son omission bloquait
  systématiquement l'enregistrement. Corrigé en le conservant comme champ caché pré-rempli plutôt
  que de le supprimer.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : modification
  réelle des informations d'un compte de test (y compris une date invalide correctement rejetée),
  et suppression complète d'un second compte de test jetable avec une commande réelle associée
  (commande et compte confirmés définitivement supprimés). Données de test supprimées après
  vérification.

### 0.6.5 — demande de service après-vente native

- L'espace client propose désormais une page « Demande S.A.V. » : un formulaire natif (commande
  concernée facultative, type de demande, sujet, description, pièce jointe facultative) et la liste
  des demandes déjà envoyées par le client, sans dépendance à un plugin de ticketing tiers.
- Chaque nouvelle demande est enregistrée et déclenche un email natif au responsable du site.
- La statistique « S.A.V. » du tableau de bord reflète désormais le vrai nombre de demandes
  actives du client.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : une demande de
  test (avec et sans pièce jointe) s'enregistre, apparaît dans la liste du client et met à jour la
  statistique du tableau de bord.

### 0.6.4 — page des téléchargements restylée

- La page « Téléchargements » affiche désormais les vrais fichiers téléchargeables du client sous
  forme de lignes stylées (icône selon le type de fichier, référence de commande, nombre de
  téléchargements restants ou « Illimité », bouton de téléchargement) plutôt que la simple liste à
  puces par défaut de WooCommerce.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : un produit
  téléchargeable de test acheté via une commande réelle apparaît correctement et le téléchargement
  du fichier fonctionne.

### 0.6.3 — gestion des adresses de facturation et de livraison

- La page « Mes adresses » affiche désormais deux cartes de résumé (facturation/livraison) au-dessus
  d'un formulaire d'édition restylé en grille 2 colonnes, réutilisant le composant déjà construit
  pour le tunnel de vente.
- Bug corrigé : visiter l'URL « Mes adresses » sans préciser facturation ou livraison provoquait la
  perte du préfixe des champs du formulaire — corrigé par une redirection immédiate vers l'adresse
  de facturation dans ce cas.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : la modification
  réelle d'une adresse de facturation et d'une adresse de livraison se met à jour correctement et
  s'affiche immédiatement dans son propre résumé.

### 0.6.2 — liste de souhaits native

- Le bouton cœur des cartes produit (catalogue, page d'accueil, produits similaires) fonctionne
  désormais réellement : bascule en AJAX, persiste par compte client dans une table dédiée du
  thème, et se reflète immédiatement sur la carte, le tableau de bord et la nouvelle page « Liste
  de souhaits » de l'espace client.
- Un visiteur non connecté cliquant ce bouton est redirigé vers la page de connexion plutôt que de
  voir la requête échouer silencieusement.
- Aucune dépendance à un plugin de wishlist tiers : stockage, endpoint AJAX et page de listing sont
  natifs au thème.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : ajouter puis
  retirer un produit persiste correctement entre les pages et les rechargements.

### 0.6.1 — liste et détail des commandes dans l'espace client

- La page « Mes commandes » affiche désormais des onglets de statut (Toutes, En cours, Livrées,
  Retours, avec leur vrai compte) et des cartes de commande (en-tête coloré, miniatures des
  articles, actions « Voir le détail »/« Recommander ») plutôt que le tableau par défaut de
  WooCommerce — chaque onglet reste un lien simple, fonctionnel sans JavaScript.
- La page de détail d'une commande affiche un en-tête restylé (numéro, date, badge de statut)
  au-dessus du tableau de détails natif de WooCommerce (articles, téléchargements éventuels,
  bouton « Recommander »), conservé tel quel.
- Décision technique : l'onglet « Retours » recense les commandes au statut « remboursé » de
  WooCommerce ; les actions de suivi de livraison/téléchargement de facture ne sont pas
  implémentées, aucun service de transport ni générateur de facture réel n'existant dans ce
  thème.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : le filtrage
  par onglet isole les bonnes commandes du compte de test, et la page de détail affiche les
  bonnes données.

### 0.6.0 — sidebar et tableau de bord de l'espace client

- L'espace client (« Mon compte ») affiche désormais une sidebar (avatar, nom, email, navigation à
  icônes) plutôt que la liste de liens par défaut de WooCommerce, et un tableau de bord avec 4
  cartes de statistiques (commandes en cours, total, liste de souhaits, demandes S.A.V.), un tableau
  des commandes récentes avec statut coloré, et 3 liens rapides — toutes ces données viennent des
  vraies commandes du compte connecté.
- Le menu du compte est réordonné et entièrement re-libellé dans le vocabulaire de la maquette
  (Tableau de bord, Mes commandes, Liste de souhaits, Mes adresses, Téléchargements, Demande
  S.A.V., Paramètres, Se déconnecter), et deux nouveaux emplacements (liste de souhaits, demande
  S.A.V.) sont réservés pour les prochaines étapes de cet espace.
- Une commande déjà livrée propose un lien « Recommander » qui ajoute au panier, en une seule
  requête protégée par un jeton de sécurité, chaque article de cette commande passée.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : un compte
  client de test avec deux commandes réelles affiche des statistiques et un statut exacts, et le
  lien « Recommander » ajoute bien les bons articles au panier (compte et commandes de test
  supprimés après vérification).

### 0.5.5 — page de confirmation de commande

- La page de confirmation affiche désormais un récapitulatif centré (coche dorée, numéro de
  commande, livraison estimée, total payé, adresse de livraison, articles commandés) à partir des
  vraies données de la commande qui vient d'être passée, avec deux boutons « Suivre ma commande » et
  « Continuer mes achats », plutôt que le récapitulatif par défaut de WooCommerce.
- La livraison estimée est une fourchette de dates calculée depuis la date de création réelle de la
  commande.
- Le tableau de détails de commande que WooCommerce ajoute par défaut en bas de cette page a été
  retiré : il faisait doublon avec le récapitulatif du thème, qui affiche déjà les mêmes données
  réelles dans son propre style. Le contenu propre à chaque moyen de paiement (par exemple les
  coordonnées bancaires d'un virement) reste affiché, puisqu'il s'agit d'une information réelle
  nécessaire au client.
- Une commande en échec affiche un message d'erreur restylé plutôt que le récapitulatif de succès.
- Le tunnel de vente (panier, connexion, livraison, paiement, confirmation) est désormais complet de
  bout en bout.
- Vérifié de bout en bout dans l'environnement Docker réel : une commande réelle placée par le
  formulaire de paiement affiche la bonne confirmation ; une commande de test en statut d'échec
  affiche la bonne branche restylée (commandes de test supprimées après vérification).

### 0.5.4 — étape de paiement dédiée, passerelles réelles

- Le paiement dispose désormais de sa propre étape sur la page de commande, distincte de l'étape
  « Livraison » qui la précédait auparavant dans la même colonne : un bouton « Procéder au paiement »
  fait passer de l'une à l'autre sans jamais soumettre le formulaire, un bouton « ← Retour » fait
  l'inverse. Le récapitulatif de commande reste affiché sur ces deux étapes plutôt que dupliqué.
- Les moyens de paiement réellement configurés par la boutique s'affichent en cartes radio avec une
  icône pertinente (virement bancaire, chèque, paiement à la livraison — aucune dépendance à un
  moyen de paiement tiers ; toute autre passerelle qu'une boutique activerait plus tard reçoit une
  icône générique plutôt que rien).
- Le bouton de validation affiche « 🔒 Payer [montant] », calculé à partir du total réel et courant
  du panier.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : une commande
  complète simulée avec la requête réelle que le formulaire de paiement soumettrait, confirmée créée
  avec le bon moyen de paiement et le bon total (commande de test supprimée après vérification).

### 0.5.3 — adresse de livraison et méthodes d'expédition réelles

- Le panneau « Livraison » de la page de paiement affiche désormais une disposition à deux colonnes :
  les champs d'adresse réels de WooCommerce en grille 2 colonnes (Prénom/Nom, Adresse, Complément,
  Code postal/Ville, Pays/Téléphone) à gauche, un récapitulatif de commande sticky à droite (même
  composant que la barre latérale du panier).
- Un champ dont le partenaire habituel est absent, ou un champ ajouté par une extension tierce, reste
  affiché seul en pleine largeur plutôt que d'être masqué ou de casser la mise en page — la
  disposition s'adapte aux champs réellement configurés par la boutique.
- Les méthodes de livraison réellement configurées (Standard/Express, par exemple) s'affichent en
  cartes radio, partagées entre le panier et la page de paiement.
- **Bug détecté et corrigé** : le récapitulatif de commande avait perdu, en abandonnant son ancienne
  structure de tableau, la classe CSS que le propre script de WooCommerce utilise pour rafraîchir les
  totaux en AJAX (changement de méthode de livraison, de champ d'adresse) — sans elle, ce
  rafraîchissement échouait silencieusement. Corrigé en conservant cette classe sur le nouvel élément
  racine.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif, y compris une
  simulation directe de la requête AJAX de mise à jour des totaux avec deux méthodes de livraison de
  test (supprimées après vérification) : total, sélection et affichage se mettent bien à jour. Aucun
  avertissement ni erreur PHP relevé.

### 0.5.2 — étape de connexion sur la page de paiement

- La page de paiement affiche désormais un panneau « Connexion » pour un visiteur non connecté
  (authentification WordPress réelle, restylée), avec « Continuer sans compte » et « Créer un
  compte » (coche la case native correspondante) ; un client déjà connecté passe directement à la
  suite du formulaire. Les boutons de connexion sociale Google/Facebook sont désactivés et
  documentés comme non implémentés (dépendance tierce hors périmètre).
- L'indicateur d'étapes reste synchronisé lors du passage d'un panneau à l'autre, sans jamais
  soumettre le formulaire de commande avant le clic final sur le bouton de paiement.
- Décision technique : le formulaire de connexion et l'encart de code promo que WooCommerce ajoute
  par défaut en haut de la page de paiement sont désormais détachés (le premier vit dans son propre
  panneau stylé, le second est déjà proposé à l'étape panier).
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : une connexion
  réelle avec un utilisateur de test (créé puis supprimé après vérification) redirige vers la page
  de paiement et fait disparaître le panneau de connexion. Aucun avertissement ni erreur PHP relevé.

### 0.5.1 — page panier réelle

- Le panier (`/cart/`) affiche désormais les vrais articles (image, nom, variante/gravure, prix), un
  formulaire de code promo et un récapitulatif sticky (sous-total, remises, livraison, total, bouton
  vers l'étape suivante), plutôt que le rendu générique de WooCommerce.
- Modifier une quantité, retirer un article et appliquer un code promo fonctionnent tous par une
  soumission de formulaire classique (les noms de champs/actions/nonces natifs de WooCommerce sont
  conservés à l'identique) ; un stepper de quantité « −»/« + » ajuste le champ natif avant l'envoi,
  même convention que celui déjà en place sur la fiche produit.
- Le bouton vers l'étape suivante nomme explicitement celle-ci (« Procéder à la connexion » ou
  « Procéder à la livraison » selon que le client est déjà connecté), plutôt qu'un texte générique.
- **Bug détecté et corrigé** : les pages Panier et Paiement de l'installation utilisaient par défaut
  les blocs Gutenberg natifs de WooCommerce, qui ne passent jamais par les gabarits PHP classiques
  qu'un thème non-FSE comme celui-ci surcharge — leur contenu réel n'est hydraté que côté client,
  invisible à toute vérification serveur. Corrigé en réassignant le contenu de ces deux pages sur les
  shortcodes classiques (`[woocommerce_cart]`/`[woocommerce_checkout]`).
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : ajout de
  plusieurs produits, modification de quantité, retrait d'article et application d'un code promo de
  test (créé puis supprimé après vérification) tous confirmés fonctionnels. Aucun avertissement ni
  erreur PHP relevé.

### 0.5.0 — début du tunnel de vente : routage et indicateur d'étapes

- Le panier et le paiement restent de vraies pages WordPress/WooCommerce, désormais servies avec le
  propre gabarit du thème (`template_include`) plutôt que le gabarit générique de page : un en-tête
  et un pied de page minimaux (marque centrée, mention « Paiement 100% sécurisé », liens légaux),
  sans navigation principale ni mega menu ni recherche, pour ne pas distraire le client en cours
  d'achat.
- Ajout d'un indicateur des 5 étapes du tunnel (Panier, Connexion, Livraison, Paiement,
  Confirmation — cercles numérotés, ligne de progression, libellés), partagé par toutes les pages du
  tunnel et reflétant correctement l'étape en cours à chaque chargement de page.
- Décision technique : les étapes Connexion, Livraison et Paiement vivent en réalité sur une seule
  page de paiement WooCommerce (un formulaire natif unique) plutôt que sur des pages séparées ;
  l'indicateur ne détermine donc, pour l'instant, que la sous-étape visible au premier chargement de
  cette page — le déplacement entre ces trois sous-étapes sans rechargement suit dans une prochaine
  version.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : la page panier
  affiche l'étape « Panier » active ; après ajout d'un produit, la page de paiement affiche « Panier »
  complétée et « Connexion » active pour un visiteur non connecté. Aucun avertissement ni erreur PHP
  relevé.
- Corrigé au passage : plusieurs fichiers déjà publiés (documentation et `STRUCTURE.md`)
  mentionnaient par erreur un numéro de groupe/étape interne au projet, en violation de la règle de
  confidentialité déjà appliquée ailleurs — reformulés en phrasing neutre.

### 0.4.11 — produits similaires et fiche produit complète

- Ajout, en bas de la fiche produit, d'une section « Produits similaires » : une grille de vrais
  produits liés WooCommerce (`wc_get_related_products()`, même correspondance catégorie/étiquette
  que les gabarits natifs de WooCommerce), réutilisant le composant carte produit déjà utilisé par
  le catalogue et les produits vedettes de la page d'accueil. Ne s'affiche pas du tout sans aucun
  produit similaire.
- Décision technique : la grille reprend le même motif de décalages « masonry » que les produits
  vedettes de la page d'accueil ; le correctif d'alignement des cartes (hauteurs fixes,
  indépendamment de la longueur du nom du produit) vit déjà dans le composant carte produit
  partagé, donc cette nouvelle section en hérite automatiquement sans code supplémentaire.
- La fiche produit est désormais complète (galerie, panneau avec variations et personnalisation,
  onglets Description/Avis/Caractéristiques, produits similaires).
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif, dans les deux
  langues installées : un produit avec plusieurs autres produits dans sa catégorie confirme la
  grille alignée avec de vraies données ; un produit dont la catégorie ne contient qu'un seul autre
  produit confirme l'affichage d'un unique produit similaire. Aucun avertissement ni erreur PHP
  relevé.

### 0.4.10 — onglets Description/Avis/Caractéristiques de la fiche produit

- Ajout, sous le panneau produit, de trois onglets (Description, Avis, Caractéristiques), suivant
  le motif d'accessibilité ARIA « tabs » : changer d'onglet fonctionne aussi bien à la souris qu'au
  clavier (flèches gauche/droite, Origine/Fin). Un onglet ne s'affiche que s'il a un contenu réel —
  un produit sans description, sans avis ouverts et sans attribut n'affiche aucune section onglets
  du tout.
- La description reprend le vrai contenu de l'article du produit (comme le ferait `the_content()`),
  avec la deuxième image de la galerie du produit en second panneau plutôt qu'un texte éditorial
  factice.
- Les avis sont natifs WooCommerce (résumé de note et distribution par étoile depuis les vraies
  données du produit, liste des avis approuvés avec badge « Achat vérifié », formulaire de
  soumission natif fonctionnel) — traduits via le catalogue propre au thème plutôt que le domaine
  de traduction de WooCommerce, cohérent avec le reste de la fiche produit. Le lien existant du
  panneau vers les avis active désormais directement cet onglet.
- Le tableau des caractéristiques reprend les mêmes règles de sélection que la fonction native de
  WooCommerce (poids, dimensions, puis chaque attribut visible du produit).
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif, dans les deux
  langues installées : un produit de test avec description, attribut couleur, poids/dimensions et
  deux avis confirme les trois onglets et leurs données réelles — produit et avis de test supprimés
  après vérification. Un produit existant sans description ni attribut confirme la dégradation
  gracieuse (seul l'onglet Avis s'affiche). Aucun avertissement ni erreur PHP relevé.

### 0.4.9 — option de personnalisation (gravure) de la fiche produit

- Ajout d'une option de personnalisation (gravure) activable produit par produit, sans dépendance à
  ACF ni à WooCommerce Product Add-ons : un supplément de prix et une longueur maximale de texte
  configurables depuis l'écran d'édition du produit (onglet « Général », champs natifs de
  l'administration WooCommerce).
- Quand elle est activée, le panneau produit affiche un interrupteur et un champ de texte ; activer
  l'interrupteur ajoute le supplément au prix affiché (même mécanisme de recalcul que les
  variations couleur/taille) et rend le champ de texte obligatoire.
- Le texte saisi (tronqué à la longueur maximale configurée) est ajouté au panier avec son
  supplément de prix, affiché comme ligne supplémentaire au panier/paiement, et se retrouve
  persisté sur la ligne de la commande une fois celle-ci passée.
- Décision technique : le supplément est recalculé depuis le vrai prix du produit/de la variante à
  chaque recalcul des totaux du panier, jamais cumulé sur le prix déjà ajusté — pour ne jamais
  appliquer le supplément deux fois.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif, dans les deux
  langues installées : les champs d'administration enregistrent correctement les valeurs
  configurées ; une requête d'ajout au panier identique à celle que soumettrait le formulaire une
  fois la gravure activée confirme que le panier WooCommerce reçoit bien le supplément de prix
  (total du panier vérifié) et le texte tronqué à la longueur configurée ; une simulation directe
  de la création de ligne de commande confirme que ce texte est bien persisté sur la commande —
  produit de test supprimé après vérification. Aucun avertissement ni erreur PHP relevé.

### 0.4.8 — variations et prix dynamique de la fiche produit

- Pour un produit variable, ajout des sélecteurs Couleur (cercles) et Taille (boutons) du panneau
  produit, branchés sur les vraies variations WooCommerce : sélectionner une combinaison recalcule
  en direct le prix affiché et l'état de disponibilité, sans rechargement de page. Une option sans
  aucune variation en stock (toutes couleurs/tailles confondues) est grisée et désactivée.
- Le bouton « Ajouter au panier » reste désactivé tant qu'une combinaison valide et en stock n'est
  pas sélectionnée, puis s'active en indiquant l'identifiant réel de la variante au formulaire
  d'ajout au panier — un ajout au panier soumet donc exactement la même requête que le mécanisme
  natif de WooCommerce.
- Décision technique : contrairement à la maquette, dont le bouton d'ajout au panier affiche
  lui-même le prix, celui-ci reste un élément séparé du bouton — un seul emplacement de prix à
  recalculer. Le prix est reformaté selon les réglages réels d'affichage de la boutique (décimales,
  séparateurs, position du symbole) plutôt qu'une valeur codée en dur.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif, dans les deux
  langues installées : un produit variable de test (deux couleurs, deux tailles, une combinaison
  volontairement en rupture de stock) confirme les sélecteurs, la plage de prix initiale, puis une
  requête d'ajout au panier identique à celle que soumettrait le formulaire confirme que le panier
  WooCommerce reçoit la bonne variante et la bonne quantité — produit et variations de test
  supprimés après vérification. Aucun avertissement ni erreur PHP relevé.

### 0.4.7 — panneau de la fiche produit

- Ajout du panneau produit, en colonne droite sticky de la fiche produit : badges, titre,
  notation (étoiles, moyenne, lien vers les avis), statut de stock, description courte, bloc de
  réassurance (livraison/retours/paiement sécurisé) et accordéon (livraison & retours, guide des
  tailles, entretien).
- Contrairement à la maquette, dont les badges « NOUVEAU »/« SOLAR PREMIUM » sont toujours
  affichés, ceux-ci sont désormais calculés depuis de vraies données WooCommerce : « Nouveau » tant
  que le produit a été publié il y a moins de 14 jours (filtrable), « Solar Premium » uniquement
  si le produit est marqué « En vedette ».
- Décision technique : le statut de stock est traduit via le catalogue propre au thème plutôt que
  de réutiliser les chaînes natives de WooCommerce, qui utilisent leur propre domaine de
  traduction — cohérent avec la fondation i18n du thème.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif, dans les deux
  langues installées : un produit de test fraîchement créé et marqué « En vedette » confirme
  l'affichage des deux badges, sa description courte, son statut de stock et la traduction
  complète du panneau — produit de test supprimé après vérification. Aucun avertissement ni
  erreur PHP relevé.

### 0.4.6 — galerie de la fiche produit

- Ajout du gabarit de la fiche produit (`single-product.php`), qui affiche pour l'instant le fil
  d'Ariane et la galerie d'images du produit : une image principale (ratio 4:5) suivie d'une bande
  de miniatures — une par image du produit (image mise en avant puis galerie WooCommerce). Cliquer
  une miniature change l'image principale sans rechargement de page.
- La bande de miniatures ne s'affiche pas du tout tant que le produit n'a qu'une seule image ; un
  produit sans aucune image se replie sur l'image de remplacement WooCommerce standard.
- Décision technique : le bouton « Agrandir » (zoom/lightbox) visible dans la maquette n'est
  volontairement pas construit à ce stade — son comportement n'est pas spécifié au-delà de la
  maquette elle-même, même raisonnement que la bascule grille/liste déjà laissée de côté sur le
  catalogue produits.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif, dans les deux
  langues installées : un produit sans image confirme le repli sur l'image de remplacement, un
  produit de test avec une image mise en avant et une image de galerie confirme la bande de
  miniatures (première miniature marquée active) — produit et images de test supprimés après
  vérification. Aucun avertissement ni erreur PHP relevé.

### 0.4.5 — alignement des cartes du catalogue produits

- Correction visuelle : dans la grille du catalogue produits, les cartes n'étaient plus alignées
  entre elles dès que des produits réels avec des noms de longueur variable remplaçaient les
  données factices d'origine — un nom plus long que ses voisins faisait varier la hauteur totale
  de sa carte et désynchronisait la rangée.
- Corrigé dans le composant carte produit partagé (`assets/scss/_product-card.scss`), pas dans la
  grille elle-même : le nom du produit est désormais limité à deux lignes (tronqué avec des points
  de suspension au-delà), et le bloc de texte de la carte (catégorie, nom, prix) réserve une
  hauteur minimale cohérente, que la catégorie soit renseignée ou non. Le décalage vertical
  masonry de la grille catalogue, lui, reste inchangé — il redevient simplement fiable maintenant
  que la hauteur de chaque carte ne varie plus avec son contenu.
- La correction de l'alignement des produits similaires en fiche produit reste hors périmètre de
  cette version (le gabarit fiche produit n'existe pas encore).
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : un produit de
  test au nom volontairement très long et sans catégorie confirme que la grille reste parfaitement
  alignée à 4, 2 et 1 colonne selon la largeur d'écran, et que ce nom est bien tronqué avec des
  points de suspension plutôt que de casser la mise en page — produit de test supprimé après
  vérification.

### 0.4.4 — chargement progressif du catalogue

- Remplacement de la pagination numérotée du catalogue par un statut « Affichage de N sur M
  produits », une barre de progression dorée, et un bouton « Charger plus de produits » qui
  ajoute la page suivante à la grille existante en AJAX, sans rechargement ni remplacement des
  produits déjà affichés.
- Bug rencontré et corrigé : le nombre de produits par page pouvait différer entre le chargement
  de page classique et la requête AJAX de la page suivante, provoquant un chevauchement/doublon de
  produits entre les deux. Corrigé en calculant explicitement ce nombre de la même façon dans les
  deux cas, plutôt que de laisser WooCommerce le déduire implicitement à chaque fois.
- Réorganisation interne : les tests JavaScript du projet vivent désormais dans `./tests/js/*`,
  au même niveau que les tests PHP (`./tests/php/*`), plutôt que dans un dossier `./test/js/*`
  distinct et incohérent avec cette convention.

### 0.4.3 — tri du catalogue

- Ajout d'un sélecteur de tri natif à la barre de filtres du catalogue (Pertinence, Prix croissant,
  Prix décroissant, Nouveautés, Meilleures ventes), branché sur les mêmes mécanismes de
  chargement de page classique/AJAX que le reste de la barre de filtres, et préservé lorsqu'un
  filtre est retiré via un chip.
- Bug rencontré et corrigé pendant le développement : le tri « Meilleures ventes » donnait un
  ordre incorrect sur un chargement de page classique (mais correct via la requête AJAX) — la
  fonctionnalité de tri native de WooCommerce lit le même paramètre d'URL que celui utilisé ici et
  réinjecte son propre ordre de tri par-dessus par effet de bord, pour certaines options de tri
  seulement. Corrigé en utilisant un paramètre d'URL dédié, distinct de celui de WooCommerce,
  supprimant toute interférence entre les deux mécanismes.

### 0.4.2 — filtres actifs du catalogue

- Ajout d'une rangée de « filtres actifs » sous la barre de filtres du catalogue : un chip par
  valeur active (catégorie, couleur, taille, note, ou la plage de prix entière en un seul chip),
  chacun avec son propre bouton de suppression, plus un lien « Effacer tout ».
- Chaque chip et le lien « Effacer tout » sont de vrais liens (fonctionnent sans JavaScript,
  puisque le filtrage du chargement de page normal existait déjà), interceptés pour resynchroniser
  la barre de filtres et se soumettre en AJAX plutôt que de recharger la page.

### 0.4.1 — barre de filtres du catalogue

- Ajout d'une barre de filtres sticky au-dessus de la grille du catalogue : Catégorie, Prix,
  Couleur, Taille, Note. Construite comme un formulaire natif (fonctionne sans JavaScript, soumet
  un chargement de page classique déjà filtré côté serveur), puis progressivement améliorée en
  AJAX pour mettre à jour la grille sans rechargement de page.
- Chaque filtre ne s'affiche que s'il a réellement une donnée à proposer : pas de filtre Couleur/
  Taille tant que la boutique n'a pas l'attribut correspondant (dont le nom exact reste
  configurable, un site pouvant l'avoir nommé différemment). Le filtre Note réutilise les mêmes
  données de notation que le mécanisme natif de WooCommerce plutôt qu'un calcul maison.
- Sélectionner une catégorie depuis la barre de filtres remplace entièrement la vue en cours
  (boutique ou une autre catégorie) plutôt que de se contenter de la restreindre, pour absorber la
  rangée de raccourcis de catégorie prévue par la maquette au même endroit.
- Bug rencontré et corrigé : une première version du filtre de prix faisait doublon avec le
  filtre de prix déjà intégré nativement à WooCommerce, et ce dernier s'est révélé en défaut pour
  des produits de démonstration insérés directement en base de données (sans passer par
  l'enregistrement normal depuis l'administration, qui met à jour une table de cache interne).
  Corrigé en resynchronisant ces produits et en conservant un filtre de prix autonome, simple et
  fiable, qui se comporte à l'identique que la requête vienne d'un chargement de page normal ou
  d'une mise à jour en AJAX.

### 0.4.0 — grille du catalogue produits

- Ajout du gabarit du catalogue produits (boutique, et par extension les archives de
  catégorie/étiquette produit, WooCommerce chargeant automatiquement ce même gabarit tant qu'aucun
  gabarit de taxonomie dédié n'existe) : fil d'Ariane, titre et compteur de résultats (singulier/
  pluriel correct), grille masonry réutilisant le composant carte produit déjà en place, cette fois
  branché sur de vraies données WooCommerce plutôt que des données factices.
- Nombre de colonnes de la grille rendu configurable via un filtre WordPress dédié, prêt pour le
  futur onglet d'administration dédié aux produits, sans construire cet onglet maintenant.
- Décision technique : la rangée de tags catégorie rapides et le bouton de bascule grille/liste
  visibles dans la maquette ne sont volontairement pas construits à ce stade — le premier chevauche
  le filtre catégorie d'une prochaine version, le second n'a aucun comportement spécifié au-delà de
  la maquette elle-même.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif et des produits de
  test répartis sur plusieurs catégories : grille, fil d'Ariane, titre et compteur corrects sur la
  boutique et sur une archive de catégorie, dans les deux langues installées ; état vide traduit
  confirmé sur une catégorie sans produit.

### 0.3.6 — section newsletter de la page d'accueil

- Ajout de la septième et dernière section de la page d'accueil : un formulaire d'inscription à la
  newsletter (email + bouton), sur fond sombre, traité nativement — sans dépendance à un plugin
  tiers de newsletter, conformément aux décisions structurantes du projet.
- Nouvelle table `wp_solar_template_newsletter_subscribers` (email unique) et une classe dédiée
  (`Solar_Template\Newsletter\SubscriberRepository`) pour l'inscription/la déduplication.
- Le formulaire est soumis en AJAX (`assets/js/newsletter.js`) vers un point d'entrée
  `admin-ajax.php` protégé par un nonce, avec un retour visuel inline (succès, déjà inscrit, email
  invalide, erreur) sans rechargement de page.
- Décision technique : le formulaire newsletter déjà présent dans le pied de page (ajouté à une
  étape antérieure, markup uniquement) n'est pas branché à ce mécanisme dans cette version — il
  reste hors périmètre de cette étape, qui ne couvre que la section dédiée de la page d'accueil.
- Bug détecté et corrigé par le linter avant la mise en production : la norme de code du projet
  interdit l'opérateur ternaire court, déjà rencontré à l'étape précédente ; un besoin similaire
  a été résolu de la même façon dans le nouveau dépôt de stockage.
- Vérifié de bout en bout dans l'environnement Docker réel avec une véritable requête AJAX
  (identique à celle que le formulaire émettrait) : une inscription valide est enregistrée et
  confirmée, une deuxième inscription avec le même email est rejetée avec le message adéquat, une
  adresse invalide est rejetée avant toute écriture en base — le tout entièrement traduit selon la
  langue active du site ; inscription de test supprimée après vérification.
- **La page d'accueil du thème est désormais complète (hero, produits vedettes, catégories, notre
  histoire, témoignages, aperçu du blog, newsletter)** et prête à recevoir les prochaines pages du
  thème.

### 0.3.5 — aperçu du blog sur la page d'accueil

- Ajout de la sixième section de la page d'accueil : un aperçu des 3 derniers articles de blog
  réellement publiés, réutilisant le composant carte article déjà en place (image à la une,
  catégorie, date et durée de lecture estimée, titre, extrait, auteur avec son avatar).
- Décision technique : contrairement au hero/à l'histoire de marque, cette section lit de vraies
  données WordPress (pas de contenu éditorial de remplacement) — elle ne s'affiche pas du tout tant
  qu'aucun article n'est publié, même logique de dégradation gracieuse que les autres sections
  branchées sur des données réelles.
- La durée de lecture est estimée à partir du nombre de mots de l'article (200 mots/minute,
  arrondi au-dessus, minimum 1 minute) plutôt que saisie manuellement.
- Vérifié de bout en bout dans l'environnement Docker réel, dans les deux langues installées : un
  article de test confirme l'affichage correct (titre, date localisée, durée de lecture traduite,
  catégorie, auteur) — article de test supprimé après vérification.

### 0.3.4 — section témoignages de la page d'accueil

- Ajout de la cinquième section de la page d'accueil : une grille de trois témoignages clients
  (citation, note en étoiles, auteur), la carte du milieu inversée (fond sombre, texte clair) comme
  dans la maquette.
- Décision technique : le contenu (citations, notes, auteurs) reste éditorial — exposé via un
  filtre WordPress unique plutôt qu'un nouveau type de contenu personnalisé, la feuille de route
  du projet demandant explicitement une source de contenu « simple » à ce stade.
- Vérifié de bout en bout dans l'environnement Docker réel, dans les deux langues installées : la
  section s'affiche sans avertissement ni erreur, entièrement traduite selon la langue active du
  site, avec la carte du milieu correctement inversée.

### 0.3.3 — section « notre histoire » de la page d'accueil

- Ajout de la quatrième section de la page d'accueil : image éditoriale avec une carte statistique
  flottante (« 10+ ans d'expertise »), texte de présentation en deux paragraphes, une ligne de
  trois statistiques (clients, produits, note moyenne) et un appel à l'action.
- Décision technique : même convention que le hero — contenu entièrement éditorial via une aide
  filtrable unique (`solar_template_brand_story_config()`), aucune donnée réelle branchée. Le
  bouton pointe vers une page de slug « about » si elle existe (même aide déjà utilisée par le
  pied de page), avec repli vers l'accueil sinon.
- Vérifié de bout en bout dans l'environnement Docker réel, dans les deux langues installées : la
  section s'affiche sans avertissement ni erreur, entièrement traduite selon la langue active du
  site.

### 0.3.2 — section catégories de la page d'accueil

- Ajout de la troisième section de la page d'accueil : une grille asymétrique (une grande catégorie
  à gauche, quatre plus petites à droite) des catégories produit WooCommerce réelles, triées par
  nombre de produits (les plus fournies en premier), chacune pointant vers sa vraie page d'archive.
- La section ne s'affiche pas du tout tant qu'aucune catégorie produit peuplée n'existe (WooCommerce
  inactif ou boutique vide), même logique de dégradation gracieuse que les sections précédentes.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : deux catégories
  de test, chacune avec un produit, confirment l'affichage correct (nom, lien vers l'archive de
  catégorie) ; leur suppression fait bien disparaître la section entière — catégories et produits de
  test supprimés après vérification.

### 0.3.1 — section produits vedettes de la page d'accueil

- Ajout de la deuxième section de la page d'accueil : une grille masonry des produits WooCommerce
  réellement marqués « en vedette » depuis l'écran d'édition produit, affichés avec le composant
  carte produit déjà en place, avec effet de décalage vertical purement visuel entre les cartes.
- Contrairement au hero, cette section lit de vraies données WooCommerce (pas de contenu
  éditorial de remplacement) : prix, promotion et badge « Promo » calculés depuis le produit réel.
  La section ne s'affiche pas du tout tant qu'aucun produit n'est marqué en vedette, plutôt que
  d'afficher une grille vide.
- Bug évité avant qu'il ne cause une régression de traduction : la chaîne « Sale » du badge de
  promotion allait initialement réutiliser le même texte source que le lien de navigation
  « Promotions », qui a pourtant une traduction française différente pour le même texte anglais —
  détecté avant la mise en production car le catalogue de traduction ne distingue pas encore les
  chaînes par contexte ; corrigé en utilisant un texte source distinct (« On Sale ») pour le badge.
- Vérifié de bout en bout dans l'environnement Docker réel avec WooCommerce actif : deux produits
  de test (dont un en promotion) créés puis marqués en vedette confirment l'affichage correct
  (prix, prix barré, pourcentage de réduction calculé), et le retrait du marquage « en vedette »
  fait bien disparaître la section entière — produits de test supprimés après vérification.

### 0.3.0 — section hero de la page d'accueil

- Ajout de `front-page.php`, la page d'accueil du thème, assemblée section par section : cette
  version livre sa première section, un hero plein écran (accroche, titre en trois lignes, deux
  appels à l'action, trust badges, carte flottante « coup de cœur » avec prix et barre de
  progression de stock).
- Décision technique : tout le contenu du hero (textes, CTA, carte flottante) vient d'une aide
  filtrable unique dans `functions.php`, même convention que les aides déjà en place pour l'en-tête
  et le pied de page — aucune donnée réelle (produit vedette, image) n'est branchée à cette étape,
  volontairement hors périmètre.
- Vérifié de bout en bout dans l'environnement Docker réel, dans les deux langues installées : la
  section s'affiche sans avertissement ni erreur, entièrement traduite selon la langue active du
  site.

### 0.2.8 — compteur panier dynamique

- Le badge du nombre d'articles au panier se met désormais à jour sans rechargement de page,
  via le mécanisme natif de « fragments » de panier de WooCommerce.
- Décision technique : aucun JavaScript propre au thème n'a été écrit pour le rafraîchissement —
  le script `wc-cart-fragments` fourni par WooCommerce s'en charge déjà nativement. Bug potentiel
  détecté en lisant le code source de WooCommerce avant qu'il ne se manifeste : ce script n'est
  automatiquement chargé que par le widget « Panier » natif, que ce thème n'utilise pas ; corrigé
  en le mettant en file explicitement.
- Vérifié de bout en bout dans l'environnement Docker réel : une requête d'ajout au panier
  identique à celle du script natif de WooCommerce, simulée sur un produit de test, renvoie bien
  le fragment à jour, et un rechargement de page avec la même session confirme que le badge
  affiché reflète directement ce même compte.
- **La structure commune du thème (en-tête, navigation avec mega menu, pied de page, recherche,
  compteur panier) est désormais complète** et prête à être utilisée par les prochaines pages du
  thème.

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
