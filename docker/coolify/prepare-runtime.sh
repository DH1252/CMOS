#!/usr/bin/env bash

set -euo pipefail

mkdir -p \
	bootstrap/cache \
	storage/app/public \
	storage/framework/cache \
	storage/framework/sessions \
	storage/framework/views \
	storage/logs

chmod -R 775 storage bootstrap/cache

rm -rf storage/framework/views/*
rm -f bootstrap/cache/*.php

php artisan view:clear >/dev/null 2>&1 || true
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan event:clear >/dev/null 2>&1 || true

if [ -e public/storage ] && [ ! -L public/storage ]; then
	rm -rf public/storage
fi

if [ ! -e public/storage ]; then
	php artisan storage:link >/dev/null 2>&1 || true
fi
