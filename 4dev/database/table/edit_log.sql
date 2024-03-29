-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- log data for backend interface, logs all user activities
-- TABLE: edit_log
-- HISTORY:

-- DROP TABLE edit_log;
CREATE TABLE edit_log (
    edit_log_id SERIAL PRIMARY KEY,
    euid INT, -- this is a foreign key, but I don't nedd to reference to it
    FOREIGN KEY (euid) REFERENCES edit_user (edit_user_id) MATCH FULL ON UPDATE CASCADE ON DELETE SET NULL,
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
