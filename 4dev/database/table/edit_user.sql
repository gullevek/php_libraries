-- $Id: edit_user.sql 4226 2012-11-02 07:19:57Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/06
-- DESCRIPTION:
-- holds the user that can login + group, scheme, lang and a default access right
-- TABLE: edit_user
-- HISTORY:

-- DROP TABLE edit_user;
CREATE TABLE edit_user (
	edit_user_id	SERIAL PRIMARY KEY,
	username	VARCHAR UNIQUE,
	password	VARCHAR,
	enabled	SMALLINT NOT NULL DEFAULT 0,
	debug	SMALLINT NOT NULL DEFAULT 0,
	db_debug	SMALLINT NOT NULL DEFAULT 0,
	email	VARCHAR,
	protected SMALLINT NOT NULL DEFAULT 0,
	admin	SMALLINT NOT NULL DEFAULT 0,
	edit_language_id INT NOT NULL,
	edit_group_id INT NOT NULL,
	edit_scheme_id INT,
	edit_access_right_id INT NOT NULL,
	FOREIGN KEY (edit_language_id) REFERENCES edit_language (edit_language_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_group_id) REFERENCES edit_group (edit_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_scheme_id) REFERENCES edit_scheme (edit_scheme_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
) INHERITS (edit_generic) WITHOUT OIDS;

-- inserts admin user so basic users can be created
DELETE FROM edit_user;
INSERT INTO edit_user (username, password, enabled, debug, db_debug, email, protected, admin, edit_language_id, edit_group_id, edit_scheme_id, edit_access_right_id) VALUES ('admin', 'admin', 1, 1, 1, '', 1, 1, 1, 1, 2, 8);
