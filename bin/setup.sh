#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env. Set the database password and APP_KEY before continuing."
fi
php bin/make-images.php
php database/generate_seed.php
echo "Import database/schema.sql and database/seed.sql, then run: php -S 127.0.0.1:8080 router.php"
