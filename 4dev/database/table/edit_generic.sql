-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, this is the generic table, inheriteded by most edit tables
-- TABLE: edit_generic
-- HISTORY:

-- DROP TABLE edit_generic;
CREATE TABLE edit_generic (
    cuid VARCHAR,
    date_created TIMESTAMP WITHOUT TIME ZONE DEFAULT clock_timestamp(),
    date_updated TIMESTAMP WITHOUT TIME ZONE
);
