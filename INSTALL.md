# Guide d'installation – Restaurant Management

## 1. Prérequis

- Windows 10 ou Windows 11.
- Docker Desktop.
- PHP 8.3.
- Composer 2.
- Git.

## 2. Récupération du projet

```powershell
git clone https://github.com/Chninifnoura1/restaurant-management.git
cd restaurant-management
```

Si le projet est déjà présent sur votre ordinateur, ouvrez directement son dossier dans PowerShell.

## 3. Installation des dépendances

Dans le dossier du projet, exécuter :

```powershell
composer install
```

## 4. Configuration de l'environnement

Copier le fichier d'exemple :

```powershell
Copy-Item .env.example .env.local
```

Pour une exécution PHP depuis un conteneur connecté au réseau Docker, configurer la connexion à MySQL dans `.env.local` :

```dotenv
APP_ENV=dev
APP_SECRET=change-me-in-local-env
DATABASE_URL="mysql://app:change-me@database:3306/restaurant?serverVersion=8.0.37&charset=utf8mb4"
```

Depuis PHP exécuté directement sur Windows, utiliser `127.0.0.1` au lieu de `database`.

Ne pas publier les secrets réels dans Git.

## 5. Démarrage de MySQL

Démarrer le service de base de données :

```powershell
docker compose up -d database
```

Vérifier son état :

```powershell
docker compose ps
```

Configuration locale :

- Base de données : restaurant
- Utilisateur : app
- Mot de passe : change-me
- Port : 3306

## 6. Construction de l'image PHP

Construire l'image PHP :

```powershell
docker build -t restaurant-php:8.3 .
```

Le Dockerfile fournit PHP 8.3 CLI avec l'extension PDO MySQL.

Le fichier Docker Compose actuel démarre uniquement MySQL. Le serveur Symfony est lancé séparément.

## 7. Vérification de Symfony

Depuis le conteneur PHP :

```powershell
docker run --rm --network challenge-restaurant_default -v "${PWD}:/app" -w /app restaurant-php:8.3 php bin/console about
```

Vérifier les migrations :

```powershell
docker run --rm --network challenge-restaurant_default -v "${PWD}:/app" -w /app restaurant-php:8.3 php bin/console doctrine:migrations:status
```

## 8. Exécution des migrations

Appliquer les migrations :

```powershell
docker run --rm --network challenge-restaurant_default -v "${PWD}:/app" -w /app restaurant-php:8.3 php bin/console doctrine:migrations:migrate
```

Vérifier les migrations avant de les appliquer.

Ne pas supprimer la base de données contenant les données de développement.

## 9. Lancement du serveur

Lancer le serveur PHP :

```powershell
docker run --rm --network challenge-restaurant_default -p 8000:8000 -v "${PWD}:/app" -w /app restaurant-php:8.3 php -S 0.0.0.0:8000 -t public
```

Ouvrir le navigateur :

http://localhost:8000

Si le port 8000 est déjà utilisé, vérifier les conteneurs actifs :

```powershell
docker ps
```

Ne pas démarrer un deuxième serveur sur le même port.

## 10. Exécution des tests

Commande :

```powershell
docker run --rm --network challenge-restaurant_default -v "${PWD}:/app" -w /app restaurant-php:8.3 php bin/phpunit
```

Les tests doivent utiliser une base de données dédiée, par exemple `restaurant_test`, et jamais la base de développement.

## 11. Dépannage

### Problème de connexion à MySQL

Vérifier que le conteneur fonctionne :

```powershell
docker compose ps
```

Vérifier également le nom d'hôte dans `DATABASE_URL`.

### Problème de port 8000

Vérifier les conteneurs :

```powershell
docker ps
```

### Problème de dépendances

Exécuter :

```powershell
composer install
```

## 12. Architecture Docker actuelle

L'environnement Docker contient :

- Un service MySQL 8.0.
- Une image PHP 8.3 CLI.
- Un serveur PHP lancé séparément.

Le projet ne possède pas encore un service Symfony intégré directement dans Docker Compose.
