/* Have to DROP TABLE(s) before we DROP DOMAIN(s) */
/* DROP TABLE(s) */
DROP TABLE IF EXISTS villages_history;
DROP TABLE IF EXISTS villages;
DROP TABLE IF EXISTS players_history;
DROP TABLE IF EXISTS players;
DROP TABLE IF EXISTS tribes_history;
DROP TABLE IF EXISTS tribes;

--- Unsigned int type for 2 byte integer
DROP DOMAIN IF EXISTS uint2;
CREATE DOMAIN uint2 AS SMALLINT CHECK(VALUE>=0);

--- Unsigned int type for 4 byte integer
DROP DOMAIN IF EXISTS uint4;
CREATE DOMAIN uint4 AS INTEGER CHECK(VALUE>=0);

--- Unsigned int type for 8 byte integer
DROP DOMAIN IF EXISTS uint8;
CREATE DOMAIN uint8 AS INTEGER CHECK(VALUE>=0);

--- Tribes' table declaration
CREATE TABLE tribes
(
    "id" uint8,--- Tribe's id on tw2
    "name" VARCHAR(32) NOT NULL,--- Tribe's name on tw2
    "nname" VARCHAR(32) NOT NULL,--- Tribe's name in normalize form for searching
    "tag" VARCHAR(3) NOT NULL,--- Tribe's short name on tw2
    "active" BOOLEAN NOT NULL,--- If the tribe still exist this should be true
    
    -- Also storing the current values here for not needing additional search on history
    "members" uint2 NOT NULL,--- Νumber of members in tribe
    "points" uint8 NOT NULL,--- Tribe's points
    "ppm" uint8 NOT NULL,--- Tribe's points per member
    "ppv" uint8 NOT NULL,--- Tribe's points per village
    "offbash" uint8 NOT NULL,--- Tribe's offensive bash points
    "defbash" uint8 NOT NULL,--- Tribe's defensive bash points
    "totalbash" uint8 NOT NULL,--- Tribe's total bash points
    "rankno" uint4 NOT NULL,--- Tribe's rank number
    "villages" uint4 NOT NULL,--- Tribe's total number of villages
    "vp" uint4,--- Tribe's total victory points
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("id")
    -- Although name and tag are supposed to be unique I don't what happens if a tribe is disband (Does name and tag are still occupied? prob. not)
);

/**
* Tribes' history table decleration
*/
CREATE TABLE tribes_history
(
    "id" BIGSERIAL,--- Internal id of record
    "tid" uint8,--- Tribe's id on tw2
    "members" uint2 NOT NULL,--- Νumber of members in tribe
    "points" uint8 NOT NULL,--- Tribe's points
    "ppm" uint8 NOT NULL,--- Tribe's points per member
    "ppv" uint8 NOT NULL,--- Tribe's points per village
    "offbash" uint8 NOT NULL,--- Tribe's offensive bash points
    "defbash" uint8 NOT NULL,--- Tribe's defensive bash points
    "totalbash" uint8 NOT NULL,--- Tribe's total bash points
    "rankno" uint4 NOT NULL,--- Tribe's rank number
    "villages" uint4 NOT NULL,--- Tribe's total number of villages
    "vp" uint4,--- Tribe's total victory points
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("id"),
    FOREIGN KEY("tid") REFERENCES tribes("id")
);

--- Players' table declaration
CREATE TABLE players
(
    "id" uint8,--- Player's id on tw2
    "name" VARCHAR(24),--- Player's name on tw2
    "nname" VARCHAR(24),--- Player's name in normalize form for searching
    
    -- Also storing the current values here for not needing additional search on history
    "tid" uint8 NOT NULL,--- Player's tribe id on tw2
    "villages" uint4 NOT NULL,--- Player's number of villages
    "points" uint8 NOT NULL,--- Player's total points
    "ppv" uint8 NOT NULL,--- Player's points per village
    "offbash" uint8 NOT NULL,--- Player's offensive bash points
    "defbash" uint8 NOT NULL,--- Player's defensive bash points
    "totalbash" uint8 NOT NULL,--- Player's total bash points
	"rankno" uint4 NOT NULL,
    "vp" uint4,--- Player's victory points
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("id"),
    UNIQUE("name")
    --tid is not foreign key because players with no tribe will have tid of 0

);

/**
* Players' history table declaration
*   Get the last one for the "current" tribes details
*/
CREATE TABLE players_history
(
    "id" BIGSERIAL,--- Internal id for record
    "pid" uint8,--- Player's id on tw2
	"prevtid" uint8 NOT NULL,--- Player's old tribe id on tw2
    "nexttid" uint8 NOT NULL,--- Player's new tribe id on tw2
    "villages" uint4 NOT NULL,--- Player's number of villages
    "points" uint8 NOT NULL,--- Player's total points
    "ppv" uint8 NOT NULL,--- Player's points per village
    "offbash" uint8 NOT NULL,--- Player's offensive bash points
    "defbash" uint8 NOT NULL,--- Player's defensive bash points
    "totalbash" uint8 NOT NULL,--- Player's total bash points
	"rankno" uint4 NOT NULL,
    "vp" uint4,--- Player's victory points
    "timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("id"),
    FOREIGN KEY("pid") REFERENCES players("id")
    --tid is not foreign key because players with no tribe will have tid of 0
);

--- Villages' table declaration
CREATE TABLE villages
(
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
	"timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY("id")
    --pid is not foreign key because barbs should have a pid of 0
);

CREATE TABLE villages_history
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
    FOREIGN KEY("vid") REFERENCES villages("id")
    --pid is not foreign key because barbs should have a pid of 0
);

/* -------- Indexes --------*/

--On tribes table
--TODO(Vasilis): Since usually the number of tribes is small I believe we don't need the indexes.
--CREATE INDEX tname_index ON tribes("name");
--CREATE INDEX ttag_index ON tribes("tag");

--On players table
--TODO(Vasilis): Same with tribe index. Must check if the index is really need it. (Here is more luckily to be needed)
--CREATE INDEX pname_index ON players("name");
CREATE INDEX pnname_index ON players("nname");

--On villages table
CREATE INDEX vname_index ON villages("name");
CREATE INDEX vnname_index ON villages("nname");
--On conquest_history table
CREATE INDEX vhname_index ON villages_history("name");
CREATE INDEX vhnname_index ON villages_history("nname");

/* -------- Dummy tribe for players not in tribe -------- */
INSERT INTO tribes
(
	"id",
	"name", "nname", "tag",
	"active",
	"members",
	"points", "ppm", "ppv",
	"offbash", "defbash", "totalbash",
	"rankno",
	"villages",
	"timestamp"
)
VALUES (0, 'Not in tribe', 'not in tribe', 'nit', false, 0, 0, 0, 0, 0, 0, 0, 0, 0, '1970-01-01 12:00:00');

INSERT INTO players
(
	"id",
	"name",
    "nname",
	"tid",
	"villages",
    "points", "ppv",
    "offbash", "defbash", "totalbash",
	"rankno",
    "vp",
	"timestamp"
)
VALUES (0, 'Barbarians', 'barbarians', 0, 0, 0, 0, 0, 0, 0, 0, 0, '1970-01-01 12:00:00');

/* -------- Triggers -------- */
/**
* Triger's function that update the history for the given tribe
*/
DROP FUNCTION IF EXISTS update_tribes_history;
CREATE FUNCTION update_tribes_history() RETURNS TRIGGER AS $uth$
BEGIN
	IF NEW.active THEN
		INSERT INTO tribes_history
		(
			"tid",
			"members",
			"points", "ppm", "ppv",
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
			NEW.points, NEW.ppm, NEW.ppv,
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

DROP TRIGGER IF EXISTS on_tribe_update ON tribes;
CREATE TRIGGER on_tribe_update
AFTER UPDATE ON tribes
FOR EACH ROW EXECUTE FUNCTION update_tribes_history();

DROP TRIGGER IF EXISTS on_tribe_creation ON tribes;
CREATE TRIGGER on_tribe_creation
AFTER INSERT ON tribes
FOR EACH ROW EXECUTE FUNCTION update_tribes_history();

DROP FUNCTION IF EXISTS init_players_history;
CREATE FUNCTION init_players_history() RETURNS TRIGGER AS $uph$
BEGIN
	INSERT INTO players_history
	(
		"pid",
		"prevtid", "nexttid",
		"villages",
		"points", "ppv",
		"offbash", "defbash", "totalbash",
		"rankno",
		"vp",
		"timestamp"
	)
	VALUES
	(
		NEW.id,
		NEW.tid, NEW.tid,
		NEW.villages,
		NEW.points, NEW.ppv,
		NEW.offbash, NEW.defbash, NEW.totalbash,
		NEW.rankno,
		NEW.vp,
		NEW.timestamp
	);
	RETURN NEW;
END;
$uph$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS update_players_history;
CREATE FUNCTION update_players_history() RETURNS TRIGGER AS $uph$
BEGIN
	INSERT INTO players_history
	(
		"pid",
		"prevtid", "nexttid",
		"villages",
		"points", "ppv",
		"offbash", "defbash", "totalbash",
		"rankno",
		"vp",
		"timestamp"
	)
	VALUES
	(
		NEW.id,
		OLD.tid, NEW.tid,
		NEW.villages,
		NEW.points, NEW.ppv,
		NEW.offbash, NEW.defbash, NEW.totalbash,
		NEW.rankno,
		NEW.vp,
		NEW.timestamp
	);
	RETURN NEW;
END;
$uph$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS on_players_update ON players;
CREATE TRIGGER on_players_update
AFTER UPDATE ON players
FOR EACH ROW EXECUTE FUNCTION update_players_history();

DROP TRIGGER IF EXISTS on_players_start ON players;
CREATE TRIGGER on_players_start
AFTER INSERT ON players
FOR EACH ROW EXECUTE FUNCTION init_players_history();

DROP FUNCTION IF EXISTS init_villages_history;
CREATE FUNCTION init_villages_history() RETURNS TRIGGER AS $ivch$
BEGIN
	INSERT INTO villages_history
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
		NEW.pid, NEW.pid,
		NEW.tid, NEW.tid,
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
	RETURN NEW;
END;
$uvch$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS on_village_spawn ON villages;
CREATE TRIGGER on_village_spawn
AFTER INSERT ON villages
FOR EACH ROW EXECUTE FUNCTION init_villages_history();

DROP TRIGGER IF EXISTS on_village_update ON villages;
CREATE TRIGGER on_village_update
AFTER UPDATE ON villages
FOR EACH ROW EXECUTE FUNCTION update_villages_history();