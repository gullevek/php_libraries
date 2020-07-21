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
