#!/usr/bin/env bash

set -euo pipefail

ROLE="${SERVICE_ROLE:-all}"

case "$ROLE" in
all | app | worker | scheduler | reverb | ssr | migrate) ;;
*)
	echo "Unsupported SERVICE_ROLE: ${ROLE}. Use 'all', 'app', 'worker', 'scheduler', 'reverb', 'ssr', or 'migrate'." >&2
	exit 1
	;;
esac

/usr/local/bin/prepare-runtime

if { [ "$ROLE" = "all" ] || [ "$ROLE" = "app" ]; } && [ "${SAIL_AUTO_MIGRATE:-true}" = "true" ]; then
	gosu www-data php artisan migrate --force --no-interaction
fi

case "$ROLE" in
all)
	exec /usr/local/bin/start-all
	;;
app)
	exec gosu www-data /usr/local/bin/start-app
	;;
worker)
	exec gosu www-data /usr/local/bin/start-worker
	;;
scheduler)
	exec gosu www-data /usr/local/bin/start-scheduler
	;;
reverb)
	exec gosu www-data /usr/local/bin/start-reverb
	;;
ssr)
	exec gosu www-data /usr/local/bin/start-ssr
	;;
migrate)
	exec gosu www-data php artisan migrate --force --no-interaction
	;;
esac
