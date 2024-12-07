-- 20241203: update edit tables
ALTER TABLE edit_generic ADD cuuid UUID DEFAULT gen_random_uuid();
ALTER TABLE edit_log ADD ecuid VARCHAR;
ALTER TABLE edit_log ADD ecuuid VARCHAR;
ALTER TABLE edit_log ADD action_sub_id VARCHAR;

-- update set_edit_gneric
-- adds the created or updated date tags

CREATE OR REPLACE FUNCTION set_edit_generic()
RETURNS TRIGGER AS
$$
DECLARE
    random_length INT = 25; -- that should be long enough
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.date_created := 'now';
        NEW.cuid := random_string(random_length);
        NEW.cuuid := gen_random_uuid();
    ELSIF TG_OP = 'UPDATE' THEN
        NEW.date_updated := 'now';
    END IF;
    RETURN NEW;
END;
$$
LANGUAGE 'plpgsql';
