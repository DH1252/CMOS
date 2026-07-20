#!/usr/bin/env bash

set -euo pipefail

mkdir -p \
	bootstrap/cache \
	storage/app/private \
	storage/app/public \
	storage/framework/cache \
	storage/framework/sessions \
	storage/framework/views \
	storage/logs \
	/home/www-data/.config \
	/home/www-data/.local/share

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache /home/www-data

rm -rf storage/framework/views/*
rm -f bootstrap/cache/*.php

if [ "${APP_ENV:-production}" = "production" ]; then
	gosu www-data php artisan view:cache >/dev/null 2>&1 || true
	gosu www-data php artisan config:cache >/dev/null 2>&1 || true
	gosu www-data php artisan route:cache >/dev/null 2>&1 || true
	gosu www-data php artisan event:cache >/dev/null 2>&1 || true
else
	gosu www-data php artisan view:clear >/dev/null 2>&1 || true
	gosu www-data php artisan config:clear >/dev/null 2>&1 || true
	gosu www-data php artisan route:clear >/dev/null 2>&1 || true
	gosu www-data php artisan event:clear >/dev/null 2>&1 || true
fi

if [ -e public/storage ] && [ ! -L public/storage ]; then
	rm -rf public/storage
fi

if [ ! -e public/storage ]; then
	gosu www-data php artisan storage:link >/dev/null 2>&1 || true
fi
