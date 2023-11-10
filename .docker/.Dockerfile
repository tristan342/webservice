FROM php:8.2-fpm-alpine

# Définition des variables d'environnement pour l'utilisateur et le groupe
ENV PHPUSER=symfony
ENV PHPGROUP=symfony

# Définition des IDs utilisateur et groupe
ENV UID=1000
ENV GID=1000

# Ajout du groupe `symfony` avec GID 1000
RUN addgroup -g ${GID} --system ${PHPGROUP}

# Ajout de l'utilisateur `symfony` avec UID 1000 et ajout à `www-data`
RUN adduser -G ${PHPGROUP} --system -D -s /bin/sh -u ${UID} ${PHPUSER}

# Création du répertoire de l'application
RUN mkdir -p /var/www/html/public

# Installation des extensions PHP nécessaires pour Symfony
RUN docker-php-ext-install pdo pdo_mysql

# Commande par défaut pour lancer PHP-FPM
CMD ["php-fpm"]

# Ajout de l'utilisateur pour Composer, identique à celui de PHP-FPM
FROM composer:2 as composer

RUN adduser -g www-data -s /bin/sh -D symfony

# Composer ne nécessite pas une commande par défaut puisqu'il est généralement
# utilisé dans des commandes `run` interactives.
