#!/usr/bin/env bash
# Exports the worlds table from the tw2-stats database, for import-worlds.sh,
# into worlds.tar in the current directory: a gzipped CSV and its SHA256SUMS.
#
# Usage: export-worlds.sh
#
# Connects with the usual libpq settings (PGHOST, PGUSER, ~/.pgpass, ...) and
# only reads.
set -euo pipefail

[[ $# -eq 0 ]] || { echo "Usage: export-worlds.sh" >&2; exit 1; }
dir=$(mktemp -d "$PWD/worlds.XXXXXX")
trap 'rm -rf "$dir"' EXIT
cd "$dir"

psql -X -q -v ON_ERROR_STOP=1 -d tw2-stats -c "\\copy worlds TO PROGRAM 'gzip > worlds.csv.gz' CSV HEADER"
sha256sum worlds.csv.gz > SHA256SUMS
tar -cf ../worlds.tar worlds.csv.gz SHA256SUMS
echo "Exported the worlds to worlds.tar"
