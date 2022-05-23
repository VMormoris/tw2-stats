#!bin/bash
psql -c "CREATE USER api WITH PASSWORD '${API_PASSWORD}';"
psql -c "CREATE USER \"tw2-stats\" WITH PASSWORD '${ADMIN_PASSWORD}';"
psql -c "CREATE DATABASE \"tw2-stats\";"
psql -d tw2-stats -a -f /home/db/tw2-stats_db_creation.sql
psql -d tw2-stats -c "GRANT ALL PRIVILEGES ON DATABASE \"tw2-stats\" TO \"tw2-stats\"; GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO \"tw2-stats\"; GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO \"tw2-stats\"; GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO \"tw2-stats\";"
psql -d tw2-stats -c "GRANT CONNECT ON DATABASE \"tw2-stats\" TO api; GRANT USAGE ON SCHEMA public TO api; GRANT SELECT ON ALL TABLES IN SCHEMA public TO api;"