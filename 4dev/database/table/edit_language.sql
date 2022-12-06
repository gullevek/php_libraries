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
    enabled SMALLINT NOT NULL DEFAULT 0,
    lang_default SMALLINT NOT NULL DEFAULT 0,
    long_name VARCHAR,
    short_name VARCHAR, -- en_US, en or en_US@latin without encoding
    iso_name VARCHAR, -- should actually be encoding
    order_number INT
) INHERITS (edit_generic) WITHOUT OIDS;
