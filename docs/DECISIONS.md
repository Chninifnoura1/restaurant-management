# Décisions techniques et métier

Projet : Restaurant Management – PFE Challenge EMC

Date : 15/11/2026

Ce document présente les principales décisions métier et techniques du projet.

## 1. Coût des plats

### Décision

Le coût d'un plat déjà vendu ne doit pas être recalculé rétroactivement lorsqu'un prix d'achat change.

### État actuel

L'entité OrderItem conserve le prix de vente unitaire de la ligne de commande.

Le suivi historique complet du coût matière reste à développer.

## 2. Gestion des pertes

### Décision

Chaque perte doit être enregistrée avec :

- L'ingrédient concerné.
- La quantité perdue.
- Le motif.
- La date d'enregistrement.

### État actuel

La méthode `StockService::registerLoss()` :

- Vérifie la quantité demandée.
- Vérifie la disponibilité du stock.
- Verrouille l'ingrédient.
- Diminue le stock.
- Enregistre la perte.
- Crée un mouvement `OUT_LOSS`.

## 3. Gestion des menus

### Décision

Un menu composé de plusieurs choix doit être distingué d'un plat individuel.

### État actuel

Les entités Dish et Category permettent de gérer les plats et leurs catégories.

L'entité DishIngredient permet d'associer les ingrédients aux plats.

La gestion complète des menus composés reste à développer.

## 4. Gestion du stock

### Décision

Le stock d'un ingrédient ne doit jamais devenir négatif.

### État actuel

Les opérations de service des commandes et d'enregistrement des pertes vérifient la disponibilité du stock.

Les opérations sensibles utilisent des transactions et des verrouillages pessimistes.

## 5. Inventaire physique

### Décision

Le stock physique peut être différent du stock théorique.

L'inventaire permet de :

- Sélectionner un ingrédient.
- Saisir la quantité réellement comptée.
- Calculer l'écart.
- Justifier l'ajustement.
- Conserver un historique.

### État actuel

La méthode `StockService::adjustInventory()` met à jour le stock physique et enregistre un mouvement `INVENTORY_ADJUSTMENT`.

Les écarts positifs, négatifs et nuls sont couverts par les tests.

## 6. Unités de mesure

### Décision

Les quantités doivent être cohérentes entre les achats, les recettes, les pertes et les stocks.

### État actuel

Les quantités sont enregistrées avec trois décimales et chaque ingrédient possède une unité.

La conversion automatique entre kg, g, litres et millilitres reste à développer.

## 7. Déduction du stock lors du service

### Décision

Le stock est déduit lorsque la commande est effectivement servie.

### État actuel

La méthode `StockService::serveOrder()` :

- Vérifie la commande.
- Vérifie la disponibilité des ingrédients.
- Diminue les quantités en stock.
- Enregistre les mouvements `OUT_ORDER`.
- Met à jour l'état de la commande.

## 8. Traçabilité des mouvements

Les principaux types de mouvements sont :

| Type | Signification |
|---|---|
| IN_PURCHASE | Entrée après achat |
| OUT_ORDER | Sortie après service |
| OUT_LOSS | Sortie après perte |
| INVENTORY_ADJUSTMENT | Ajustement après inventaire |

Chaque mouvement est associé à un ingrédient, une quantité et une date.

## 9. Gestion des allergènes

### Décision

Une information allergène inconnue ne doit jamais être présentée comme une absence d'allergène.

### État actuel

La gestion complète des allergènes reste à développer.

## 10. Architecture technique

Le projet utilise une architecture monolithique Symfony.

Les responsabilités sont séparées entre :

- Controllers.
- Entities.
- Repositories.
- Forms.
- Services.
- Templates Twig.

Le service StockService centralise les opérations métier liées aux stocks.

La base de données utilisée est MySQL 8.0 avec Doctrine ORM.

## 11. Configuration et sécurité

Les informations de connexion et les secrets doivent être conservés dans les fichiers d'environnement locaux.

Les secrets réels ne doivent pas être publiés dans le dépôt Git.

La gestion complète des comptes, des rôles et des autorisations reste à compléter ou à vérifier.
