# Restaurant Management System

## Présentation du projet

**Restaurant Management** est une application web développée avec Symfony permettant de gérer les principales activités d'un restaurant : les plats, les ingrédients, les achats, le stock, les pertes et les commandes.

L'application dispose d'un système d'authentification avec gestion des rôles et des autorisations.

## Technologies utilisées

- PHP 8.3
- Symfony
- Doctrine ORM
- MySQL
- Twig
- Docker
- PHPUnit

## Fonctionnalités principales

- Authentification et déconnexion.
- Gestion des utilisateurs et des rôles.
- Tableau de bord.
- Gestion des plats.
- Gestion des ingrédients.
- Gestion des achats.
- Gestion du stock.
- Gestion des pertes.
- Gestion des commandes.
- Interface dédiée au personnel de cuisine.

## Installation et lancement

### 1. Cloner le projet

```bash
git clone URL_DU_REPOSITORY
cd challenge-restaurant
```

### 2. Configurer les variables d'environnement

Configurer la connexion à la base de données dans le fichier `.env.local`.

Ne jamais publier les mots de passe ou les informations sensibles.

### 3. Démarrer Docker

```bash
docker compose up -d --build
```

### 4. Exécuter les migrations

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### 5. Accéder à l'application

```text
http://localhost:8000
```

## Comptes de démonstration

Les comptes suivants sont prévus pour tester les différents rôles :

| Rôle | Adresse e-mail |
|---|---|
| Manager | admin@restaurant.com |
| Serveur | waiter@restaurant.com |
| Cuisine | kitchen@restaurant.com |

Les mots de passe de démonstration sont communiqués séparément.

**Important :** ces comptes doivent être créés dans la base de données de l'environnement utilisé pour la démonstration.

## Tests

Pour exécuter les tests automatisés :

```bash
php bin/phpunit
```

Dernier résultat obtenu pendant le développement :

**13 tests réussis et 45 assertions.**

## Auteur

Projet de fin d'études (PFE).

