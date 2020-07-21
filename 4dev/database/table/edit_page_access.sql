-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- groups pages together to one page group to which the user is then subscribed
-- TABLE: edit_page_access
-- HISTORY:

-- DROP TABLE edit_page_access;
CREATE TABLE edit_page_access (
	edit_page_access_id	SERIAL PRIMARY KEY,
	edit_group_id	INT NOT NULL,
	FOREIGN KEY (edit_group_id) REFERENCES edit_group (edit_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_page_id	INT NOT NULL,
	FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_access_right_id	INT NOT NULL,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	enabled	SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;


