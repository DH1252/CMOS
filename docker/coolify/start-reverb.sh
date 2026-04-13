#!/usr/bin/env bash

set -euo pipefail

/usr/local/bin/prepare-runtime

exec bash /app/reverb-start.sh
