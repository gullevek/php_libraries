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
    ecuuid UUID, -- this is the one we want to use, full UUIDv4 from the edit user table
    username VARCHAR,
    password VARCHAR,
    event_date TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR, -- just the REMOTE_IP, full set see ip_address
    ip_address JSONB, -- REMOTE_IP and all other IPs (X_FORWARD, etc) as JSON block
    error TEXT,
    event TEXT,
    data_binary BYTEA,
    data TEXT,
    page VARCHAR,
    -- various info data sets
    user_agent VARCHAR,
    referer VARCHAR,
    script_name VARCHAR,
    query_string VARCHAR,
    request_scheme VARCHAR, -- http or https
    server_name VARCHAR,
    http_host VARCHAR,
    http_data JSONB,
    http_accept VARCHAR, -- in http_data
    http_accept_charset VARCHAR, -- in http_data
    http_accept_encoding VARCHAR, -- in http_data
    -- session ID if set
    session_id VARCHAR.
    -- any action var, -> same set in action_data as JSON
    action_data JSONB,
    action VARCHAR, -- in action_data
    action_id VARCHAR, -- in action_data
    action_sub_id VARCHAR, -- in action_data
    action_yes VARCHAR, -- in action_data
    action_flag VARCHAR, -- in action_data
    action_menu VARCHAR, -- in action_data
    action_loaded VARCHAR, -- in action_data
    action_value VARCHAR, -- in action_data
    action_type VARCHAR, -- in action_data
    action_error VARCHAR -- in action_data
) INHERITS (edit_generic) WITHOUT OIDS;
