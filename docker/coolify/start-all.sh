#!/usr/bin/env bash

set -euo pipefail

/usr/local/bin/prepare-runtime

export PORT="${PORT:-8080}"
export APP_SERVER_HOST="${APP_SERVER_HOST:-127.0.0.1}"
export APP_SERVER_PORT="${APP_SERVER_PORT:-8000}"
export REVERB_SERVER_HOST="${REVERB_SERVER_HOST:-127.0.0.1}"
export REVERB_SERVER_PORT="${REVERB_SERVER_PORT:-8081}"

envsubst '${PORT} ${APP_SERVER_HOST} ${APP_SERVER_PORT} ${REVERB_SERVER_HOST} ${REVERB_SERVER_PORT}' \
	</etc/nginx/templates/coolify.conf.template \
	>/etc/nginx/conf.d/default.conf

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/coolify.conf
