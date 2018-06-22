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

INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Default Scheme', 'E0E2FF', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Admin', 'CC7E7E', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Visitor', 'B0C4B3', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('User', '1E789E', 1);
