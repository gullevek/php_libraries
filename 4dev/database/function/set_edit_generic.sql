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
	ELSIF TG_OP = 'UPDATE' THEN
		NEW.date_updated := 'now';
	END IF;
	RETURN NEW;
END;
$$
LANGUAGE 'plpgsql';
