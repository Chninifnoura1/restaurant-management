# Restaurant Management – PFE Challenge EMC

Application web de gestion d'un restaurant développée avec Symfony 7.4, PHP 8.3 et MySQL 8.0.

## 1. Présentation du projet

Ce projet a pour objectif de faciliter la gestion quotidienne d'un restaurant à travers une application web permettant de gérer les ingrédients, les plats, les commandes, les achats et les stocks.

## 2. Technologies utilisées

| Technologie | Utilisation |
|---|---|
| PHP 8.3 | Langage backend |
| Symfony 7.4 | Framework |
| Doctrine ORM 3 | Gestion de la base de données |
| MySQL 8.0 | Système de gestion de base de données |
| Twig | Interface utilisateur |
| PHPUnit 11.5 | Tests automatisés |
| Docker | Environnement technique |

## 3. Fonctionnalités implémentées

- Gestion des ingrédients : ajout, modification, suppression et consultation.
- Gestion des catégories et des plats.
- Gestion des recettes et des ingrédients associés.
- Gestion des commandes.
- Vérification du stock avant le service d'une commande.
- Déduction automatique des ingrédients lors du service.
- Gestion des achats et augmentation automatique du stock.
- Gestion des pertes et diminution du stock.
- Gestion des mouvements de stock.
- Inventaire physique des ingrédients.
- Calcul des écarts entre stock théorique et stock physique.
- Historique des ajustements d'inventaire.

## 4. Architecture du projet

Le projet est organisé selon une architecture Symfony :

- `src/Controller` : gestion des requêtes HTTP.
- `src/Entity` : entités métier.
- `src/Form` : formulaires.
- `src/Repository` : accès aux données.
- `src/Service` : logique métier.
- `templates` : interfaces Twig.
- `migrations` : migrations de la base de données.
- `tests` : tests automatisés.

Le service `StockService` centralise les principales opérations liées à la gestion du stock.

## 5. Base de données

La migration initiale définit notamment les tables suivantes :

- category
- dish
- dish_ingredient
- ingredient
- loss
- order_item
- purchase
- purchase_item
- restaurant_order
- stock_movement

## 6. Tests

Les tests automatisés sont réalisés avec PHPUnit.

Commande d'exécution :

```powershell
php bin/phpunit
```

Les tests doivent être exécutés avec une base de données de test séparée de la base de développement.

## 7. Fonctionnalités à développer ou à compléter

Les évolutions envisagées comprennent :

- Tableau de bord et statistiques.
- Calcul des marges par plat.
- Gestion avancée des menus.
- Gestion des utilisateurs et des rôles.
- Interfaces dédiées à la salle et à la cuisine.
- Intégration Open Food Facts.
- Gestion des allergènes.
- Conversion normalisée des unités de mesure.

## 8. Installation

Consulter le fichier `INSTALL.md` pour les instructions d'installation et de lancement.

## 9. Décisions techniques

Les décisions métier et techniques sont détaillées dans `docs/DECISIONS.md`.

## 10. Contexte académique

Projet réalisé dans le cadre du PFE Challenge EMC – Sujet 2 : Gestion d'un restaurant, de sa carte, de sa cuisine et de ses stocks.
