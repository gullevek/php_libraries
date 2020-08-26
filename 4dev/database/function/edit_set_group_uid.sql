-- add uid add for edit_group table

CREATE OR REPLACE FUNCTION set_edit_group_uid() RETURNS TRIGGER AS
$$
	DECLARE
		myrec RECORD;
		v_uid VARCHAR;
	BEGIN
		-- skip if NEW.name is not set
		IF NEW.name IS NOT NULL AND NEW.name <> '' THEN
			-- use NEW.name as base, remove all spaces
			-- name data is already unique, so we do not need to worry about this here
			v_uid := REPLACE(NEW.name, ' ', '');
			IF TG_OP = 'INSERT' THEN
				-- always set
				NEW.uid := v_uid;
			ELSIF TG_OP = 'UPDATE' THEN
				-- check if not set, then set
				SELECT INTO myrec t.* FROM edit_group t WHERE edit_group_id = NEW.edit_group_id;
				IF FOUND THEN
					NEW.uid := v_uid;
				END IF;
			END IF;
		END IF;
		RETURN NEW;
	END;
$$
	LANGUAGE 'plpgsql';
