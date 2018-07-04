-- adds the created or updated date tags

CREATE OR REPLACE FUNCTION set_generic() RETURNS TRIGGER AS '
	BEGIN
		IF TG_OP = ''INSERT'' THEN
			NEW.cuid := random_string(random_length);
		ELSIF TG_OP = ''UPDATE'' THEN
			NEW.date_updated := ''now'';
		END IF;
		RETURN NEW;
	END;
' LANGUAGE 'plpgsql';
