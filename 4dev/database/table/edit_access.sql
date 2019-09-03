-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- is a "group" for the outside, a user can have serveral groups with different rights so he can access several parts from the outside
-- TABLE: edit_access
-- HISTORY:

-- DROP TABLE edit_access;
CREATE TABLE edit_access (
	edit_access_id	SERIAL PRIMARY KEY,
	name	VARCHAR UNIQUE,
	description	VARCHAR,
	color	VARCHAR,
	uid	VARCHAR,
	enabled	SMALLINT NOT NULL DEFAULT 0,
	protected INT,
	deleted	SMALLINT DEFAULT 0,
	additional_acl	JSONB
) INHERITS (edit_generic) WITHOUT OIDS;
