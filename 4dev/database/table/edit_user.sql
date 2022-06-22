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
	-- username/password
	username	VARCHAR UNIQUE,
	password	VARCHAR,
	-- name block
	first_name	VARCHAR,
	last_name	VARCHAR,
	first_name_furigana	VARCHAR,
	last_name_furigana	VARCHAR,
	-- email
	email	VARCHAR,
	-- eanbled/deleted flag
	enabled	SMALLINT NOT NULL DEFAULT 0,
	deleted	SMALLINT NOT NULL DEFAULT 0,
	-- general flags
	strict	SMALLINT DEFAULT 0,
	locked	SMALLINT DEFAULT 0,
	protected SMALLINT NOT NULL DEFAULT 0,
	-- legacy, debug flags
	debug	SMALLINT NOT NULL DEFAULT 0,
	db_debug	SMALLINT NOT NULL DEFAULT 0,
	-- is admin user
	admin	SMALLINT NOT NULL DEFAULT 0,
	-- last login log
	last_login	TIMESTAMP WITHOUT TIME ZONE,
	-- login error
	login_error_count	INT DEFAULT 0,
	login_error_date_last	TIMESTAMP WITHOUT TIME ZONE,
	login_error_date_first	TIMESTAMP WITHOUT TIME ZONE,
	-- time locked
	lock_until	TIMESTAMP WITHOUT TIME ZONE,
	lock_after	TIMESTAMP WITHOUT TIME ZONE,
	-- password change
	password_change_date	TIMESTAMP WITHOUT TIME ZONE, -- only when password is first set or changed
	password_change_interval	INTERVAL, -- null if no change is needed, or d/m/y time interval
	password_reset_time	TIMESTAMP WITHOUT TIME ZONE, -- when the password reset was requested
	password_reset_uid	VARCHAR, -- the uid to access the password reset page
	-- _GET login id for direct login
	login_user_id	VARCHAR UNIQUE, -- the login uid, at least 32 chars
	login_user_id_set_date	TIMESTAMP WITHOUT TIME ZONE, -- when above uid was set
	login_user_id_last_login	TIMESTAMP WITHOUT TIME ZONE, -- when the last login was done with user name and password
	login_user_id_valid_from	TIMESTAMP WITHOUT TIME ZONE, -- if set, from when the above uid is valid
	login_user_id_valid_until	TIMESTAMP WITHOUT TIME ZONE, -- if set, until when the above uid is valid
	login_user_id_revalidate_after	INTERVAL, -- user must login to revalidated login id after set days, 0 for forever
	login_user_id_locked	SMALLINT DEFAULT 0, -- lock for login user id, but still allow normal login
	-- additional ACL json block
	additional_acl	JSONB -- additional ACL as JSON string (can be set by other pages)
) INHERITS (edit_generic) WITHOUT OIDS;

-- create unique index
-- CREATE UNIQUE INDEX edit_user_login_user_id_key ON edit_user (login_user_id) WHERE login_user_id IS NOT NULL;

COMMENT ON COLUMN edit_user.username IS 'Login username, must set';
COMMENT ON COLUMN edit_user.password IS 'Login password, must set';
COMMENT ON COLUMN edit_user.enabled IS 'Login is enabled (master switch)';
COMMENT ON COLUMN edit_user.deleted IS 'Login is deleted (master switch), overrides all other';
COMMENT ON COLUMN edit_user.strict IS 'If too many failed logins user will be locked, default off';
COMMENT ON COLUMN edit_user.locked IS 'Locked from too many wrong password logins';
COMMENT ON COLUMN edit_user.protected IS 'User can only be chnaged by admin user';
COMMENT ON COLUMN edit_user.debug IS 'Turn debug flag on (legacy)';
COMMENT ON COLUMN edit_user.db_debug IS 'Turn DB debug flag on (legacy)';
COMMENT ON COLUMN edit_user.admin IS 'If set, this user is SUPER admin';
COMMENT ON COLUMN edit_user.last_login IS 'Last succesfull login tiemstamp';
COMMENT ON COLUMN edit_user.login_error_count IS 'Number of failed logins, reset on successful login';
COMMENT ON COLUMN edit_user.login_error_date_last IS 'Last login error date';
COMMENT ON COLUMN edit_user.login_error_date_first IS 'First login error date, reset on successfull login';
COMMENT ON COLUMN edit_user.lock_until IS 'Account is locked until this date, <';
COMMENT ON COLUMN edit_user.lock_after IS 'Account is locked after this date, >';
COMMENT ON COLUMN edit_user.password_change_date IS 'Password was changed on';
COMMENT ON COLUMN edit_user.password_change_interval IS 'After how many days the password has to be changed';
COMMENT ON COLUMN edit_user.password_reset_time IS 'When the password reset was requested. For reset page uid valid check';
COMMENT ON COLUMN edit_user.password_reset_uid IS 'Password reset page uid, one time, invalid after reset successful or time out';
COMMENT ON COLUMN edit_user.login_user_id IS 'Min 32 character UID to be used to login without password. Via GET/POST parameter';
COMMENT ON COLUMN edit_user.login_user_id_set_date IS 'login id was set at what date';
COMMENT ON COLUMN edit_user.login_user_id_last_login IS 'set when username/password login is done';
COMMENT ON COLUMN edit_user.login_user_id_valid_from IS 'login id is valid from this date, >=';
COMMENT ON COLUMN edit_user.login_user_id_valid_until IS 'login id is valid until this date, <=';
COMMENT ON COLUMN edit_user.login_user_id_revalidate_after IS 'If set to a number greater 0 then user must login after given amount of days to revalidate, set to 0 for valid forver';
COMMENT ON COLUMN edit_user.login_user_id_locked IS 'A separte lock flag for login id, user can still login normal';
COMMENT ON COLUMN edit_user.additional_acl IS 'Additional Access Control List stored in JSON format';
