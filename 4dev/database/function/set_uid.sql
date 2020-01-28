-- adds the created or updated date tags

CREATE OR REPLACE FUNCTION set_uid() RETURNS TRIGGER AS '
	DECLARE
		random_length INT = 32; -- that should be long enough
	BEGIN
		IF TG_OP = ''INSERT'' THEN
			NEW.uid := random_string(random_length);
		END IF;
		RETURN NEW;
	END;
' LANGUAGE 'plpgsql';
