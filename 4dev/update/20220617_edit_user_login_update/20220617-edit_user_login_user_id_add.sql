-- 2022/6/17 update edit_user with login uid

-- !!! COPY TABLE ARRAY FOLDER !!!

-- the login uid, at least 32 chars
ALTER TABLE edit_user ADD login_user_id VARCHAR UNIQUE;
-- CREATE UNIQUE INDEX edit_user_login_user_id_key ON edit_user (login_user_id) WHERE login_user_id IS NOT NULL;
-- ALTER TABLE edit_user ADD CONSTRAINT edit_user_login_user_id_key UNIQUE (login_user_id);
-- when above uid was set
ALTER TABLE edit_user ADD login_user_id_set_date TIMESTAMP WITHOUT TIME ZONE;
ALTER TABLE edit_user ADD login_user_id_last_revalidate TIMESTAMP WITHOUT TIME ZONE;
-- if set, from/until when the above uid is valid
ALTER TABLE edit_user ADD login_user_id_valid_from TIMESTAMP WITHOUT TIME ZONE;
ALTER TABLE edit_user ADD login_user_id_valid_until TIMESTAMP WITHOUT TIME ZONE;
-- user must login to revalidated login id after set days, 0 for forever
ALTER TABLE edit_user ADD login_user_id_revalidate_after INTERVAL;
-- lock for login user id, but still allow normal login
ALTER TABLE edit_user ADD login_user_id_locked SMALLINT NOT NULL DEFAULT 0;

-- disable login before date
ALTER TABLE edit_user ADD lock_until TIMESTAMP WITHOUT TIME ZONE;
-- disable login after date
ALTER TABLE edit_user ADD lock_after TIMESTAMP WITHOUT TIME ZONE;

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

CREATE TRIGGER trg_edit_user_set_login_user_id_set_date
BEFORE INSERT OR UPDATE ON edit_user
FOR EACH ROW EXECUTE PROCEDURE set_login_user_id_set_date();

-- __END__
