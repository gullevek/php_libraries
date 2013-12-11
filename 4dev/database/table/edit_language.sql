-- $Id: edit_language.sql 3158 2010-09-02 02:49:00Z gullevek $
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
	short_name VARCHAR(2),
	long_name VARCHAR(70),
	iso_name VARCHAR(12),
	order_number INT,
	enabled SMALLINT NOT NULL DEFAULT 0,
	lang_default SMALLINT NOT NULL DEFAULT 0 UNIQUE
) INHERITS (edit_generic) WITHOUT OIDS;

INSERT INTO edit_language (short_name, long_name, iso_name, order_number, enabled, lang_default) VALUES ('en', 'English', 'UTF-8', 1, 1, 1);
