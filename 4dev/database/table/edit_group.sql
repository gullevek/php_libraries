-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- list of pages the user can access, with a generic access level, one group per user
-- TABLE: edit_group
-- HISTORY:

-- DROP TABLE edit_group;
CREATE TABLE edit_group (
    edit_group_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    edit_scheme_id INT,
    FOREIGN KEY (edit_scheme_id) REFERENCES edit_scheme (edit_scheme_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_access_right_id INT NOT NULL,
    FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    enabled SMALLINT NOT NULL DEFAULT 0,
    deleted SMALLINT DEFAULT 0,
    uid VARCHAR,
    name VARCHAR,
    additional_acl JSONB
) INHERITS (edit_generic) WITHOUT OIDS;
