-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- groups pages together to one page group to which the user is then subscribed
-- TABLE: edit_page_access
-- HISTORY:

-- DROP TABLE edit_page_access;
CREATE TABLE edit_page_access (
	edit_page_access_id	SERIAL PRIMARY KEY,
	enabled	SMALLINT NOT NULL DEFAULT 0,
	edit_group_id	INT NOT NULL,
	edit_page_id	INT NOT NULL,
	edit_access_right_id	INT NOT NULL,
	FOREIGN KEY (edit_group_id) REFERENCES edit_group (edit_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
) INHERITS (edit_generic) WITHOUT OIDS;

INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 1, 8);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 2, 8);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 3, 8);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 4, 8);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 5, 8);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 6, 8);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 7, 8);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 8, 8);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1, 1, 9, 8);
