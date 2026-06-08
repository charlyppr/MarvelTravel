# Serveur web intégré de PHP : pas d'Apache => plus aucun problème de MPM
FROM php:8.3-cli

WORKDIR /var/www/html

# Copier tout le projet dans le conteneur
COPY . .

# Conserver une copie des JSON par défaut, pour pré-remplir le volume au 1er démarrage
RUN cp -r json /opt/json-seed \
    && chmod -R 775 json \
    && chmod +x docker-entrypoint.sh

# Railway/Render fournissent $PORT ; 8080 par défaut en local
ENV PORT=8080
# Plusieurs workers pour gérer les requêtes simultanées (assets css/js/img en parallèle)
ENV PHP_CLI_SERVER_WORKERS=4
EXPOSE 8080

# L'entrypoint seed le volume puis lance le serveur PHP
CMD ["./docker-entrypoint.sh"]
