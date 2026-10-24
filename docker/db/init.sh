#!/bin/bash
# Runs once, when the database volume is empty: creates the roles and the
# tw2-stats database with the worlds table.
set -euo pipefail

psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" \
    -v api_password="$DB_PASSWORD" -v admin_password="$DB_ADMIN_PASSWORD" <<'SQL'
CREATE ROLE api LOGIN PASSWORD :'api_password';
CREATE ROLE "tw2-stats" LOGIN PASSWORD :'admin_password';
CREATE DATABASE "tw2-stats";
SQL

psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d tw2-stats -f /initdb/tw2-stats_db_creation.sql
psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d tw2-stats -f /initdb/grants.sql
