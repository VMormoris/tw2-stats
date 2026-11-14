/**
* Schema of the tw2-stats database, applied to an empty database.
*
* Every world's data lives in the same tables, partitioned by the world's id
* (wid). create_world_partitions() adds a world's partitions; ids from the game
* are only unique within a world, so rows are identified by (wid, id).
*
* The *_history tables only store changes: the triggers add a row when a
* tracked value differs from the current one, so the value at any time T is the
* last history row at or before T.
*/

--- Unsigned int type for 2 byte integer
CREATE DOMAIN uint2 AS SMALLINT CHECK(VALUE>=0);

--- Unsigned int type for 4 byte integer
CREATE DOMAIN uint4 AS INTEGER CHECK(VALUE>=0);

--- Unsigned int type for 8 byte integer
CREATE DOMAIN uint8 AS BIGINT CHECK(VALUE>=0);

--- Enumaration for win condition (Using an enum for potetianally new conditions)
CREATE TYPE win_codition AS ENUM
(
    'Domination',
    'VP'
);

--- Enumaration for activity status for different in game settings
CREATE TYPE activity_t AS ENUM
(
    'Active',
    'Inactive'
);

--- Table that hold records with general details for all the world we are running
CREATE TABLE worlds
(
    "id" BIGSERIAL,--- ID for record (only for internal use)
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
    "name" VARCHAR(128) NOT NULL,--- World's name in game
    "server" VARCHAR(4) NOT NULL,--- World's server (aka country subdomain)
    "url" VARCHAR(29) NOT NULL,--- URL for game connection
    "win_condition" win_codition NOT NULL,--- World's win condition
    "win_ammount" uint2 NOT NULL,--- Number to met for the win condition (% if domination just VP in a VP world)
    "tribes" uint4 NOT NULL,--- Number of active (aka not disband) tribes
    "players" uint4 NOT NULL,--- Number of players on world
    "villages" uint8 NOT NULL,--- Number of villages
    "finished" BOOLEAN NOT NULL,--- Flag for whether the world is finished or not
    "running" BOOLEAN NOT NULL,--- Flag for whether the tool running for this world
    "moral" activity_t NOT NULL,--- Flag for whether moral active or inactive on this world
    "relocation" activity_t NOT NULL,--- Flag for relocation whether active or inactive on this world
    "night_bonus" activity_t NOT NULL,--- Flag for whether night bonus is active or inactive on this world
    "time_offset" SMALLINT NOT NULL,--- Time offset from (aka +1 GMT etc)
    "start" DATE NOT NULL,--- Date of when the world started
    "end" DATE,--- Date of when the world ended
    "night_start" TIME WITHOUT TIME ZONE DEFAULT '00:00:00',--- Time of when night bonus start on this world
    "night_end" TIME WITHOUT TIME ZONE DEFAULT '00:00:00',--- Time of when night bonus ends on this world
    PRIMARY KEY("id"),
    UNIQUE("wid")
);

--- Tribes' table declaration
CREATE TABLE tribes
(
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
    "id" uint8,--- Tribe's id on tw2
    "name" VARCHAR(32) NOT NULL,--- Tribe's name on tw2
    "nname" VARCHAR(32) NOT NULL,--- Tribe's name in normalize form for searching
    "tag" VARCHAR(3) NOT NULL,--- Tribe's short name on tw2
    "active" BOOLEAN NOT NULL,--- If the tribe still exist this should be true

    -- Also storing the current values here for not needing additional search on history
    "members" uint2 NOT NULL,--- Νumber of members in tribe
    "points" uint8 NOT NULL,--- Tribe's points
    "offbash" uint8 NOT NULL,--- Tribe's offensive bash points
    "defbash" uint8 NOT NULL,--- Tribe's defensive bash points
    "totalbash" uint8 NOT NULL,--- Tribe's total bash points
    "rankno" uint4 NOT NULL,--- Tribe's rank number
    "villages" uint4 NOT NULL,--- Tribe's total number of villages
    "vp" uint4,--- Tribe's total victory points
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,--- Last dump the tribe was in
    PRIMARY KEY("wid", "id"),
    FOREIGN KEY("wid") REFERENCES worlds("wid")
    -- Although name and tag are supposed to be unique I don't what happens if a tribe is disband (Does name and tag are still occupied? prob. not)
) PARTITION BY LIST ("wid");

/**
* Tribes' history table decleration
*/
CREATE TABLE tribes_history
(
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
    "id" BIGSERIAL,--- Internal id of record
    "tid" uint8,--- Tribe's id on tw2
    "members" uint2 NOT NULL,--- Νumber of members in tribe
    "points" uint8 NOT NULL,--- Tribe's points
    "offbash" uint8 NOT NULL,--- Tribe's offensive bash points
    "defbash" uint8 NOT NULL,--- Tribe's defensive bash points
    "totalbash" uint8 NOT NULL,--- Tribe's total bash points
    "rankno" uint4 NOT NULL,--- Tribe's rank number
    "villages" uint4 NOT NULL,--- Tribe's total number of villages
    "vp" uint4,--- Tribe's total victory points
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("wid", "id"),
    UNIQUE("wid", "tid", "timestamp"),
    FOREIGN KEY("wid", "tid") REFERENCES tribes("wid", "id")
) PARTITION BY LIST ("wid");

--- Players' table declaration
CREATE TABLE players
(
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
    "id" uint8,--- Player's id on tw2
    "name" VARCHAR(24),--- Player's name on tw2
    "nname" VARCHAR(24),--- Player's name in normalize form for searching

    -- Also storing the current values here for not needing additional search on history
    "tid" uint8 NOT NULL,--- Player's tribe id on tw2
    "villages" uint4 NOT NULL,--- Player's number of villages
    "points" uint8 NOT NULL,--- Player's total points
    "offbash" uint8 NOT NULL,--- Player's offensive bash points
    "defbash" uint8 NOT NULL,--- Player's defensive bash points
    "totalbash" uint8 NOT NULL,--- Player's total bash points
	"rankno" uint4 NOT NULL,
    "vp" uint4,--- Player's victory points
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,--- Last dump the player was in
    PRIMARY KEY("wid", "id"),
    UNIQUE("wid", "name"),
    FOREIGN KEY("wid") REFERENCES worlds("wid"),
	FOREIGN KEY("wid", "tid") REFERENCES tribes("wid", "id")
) PARTITION BY LIST ("wid");

/**
* Players' history table declaration
*/
CREATE TABLE players_history
(
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
    "id" BIGSERIAL,--- Internal id for record
    "pid" uint8,--- Player's id on tw2
    "tid" uint8 NOT NULL,--- Player's new tribe id on tw2
    "villages" uint4 NOT NULL,--- Player's number of villages
    "points" uint8 NOT NULL,--- Player's total points
    "offbash" uint8 NOT NULL,--- Player's offensive bash points
    "defbash" uint8 NOT NULL,--- Player's defensive bash points
    "totalbash" uint8 NOT NULL,--- Player's total bash points
	"rankno" uint4 NOT NULL,
    "vp" uint4,--- Player's victory points
    "timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("wid", "id"),
    UNIQUE("wid", "pid", "timestamp"),
    FOREIGN KEY("wid", "pid") REFERENCES players("wid", "id"),
	FOREIGN KEY("wid", "tid") REFERENCES tribes("wid", "id")
) PARTITION BY LIST ("wid");

--- Villages' table declaration
CREATE TABLE villages
(
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
    "id" uint8,--- Village's id on tw2
    "name" VARCHAR(49) NOT NULL,--- Village's name
    "nname" VARCHAR(49) NOT NULL,--- Village's name in normalize form for searching

	-- Also storing the current values here for not needing additional search on history
    "pid" uint8 NOT NULL,--- Village's owner
	"tid" uint8 NOT NULL,--- Owner's tribe
    "x" uint2 NOT NULL,--- Village's x-axis
    "y" uint2 NOT NULL,--- Village's y-axis
    "points" uint8 NOT NULL,--- Village's points
    "provname" VARCHAR(32),--TODO(Vasilis): Check real length if we can
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,--- Last dump the village was in
    PRIMARY KEY("wid", "id"),
    FOREIGN KEY("wid") REFERENCES worlds("wid"),
	FOREIGN KEY("wid", "pid") REFERENCES players("wid", "id"),
    FOREIGN KEY("wid", "tid") REFERENCES tribes("wid", "id")
) PARTITION BY LIST ("wid");

CREATE TABLE villages_history
(
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
    "id" BIGSERIAL,--- Internal id for record
    "vid" uint8,--- Village's id
    "name" VARCHAR(49) NOT NULL,
    "nname" VARCHAR(49) NOT NULL,
    "pid" uint8 NOT NULL,
	"tid" uint8 NOT NULL,
    "points" uint8 NOT NULL,
    "timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("wid", "id"),
    UNIQUE("wid", "vid", "timestamp"),
    FOREIGN KEY("wid", "vid") REFERENCES villages("wid", "id"),
	FOREIGN KEY("wid", "pid") REFERENCES players("wid", "id"),
	FOREIGN KEY("wid", "tid") REFERENCES tribes("wid", "id")
) PARTITION BY LIST ("wid");

-- This table is basically a subset of villages_history containing only records of conquers
CREATE TABLE conquers
(
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
	"id" BIGSERIAL,--- Internal id for record
    "vid" uint8,--- Village's id
    "name" VARCHAR(49) NOT NULL,
    "nname" VARCHAR(49) NOT NULL,
    "prevpid" uint8 NOT NULL,
    "nextpid" uint8 NOT NULL,
	"prevtid" uint8 NOT NULL,
	"nexttid" uint8 NOT NULL,
    "points" uint8 NOT NULL,
    "timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("wid", "id"),
    FOREIGN KEY("wid", "vid") REFERENCES villages("wid", "id"),
	FOREIGN KEY("wid", "prevpid") REFERENCES players("wid", "id"),
	FOREIGN KEY("wid", "nextpid") REFERENCES players("wid", "id"),
	FOREIGN KEY("wid", "prevtid") REFERENCES tribes("wid", "id"),
	FOREIGN KEY("wid", "nexttid") REFERENCES tribes("wid", "id")
) PARTITION BY LIST ("wid");

CREATE TABLE tribe_changes
(
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
	"id" BIGSERIAL,--- Internal id for record
	"pid" uint8 NOT NULL,
	"prevtid" uint8 NOT NULL,
	"nexttid" uint8 NOT NULL,
	"villages" uint4 NOT NULL,--- Player's number of villages
    "points" uint8 NOT NULL,--- Player's total points
    "offbash" uint8 NOT NULL,--- Player's offensive bash points
    "defbash" uint8 NOT NULL,--- Player's defensive bash points
    "totalbash" uint8 NOT NULL,--- Player's total bash points
	"rankno" uint4 NOT NULL,
    "vp" uint4,--- Player's victory points
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
	PRIMARY KEY("wid", "id"),
	FOREIGN KEY("wid", "pid") REFERENCES players("wid", "id"),
	FOREIGN KEY("wid", "prevtid") REFERENCES tribes("wid", "id"),
	FOREIGN KEY("wid", "nexttid") REFERENCES tribes("wid", "id")
) PARTITION BY LIST ("wid");

/* -------- Indexes --------*/
-- Created on the partitioned tables, so every world's partitions get them. A
-- partition only holds one world, so they don't include wid.
-- The history tables are looked up by their UNIQUE (wid, entity, timestamp) keys.
-- Names are filtered with LIKE '%...%', which a btree index can't serve.
CREATE INDEX players_tid_index ON players("tid");
CREATE INDEX villages_pid_index ON villages("pid");
CREATE INDEX villages_tid_index ON villages("tid");
CREATE INDEX conquers_vid_index ON conquers("vid");
CREATE INDEX conquers_prevpid_index ON conquers("prevpid");
CREATE INDEX conquers_nextpid_index ON conquers("nextpid");
CREATE INDEX conquers_prevtid_index ON conquers("prevtid");
CREATE INDEX conquers_nexttid_index ON conquers("nexttid");
CREATE INDEX tribe_changes_pid_index ON tribe_changes("pid");
CREATE INDEX tribe_changes_prevtid_index ON tribe_changes("prevtid");
CREATE INDEX tribe_changes_nexttid_index ON tribe_changes("nexttid");

/* -------- Triggers -------- */
-- A dump that runs twice in the same hour (a retry, or the hour the clocks go
-- back) replaces that hour's history row instead of adding a second one.

/**
* Records the tribe's values in its history
*/
CREATE FUNCTION record_tribes_history() RETURNS TRIGGER AS $rth$
BEGIN
	INSERT INTO tribes_history
	(
		"wid",
		"tid",
		"members",
		"points",
		"offbash", "defbash", "totalbash",
		"rankno",
		"villages",
		"vp",
		"timestamp"
	)
	VALUES
	(
		NEW.wid,
		NEW.id,
		NEW.members,
		NEW.points,
		NEW.offbash, NEW.defbash, NEW.totalbash,
		NEW.rankno,
		NEW.villages,
		NEW.vp,
		NEW.timestamp
	)
	ON CONFLICT ("wid", "tid", "timestamp") DO UPDATE SET
		"members" = EXCLUDED.members,
		"points" = EXCLUDED.points,
		"offbash" = EXCLUDED.offbash, "defbash" = EXCLUDED.defbash, "totalbash" = EXCLUDED.totalbash,
		"rankno" = EXCLUDED.rankno,
		"villages" = EXCLUDED.villages,
		"vp" = EXCLUDED.vp;
	RETURN NULL;
END;
$rth$ LANGUAGE plpgsql;

CREATE TRIGGER on_tribe_creation
AFTER INSERT ON tribes
FOR EACH ROW EXECUTE FUNCTION record_tribes_history();

-- Disbanded tribes are only marked inactive, which isn't recorded
CREATE TRIGGER on_tribe_update
AFTER UPDATE ON tribes
FOR EACH ROW
WHEN (NEW.active AND (OLD.members, OLD.points, OLD.offbash, OLD.defbash, OLD.totalbash, OLD.rankno, OLD.villages, OLD.vp)
	IS DISTINCT FROM (NEW.members, NEW.points, NEW.offbash, NEW.defbash, NEW.totalbash, NEW.rankno, NEW.villages, NEW.vp))
EXECUTE FUNCTION record_tribes_history();

/**
* Records the player's values in its history, and the tribe change if the player
* joined or left a tribe
*/
CREATE FUNCTION record_players_history() RETURNS TRIGGER AS $rph$
BEGIN
	INSERT INTO players_history
	(
		"wid",
		"pid",
		"tid",
		"villages",
		"points",
		"offbash", "defbash", "totalbash",
		"rankno",
		"vp",
		"timestamp"
	)
	VALUES
	(
		NEW.wid,
		NEW.id,
		NEW.tid,
		NEW.villages,
		NEW.points,
		NEW.offbash, NEW.defbash, NEW.totalbash,
		NEW.rankno,
		NEW.vp,
		NEW.timestamp
	)
	ON CONFLICT ("wid", "pid", "timestamp") DO UPDATE SET
		"tid" = EXCLUDED.tid,
		"villages" = EXCLUDED.villages,
		"points" = EXCLUDED.points,
		"offbash" = EXCLUDED.offbash, "defbash" = EXCLUDED.defbash, "totalbash" = EXCLUDED.totalbash,
		"rankno" = EXCLUDED.rankno,
		"vp" = EXCLUDED.vp;
	IF (TG_OP = 'INSERT' AND NEW.tid <> 0) OR (TG_OP = 'UPDATE' AND OLD.tid <> NEW.tid) THEN
		INSERT INTO tribe_changes
		(
			"wid",
			"pid",
			"prevtid", "nexttid",
			"villages",
			"points",
			"offbash",
			"defbash",
			"totalbash",
			"rankno",
			"vp",
			"timestamp"
		)
		VALUES
		(
			NEW.wid,
			NEW.id,
			CASE WHEN TG_OP = 'INSERT' THEN 0 ELSE OLD.tid END, NEW.tid,
			NEW.villages,
			NEW.points,
			NEW.offbash, NEW.defbash, NEW.totalbash,
			NEW.rankno,
			NEW.vp,
			NEW.timestamp
		);
	END IF;
	RETURN NULL;
END;
$rph$ LANGUAGE plpgsql;

CREATE TRIGGER on_players_start
AFTER INSERT ON players
FOR EACH ROW EXECUTE FUNCTION record_players_history();

CREATE TRIGGER on_players_update
AFTER UPDATE ON players
FOR EACH ROW
WHEN ((OLD.tid, OLD.villages, OLD.points, OLD.offbash, OLD.defbash, OLD.totalbash, OLD.rankno, OLD.vp)
	IS DISTINCT FROM (NEW.tid, NEW.villages, NEW.points, NEW.offbash, NEW.defbash, NEW.totalbash, NEW.rankno, NEW.vp))
EXECUTE FUNCTION record_players_history();

/**
* Records the village's values in its history, and the conquer if a player took
* the village over
*/
CREATE FUNCTION record_villages_history() RETURNS TRIGGER AS $rvh$
BEGIN
	INSERT INTO villages_history
	(
		"wid",
		"vid",
		"name",
		"nname",
		"pid",
		"tid",
		"points",
		"timestamp"
	)
	VALUES
	(
		NEW.wid,
		NEW.id,
		NEW.name,
		NEW.nname,
		NEW.pid,
		NEW.tid,
		NEW.points,
		NEW.timestamp
	)
	ON CONFLICT ("wid", "vid", "timestamp") DO UPDATE SET
		"name" = EXCLUDED.name,
		"nname" = EXCLUDED.nname,
		"pid" = EXCLUDED.pid,
		"tid" = EXCLUDED.tid,
		"points" = EXCLUDED.points;
	IF TG_OP = 'UPDATE' AND OLD.pid <> NEW.pid AND NEW.pid <> 0 THEN
		INSERT INTO conquers
		(
			"wid",
			"vid",
			"name",
			"nname",
			"prevpid", "nextpid",
			"prevtid", "nexttid",
			"points",
			"timestamp"
		)
		VALUES
		(
			NEW.wid,
			NEW.id,
			NEW.name,
			NEW.nname,
			OLD.pid, NEW.pid,
			OLD.tid, NEW.tid,
			NEW.points,
			NEW.timestamp
		);
	END IF;
	RETURN NULL;
END;
$rvh$ LANGUAGE plpgsql;

CREATE TRIGGER on_village_spawn
AFTER INSERT ON villages
FOR EACH ROW EXECUTE FUNCTION record_villages_history();

CREATE TRIGGER on_village_update
AFTER UPDATE ON villages
FOR EACH ROW
WHEN ((OLD.name, OLD.nname, OLD.pid, OLD.tid, OLD.points)
	IS DISTINCT FROM (NEW.name, NEW.nname, NEW.pid, NEW.tid, NEW.points))
EXECUTE FUNCTION record_villages_history();

/* -------- Worlds' partitions -------- */

/**
* Creates a world's partitions, with the dummy tribe for players not in a tribe
* and the dummy player for barbarians. The world must already be in the worlds
* table.
*/
CREATE FUNCTION create_world_partitions(world VARCHAR) RETURNS VOID AS $cwp$
DECLARE
	t TEXT;
BEGIN
	IF world !~ '^[a-z0-9]{1,5}$' THEN
		RAISE EXCEPTION 'Invalid world id: %', world;
	END IF;
	FOREACH t IN ARRAY ARRAY['tribes', 'tribes_history', 'players', 'players_history', 'villages', 'villages_history', 'conquers', 'tribe_changes'] LOOP
		EXECUTE format('CREATE TABLE %I PARTITION OF %I FOR VALUES IN (%L)', t || '_' || world, t, world);
	END LOOP;

	INSERT INTO tribes
	(
		"wid",
		"id",
		"name", "nname", "tag",
		"active",
		"members",
		"points",
		"offbash", "defbash", "totalbash",
		"rankno",
		"villages",
		"timestamp"
	)
	VALUES (world, 0, 'Not in tribe', 'not in tribe', 'nit', false, 0, 0, 0, 0, 0, 0, 0, '1970-01-01 12:00:00');

	INSERT INTO players
	(
		"wid",
		"id",
		"name",
		"nname",
		"tid",
		"villages",
		"points",
		"offbash", "defbash", "totalbash",
		"rankno",
		"vp",
		"timestamp"
	)
	VALUES (world, 0, 'Barbarians', 'barbarians', 0, 0, 0, 0, 0, 0, 0, 0, '1970-01-01 12:00:00');

	-- The dummies aren't part of the world's history
	DELETE FROM tribes_history WHERE "wid" = world AND "tid" = 0;
	DELETE FROM players_history WHERE "wid" = world AND "pid" = 0;
END;
$cwp$ LANGUAGE plpgsql;

/**
* Drops a world's partitions, and with them all of its data. The worlds row is
* left for the caller to delete.
*/
CREATE FUNCTION drop_world_partitions(world VARCHAR) RETURNS VOID AS $dwp$
DECLARE
	t TEXT;
BEGIN
	IF world !~ '^[a-z0-9]{1,5}$' THEN
		RAISE EXCEPTION 'Invalid world id: %', world;
	END IF;
	-- Tables that reference others go first. A partition that other tables
	-- reference can't be dropped while attached.
	FOREACH t IN ARRAY ARRAY['tribe_changes', 'conquers', 'villages_history', 'villages', 'players_history', 'players', 'tribes_history', 'tribes'] LOOP
		EXECUTE format('ALTER TABLE %I DETACH PARTITION %I', t, t || '_' || world);
		EXECUTE format('DROP TABLE %I', t || '_' || world);
	END LOOP;
END;
$dwp$ LANGUAGE plpgsql;
