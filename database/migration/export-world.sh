#!/usr/bin/env bash
# Exports a world from its own database, for import-world.sh, into <world>.tar
# in the current directory: a gzipped CSV per table, with only the history
# rows that changed, and their SHA256SUMS.
#
# Usage: export-world.sh <world>
#
# Connects with the usual libpq settings (PGHOST, PGUSER, ~/.pgpass, ...) and
# only reads.
set -euo pipefail

[[ $# -eq 1 && $1 =~ ^[a-z0-9]{1,5}$ ]] || { echo "Usage: export-world.sh <world>" >&2; exit 1; }
world=$1
dir=$(mktemp -d "$PWD/$world.XXXXXX")
trap 'rm -rf "$dir"' EXIT
# The files are written in psql's directory, so its path isn't in the commands
cd "$dir"

csv() { # <name> <query>
    echo "\\copy ($2) TO PROGRAM 'gzip > $1.csv.gz' CSV HEADER"
}
# The last row of an hour that was recorded twice, then only the rows that
# differ from the entity's previous one
history() { # <table> <entity column> <tracked columns>
    csv "$1" "SELECT id, $2, $3, timestamp FROM (SELECT *, lag(ROW($3)) OVER (PARTITION BY $2 ORDER BY timestamp) AS previous FROM (SELECT DISTINCT ON ($2, timestamp) * FROM $1 ORDER BY $2, timestamp, id DESC) AS hours) AS h WHERE ROW($3) IS DISTINCT FROM previous"
}

# Every table from the same snapshot of the world
{
    echo 'BEGIN ISOLATION LEVEL REPEATABLE READ READ ONLY;'
    for table in tribes players villages conquers tribe_changes; do
        csv "$table" "SELECT * FROM $table"
    done
    history tribes_history tid 'members, points, offbash, defbash, totalbash, rankno, villages, vp'
    history players_history pid 'tid, villages, points, offbash, defbash, totalbash, rankno, vp'
    history villages_history vid 'name, nname, pid, tid, points'
    echo 'COMMIT;'
} | psql -X -q -v ON_ERROR_STOP=1 -d "$world"

sha256sum ./*.csv.gz > SHA256SUMS
tar -cf "../$world.tar" ./*.csv.gz SHA256SUMS
echo "Exported $world to $world.tar"
