#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
LOG_FILE="${REVERB_LOG_FILE:-${ROOT_DIR}/storage/logs/reverb.log}"
HOST="${REVERB_SERVER_HOST:-0.0.0.0}"
PORT="${REVERB_SERVER_PORT:-${REVERB_PORT:-8080}}"
DEBUG_VALUE="${REVERB_DEBUG:-false}"

mkdir -p "$(dirname -- "$LOG_FILE")"

COMMAND=(php artisan reverb:start --host="$HOST" --port="$PORT")

case "${DEBUG_VALUE,,}" in
1 | true | yes | on)
	COMMAND+=(--debug)
	;;
esac

printf '\n[%s] Starting Reverb host=%s port=%s debug=%s\n' \
	"$(date '+%Y-%m-%d %H:%M:%S')" \
	"$HOST" \
	"$PORT" \
	"$DEBUG_VALUE" | tee -a "$LOG_FILE"

"${COMMAND[@]}" 2>&1 | tee -a "$LOG_FILE"
