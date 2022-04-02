/* Have to DROP TABLE(s) before we DROP DOMAIN(s) */
/* DROP TABLE(s) */
DROP TABLE worlds;

--- Unsigned int type for 2 byte integer
DROP DOMAIN IF EXISTS uint2;
CREATE DOMAIN uint2 AS SMALLINT CHECK(VALUE>=0);

--- Unsigned int type for 4 byte integer
DROP DOMAIN IF EXISTS uint4;
CREATE DOMAIN uint4 AS INTEGER CHECK(VALUE>=0);

--- Unsigned int type for 8 byte integer
DROP DOMAIN IF EXISTS uint8;
CREATE DOMAIN uint8 AS BIGINT CHECK(VALUE>=0);

--- Enumaration for win condition (Using an enum for potetianally new conditions)
DROP TYPE IF EXISTS win_codition;
CREATE TYPE win_codition AS ENUM
(
    'Domination',
    'VP'
);

--- Enumaration for activity status for different in game settings
DROP TYPE IF EXISTS activity_t;
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
    "time_offset" uint2 NOT NULL,--- Time offset from (aka +1 GMT etc)
    "start" DATE NOT NULL,--- Date of when the world started
    "end" DATE,--- Date of when the world ended
    "night_start" TIME WITHOUT TIME ZONE DEFAULT '00:00:00',--- Time of when night bonus start on this world
    "night_end" TIME WITHOUT TIME ZONE DEFAULT '00:00:00',--- Time of when night bonus ends on this world
    PRIMARY KEY("id"),
    UNIQUE("wid")
);