-- $Id: edit_access.sql 4382 2013-02-18 07:27:24Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- is a "group" for the outside, a user can have serveral groups with different rights so he can access several parts from the outside
-- TABLE: edit_access
-- HISTORY:

-- DROP TABLE edit_access;
CREATE TABLE edit_access (
	edit_access_id	SERIAL PRIMARY KEY,
	name	VARCHAR(255) UNIQUE,
	description	VARCHAR,
	COLOR	VARCHAR
) INHERITS (edit_generic) WITHOUT OIDS;

DELETE FROM edit_access;
INSERT INTO edit_access (name) VALUES ('Admin Access');
