#!/bin/sh
set -e

# Au premier démarrage, le volume monté sur json/ est vide et masque les fichiers
# de l'image. On y copie les JSON par défaut UNIQUEMENT s'ils n'existent pas déjà,
# pour ne jamais écraser les données déjà présentes (inscriptions, commandes...).
if [ -d /opt/json-seed ]; then
    for f in /opt/json-seed/.* /opt/json-seed/*; do
        [ -e "$f" ] || continue
        name=$(basename "$f")
        case "$name" in
            "." | "..") continue ;;
        esac
        if [ ! -e "/var/www/html/json/$name" ]; then
            cp -p "$f" "/var/www/html/json/$name"
        fi
    done
fi

chmod -R 775 /var/www/html/json 2>/dev/null || true

exec php -c /var/www/html/php.ini -S 0.0.0.0:"${PORT}" -t /var/www/html
