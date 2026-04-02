#!/bin/bash

echo "🛠️ Corrigiendo permisos..."
mkdir -p storage/logs
chmod -R 775 storage
chown -R www-data:www-data storage

exec "$@"