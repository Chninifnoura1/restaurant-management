# Architecture technique

## Style
Monolithe modulaire Symfony 7.4, PHP >= 8.2, Doctrine ORM et MySQL 8. Cette option limite la complexité opérationnelle du PFE tout en séparant nettement le domaine métier.

## Couches
- **Controller** : HTTP, authentification, validation d'entrée et réponse. Aucune règle de stock complexe.
- **Form/DTO** : validation et transformation des données saisies.
- **Service** : cas d'usage transactionnels (commande, consommation, achat, inventaire, coût recette).
- **Entity** : état et invariants locaux du domaine.
- **Repository** : requêtes de lecture et agrégations persistées.
- **Twig/JavaScript** : interface Bootstrap responsive ; Chart.js pour les statistiques.
- **Infrastructure** : client HTTP Open Food Facts, cache, configuration.

## Modules
Catalog (Category, Dish, Menu), Recipe (Recipe, Ingredient, Preparation), Stock (StockMovement, Inventory), Purchasing (Supplier, Purchase), Ordering (RestaurantOrder, OrderItem), Identity (User, Role), Integration (ProductReference).

## Organisation cible
```text
src/
  Controller/{Security,Catalog,Recipe,Stock,Purchasing,Ordering,Kitchen,Dashboard}/
  Entity/
  Enum/
  Form/
  Repository/
  Security/
  Service/{Stock,Ordering,Purchasing,Recipe,Integration}/
templates/{base,security,catalog,stock,orders,kitchen,dashboard}/
assets/{styles,controllers}/
migrations/
tests/{Unit,Functional}/
docs/
```

## Transactions et concurrence
Les opérations de consommation et réception sont exécutées dans une transaction DB. Les ingrédients sont verrouillés pessimiste (`SELECT ... FOR UPDATE`) dans un ordre déterministe pour réduire les interblocages. La clé d'idempotence empêche la double consommation. Le service vérifie à nouveau la disponibilité au moment de l'opération : un contrôle effectué lors de la prise de commande ne suffit pas face à la concurrence.

## Unités et précision
Stockage normalisé par dimension : masse en grammes, volume en millilitres, pièce en unités. Les quantités sont des DECIMAL ; les calculs critiques évitent les floats binaires (valeurs décimales ou objets dédiés). Une conversion masse-volume exige une densité documentée.

## Sécurité
Rôles ROLE_MANAGER, ROLE_WAITER, ROLE_KITCHEN ; contrôles au niveau des routes et des actions sensibles. Les validations côté serveur restent obligatoires même si l'interface masque les actions.

## API externe
Open Food Facts via HttpClient Symfony, timeout, cache et gestion des erreurs. Une donnée allergène absente est affichée « information inconnue ». La fiche externe reste une source informative à confirmer, pas une certification.
