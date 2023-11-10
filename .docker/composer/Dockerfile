# Utilisez l'image officielle de Composer
FROM composer:2

# Définition de l'utilisateur et du groupe (optionnel, dépend de vos besoins de permissions)
ARG UID=1000
ARG GID=1000

# Ajout du groupe `symfony` si vous avez besoin d'un groupe spécifique
RUN addgroup -g ${GID} symfony || true

# Ajout de l'utilisateur `symfony` si vous avez besoin d'un utilisateur spécifique
RUN adduser -G symfony --uid ${UID} --disabled-password --gecos "" symfony || true

# Définissez le répertoire de travail
WORKDIR /var/www/html

# Vous pouvez copier le fichier composer.json et composer.lock à l'avance si vous le souhaitez,
# ou vous pouvez monter votre code source en tant que volume dans docker-compose.yml.
# COPY composer.json composer.lock ./

# Exécuter Composer install comme une étape de construction peut pré-installer vos dépendances
# RUN composer install --prefer-dist --no-scripts --no-dev --no-autoloader && rm -rf /root/.composer

# À la fin, vous pouvez laisser le conteneur avec une commande shell ouverte ou simplement terminer la construction.
# En général, vous n'avez pas besoin d'une commande CMD ici car vous allez exécuter Composer manuellement via `docker-compose run`
# CMD ["sh"]
