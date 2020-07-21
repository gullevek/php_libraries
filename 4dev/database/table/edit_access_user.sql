-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- groupings which user has rights to which access groups (incl ACL)
-- TABLE: edit_access_user
-- HISTORY:

-- DROP TABLE edit_access_user;
CREATE TABLE edit_access_user (
	edit_access_user_id	SERIAL PRIMARY KEY,
	edit_access_id	INT NOT NULL,
	FOREIGN KEY (edit_access_id) REFERENCES edit_access (edit_access_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_user_id	INT NOT NULL,
	FOREIGN KEY (edit_user_id) REFERENCES edit_user (edit_user_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_access_right_id	INT NOT NULL,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_default	SMALLINT DEFAULT 0,
	enabled	SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;
