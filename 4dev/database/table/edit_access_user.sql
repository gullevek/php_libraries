-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- groupings which user has rights to which access groups (incl ACL)
-- TABLE: edit_access_user
-- HISTORY:

-- DROP TABLE edit_access_user;
CREATE TABLE edit_access_user (
	edit_access_user_id	SERIAL PRIMARY KEY,
	edit_default	SMALLINT DEFAULT 0,
	edit_access_id	INT NOT NULL,
	edit_user_id	INT NOT NULL,
	edit_access_right_id	INT NOT NULL,
	enabled	SMALLINT NOT NULL DEFAULT 0,
	FOREIGN KEY (edit_access_id) REFERENCES edit_access (edit_access_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_user_id) REFERENCES edit_user (edit_user_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
) INHERITS (edit_generic) WITHOUT OIDS;

DELETE FROM edit_access_user;
INSERT INTO edit_access_user (edit_default, edit_access_id, edit_user_id, edit_access_right_id) VALUES (1,
	(SELECT edit_access_id FROM edit_access WHERE uid = 'AdminAccess')
	(SELECT edit_user_id FROM edit_user WHERE username = 'admin')
	(SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
