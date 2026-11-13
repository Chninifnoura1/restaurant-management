# Installation

## État
Ce guide décrit la cible d'installation. Le projet fourni à ce stade contient la conception et la configuration initiale ; le démarrage Symfony ne peut pas encore être certifié car Composer et Docker ne sont pas disponibles dans l'environnement d'exécution utilisé pour cette première étape.

## Prérequis
- PHP 8.2 ou supérieur avec extensions `ctype`, `iconv`, `intl`, `pdo_mysql`, `xml`, `mbstring`.
- Composer 2.
- MySQL 8, ou Docker Engine avec Docker Compose.

## Installation locale
1. Cloner le dépôt.
2. Exécuter `composer install`.
3. Copier `.env.example` vers `.env`.
4. Renseigner `DATABASE_URL` avec les identifiants de la base locale.
5. Exécuter `php bin/console doctrine:database:create`.
6. Après création des migrations : `php bin/console doctrine:migrations:migrate`.
7. Après création des fixtures : `php bin/console doctrine:fixtures:load`.
8. Lancer `symfony server:start` ou `php -S 127.0.0.1:8000 -t public`.

## Installation Docker
La configuration Compose sera ajoutée après validation du modèle et des services. La commande cible est `docker compose up --build -d`.

## Comptes de démonstration
Aucun compte n'est déclaré à ce stade. Les comptes seront créés par fixtures ; les mots de passe de démonstration seront documentés ici uniquement après implémentation et vérification.

## Vérifications
```bash
php bin/console about
php bin/console doctrine:migrations:status
php bin/phpunit
```
