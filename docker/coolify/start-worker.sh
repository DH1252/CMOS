#!/usr/bin/env bash

set -euo pipefail

/usr/local/bin/prepare-runtime

exec php artisan queue:work \
	--queue="${QUEUE_WORKER_QUEUE:-default}" \
	--tries="${QUEUE_WORKER_TRIES:-3}" \
	--timeout="${QUEUE_WORKER_TIMEOUT:-90}" \
	--sleep="${QUEUE_WORKER_SLEEP:-3}" \
	--memory="${QUEUE_WORKER_MEMORY:-256}"
