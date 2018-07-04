-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- holds backend template schemes
-- TABLE: edit_scheme
-- HISTORY:

-- DROP TABLE edit_scheme;
CREATE TABLE edit_scheme (
	edit_scheme_id	SERIAL PRIMARY KEY,
	enabled	SMALLINT NOT NULL DEFAULT 0,
	name	VARCHAR,
	header_color	VARCHAR,
	css_file	VARCHAR,
	template	VARCHARs
) INHERITS (edit_generic) WITHOUT OIDS;
