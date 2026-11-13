# Restaurant Management — PFE Challenge EMC

Application de gestion d'un restaurant (carte, cuisine, commandes et stocks), conçue pour Symfony 7.4 et MySQL 8.

## État du projet
- [x] Analyse du sujet officiel et cadrage fonctionnel
- [x] Conception initiale : MCD, MLD, cas d'utilisation, classes, séquences
- [x] Décisions métier documentées
- [ ] Squelette Symfony installé avec Composer
- [ ] Entités Doctrine, migrations et fixtures
- [ ] Services métier et tests
- [ ] Interfaces, sécurité, API Open Food Facts et Docker

Les cases ne seront cochées qu'après vérification effective.

## Architecture
Application monolithique Symfony en couches : contrôleurs minces, services métier transactionnels, entités Doctrine, repositories dédiés, formulaires Symfony et vues Twig/Bootstrap.

## Prérequis
PHP >= 8.2, Composer 2, MySQL 8 et Docker Compose (recommandé). Voir [INSTALL.md](INSTALL.md).

## Démarrage
Le squelette applicatif est préparé dans `composer.json`, mais l'installation des dépendances n'a pas encore été exécutée dans l'environnement de travail. Après installation de Composer :
```bash
composer install
cp .env.example .env
# Configurer DATABASE_URL, puis :
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

## Documentation
Voir `docs/` pour l'analyse, les modèles, les diagrammes et les décisions.
