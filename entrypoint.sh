#!/bin/bash

# Esperar o banco estar pronto (opcional: útil com containers do banco)
# ./wait-for-it.sh db:5432 -- php artisan migrate --force
echo "🔁 Rodando migrations..."
php artisan migrate --force

echo "🚀 Iniciando servidor Laravel..."
php artisan serve --host=0.0.0.0 --port=8000

# echo "📬 Iniciando queue worker..."
# php artisan queue:work 'database' --sleep=10 --daemon --quiet --timeout=90 --delay=3 --tries=3 --queue='default'
