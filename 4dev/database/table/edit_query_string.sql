-- $Id: edit_query_string.sql 3158 2010-09-02 02:49:00Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables
-- TABLE: edit_query_string
-- HISTORY:

-- DROP TABLE edit_query_string;
CREATE TABLE edit_query_string (
	edit_query_string_id	SERIAL PRIMARY KEY,
	name	VARCHAR(255),
	value	VARCHAR(255),
	enabled	SMALLINT NOT NULL DEFAULT 0,
	dynamic	SMALLINT NOT NULL DEFAULT 0,
	edit_page_id	INT NOT NULL,
	FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
) INHERITS (edit_generic) WITHOUT OIDS;
