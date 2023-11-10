# webservice

Cloner le projet depuis l'url : https://github.com/tristan342/webservice.git

Lancer dans le terminal, à la racine du projet, la commande : **composer install**

Créer une base de données locale du nom de **webservice**.

Lancer la commande : **php bin/console d:s:u --force**

Puis :  **php bin/console doctrine:fixtures:load**



## Démarrage rapide avec Docker

Pour configurer et démarrer l'application :

```bash
# Construire et démarrer les conteneurs
docker-compose up -d --build

# Installer les dépendances avec Composer
docker-compose run composer install

# Créer la base de données
docker-compose exec php bin/console doctrine:database:create

# Appliquer les migrations
docker-compose exec php bin/console doctrine:migrations:migrate

# Charger les données de test (fixtures)
docker-compose exec php bin/console doctrine:fixtures:load
```

L'application est maintenant accessible à l'adresse [http://localhost:8080](http://localhost:8080).
