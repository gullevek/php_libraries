-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- languages for the backend, this not used for the encoding, but only for having different language strings
-- the backend encoding is all UTF-8 (not changeable)
-- TABLE: edit_language
-- HISTORY:

-- DROP TABLE edit_language;
CREATE TABLE edit_language (
	edit_language_id SERIAL PRIMARY KEY,
	short_name VARCHAR,
	long_name VARCHAR,
	iso_name VARCHAR,
	order_number INT,
	enabled SMALLINT NOT NULL DEFAULT 0,
	lang_default SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;

INSERT INTO edit_language (short_name, long_name, iso_name, order_number, enabled, lang_default) VALUES ('en', 'English', 'UTF-8', 1, 1, 1);
INSERT INTO edit_language (short_name, long_name, iso_name, order_number, enabled, lang_default) VALUES ('ja', 'Japanese', 'UTF-8', 2, 1, 0);
