# Serveur web intégré de PHP : pas d'Apache => plus aucun problème de MPM
FROM php:8.3-cli

WORKDIR /var/www/html

# Copier tout le projet dans le conteneur
COPY . .

# Droits d'écriture sur les données JSON (inscriptions, commandes, panier...)
RUN chmod -R 775 json

# Railway/Render fournissent $PORT ; 8080 par défaut en local
ENV PORT=8080
# Plusieurs workers pour gérer les requêtes simultanées (assets css/js/img en parallèle)
ENV PHP_CLI_SERVER_WORKERS=4
EXPOSE 8080

# Lancer le serveur PHP intégré, racine = dossier du projet
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t /var/www/html"]
