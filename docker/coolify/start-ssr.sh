#!/usr/bin/env bash

set -euo pipefail

SSR_RUNTIME="${INERTIA_SSR_RUNTIME:-node}"

exec php artisan inertia:start-ssr --runtime="${SSR_RUNTIME}"
