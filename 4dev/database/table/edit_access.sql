-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- is a "group" for the outside, a user can have serveral groups with different rights so he can access several parts from the outside
-- TABLE: edit_access
-- HISTORY:

-- DROP TABLE edit_access;
CREATE TABLE edit_access (
    edit_access_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    enabled SMALLINT NOT NULL DEFAULT 0,
    protected SMALLINT DEFAULT 0,
    deleted SMALLINT DEFAULT 0,
    uid VARCHAR,
    name VARCHAR UNIQUE,
    description VARCHAR,
    color VARCHAR,
    additional_acl JSONB
) INHERITS (edit_generic) WITHOUT OIDS;
