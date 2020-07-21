-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/06
-- DESCRIPTION:
-- holds the user that can login + group, scheme, lang and a default access right
-- TABLE: edit_user
-- HISTORY:

-- DROP TABLE edit_user;
CREATE TABLE edit_user (
	edit_user_id	SERIAL PRIMARY KEY,
	connect_edit_user_id	INT, -- possible reference to other user
	FOREIGN KEY (connect_edit_user_id) REFERENCES edit_user (edit_user_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_language_id INT NOT NULL,
	FOREIGN KEY (edit_language_id) REFERENCES edit_language (edit_language_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_group_id INT NOT NULL,
	FOREIGN KEY (edit_group_id) REFERENCES edit_group (edit_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_scheme_id INT,
	FOREIGN KEY (edit_scheme_id) REFERENCES edit_scheme (edit_scheme_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	edit_access_right_id INT NOT NULL,
	FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	enabled	SMALLINT NOT NULL DEFAULT 0,
	deleted	SMALLINT NOT NULL DEFAULT 0,
	username	VARCHAR UNIQUE,
	password	VARCHAR,
	first_name	VARCHAR,
	last_name	VARCHAR,
	first_name_furigana	VARCHAR,
	last_name_furigana	VARCHAR,
	debug	SMALLINT NOT NULL DEFAULT 0,
	db_debug	SMALLINT NOT NULL DEFAULT 0,
	email	VARCHAR,
	protected SMALLINT NOT NULL DEFAULT 0,
	admin	SMALLINT NOT NULL DEFAULT 0,
	login_error_count	INT DEFAULT 0,
	login_error_date_last	TIMESTAMP WITHOUT TIME ZONE,
	login_error_date_first	TIMESTAMP WITHOUT TIME ZONE,
	strict	SMALLINT DEFAULT 0,
	locked	SMALLINT DEFAULT 0,
	password_change_date	TIMESTAMP WITHOUT TIME ZONE, -- only when password is first set or changed
	password_change_interval	INTERVAL, -- null if no change is needed, or d/m/y time interval
	additional_acl	JSONB -- additional ACL as JSON string (can be set by other pages)
) INHERITS (edit_generic) WITHOUT OIDS;
