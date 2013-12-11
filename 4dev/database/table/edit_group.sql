-- $Id: edit_group.sql 3158 2010-09-02 02:49:00Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- list of pages the user can access, with a generic access level, one group per user
-- TABLE: edit_group
-- HISTORY:

-- DROP TABLE edit_group;
CREATE TABLE edit_group (
	edit_group_id	SERIAL PRIMARY KEY,
	name	VARCHAR(50),
	enabled	SMALLINT NOT NULL DEFAULT 0,
	edit_scheme_id INT,
	edit_access_right_id INT NOT NULL,
	FOREIGN KEY (edit_scheme_id) REFERENCES edit_scheme (edit_scheme_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
) INHERITS (edit_generic) WITHOUT OIDS;

INSERT INTO edit_group (name, enabled, edit_scheme_id, edit_access_right_id) VALUES ('Admin', 1, 2, 8);
