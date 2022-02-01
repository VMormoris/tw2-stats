--- Add forgotten foreign keys since we added dummy player for barbs and tribe for players without one
ALTER TABLE players ADD CONSTRAINT players_tid_fkey FOREIGN KEY("tid") REFERENCES tribes("id");
ALTER TABLE villages ADD CONSTRAINT villages_pid_fkey FOREIGN KEY("pid") REFERENCES players("id");
ALTER TABLE villages ADD CONSTRAINT villages_tid_fkey FOREIGN KEY("tid") REFERENCES tribes("id");

--- Remove ppv and ppm records don't need to store them can be calculated
ALTER TABLE tribes DROP COLUMN ppm, DROP COLUMN ppv;
ALTER TABLE tribes_history DROP COLUMN ppm, DROP COLUMN ppv;
ALTER TABLE players DROP COLUMN ppv;
ALTER TABLE players_history DROP COLUMN ppv;

-- This table is basically a subset of villages_history containing only records of conquers
CREATE TABLE conquers
(
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
    PRIMARY KEY("id"),
    FOREIGN KEY("vid") REFERENCES villages("id"),
	FOREIGN KEY("prevpid") REFERENCES players("id"),
	FOREIGN KEY("nextpid") REFERENCES players("id"),
	FOREIGN KEY("prevtid") REFERENCES tribes("id"),
	FOREIGN KEY("nexttid") REFERENCES tribes("id")
);

--- Populate conquers table
INSERT INTO conquers("vid", "name", "nname", "prevpid", "nextpid", "prevtid", "nexttid", "points", "timestamp")
SELECT "vid", "name", "nname", "prevpid", "nextpid", "prevtid", "nexttid", "points", "timestamp"
FROM villages_history WHERE prevpid <> nextpid AND nextpid <> 0
ORDER BY "id" ASC;

CREATE TABLE tribe_changes
(
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
	PRIMARY KEY("id"),
	FOREIGN KEY("pid") REFERENCES players("id"),
	FOREIGN KEY("prevtid") REFERENCES tribes("id"),
	FOREIGN KEY("nexttid") REFERENCES tribes("id")
);

--- Populate tribe changes tables
INSERT INTO tribe_changes("pid", "prevtid", "nexttid", "villages", "points", "offbash", "defbash", "totalbash", "rankno", "vp", "timestamp")
SELECT "pid", "prevtid", "nexttid", "villages", "points", "offbash", "defbash", "totalbash", "rankno", "vp", "timestamp"
FROM players_history WHERE "prevtid" <> "nexttid"
ORDER BY "id" ASC;

--- Update villages & players history structure & indexes
DROP INDEX oldowner_index;
DROP INDEX newowner_index;
DROP INDEX oldtribe_index;
DROP INDEX newtribe_index;
ALTER TABLE players_history DROP COLUMN "prevtid";
ALTER TABLE players_history RENAME COLUMN "nexttid" TO "tid";
ALTER TABLE players_history ADD CONSTRAINT players_history_tid_fkey FOREIGN KEY("tid") REFERENCES tribes("id");
ALTER TABLE villages_history DROP COLUMN "prevpid";
ALTER TABLE villages_history DROP COLUMN "prevtid";
ALTER TABLE villages_history RENAME COLUMN "nextpid" TO "pid";
ALTER TABLE villages_history RENAME COLUMN "nexttid" TO "tid";
ALTER TABLE villages_history ADD CONSTRAINT villages_history_pid_fkey FOREIGN KEY("pid") REFERENCES players("id");
ALTER TABLE villages_history ADD CONSTRAINT villages_history_tid_fkey FOREIGN KEY("tid") REFERENCES tribes("id");

CREATE INDEX thtid_index ON tribes_history("tid");
CREATE INDEX phpid_index ON players_history("pid");
CREATE INDEX vhvid_index ON villages_history("vid");

--- Recreate Triggers
--- Since we are dropping the triggers it's good time that we update the history counts
DROP TRIGGER IF EXISTS on_village_spawn ON villages;
DROP TRIGGER IF EXISTS on_village_update ON villages;

DROP TRIGGER IF EXISTS on_players_start ON players;
DROP TRIGGER IF EXISTS on_players_update ON players;

DROP TRIGGER IF EXISTS on_tribe_creation ON tribes;
DROP TRIGGER IF EXISTS on_tribe_update ON tribes;

--- Setup new triggers
DROP FUNCTION IF EXISTS init_tribes_history;
CREATE FUNCTION init_tribes_history() RETURNS TRIGGER AS $uth$
BEGIN
	INSERT INTO tribes_history
	(
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
		NEW.id,
		NEW.members,
		NEW.points,
		NEW.offbash, NEW.defbash, NEW.totalbash,
		NEW.rankno,
		NEW.villages,
		NEW.vp,
		NEW.timestamp
	);
	RETURN NEW;
END;
$uth$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS update_tribes_history;
CREATE FUNCTION update_tribes_history() RETURNS TRIGGER AS $uth$
BEGIN
	IF NEW.active THEN
		INSERT INTO tribes_history
		(
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
			NEW.id,
			NEW.members,
			NEW.points,
			NEW.offbash, NEW.defbash, NEW.totalbash,
			NEW.rankno,
			NEW.villages,
			NEW.vp,
			NEW.timestamp
		);
	END IF;
	RETURN NEW;
END;
$uth$ LANGUAGE plpgsql;

CREATE TRIGGER on_tribe_creation
AFTER INSERT ON tribes
FOR EACH ROW EXECUTE FUNCTION init_tribes_history();

CREATE TRIGGER on_tribe_update
BEFORE UPDATE ON tribes
FOR EACH ROW EXECUTE FUNCTION update_tribes_history();

DROP FUNCTION IF EXISTS init_players_history;
CREATE FUNCTION init_players_history() RETURNS TRIGGER AS $uph$
BEGIN
	INSERT INTO players_history
	(
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
		NEW.id,
		NEW.tid,
		NEW.villages,
		NEW.points,
		NEW.offbash, NEW.defbash, NEW.totalbash,
		NEW.rankno,
		NEW.vp,
		NEW.timestamp
	);
	IF (NEW.tid <> 0) THEN
		INSERT INTO tribe_changes
		(
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
			NEW.id,
			0, NEW.tid,
			NEW.villages,
			NEW.points,
			NEW.offbash, NEW.defbash, NEW.totalbash,
			NEW.rankno,
			NEW.vp,
			NEW.timestamp
		);
	END IF;
	RETURN NEW;
END;
$uph$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS update_players_history;
CREATE FUNCTION update_players_history() RETURNS TRIGGER AS $uph$
BEGIN
	INSERT INTO players_history
	(
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
		NEW.id,
		NEW.tid,
		NEW.villages,
		NEW.points,
		NEW.offbash, NEW.defbash, NEW.totalbash,
		NEW.rankno,
		NEW.vp,
		NEW.timestamp
	);
	IF(OLD.tid <> NEW.tid) THEN
		INSERT INTO tribe_changes
		(
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
			NEW.id,
			OLD.tid, NEW.tid,
			NEW.villages,
			NEW.points,
			NEW.offbash, NEW.defbash, NEW.totalbash,
			NEW.rankno,
			NEW.vp,
			NEW.timestamp
		);
	END IF;
	RETURN NEW;
END;
$uph$ LANGUAGE plpgsql;

CREATE TRIGGER on_players_start
AFTER INSERT ON players
FOR EACH ROW EXECUTE FUNCTION init_players_history();

CREATE TRIGGER on_players_update
BEFORE UPDATE ON players
FOR EACH ROW EXECUTE FUNCTION update_players_history();

DROP FUNCTION IF EXISTS init_villages_history;
CREATE FUNCTION init_villages_history() RETURNS TRIGGER AS $ivch$
BEGIN
	INSERT INTO villages_history
	(
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
		NEW.id,
		NEW.name,
        NEW.nname,
		NEW.pid,
		NEW.tid,
		NEW.points,
		NEW.timestamp
	);
	RETURN NEW;
END;
$ivch$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS update_villages_history;
CREATE FUNCTION update_villages_history() RETURNS TRIGGER AS $uvch$
BEGIN
	INSERT INTO villages_history
	(
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
		NEW.id,
		NEW.name,
        NEW.nname,
		NEW.pid,
		NEW.tid,
		NEW.points,
		NEW.timestamp
	);
	IF (OLD.pid <> NEW.pid) AND (NEW.pid <> 0) THEN
		INSERT INTO conquers
		(
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
			NEW.id,
			NEW.name,
			NEW.nname,
			OLD.pid, NEW.pid,
			OLD.tid, NEW.tid,
			NEW.points,
			NEW.timestamp
		);
	END IF;
	RETURN NEW;
END;
$uvch$ LANGUAGE plpgsql;

CREATE TRIGGER on_village_spawn
AFTER INSERT ON villages
FOR EACH ROW EXECUTE FUNCTION init_villages_history();

CREATE TRIGGER on_village_update
BEFORE UPDATE ON villages
FOR EACH ROW EXECUTE FUNCTION update_villages_history();

CLUSTER tribes_history USING thtid_index;
CLUSTER players_history USING phpid_index;
CLUSTER villages_history USING vhvid_index;