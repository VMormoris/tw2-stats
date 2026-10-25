#!/usr/bin/env bash
# Imports a world exported by export-world.sh into the tw2-stats database of
# the compose project.
#
# Usage: import-world.sh <world>.tar
#
# Runs on the compose host, from the project's directory (or with
# COMPOSE_FILE set). The world's row must already be in the worlds table. The
# website caches the list of worlds for 5 minutes.
set -euo pipefail

[[ $# -eq 1 && $1 == *.tar ]] || { echo "Usage: import-world.sh <world>.tar" >&2; exit 1; }
world=$(basename "$1" .tar)
[[ $world =~ ^[a-z0-9]{1,5}$ ]] || { echo "Invalid world id: $world" >&2; exit 1; }
dir=$(mktemp -d)
trap 'rm -rf "$dir"' EXIT
tar -xf "$1" -C "$dir"
(cd "$dir" && sha256sum --quiet -c SHA256SUMS)

sql() { docker compose exec -T -u postgres db psql -X -q -v ON_ERROR_STOP=1 -d tw2-stats "$@"; }

# Creating the partitions locks every world's tables, reads too, so it's
# committed on its own before the load, which only holds off their writes
sql -c "SET ROLE \"tw2-stats\";
    DO \$\$ BEGIN IF NOT EXISTS (SELECT FROM worlds WHERE wid = '$world') THEN RAISE EXCEPTION '$world isn''t in the worlds table'; END IF; END \$\$;
    SELECT create_world_partitions('$world');" > /dev/null

# In the order they reference each other
tables=(tribes players villages tribes_history players_history villages_history conquers tribe_changes)
{
    # Only errors
    echo '\o /dev/null'
    echo 'BEGIN;'
    # No triggers: they'd record history the export already has, and its
    # foreign keys were already checked by the world's database, which has the
    # same ones (without wid). Needs the superuser, so it comes before SET ROLE.
    echo 'SET LOCAL session_replication_role = replica;'
    echo 'SET ROLE "tw2-stats";'
    # The sequences are shared by all worlds and the setval()s below aren't
    # atomic with the dumper's nextval()s, so its writes wait until the commit
    # (reads don't). Before any of this world's partitions are locked, since the
    # dumper locks the parents first, and tribes first too.
    echo 'LOCK TABLE tribes, tribes_history, players_history, villages_history, conquers, tribe_changes IN SHARE ROW EXCLUSIVE MODE;'
    # Its dummy tribe and player are in the export too
    echo "DELETE FROM players_$world;"
    echo "DELETE FROM tribes_$world;"
    for table in "${tables[@]}"; do
        file=$dir/$table.csv.gz
        echo "ALTER TABLE ${table}_$world ALTER wid SET DEFAULT '$world';"
        echo "COPY ${table}_$world ($(gzip -dc "$file" | head -1)) FROM STDIN CSV HEADER;"
        gzip -dc "$file"
        echo '\.'
        echo "ALTER TABLE ${table}_$world ALTER wid DROP DEFAULT;"
        echo "ANALYZE ${table}_$world;"
    done
    # So the dumper's rows get ids after the imported ones
    for table in "${tables[@]:3}"; do
        echo "SELECT setval(pg_get_serial_sequence('$table', 'id'), GREATEST(nextval(pg_get_serial_sequence('$table', 'id')), max(id))) FROM ${table}_$world;"
    done
    echo 'COMMIT;'
} | sql || {
    sql -c "SET ROLE \"tw2-stats\"; SELECT drop_world_partitions('$world');" > /dev/null
    echo "Import failed, removed $world's partitions" >&2
    exit 1
}
echo "Imported $world"
