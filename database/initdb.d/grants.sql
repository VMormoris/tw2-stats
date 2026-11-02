--- Privileges for the website (read-only) and the dumper (read/write) on the current database
-- Grants on a partitioned table cover access to its partitions through it, so
-- partitions added later need none
GRANT USAGE ON SCHEMA public TO api, "tw2-stats";
GRANT SELECT ON ALL TABLES IN SCHEMA public TO api;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO "tw2-stats";
GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA public TO "tw2-stats";
