-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- holds all access right levels for the edit interface and other access areas
-- this table is fixed, prefilled and not changable
-- TABLE: edit_access_right
-- HISTORY:

-- DROP TABLE edit_access_right;
CREATE TABLE edit_access_right (
    edit_access_right_id SERIAL PRIMARY KEY,
    name VARCHAR,
    level SMALLINT,
    type VARCHAR,
    UNIQUE (level,type)
) INHERITS (edit_generic) WITHOUT OIDS;
