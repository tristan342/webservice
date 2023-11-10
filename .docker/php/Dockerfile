# Utiliser une image PHP avec FPM basée sur Alpine pour une image plus légère
FROM php:8.2-fpm-alpine

# Définir des arguments pour UID et GID pour un contrôle optionnel sur les permissions des fichiers
ARG UID=1000
ARG GID=1000

# Installer les extensions et les dépendances nécessaires à Symfony
RUN docker-php-ext-install pdo pdo_mysql

# Installer Composer dans le conteneur PHP pour faciliter la gestion des dépendances
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Ajouter un groupe `symfony` si vous avez besoin d'un groupe spécifique
RUN addgroup -g ${GID} symfony || true

# Ajouter un utilisateur `symfony` si vous avez besoin d'un utilisateur spécifique
RUN adduser -G symfony --uid ${UID} --disabled-password --gecos "" symfony || true

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier le code source dans le conteneur, vous pouvez commenter cela si vous utilisez des volumes à la place
# COPY . /var/www/html

# Changer la propriété du répertoire pour l'utilisateur `symfony`
RUN chown -R symfony:symfony /var/www/html

# Exposer le port sur lequel FPM écoute, par défaut c'est le port 9000
EXPOSE 9000

# Utiliser l'utilisateur `symfony` pour exécuter le processus PHP-FPM
USER symfony

# Lancer PHP-FPM lors du démarrage du conteneur
CMD ["php-fpm"]
