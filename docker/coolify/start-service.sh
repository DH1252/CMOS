#!/usr/bin/env bash

set -euo pipefail

case "${SERVICE_ROLE:-all}" in
all)
	exec /usr/local/bin/start-all
	;;
app)
	exec /usr/local/bin/start-app
	;;
worker)
	exec /usr/local/bin/start-worker
	;;
reverb)
	exec /usr/local/bin/start-reverb
	;;
ssr)
	exec /usr/local/bin/start-ssr
	;;
*)
	echo "Unsupported SERVICE_ROLE: ${SERVICE_ROLE}. Use 'all', 'app', 'worker', 'reverb', or 'ssr'." >&2
	exit 1
	;;
esac
