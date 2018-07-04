-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, this table contains all pages in the edit interface and allocates rights + values to it
-- TABLE: edit_table
-- HISTORY:

-- DROP TABLE edit_page;
CREATE TABLE edit_page (
	edit_page_id	SERIAL PRIMARY KEY,
	filename	VARCHAR,
	name	VARCHAR UNIQUE,
	order_number INT NOT NULL,
	online	SMALLINT NOT NULL DEFAULT 0,
	menu	SMALLINT NOT NULL DEFAULT 0,
	popup	SMALLINT NOT NULL DEFAULT 0,
	popup_x	SMALLINT,
	popup_y SMALLINT
) INHERITS (edit_generic) WITHOUT OIDS;
