-- adds the created or updated date tags

CREATE OR REPLACE FUNCTION set_edit_generic()
RETURNS TRIGGER AS
$$
DECLARE
    random_length INT = 25; -- that should be long enough
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.date_created := clock_timestamp();
        NEW.cuid := random_string(random_length);
        NEW.cuuid := gen_random_uuid();
    ELSIF TG_OP = 'UPDATE' THEN
        NEW.date_updated := clock_timestamp();
    END IF;
    RETURN NEW;
END;
$$
LANGUAGE 'plpgsql';
