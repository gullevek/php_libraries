-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables
-- TABLE: edit_query_string
-- HISTORY:

-- DROP TABLE edit_query_string;
CREATE TABLE edit_query_string (
    edit_query_string_id SERIAL PRIMARY KEY,
    edit_page_id INT NOT NULL,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    enabled SMALLINT NOT NULL DEFAULT 0,
    name VARCHAR,
    value VARCHAR,
    dynamic SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;
