#!/usr/bin/env bash
# Imports the worlds exported by export-worlds.sh into the tw2-stats database of
# the compose project, in one transaction. Fails if any of them is already
# there.
#
# Usage: import-worlds.sh worlds.tar
#
# Runs on the compose host, from the project's directory (or with
# COMPOSE_FILE set).
set -euo pipefail

[[ $# -eq 1 ]] || { echo "Usage: import-worlds.sh worlds.tar" >&2; exit 1; }
dir=$(mktemp -d)
trap 'rm -rf "$dir"' EXIT
tar -xf "$1" -C "$dir"
(cd "$dir" && sha256sum --quiet -c SHA256SUMS)

{
    echo '\o /dev/null'
    echo 'BEGIN;'
    echo 'SET ROLE "tw2-stats";'
    # The setval() below isn't atomic with nextval(), so worlds added meanwhile
    # wait until the commit
    echo 'LOCK TABLE worlds IN SHARE ROW EXCLUSIVE MODE;'
    # Its header has to name the table's columns, in their order
    echo 'COPY worlds FROM STDIN (FORMAT csv, HEADER match);'
    gzip -dc "$dir/worlds.csv.gz"
    echo '\.'
    # So worlds added later get ids after the imported ones
    echo "SELECT setval(pg_get_serial_sequence('worlds', 'id'), GREATEST(nextval(pg_get_serial_sequence('worlds', 'id')), max(id))) FROM worlds;"
    echo 'COMMIT;'
} | docker compose exec -T -u postgres db psql -X -q -v ON_ERROR_STOP=1 -d tw2-stats
echo "Imported the worlds"
