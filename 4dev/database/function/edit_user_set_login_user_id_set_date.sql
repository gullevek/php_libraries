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
	ELSIF NEW.login_user_id IS NULL OR NEW.login_user_id = '' THEN
		NEW.login_user_id_set_date = NULL;
	END IF;
	RETURN NEW;
END;
$$
LANGUAGE 'plpgsql';
