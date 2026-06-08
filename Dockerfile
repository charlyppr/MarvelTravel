# Image PHP + Apache : disque inscriptible, sessions et écriture JSON fonctionnent
FROM php:8.3-apache

# Activer la réécriture d'URL (utile si besoin plus tard)
RUN a2enmod rewrite

# Copier tout le projet dans le docroot d'Apache
COPY . /var/www/html/

# Donner à Apache les droits d'écriture sur les données JSON (inscriptions, commandes, panier...)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/json

# Apache écoute sur le port fourni par l'hébergeur (Railway/Render fixent $PORT), sinon 80
ENV PORT=80
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

CMD ["apache2-foreground"]
