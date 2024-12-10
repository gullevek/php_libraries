-- START: function/random_string.sql
-- create random string with length X

CREATE FUNCTION random_string(randomLength int)
RETURNS text AS
$$
SELECT array_to_string(
    ARRAY(
        SELECT substring(
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            trunc(random() * 62)::int + 1,
            1
        )
        FROM generate_series(1, randomLength) AS gs(x)
    ),
    ''
)
$$
LANGUAGE SQL
RETURNS NULL ON NULL INPUT
VOLATILE; -- LEAKPROOF;
-- END: function/random_string.sql
-- START: function/set_edit_generic.sql
-- adds the created or updated date tags

CREATE OR REPLACE FUNCTION set_edit_generic()
RETURNS TRIGGER AS
$$
DECLARE
    random_length INT = 12; -- that should be long enough
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.date_created := 'now';
        NEW.cuid := random_string(random_length);
        NEW.cuuid := gen_random_uuid();
    ELSIF TG_OP = 'UPDATE' THEN
        NEW.date_updated := 'now';
    END IF;
    RETURN NEW;
END;
$$
LANGUAGE 'plpgsql';
-- END: function/set_edit_generic.sql
-- START: function/edit_access_set_uid.sql
-- add uid add for edit_access table

CREATE OR REPLACE FUNCTION set_edit_access_uid() RETURNS TRIGGER AS
$$
DECLARE
    myrec RECORD;
    v_uid VARCHAR;
BEGIN
    -- skip if NEW.name is not set
    IF NEW.name IS NOT NULL AND NEW.name <> '' THEN
        -- use NEW.name as base, remove all spaces
        -- name data is already unique, so we do not need to worry about this here
        v_uid := REPLACE(NEW.name, ' ', '');
        IF TG_OP = 'INSERT' THEN
            -- always set
            NEW.uid := v_uid;
        ELSIF TG_OP = 'UPDATE' THEN
            -- check if not set, then set
            SELECT INTO myrec t.* FROM edit_access t WHERE edit_access_id = NEW.edit_access_id;
            IF FOUND THEN
                NEW.uid := v_uid;
            END IF;
        END IF;
    END IF;
    RETURN NEW;
END;
$$
    LANGUAGE 'plpgsql';
-- END: function/edit_access_set_uid.sql
-- START: function/edit_group_set_uid.sql
-- add uid add for edit_group table

CREATE OR REPLACE FUNCTION set_edit_group_uid() RETURNS TRIGGER AS
$$
DECLARE
    myrec RECORD;
    v_uid VARCHAR;
BEGIN
    -- skip if NEW.name is not set
    IF NEW.name IS NOT NULL AND NEW.name <> '' THEN
        -- use NEW.name as base, remove all spaces
        -- name data is already unique, so we do not need to worry about this here
        v_uid := REPLACE(NEW.name, ' ', '');
        IF TG_OP = 'INSERT' THEN
            -- always set
            NEW.uid := v_uid;
        ELSIF TG_OP = 'UPDATE' THEN
            -- check if not set, then set
            SELECT INTO myrec t.* FROM edit_group t WHERE edit_group_id = NEW.edit_group_id;
            IF FOUND THEN
                NEW.uid := v_uid;
            END IF;
        END IF;
    END IF;
    RETURN NEW;
END;
$$
    LANGUAGE 'plpgsql';
-- END: function/edit_group_set_uid.sql
-- START: function/edit_log_partition_insert.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2018-07-17
-- DESCRIPTION:
-- partition the edit_log table by year
-- auto creates table if missing, if failure writes to overflow table
-- HISTORY:

CREATE OR REPLACE FUNCTION edit_log_insert_trigger ()
RETURNS TRIGGER AS
$$
DECLARE
    start_date DATE := '2010-01-01';
    end_date DATE;
    timeformat TEXT := 'YYYY';
    selector TEXT := 'year';
    base_table TEXT := 'edit_log';
    _interval INTERVAL := '1 ' || selector;
    _interval_next INTERVAL := '2 ' || selector;
    table_name TEXT;
    -- compare date column
    compare_date DATE := NEW.event_date;
    compare_date_name TEXT := 'event_date';
    -- the create commands
    command_create_table TEXT := 'CREATE TABLE IF NOT EXISTS {TABLE_NAME} (CHECK({COMPARE_DATE_NAME} >= {START_DATE} AND {COMPARE_DATE_NAME} < {END_DATE})) INHERITS ({BASE_NAME})';
    command_create_primary_key TEXT := 'ALTER TABLE {TABLE_NAME} ADD PRIMARY KEY ({BASE_TABLE}_id)';
    command_create_foreign_key_1 TEXT := 'ALTER TABLE {TABLE_NAME} ADD CONSTRAINT {TABLE_NAME}_euid_fkey FOREIGN KEY (euid) REFERENCES edit_user (edit_user_id) MATCH FULL ON UPDATE CASCADE ON DELETE SET NULL';
    command_create_trigger_1 TEXT = 'CREATE TRIGGER trg_{TABLE_NAME} BEFORE INSERT OR UPDATE ON {TABLE_NAME} FOR EACH ROW EXECUTE PROCEDURE set_edit_generic()';
BEGIN
    -- we are in valid start time area
    IF (NEW.event_date >= start_date) THEN
        -- current table name
        table_name := base_table || '_' || to_char(NEW.event_date, timeformat);
        BEGIN
            EXECUTE 'INSERT INTO ' || quote_ident(table_name) || ' SELECT ($1).*' USING NEW;
        -- if insert failed because of missing table, create new below
        EXCEPTION
        WHEN undefined_table THEN
            -- another block, so in case the creation fails here too
            BEGIN
                -- create new table here + all indexes
                start_date := date_trunc(selector, NEW.event_date);
                end_date := date_trunc(selector, NEW.event_date + _interval);
                -- creat table
                EXECUTE format(REPLACE( -- end date
                    REPLACE( -- start date
                        REPLACE( -- compare date name
                            REPLACE( -- base name (inherit)
                                REPLACE( -- table name
                                    command_create_table,
                                    '{TABLE_NAME}',
                                    table_name
                                ),
                                '{BASE_NAME}',
                                base_table
                            ),
                            '{COMPARE_DATE_NAME}',
                            compare_date_name
                        ),
                        '{START_DATE}',
                        quote_literal(start_date)
                    ),
                    '{END_DATE}',
                    quote_literal(end_date)
                ));
                -- create all indexes and triggers
                EXECUTE format(REPLACE(
                    REPLACE(
                        command_create_primary_key,
                        '{TABLE_NAME}',
                        table_name
                    ),
                    '{BASE_TABLE}',
                    base_table
                ));
                -- FK constraints
                EXECUTE format(REPLACE(command_create_foreign_key_1, '{TABLE_NAME}', table_name));
                -- generic trigger
                EXECUTE format(REPLACE(command_create_trigger_1, '{TABLE_NAME}', table_name));

                -- insert try again
                EXECUTE 'INSERT INTO ' || quote_ident(table_name) || ' SELECT ($1).*' USING NEW;
            EXCEPTION
            WHEN OTHERS THEN
                -- if this faled, throw it into the overflow table (so we don't loose anything)
                INSERT INTO edit_log_overflow VALUES (NEW.*);
            END;
        -- other errors, insert into overlow
        WHEN OTHERS THEN
            -- if this faled, throw it into the overflow table (so we don't loose anything)
            INSERT INTO edit_log_overflow VALUES (NEW.*);
        END;
        -- main insert run done, check if we have to create next months table
        BEGIN
            -- check if next month table exists
            table_name := base_table || '_' || to_char((SELECT NEW.event_date + _interval)::DATE, timeformat);
            -- RAISE NOTICE 'SEARCH NEXT: %', table_name;
            IF  (SELECT to_regclass(table_name)) IS NULL THEN
                -- move inner interval same
                start_date := date_trunc(selector, NEW.event_date + _interval);
                end_date := date_trunc(selector, NEW.event_date + _interval_next);
                -- RAISE NOTICE 'CREATE NEXT: %', table_name;
                -- create table
                EXECUTE format(REPLACE( -- end date
                    REPLACE( -- start date
                        REPLACE( -- compare date name
                            REPLACE( -- base name (inherit)
                                REPLACE( -- table name
                                    command_create_table,
                                    '{TABLE_NAME}',
                                    table_name
                                ),
                                '{BASE_NAME}',
                                base_table
                            ),
                            '{COMPARE_DATE_NAME}',
                            compare_date_name
                        ),
                        '{START_DATE}',
                        quote_literal(start_date)
                    ),
                    '{END_DATE}',
                    quote_literal(end_date)
                ));
                -- create all indexes and triggers
                EXECUTE format(REPLACE(
                    REPLACE(
                        command_create_primary_key,
                        '{TABLE_NAME}',
                        table_name
                    ),
                    '{BASE_TABLE}',
                    base_table
                ));
                -- FK constraints
                EXECUTE format(REPLACE(command_create_foreign_key_1, '{TABLE_NAME}', table_name));
                -- generic trigger
                EXECUTE format(REPLACE(command_create_trigger_1, '{TABLE_NAME}', table_name));
            END IF;
        EXCEPTION
        WHEN OTHERS THEN
            RAISE NOTICE 'Failed to create next table: %', table_name;
        END;
    ELSE
        -- if outside valid date, insert into overflow
        INSERT INTO edit_log_overflow VALUES (NEW.*);
    END IF;
    RETURN NULL;
END
$$
LANGUAGE 'plpgsql';
-- END: function/edit_log_partition_insert.sql
-- START: function/edit_user_set_login_user_id_set_date.sql
-- set edit user login_user_id_set_date if login_user_id is set
-- NOW() if not empty

CREATE OR REPLACE FUNCTION set_login_user_id_set_date()
RETURNS TRIGGER AS
$$
BEGIN
    -- if new is not null/empty
    -- and old one is null or old one different new one
    -- set NOW()
    -- if new one is NULL
    -- set NULL
    IF
        NEW.login_user_id IS NOT NULL AND NEW.login_user_id <> '' AND
        (OLD.login_user_id IS NULL OR NEW.login_user_id <> OLD.login_user_id)
    THEN
        NEW.login_user_id_set_date = NOW();
        NEW.login_user_id_last_revalidate = NOW();
    ELSIF NEW.login_user_id IS NULL OR NEW.login_user_id = '' THEN
        NEW.login_user_id_set_date = NULL;
        NEW.login_user_id_last_revalidate = NULL;
    END IF;
    RETURN NEW;
END;
$$
LANGUAGE 'plpgsql';
-- END: function/edit_user_set_login_user_id_set_date.sql
-- START: table/edit_temp_files.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/08
-- DESCRIPTION:
-- edit interface temporary files, list of all files in edit (admin) directory
-- TABLE: temp_files
-- HISTORY:

-- DROP TABLE temp_files;
CREATE TABLE temp_files (
    filename VARCHAR,
    folder VARCHAR
);
-- END: table/edit_temp_files.sql
-- START: table/edit_generic.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, this is the generic table, inheriteded by most edit tables
-- TABLE: edit_generic
-- HISTORY:

-- DROP TABLE edit_generic;
CREATE TABLE edit_generic (
    cuid VARCHAR,
    cuuid UUID,
    date_created TIMESTAMP WITHOUT TIME ZONE DEFAULT clock_timestamp(),
    date_updated TIMESTAMP WITHOUT TIME ZONE
);
-- END: table/edit_generic.sql
-- START: table/edit_visible_group.sql
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
-- END: table/edit_visible_group.sql
-- START: table/edit_menu_group.sql
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


-- END: table/edit_menu_group.sql
-- START: table/edit_page.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, this table contains all pages in the edit interface and allocates rights + values to it
-- TABLE: edit_page
-- HISTORY:

-- DROP TABLE edit_page;
CREATE TABLE edit_page (
    edit_page_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    content_alias_edit_page_id	INT, -- alias for page content, if the page content is defined on a different page, ege for ajax backend pages
    FOREIGN KEY (content_alias_edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE,
    filename VARCHAR,
    name VARCHAR UNIQUE,
    order_number INT NOT NULL,
    online SMALLINT NOT NULL DEFAULT 0,
    menu SMALLINT NOT NULL DEFAULT 0,
    popup SMALLINT NOT NULL DEFAULT 0,
    popup_x SMALLINT,
    popup_y SMALLINT,
    hostname VARCHAR
) INHERITS (edit_generic) WITHOUT OIDS;
-- END: table/edit_page.sql
-- START: table/edit_query_string.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables
-- TABLE: edit_query_string
-- HISTORY:

-- DROP TABLE edit_query_string;
CREATE TABLE edit_query_string (
    edit_query_string_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    edit_page_id INT NOT NULL,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    enabled SMALLINT NOT NULL DEFAULT 0,
    name VARCHAR,
    value VARCHAR,
    dynamic SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;
-- END: table/edit_query_string.sql
-- START: table/edit_page_visible_group.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- reference table between visible groups and pages
-- TABLE: edit_page_visible_group
-- HISTORY:

-- DROP TABLE edit_page_visible_group;
CREATE TABLE edit_page_visible_group (
    edit_page_id INT NOT NULL,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_visible_group_id INT NOT NULL,
    FOREIGN KEY (edit_visible_group_id) REFERENCES edit_visible_group (edit_visible_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
);
-- END: table/edit_page_visible_group.sql
-- START: table/edit_page_menu_group.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- reference table between menu groups and pages
-- TABLE: edit_page_menu_group
-- HISTORY:

-- DROP TABLE edit_page_menu_group;
CREATE TABLE edit_page_menu_group (
    edit_page_id INT NOT NULL,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_menu_group_id INT NOT NULL,
    FOREIGN KEY (edit_menu_group_id) REFERENCES edit_menu_group (edit_menu_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
);
-- END: table/edit_page_menu_group.sql
-- START: table/edit_access_right.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- holds all access right levels for the edit interface and other access areas
-- this table is fixed, prefilled and not changable
-- TABLE: edit_access_right
-- HISTORY:

-- DROP TABLE edit_access_right;
CREATE TABLE edit_access_right (
    edit_access_right_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR,
    level SMALLINT,
    type VARCHAR,
    UNIQUE (level,type)
) INHERITS (edit_generic) WITHOUT OIDS;
-- END: table/edit_access_right.sql
-- START: table/edit_scheme.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- holds backend template schemes
-- TABLE: edit_scheme
-- HISTORY:

-- DROP TABLE edit_scheme;
CREATE TABLE edit_scheme (
    edit_scheme_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    enabled SMALLINT NOT NULL DEFAULT 0,
    name VARCHAR,
    header_color VARCHAR,
    css_file VARCHAR,
    template VARCHAR
) INHERITS (edit_generic) WITHOUT OIDS;
-- END: table/edit_scheme.sql
-- START: table/edit_language.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- languages for the backend, this not used for the encoding, but only for having different language strings
-- the backend encoding is all UTF-8 (not changeable)
-- TABLE: edit_language
-- HISTORY:

-- DROP TABLE edit_language;
CREATE TABLE edit_language (
    edit_language_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    enabled SMALLINT NOT NULL DEFAULT 0,
    lang_default SMALLINT NOT NULL DEFAULT 0,
    long_name VARCHAR,
    short_name VARCHAR, -- en_US, en or en_US@latin without encoding
    iso_name VARCHAR, -- should actually be encoding
    order_number INT
) INHERITS (edit_generic) WITHOUT OIDS;
-- END: table/edit_language.sql
-- START: table/edit_group.sql
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
-- END: table/edit_group.sql
-- START: table/edit_page_access.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- groups pages together to one page group to which the user is then subscribed
-- TABLE: edit_page_access
-- HISTORY:

-- DROP TABLE edit_page_access;
CREATE TABLE edit_page_access (
    edit_page_access_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    edit_group_id INT NOT NULL,
    FOREIGN KEY (edit_group_id) REFERENCES edit_group (edit_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_page_id INT NOT NULL,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_access_right_id INT NOT NULL,
    FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    enabled SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;


-- END: table/edit_page_access.sql
-- START: table/edit_page_content.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2019/9/9
-- DESCRIPTION:
-- sub content to one page with additional edit access right set
-- can be eg JS content groups on one page
-- TABLE: edit_page_content
-- HISTORY:

-- DROP TABLE edit_page_content;
CREATE TABLE edit_page_content (
    edit_page_content_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    edit_page_id INT NOT NULL,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_access_right_id INT NOT NULL,
    FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    uid VARCHAR UNIQUE,
    name VARCHAR,
    order_number INT NOT NULL,
    online SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;
-- END: table/edit_page_content.sql
-- START: table/edit_user.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/06
-- DESCRIPTION:
-- holds the user that can login + group, scheme, lang and a default access right
-- TABLE: edit_user
-- HISTORY:

-- DROP TABLE edit_user;
CREATE TABLE edit_user (
    edit_user_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    connect_edit_user_id INT, -- possible reference to other user
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
    username VARCHAR UNIQUE,
    password VARCHAR,
    -- name block
    first_name VARCHAR,
    last_name VARCHAR,
    first_name_furigana VARCHAR,
    last_name_furigana VARCHAR,
    -- email
    email VARCHAR,
    -- eanbled/deleted flag
    enabled SMALLINT NOT NULL DEFAULT 0,
    deleted SMALLINT NOT NULL DEFAULT 0,
    -- general flags
    strict SMALLINT DEFAULT 0,
    locked SMALLINT DEFAULT 0,
    protected SMALLINT NOT NULL DEFAULT 0,
    -- is admin user
    admin SMALLINT NOT NULL DEFAULT 0,
    -- last login log
    last_login TIMESTAMP WITHOUT TIME ZONE,
    -- login error
    login_error_count INT DEFAULT 0,
    login_error_date_last TIMESTAMP WITHOUT TIME ZONE,
    login_error_date_first TIMESTAMP WITHOUT TIME ZONE,
    -- time locked
    lock_until TIMESTAMP WITHOUT TIME ZONE,
    lock_after TIMESTAMP WITHOUT TIME ZONE,
    -- password change
    password_change_date TIMESTAMP WITHOUT TIME ZONE, -- only when password is first set or changed
    password_change_interval INTERVAL, -- null if no change is needed, or d/m/y time interval
    password_reset_time TIMESTAMP WITHOUT TIME ZONE, -- when the password reset was requested
    password_reset_uid VARCHAR, -- the uid to access the password reset page
    -- _GET login id for direct login
    login_user_id VARCHAR UNIQUE, -- the loginUserId, at least 32 chars
    login_user_id_set_date TIMESTAMP WITHOUT TIME ZONE, -- when above uid was set
    login_user_id_last_revalidate TIMESTAMP WITHOUT TIME ZONE, -- when the last login was done with user name and password
    login_user_id_valid_from TIMESTAMP WITHOUT TIME ZONE, -- if set, from when the above uid is valid
    login_user_id_valid_until TIMESTAMP WITHOUT TIME ZONE, -- if set, until when the above uid is valid
    login_user_id_revalidate_after INTERVAL, -- user must login to revalidated loginUserId after set days, 0 for forever
    login_user_id_locked SMALLINT DEFAULT 0, -- lock for loginUserId, but still allow normal login
    -- additional ACL json block
    additional_acl JSONB -- additional ACL as JSON string (can be set by other pages)
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
COMMENT ON COLUMN edit_user.login_user_id_set_date IS 'loginUserId was set at what date';
COMMENT ON COLUMN edit_user.login_user_id_last_revalidate IS 'set when username/password login is done and loginUserId is set';
COMMENT ON COLUMN edit_user.login_user_id_valid_from IS 'loginUserId is valid from this date, >=';
COMMENT ON COLUMN edit_user.login_user_id_valid_until IS 'loginUserId is valid until this date, <=';
COMMENT ON COLUMN edit_user.login_user_id_revalidate_after IS 'If set to a number greater 0 then user must login after given amount of days to revalidate the loginUserId, set to 0 for valid forver';
COMMENT ON COLUMN edit_user.login_user_id_locked IS 'A separte lock flag for loginUserId, user can still login normal';
COMMENT ON COLUMN edit_user.additional_acl IS 'Additional Access Control List stored in JSON format';
-- END: table/edit_user.sql
-- START: table/edit_log.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- log data for backend interface, logs all user activities
-- TABLE: edit_log
-- HISTORY:

-- DROP TABLE edit_log;
CREATE TABLE edit_log (
    edit_log_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    euid INT, -- this is a foreign key, but I don't nedd to reference to it
    FOREIGN KEY (euid) REFERENCES edit_user (edit_user_id) MATCH FULL ON UPDATE CASCADE ON DELETE SET NULL,
    ecuid VARCHAR,
    ecuuid UUID,
    username VARCHAR,
    password VARCHAR,
    event_date TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR,
    error TEXT,
    event TEXT,
    data_binary BYTEA,
    data TEXT,
    page VARCHAR,
    action VARCHAR,
    action_id VARCHAR,
    action_sub_id VARCHAR,
    action_yes VARCHAR,
    action_flag VARCHAR,
    action_menu VARCHAR,
    action_loaded VARCHAR,
    action_value VARCHAR,
    action_type VARCHAR,
    action_error VARCHAR,
    user_agent VARCHAR,
    referer VARCHAR,
    script_name VARCHAR,
    query_string VARCHAR,
    server_name VARCHAR,
    http_host VARCHAR,
    http_accept VARCHAR,
    http_accept_charset VARCHAR,
    http_accept_encoding VARCHAR,
    session_id VARCHAR
) INHERITS (edit_generic) WITHOUT OIDS;
-- END: table/edit_log.sql
-- START: table/edit_log_overflow.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2020/1/28
-- DESCRIPTION:
-- edit log overflow table
-- this is the overflow table for partition
-- TABLE: edit_log_overflow
-- HISTORY:

-- DROP TABLE edit_log_overflow;
CREATE TABLE IF NOT EXISTS edit_log_overflow () INHERITS (edit_log);
ALTER TABLE edit_log_overflow ADD PRIMARY KEY (edit_log_id);
ALTER TABLE edit_log_overflow ADD CONSTRAINT edit_log_overflow_euid_fkey FOREIGN KEY (euid) REFERENCES edit_user (edit_user_id) MATCH FULL ON UPDATE CASCADE ON DELETE SET NULL;
-- END: table/edit_log_overflow.sql
-- START: table/edit_access.sql
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
-- END: table/edit_access.sql
-- START: table/edit_access_user.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- groupings which user has rights to which access groups (incl ACL)
-- TABLE: edit_access_user
-- HISTORY:

-- DROP TABLE edit_access_user;
CREATE TABLE edit_access_user (
    edit_access_user_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    edit_access_id INT NOT NULL,
    FOREIGN KEY (edit_access_id) REFERENCES edit_access (edit_access_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_user_id INT NOT NULL,
    FOREIGN KEY (edit_user_id) REFERENCES edit_user (edit_user_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_access_right_id INT NOT NULL,
    FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_default SMALLINT DEFAULT 0,
    enabled SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;
-- END: table/edit_access_user.sql
-- START: table/edit_access_data.sql
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2016/7/15
-- DESCRIPTION:
-- sub table to edit access, holds additional data for access group
-- TABLE: edit_access_data
-- HISTORY:

-- DROP TABLE edit_access_data;
CREATE TABLE edit_access_data (
    edit_access_data_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    edit_access_id INT NOT NULL,
    FOREIGN KEY (edit_access_id) REFERENCES edit_access (edit_access_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    enabled SMALLINT NOT NULL DEFAULT 0,
    name VARCHAR,
    value VARCHAR
) INHERITS (edit_generic) WITHOUT OIDS;

-- create a unique index for each attached data block for each edit access can
-- only have ONE value;
CREATE UNIQUE INDEX edit_access_data_edit_access_id_name_ukey ON edit_access_data (edit_access_id, name);
-- END: table/edit_access_data.sql
-- START: trigger/trg_edit_access_right.sql
-- DROP TRIGGER IF EXISTS trg_edit_access_right ON edit_access_right;
CREATE TRIGGER trg_edit_access_right
BEFORE INSERT OR UPDATE ON edit_access_right
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_access_right.sql
-- START: trigger/trg_edit_access.sql
-- DROP TRIGGER IF EXISTS trg_edit_access ON edit_access;
CREATE TRIGGER trg_edit_access
BEFORE INSERT OR UPDATE ON edit_access
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();

-- DROP TRIGGER IF EXISTS trg_set_edit_access_uid ON edit_access;
CREATE TRIGGER trg_set_edit_access_uid
BEFORE INSERT OR UPDATE ON edit_access
FOR EACH ROW EXECUTE PROCEDURE set_edit_access_uid();
-- END: trigger/trg_edit_access.sql
-- START: trigger/trg_edit_access_data.sql
-- DROP TRIGGER IF EXISTS trg_edit_access_data ON edit_access_data;
CREATE TRIGGER trg_edit_access_data
BEFORE INSERT OR UPDATE ON edit_access_data
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_access_data.sql
-- START: trigger/trg_edit_access_user.sql
-- DROP TRIGGER IF EXISTS trg_edit_access_user ON edit_access_user;
CREATE TRIGGER trg_edit_access_user
BEFORE INSERT OR UPDATE ON edit_access_user
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_access_user.sql
-- START: trigger/trg_edit_group.sql
-- DROP TRIGGER IF EXISTS trg_edit_group ON edit_group;
CREATE TRIGGER trg_edit_group
BEFORE INSERT OR UPDATE ON edit_group
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();

-- DROP TRIGGER IF EXISTS trg_set_edit_group_uid ON edit_group;
CREATE TRIGGER trg_set_edit_group_uid
BEFORE INSERT OR UPDATE ON edit_group
FOR EACH ROW EXECUTE PROCEDURE set_edit_group_uid();
-- END: trigger/trg_edit_group.sql
-- START: trigger/trg_edit_language.sql
-- DROP TRIGGER IF EXISTS trg_edit_language ON edit_language;
CREATE TRIGGER trg_edit_language
BEFORE INSERT OR UPDATE ON edit_language
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_language.sql
-- START: trigger/trg_edit_log_overflow.sql
-- DROP TRIGGER IF EXISTS trg_edit_log_overflow ON edit_log_overflow;
CREATE TRIGGER trg_edit_log_overflow
BEFORE INSERT OR UPDATE ON edit_log_overflow
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_log_overflow.sql
-- START: trigger/trg_edit_log.sql
-- DROP TRIGGER IF EXISTS trg_edit_log ON edit_log;
CREATE TRIGGER trg_edit_log
BEFORE INSERT OR UPDATE ON edit_log
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();

-- DROP TRIGGER IF EXISTS trg_edit_log_insert_partition ON edit_log;
CREATE TRIGGER trg_edit_log_insert_partition
BEFORE INSERT OR UPDATE ON edit_log
FOR EACH ROW EXECUTE PROCEDURE edit_log_insert_trigger();
-- END: trigger/trg_edit_log.sql
-- START: trigger/trg_edit_page_access.sql
-- DROP TRIGGER IF EXISTS trg_edit_page_access ON edit_page_access;
CREATE TRIGGER trg_edit_page_access
BEFORE INSERT OR UPDATE ON edit_page_access
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_page_access.sql
-- START: trigger/trg_edit_page_content.sql
-- DROP TRIGGER IF EXISTS trg_edit_page_content ON edit_page_content;
CREATE TRIGGER trg_edit_page_content
BEFORE INSERT OR UPDATE ON edit_page_content
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_page_content.sql
-- START: trigger/trg_edit_page.sql
-- DROP TRIGGER IF EXISTS trg_edit_page ON edit_page;
CREATE TRIGGER trg_edit_page
BEFORE INSERT OR UPDATE ON edit_page
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_page.sql
-- START: trigger/trg_edit_query_string.sql
-- DROP TRIGGER IF EXISTS trg_edit_query_string ON edit_query_string;
CREATE TRIGGER trg_edit_query_string
BEFORE INSERT OR UPDATE ON edit_query_string
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_query_string.sql
-- START: trigger/trg_edit_scheme.sql
-- DROP TRIGGER IF EXISTS trg_edit_scheme ON edit_scheme;
CREATE TRIGGER trg_edit_scheme
BEFORE INSERT OR UPDATE ON edit_scheme
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_scheme.sql
-- START: trigger/trg_edit_user.sql
-- DROP TRIGGER IF EXISTS trg_edit_user ON edit_user;
CREATE TRIGGER trg_edit_user
BEFORE INSERT OR UPDATE ON edit_user
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();

-- DROP TRIGGER IF EXISTS trg_edit_user_set_login_user_id_set_date ON edit_user;
CREATE TRIGGER trg_edit_user_set_login_user_id_set_date
BEFORE INSERT OR UPDATE ON edit_user
FOR EACH ROW EXECUTE PROCEDURE set_login_user_id_set_date();
-- END: trigger/trg_edit_user.sql
-- START: trigger/trg_edit_visible_group.sql
-- DROP TRIGGER IF EXISTS trg_edit_visible_group ON edit_visible_group;
CREATE TRIGGER trg_edit_visible_group
BEFORE INSERT OR UPDATE ON edit_visible_group
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_visible_group.sql
-- START: trigger/trg_edit_menu_group.sql
-- DROP TRIGGER IF EXISTS trg_edit_menu_group ON edit_menu_group;
CREATE TRIGGER trg_edit_menu_group
BEFORE INSERT OR UPDATE ON edit_menu_group
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
-- END: trigger/trg_edit_menu_group.sql
-- START: data/edit_tables.sql
-- edit tables insert data in order

-- edit visible group
DELETE FROM edit_visible_group;
INSERT INTO edit_visible_group (name, flag) VALUES ('Main Menu', 'main');
INSERT INTO edit_visible_group (name, flag) VALUES ('Data popup Menu', 'datapopup');

-- edit menu group
DELETE FROM edit_menu_group;
INSERT INTO edit_menu_group (name, flag, order_number) VALUES ('Admin Menu', 'admin', 1);
INSERT INTO edit_menu_group (name, flag, order_number) VALUES ('Admin Data Popup Menu', 'AdminDataPopup', 2);

-- edit page
DELETE FROM edit_page;
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_pages.php', 'Edit Pages', 1, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_users.php', 'Edit Users', 2, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_languages.php', 'Edit Languages', 3, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_schemes.php', 'Edit Schemes', 4, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_groups.php', 'Edit Groups', 5, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_visible_group.php', 'Edit Visible Groups', 6, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_menu_group.php', 'Edit Menu Groups', 7, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_access.php', 'Edit Access', 8, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_order.php', 'Edit Order', 9, 1, 0);

-- edit visible group
DELETE FROM edit_page_visible_group;
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Pages'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Users'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Languages'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Schemes'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Groups'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Visible Groups'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Menu Groups'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Access'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
-- INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Order'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));

-- edit page menu group
DELETE FROM edit_page_menu_group;
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Pages'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Users'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Languages'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Schemes'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Groups'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Visible Groups'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Menu Groups'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Access'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
-- INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Order'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));


-- edit access right
DELETE FROM edit_access_right;
INSERT INTO edit_access_right (name, level, type) VALUES ('Default', -1, 'default');
INSERT INTO edit_access_right (name, level, type) VALUES ('No Access', 0, 'none');
INSERT INTO edit_access_right (name, level, type) VALUES ('List', 10, 'list');
INSERT INTO edit_access_right (name, level, type) VALUES ('Read', 20, 'read');
INSERT INTO edit_access_right (name, level, type) VALUES ('Translator', 30, 'mod_trans');
INSERT INTO edit_access_right (name, level, type) VALUES ('Modify', 40, 'mod');
INSERT INTO edit_access_right (name, level, type) VALUES ('Create/Write', 60, 'write');
INSERT INTO edit_access_right (name, level, type) VALUES ('Delete', 80, 'del');
INSERT INTO edit_access_right (name, level, type) VALUES ('Site Admin', 90, 'siteadmin');
INSERT INTO edit_access_right (name, level, type) VALUES ('Admin', 100, 'admin');

-- edit scheme
DELETE FROM edit_scheme;
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Default Scheme', 'E0E2FF', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Admin', 'CC7E7E', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Visitor', 'B0C4B3', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('User', '1E789E', 1);

-- edit language
-- short_name = locale without encoding
-- iso_name = encoding
DELETE FROM edit_language;
INSERT INTO edit_language (long_name, short_name, iso_name, order_number, enabled, lang_default) VALUES ('English', 'en_US', 'UTF-8', 1, 1, 1);
INSERT INTO edit_language (long_name, short_name, iso_name, order_number, enabled, lang_default) VALUES ('Japanese', 'ja_JP', 'UTF-8', 2, 1, 0);

-- edit group
DELETE FROM edit_group;
INSERT INTO edit_group (name, enabled, edit_scheme_id, edit_access_right_id) VALUES ('Admin', 1, (SELECT edit_scheme_id FROM edit_scheme WHERE name = 'Admin'), (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin'));
INSERT INTO edit_group (name, enabled, edit_scheme_id, edit_access_right_id) VALUES ('User', 1, (SELECT edit_scheme_id FROM edit_scheme WHERE name = 'User'), (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'write'));

-- edit page access
DELETE FROM edit_page_access;
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Pages'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Users'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Languages'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Schemes'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Groups'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Visible Groups'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Menu Groups'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Access'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Order'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);

-- edit user
-- inserts admin user so basic users can be created
DELETE FROM edit_user;
INSERT INTO edit_user (username, password, enabled, email, protected, admin, edit_language_id, edit_group_id, edit_scheme_id, edit_access_right_id) VALUES ('admin', 'admin', 1, 'test@tequila.jp', 1, 1,
    (SELECT edit_language_id FROM edit_language WHERE short_name = 'en_US'),
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_scheme_id FROM edit_scheme WHERE name = 'Admin'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);

-- edit access
DELETE FROM edit_access;
INSERT INTO edit_access (name, enabled, protected) VALUES ('Admin Access', 1, 1);

-- edit access user
DELETE FROM edit_access_user;
INSERT INTO edit_access_user (edit_default, enabled, edit_access_id, edit_user_id, edit_access_right_id) VALUES (1, 1,
    (SELECT edit_access_id FROM edit_access WHERE uid = 'AdminAccess'),
    (SELECT edit_user_id FROM edit_user WHERE username = 'admin'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
-- END: data/edit_tables.sql
