-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, groupings for menu
-- TABLE: edit_menu_group
-- HISTORY

-- DROP TABLE edit_menu_group;
CREATE TABLE edit_menu_group (
    edit_menu_group_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR,
    flag VARCHAR,
    order_number INT NOT NULL
) INHERITS (edit_generic) WITHOUT OIDS;


