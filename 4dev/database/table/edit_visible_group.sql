-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, postgres SQL statements for the mysql definitions
-- TABLE: edit_visible_group
-- HISTORY

-- DROP TABLE edit_visible_group;
CREATE TABLE edit_visible_group (
    edit_visible_group_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR,
    flag VARCHAR
) INHERITS (edit_generic) WITHOUT OIDS;
