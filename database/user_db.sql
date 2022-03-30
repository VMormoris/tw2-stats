
/* Have to DROP TABLE(s) before we DROP DOMAIN(s) & ENUMARATION(S) */
/* DROP TABLE(s) */
DROP TABLE IF EXISTS tokens;
DROP TABLE IF EXISTS resets;
DROP TABLE IF EXISTS signin_log;
DROP TABLE IF EXISTS users;

--- Unsigned int type for 2 byte integer
DROP DOMAIN IF EXISTS uint2;
CREATE DOMAIN uint2 AS SMALLINT CHECK(VALUE>=0);

--- Unsigned int type for 4 byte integer
DROP DOMAIN IF EXISTS uint4;
CREATE DOMAIN uint4 AS INTEGER CHECK(VALUE>=0);

--- Unsigned int type for 8 byte integer
DROP DOMAIN IF EXISTS uint8;
CREATE DOMAIN uint8 AS INTEGER CHECK(VALUE>=0);

--- Enumaration for different levels of privileges that can be assign to a user
DROP TYPE IF EXISTS user_privileges;
CREATE TYPE user_privileges AS ENUM
(
    'default',---A normal user that just create an acount 
    'mod',--- A user higher level privileges than a normal user
    'admin'---The highest level privileges that one can have
);

-- Enumaratio for user's state
DROP TYPE IF EXISTS user_state;
CREATE TYPE user_state AS ENUM
(
	'new',--- User was just created
	'valid',--- User has validate his email address (and whatever else is needed it)
	'blocked',--- User has been blocked due to many failed attempts to login
	'banned'--- User is permantly banned (for some reason)
);

--- Enumaration for user flag on specific attempt
DROP TYPE IF EXISTS log_flag;
CREATE TYPE log_flag AS ENUM
(
    'not_flagged',--- Not flagged from a user yet
    'dont_know',--- User isn't sure if he attempt it or someone else
    'safe',--- User is sure that the login attempt was from him/her
    'malicious'--- User has flagged the attempt as a malicious attempt that was done from him
);

--- Enumaration for resets states
DROP TYPE IF EXISTS resets_state;
CREATE TYPE resets_state AS ENUM
(
	'new_user',--- Reset link is for a new user (link-shouldn't be expire)
	'forgot_passwd',--- Reset link that was asked by the user because he forgot his/her password
	'reset_passwd',--- Reset link that was send to user because he had too many failed attempts
	'expired'--- Link has expired
);

--- Users' table
CREATE TABLE users
(
    "id" BIGSERIAL,--- User's ID (only for internal use)
    "username" VARCHAR(64) NOT NULL,--- User's nickname (Not sure if should be unique or not yet)
    /**
    * According to the results that googles mines for me from rfc-editor.org, 320 is the limit for email addresses: https://prnt.sc/zLMTKFFMkjmF
    *   if you know anything more please contact me :P
    */
    "email" VARCHAR(320) NOT NULL,--- User's email address must be validated
    "salt" VARCHAR(16) NOT NULL,--- To be blend and hash with password. - I'm thinking an 128 bit UUID for salt so it's hopefully unique for everybody (but is it enough for security?)
    "password" VARCHAR(512) NOT NULL,--- We are using SHA-512 together with the salt to store the password
    "privileges" privileges DEFAULT 'default',--- User's privileges level
    /**
    * Unfortunately I'm working alone on this project so I can't even attempt to monitor properly users accounts from
    *   malicious users. So after N number of failed attempts I'm planning to block the user and email him to change password
    *   just for adding an extra layer of security I understand that it might be annoying but I can't do anything else with my resources.
    */
    "attempts" uint2 DEFAULT 0,--- Number of attempts left to enter the password wrong before the account is blocked
    "state" user_state DEFAULT true,--- Whether the user is blocked or not (User start as block until the validate their email)
    "created" TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,--- Time uppon the account was created
    PRIMARY KEY("id"),
    UNIQUE("email")
);

/**
* Table that only used on the background for security reasons
*   this tables aim to log every sign in attempt by users
*/
CREATE TABLE signin_log
(
    "id" BIGSERIAL,--- Log's ID (only for internal use) 
    "uid" uint8 NOT NULL,--- User's ID
    "flag" log_flag NOT NULL,--- Flag that user has assign to the specific record
    "ip" VARCHAR(15) NOT NULL,--- IPv4 that was used during the signin attempt
    "result" BOOLEAN NOT NULL,---  Whether the signin attempt was successful or not
    "timestamp" TIMESTAMP WITHOUT TIME ZONE NOT NULL,--- Timestamp for when the signin try occuried
    PRIMARY KEY("id"),
    FOREIGN KEY("uid") REFERENCES users("id")
);

/**
* Table that contains "links" that can be used by user in order to change password
*   here are also stored "links" for newly created users in order to verify their email
*/
CREATE TABLE resets
(
    "id" BIGSERIAL,--- Record's ID (only for internal use)
    "uid" uint8 NOT NULL,--- User's ID
    "state" resets_state NOT NULL,--- Flag for whether they link is for a new account or not
    "link" VARCHAR(128) NOT NULL,--- Link that will be append to the url in order to be used by the user to change password or verify it's email address
    ---TODO(Vasilis): Maybe at default the earliest date possible (as an invalid timestamp)
    "expires" TIMESTAMP WITHOUT TIME ZONE NOT NULL,--- Timestamp for when the links expires
    PRIMARY KEY("id"),
    FOREIGN KEY("uid") REFERENCES users("id"),
    UNIQUE("link")
);

/**
* Table that contains tokens that can be used by a user instead of a password
*   The aims behind those tokens is to minized password transactions for security reasons
*/
CREATE TABLE tokens
(
    "id" BIGSERIAL,--- Token's ID (only for internal use)
    "uid" uint8 NOT NULL,--- User's ID
    "token" VARCHAR(16) NOT NULL,--- To be used by user after authetication in order to not used password on the next transactions
    "expires" TIMESTAMP WITHOUT TIME ZONE NOT NULL,--- Timestamp for when token expires
    PRIMARY KEY("id"),
    FOREIGN KEY("uid") REFERENCES users("id")
);