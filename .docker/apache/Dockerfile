# Utilisez l'image de base officielle d'Apache avec PHP
FROM php:8.2-apache

# Installez les extensions PHP requises par Symfony
RUN docker-php-ext-install pdo pdo_mysql

# Activez mod_rewrite pour les URL réécrites
RUN a2enmod rewrite

# Copiez les fichiers de configuration virtuelle
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Définissez les variables d'environnement pour Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Mettez à jour le DocumentRoot d'Apache
# RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
# RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copiez votre code source dans le conteneur
COPY . /var/www/html

# Donnez la propriété du répertoire à l'utilisateur www-data
RUN chown -R www-data:www-data /var/www/html

# Exposez le port 80 pour accéder à Apache
EXPOSE 80

# Lancez Apache en arrière-plan
CMD ["apache2-foreground"]
