#!/usr/bin/env bash

set -euo pipefail

project_dir="${1:-$(pwd)}"
compose_cmd="docker compose --env-file ${project_dir}/.env -f ${project_dir}/compose.production.yaml"

cd "$project_dir"

$compose_cmd pull
$compose_cmd up -d mysql

until $compose_cmd exec -T mysql sh -lc 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mysqladmin ping -h127.0.0.1 -uroot --silent'; do
    sleep 3
done

$compose_cmd up -d web worker scheduler
$compose_cmd exec -T -u www-data web php artisan migrate --force
$compose_cmd exec -T -u www-data web php artisan config:cache
$compose_cmd exec -T -u www-data web php artisan route:cache
$compose_cmd exec -T -u www-data web php artisan view:cache
