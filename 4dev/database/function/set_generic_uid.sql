-- set generic with date and uid combined
-- don't use with set_generic/set_uid together

CREATE OR REPLACE FUNCTION set_generic()
RETURNS TRIGGER AS
$$
DECLARE
	random_length INT = 32; -- long for massive data
BEGIN
	IF TG_OP = 'INSERT' THEN
		NEW.date_created := 'now';
		IF NEW.uid IS NULL THEN
			NEW.uid := random_string(random_length);
		END IF;
	ELSIF TG_OP = 'UPDATE' THEN
		NEW.date_updated := 'now';
	END IF;
	RETURN NEW;
END;
$$
LANGUAGE 'plpgsql';
