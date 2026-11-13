# Analyse fonctionnelle

## Présentation
Le système centralise l'activité d'un restaurant de 60 couverts : carte et menus, recettes et préparations, prise de commande en salle, suivi cuisine, approvisionnement et stocks.

## Problématique
Le papier et le suivi visuel des stocks provoquent des pertes d'information, des ruptures en service, du gaspillage et une méconnaissance du coût réel des plats.

## Objectifs
1. Fiabiliser la transmission salle-cuisine.
2. Connaître la disponibilité réelle des plats à partir des composants de recette.
3. Décrémenter le stock au moment métier convenu (service effectif).
4. Tracer achats, pertes, corrections et inventaires.
5. Calculer coût matière et marge brute avec des prix d'achat historisés.
6. Fournir au gérant des indicateurs fiables.

## Acteurs
- **Gérant** : paramètre la carte, les utilisateurs, les fournisseurs, achats, stocks, inventaires et consulte les indicateurs.
- **Serveur** : crée et suit les commandes, affecte une table et transmet les demandes.
- **Cuisine** : consulte le ticket, démarre la préparation, signale un incident et marque les plats prêts/servis selon le workflow autorisé.

## Besoins fonctionnels
- CRUD/archivage des catégories, plats, ingrédients, recettes, préparations et fournisseurs.
- Composition de recettes avec quantités et unités normalisées.
- Calcul du coût matière, marge brute et taux de marge.
- Création de commandes multi-plats, quantités, table et serveur.
- Workflow de commande et statut indépendant par ligne/plat.
- Entrées d'achat à réception, pertes motivées, ajustements et inventaires.
- Historique immuable des mouvements de stock.
- Alertes sous seuil et indisponibilité des plats non réalisables.
- Authentification, autorisations par rôle.
- Recherche Open Food Facts et affichage prudent des allergènes.

## Besoins non fonctionnels
- Cohérence transactionnelle et absence de stock négatif.
- Traçabilité des opérations sensibles.
- Sécurité des mots de passe et contrôle d'accès côté serveur.
- Interface responsive, utilisable sur écran de cuisine et poste de gestion.
- Montants en DECIMAL et quantités décimales à précision maîtrisée.
- Tests reproductibles, configuration sans secrets committés, installation documentée.

## Cas d'utilisation
Voir `USE_CASES.md`.

## Règles métier
- Une commande ne peut contenir qu'un plat actif et disponible au moment de sa validation.
- La disponibilité est calculée en agrégeant les besoins des recettes et préparations.
- Le stock n'est jamais négatif ; une sortie est refusée si le disponible est insuffisant.
- La sortie matière se fait à l'événement « servi », une seule fois par ligne de commande.
- L'opération complète est atomique : toutes les lignes de consommation ou aucune.
- Une commande annulée ne consomme rien ; une ligne déjà servie ne peut pas être annulée rétroactivement sans mouvement correctif tracé.
- Toute perte porte un motif, une quantité, une date et un auteur.
- Toute réception d'achat crée un mouvement d'entrée et conserve le prix d'achat.
- Les conversions ne sont permises qu'entre unités de même dimension (masse, volume, pièce) ; aucune conversion masse-volume sans densité explicite.
- Une donnée allergène absente reste « inconnue », jamais « sans allergène ».

## Périmètre
Le périmètre prioritaire comprend le modèle de données, le CRUD, recettes, stocks, commandes, règles critiques, tests, rôles et écran cuisine. Open Food Facts et Docker sont des niveaux ultérieurs. La comptabilité complète, paie, réservation en ligne et livraison externe sont hors périmètre initial.
