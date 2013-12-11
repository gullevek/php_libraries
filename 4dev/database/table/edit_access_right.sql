-- $Id: edit_access_right.sql 4382 2013-02-18 07:27:24Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- holds all access right levels for the edit interface and other access areas
-- this table is fixed, prefilled and not changable
-- TABLE: edit_access_right
-- HISTORY:

-- DROP TABLE edit_access_right;
CREATE TABLE edit_access_right (
	edit_access_right_id SERIAL PRIMARY KEY,
	name VARCHAR,
	level SMALLINT,
	type VARCHAR,
	UNIQUE (level,type)
) INHERITS (edit_generic) WITHOUT OIDS;

DELETE FROM edit_access_right;
INSERT INTO edit_access_right (name, level, type) VALUES ('Default', -1, 'default');
INSERT INTO edit_access_right (name, level, type) VALUES ('No Access', 0, 'none');
INSERT INTO edit_access_right (name, level, type) VALUES ('Read', 20, 'read');
INSERT INTO edit_access_right (name, level, type) VALUES ('Translator', 30, 'mod_trans');
INSERT INTO edit_access_right (name, level, type) VALUES ('Modify', 40, 'mod');
INSERT INTO edit_access_right (name, level, type) VALUES ('Create/Write', 60, 'write');
INSERT INTO edit_access_right (name, level, type) VALUES ('Delete', 80, 'del');
INSERT INTO edit_access_right (name, level, type) VALUES ('Site Admin', 90, 'siteadmin');
INSERT INTO edit_access_right (name, level, type) VALUES ('Admin', 100, 'admin');
