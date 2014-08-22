-- update edit tables
-- add login error count and last login error

-- count login errors
ALTER TABLE edit_user ADD login_error_count INT DEFAULT 0;
-- last login error date
ALTER TABLE edit_user ADD login_error_date TIMESTAMP WITHOUT TIME ZONE;
-- if this is set to true, this user gets locked after max login errors are reached
ALTER TABLE edit_user ADD strict SMALLINT DEFAULT 0;
ALTER TABLE edit_user ADD locked SMALLINT DEFAULT 0;
