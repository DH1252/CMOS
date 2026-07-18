#!/usr/bin/env bash

set -euo pipefail

/usr/local/bin/prepare-runtime

exec php artisan schedule:work
