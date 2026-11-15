#!/bin/bash
# Runs once, when the database volume is empty: creates the roles and the
# tw2-stats database with its schema.
set -euo pipefail

psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" \
    -v api_password="$DB_PASSWORD" -v admin_password="$DB_ADMIN_PASSWORD" <<'SQL'
CREATE ROLE api LOGIN PASSWORD :'api_password';
CREATE ROLE "tw2-stats" LOGIN PASSWORD :'admin_password';
CREATE DATABASE "tw2-stats" OWNER "tw2-stats";
SQL

# The dumper's role owns the tables, since adding a world's partitions needs
# the partitioned tables' owner
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d tw2-stats <<'SQL'
SET ROLE "tw2-stats";
\i /initdb/schema.sql
\i /initdb/grants.sql
SQL
