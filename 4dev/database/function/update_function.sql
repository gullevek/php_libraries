-- $Id: update_function.sql 3158 2010-09-02 02:49:00Z gullevek $
-- adds the created or updated date tags

CREATE OR REPLACE FUNCTION set_generic() RETURNS TRIGGER AS '
	BEGIN
		IF TG_OP = ''INSERT'' THEN
			NEW.date_created := ''now'';
			NEW.user_created := current_user;
		ELSIF TG_OP = ''UPDATE'' THEN
			NEW.date_updated := ''now'';
			NEW.user_updated := current_user;
		END IF;
		RETURN NEW;
	END;
' LANGUAGE 'plpgsql';
