-- AUTHOR: Clemens Schwaighofer
-- DATE: 2019/9/9
-- DESCRIPTION:
-- sub content to one page with additional edit access right set
-- can be eg JS content groups on one page
-- TABLE: edit_page_content
-- HISTORY:

-- DROP TABLE edit_page_content;
CREATE TABLE edit_page_content (
	edit_page_content_id	SERIAL PRIMARY KEY,
	edit_page_id	INT NOT NULL,
	edit_access_right_id	INT NOT NULL,
	name	VARCHAR,
	uid	VARCHAR UNIQUE,
	order_number INT NOT NULL,
	online	SMALLINT NOT NULL DEFAULT 0,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
) INHERITS (edit_generic) WITHOUT OIDS;
