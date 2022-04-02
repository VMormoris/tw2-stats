/* Have to DROP TABLE(s) before we DROP DOMAIN(s) */
/* DROP TABLE(s) */


--- Unsigned int type for 2 byte integer
DROP DOMAIN IF EXISTS uint2;
CREATE DOMAIN uint2 AS SMALLINT CHECK(VALUE>=0);

--- Unsigned int type for 4 byte integer
DROP DOMAIN IF EXISTS uint4;
CREATE DOMAIN uint4 AS INTEGER CHECK(VALUE>=0);

--- Unsigned int type for 8 byte integer
DROP DOMAIN IF EXISTS uint8;
CREATE DOMAIN uint8 AS BIGINT CHECK(VALUE>=0);

--- Enumaration for wind condition (Using an enum for potetianally new conditions)
DROP TYPE IF EXISTS win_codition;
CREATE TYPE win_codition AS ENUM
(
    'Domination',
    'VP'
)

CREATE TABLE worlds
(
    "id" BIGSERIAL,--- ID for record (only for internal use)
    "wid" VARCHAR(5) NOT NULL,--- World's ID in game
    "name" VARCHAR(128) NOT NULL,--- World's name in game
    "win_condition" win_codition NOT NULL,--- World's win condition
    "win_ammount" uint2 NOT NULL,--- Number to met for the win condition (% if domination just VP in a VP world)
    "tribes" uint4 NOT NULL,--- Number of active (aka not disband) tribes
    "players" uint4 NOT NULL,--- Number of players on world
    "villages" uint8 NOT NULL,--- Number of villages
);

CREATE DOMAIN dummy AS BIGINT CHECK(VALUE>=0);

ALTER TABLE tribes
    ALTER COLUMN "id" TYPE dummy,
    ALTER COLUMN "points" TYPE dummy,
    ALTER COLUMN "offbash" TYPE dummy,
    ALTER COLUMN "defbash" TYPE dummy,
    ALTER COLUMN "totalbash" TYPE dummy;

ALTER TABLE tribes_history
    ALTER COLUMN "tid" TYPE dummy,
    ALTER COLUMN "points" TYPE dummy,
    ALTER COLUMN "offbash" TYPE dummy,
    ALTER COLUMN "defbash" TYPE dummy,
    ALTER COLUMN "totalbash" TYPE dummy;

ALTER TABLE players
    ALTER COLUMN "id" TYPE dummy,
    ALTER COLUMN "tid" TYPE dummy,
    ALTER COLUMN "points" TYPE dummy,
    ALTER COLUMN "offbash" TYPE dummy,
    ALTER COLUMN "defbash" TYPE dummy,
    ALTER COLUMN "totalbash" TYPE dummy;

ALTER TABLE players_history
    ALTER COLUMN "id" TYPE dummy,
    ALTER COLUMN "pid" TYPE dummy,
    ALTER COLUMN "tid" TYPE dummy,
    ALTER COLUMN "points" TYPE dummy,
    ALTER COLUMN "offbash" TYPE dummy,
    ALTER COLUMN "defbash" TYPE dummy,
    ALTER COLUMN "totalbash" TYPE dummy;

ALTER TABLE villages
    ALTER COLUMN "id" TYPE dummy,
    ALTER COLUMN "pid" TYPE dummy,
    ALTER COLUMN "tid" TYPE dummy,
    ALTER COLUMN "points" TYPE dummy;

ALTER TABLE villages_history
    ALTER COLUMN "id" TYPE dummy,
    ALTER COLUMN "vid" TYPE dummy,
    ALTER COLUMN "pid" TYPE dummy,
    ALTER COLUMN "tid" TYPE dummy,
    ALTER COLUMN "points" TYPE dummy;

ALTER TABLE conquers
    ALTER COLUMN "id" TYPE dummy,
    ALTER COLUMN "vid" TYPE dummy,
    ALTER COLUMN "prevpid" TYPE dummy,
    ALTER COLUMN "nextpid" TYPE dummy,
    ALTER COLUMN "prevtid" TYPE dummy,
    ALTER COLUMN "nexttid" TYPE dummy,
    ALTER COLUMN "points" TYPE dummy;

ALTER TABLE tribe_changes
    ALTER COLUMN "id" TYPE dummy,
    ALTER COLUMN "pid" TYPE dummy,
    ALTER COLUMN "prevtid" TYPE dummy,
    ALTER COLUMN "nexttid" TYPE dummy,
    ALTER COLUMN "points" TYPE dummy,
    ALTER COLUMN "offbash" TYPE dummy,
    ALTER COLUMN "defbash" TYPE dummy,
    ALTER COLUMN "totalbash" TYPE dummy;

DROP DOMAIN IF EXISTS uint8;
CREATE DOMAIN uint8 AS BIGINT CHECK(VALUE>=0);

ALTER TABLE tribes
    ALTER COLUMN "id" TYPE uint8,
    ALTER COLUMN "points" TYPE uint8,
    ALTER COLUMN "offbash" TYPE uint8,
    ALTER COLUMN "defbash" TYPE uint8,
    ALTER COLUMN "totalbash" TYPE uint8;

ALTER TABLE tribes_history
    ALTER COLUMN "tid" TYPE uint8,
    ALTER COLUMN "points" TYPE uint8,
    ALTER COLUMN "offbash" TYPE uint8,
    ALTER COLUMN "defbash" TYPE uint8,
    ALTER COLUMN "totalbash" TYPE uint8;

ALTER TABLE players
    ALTER COLUMN "id" TYPE uint8,
    ALTER COLUMN "tid" TYPE uint8,
    ALTER COLUMN "points" TYPE uint8,
    ALTER COLUMN "offbash" TYPE uint8,
    ALTER COLUMN "defbash" TYPE uint8,
    ALTER COLUMN "totalbash" TYPE uint8;

ALTER TABLE players_history
    ALTER COLUMN "id" TYPE uint8,
    ALTER COLUMN "pid" TYPE uint8,
    ALTER COLUMN "tid" TYPE uint8,
    ALTER COLUMN "points" TYPE uint8,
    ALTER COLUMN "offbash" TYPE uint8,
    ALTER COLUMN "defbash" TYPE uint8,
    ALTER COLUMN "totalbash" TYPE uint8;

ALTER TABLE villages
    ALTER COLUMN "id" TYPE uint8,
    ALTER COLUMN "pid" TYPE uint8,
    ALTER COLUMN "tid" TYPE uint8,
    ALTER COLUMN "points" TYPE uint8;

ALTER TABLE villages_history
    ALTER COLUMN "id" TYPE uint8,
    ALTER COLUMN "vid" TYPE uint8,
    ALTER COLUMN "pid" TYPE uint8,
    ALTER COLUMN "tid" TYPE uint8,
    ALTER COLUMN "points" TYPE uint8;

ALTER TABLE conquers
    ALTER COLUMN "id" TYPE uint8,
    ALTER COLUMN "vid" TYPE uint8,
    ALTER COLUMN "prevpid" TYPE uint8,
    ALTER COLUMN "nextpid" TYPE uint8,
    ALTER COLUMN "prevtid" TYPE uint8,
    ALTER COLUMN "nexttid" TYPE uint8,
    ALTER COLUMN "points" TYPE uint8;

ALTER TABLE tribe_changes
    ALTER COLUMN "id" TYPE uint8,
    ALTER COLUMN "pid" TYPE uint8,
    ALTER COLUMN "prevtid" TYPE uint8,
    ALTER COLUMN "nexttid" TYPE uint8,
    ALTER COLUMN "points" TYPE uint8,
    ALTER COLUMN "offbash" TYPE uint8,
    ALTER COLUMN "defbash" TYPE uint8,
    ALTER COLUMN "totalbash" TYPE uint8;

DROP DOMAIN dummy;