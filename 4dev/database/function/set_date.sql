-- adds the created or updated date tags

CREATE OR REPLACE FUNCTION set_date()
RETURNS TRIGGER AS
$$
BEGIN
	IF TG_OP = 'INSERT' THEN
		NEW.date_created := 'now';
	ELSIF TG_OP = 'UPDATE' THEN
		NEW.date_updated := 'now';
	END IF;
	RETURN NEW;
END;
$$
LANGUAGE 'plpgsql';
