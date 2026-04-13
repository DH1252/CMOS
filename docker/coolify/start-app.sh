#!/usr/bin/env bash

set -euo pipefail

/usr/local/bin/prepare-runtime

APP_SERVER_HOST="${APP_SERVER_HOST:-0.0.0.0}"
APP_SERVER_PORT="${APP_SERVER_PORT:-${PORT:-8080}}"

exec php artisan serve --host="${APP_SERVER_HOST}" --port="${APP_SERVER_PORT}"
