-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, this table contains all pages in the edit interface and allocates rights + values to it
-- TABLE: edit_page
-- HISTORY:

-- DROP TABLE edit_page;
CREATE TABLE edit_page (
	edit_page_id	SERIAL PRIMARY KEY,
	content_alias_edit_page_id	INT, -- alias for page content, if the page content is defined on a different page, ege for ajax backend pages
	FOREIGN KEY (content_alias_edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE,
	filename	VARCHAR,
	name	VARCHAR UNIQUE,
	order_number INT NOT NULL,
	online	SMALLINT NOT NULL DEFAULT 0,
	menu	SMALLINT NOT NULL DEFAULT 0,
	popup	SMALLINT NOT NULL DEFAULT 0,
	popup_x	SMALLINT,
	popup_y SMALLINT,
	hostname	VARCHAR
) INHERITS (edit_generic) WITHOUT OIDS;
