CREATE OR REPLACE FUNCTION edit_log_insert_trigger ()
RETURNS TRIGGER AS
$$
DECLARE
	start_date DATE := '2010-01-01';
	end_date DATE;
	timeformat TEXT := 'YYYY';
	selector TEXT := 'year';
	_interval INTERVAL;
	table_name TEXT;
BEGIN
	-- get year and month from edit_log date so we can build the target edit_log table

	-- move interval
	_interval := '1 ' || selector;
	-- current table name
	table_name := 'edit_log_' || to_char(NEW.event_date, timeformat);

	-- we are in valid start time area
	IF (NEW.event_date >= start_date) THEN
		BEGIN
			EXECUTE 'INSERT INTO ' || quote_ident(table_name) || ' SELECT ($1).*' USING NEW;
		-- if insert failed because of missing table, create new below
		EXCEPTION
		WHEN undefined_table THEN
			-- another block, so in case the creation fails here too
			BEGIN
				-- create new talbe here + all indexes
				start_date := date_trunc(selector, NEW.event_date);
				end_date := date_trunc(selector, NEW.event_date + _interval);
				-- creat table
				EXECUTE 'CREATE TABLE IF NOT EXISTS ' || quote_ident(table_name) || ' ( CHECK ( event_date >= ' || quote_literal(start_date) || ' AND event_date < ' || quote_literal(end_date) || ' ) ) INHERITS (edit_log)';
				-- create all indexes and triggers
				EXECUTE 'ALTER TABLE ' || quote_ident(table_name) || ' ADD PRIMARY KEY (edit_log_id)';
				-- FK constraints
				EXECUTE 'ALTER TABLE ' || quote_ident(table_name) || ' ADD CONSTRAINT fk_' || quote_ident(table_name) || '_euid_fkey FOREIGN KEY (euid) REFERENCES edit_user (edit_user_id) MATCH FULL ON UPDATE CASCADE ON DELETE CASCADE';
				-- generic trigger
				EXECUTE 'CREATE TRIGGER trg_' || quote_ident(table_name) || ' BEFORE INSERT OR UPDATE ON ' || quote_ident(table_name) || ' FOR EACH ROW EXECUTE PROCEDURE set_edit_generic()';

				-- insert try again
				EXECUTE 'INSERT INTO ' || quote_ident(table_name) || ' SELECT ($1).*' USING NEW;
			EXCEPTION
			WHEN OTHERS THEN
				-- if this faled, throw it into the overflow table (so we don't loose anything)
				INSERT INTO edit_log_overflow VALUES (NEW.*);
			END;
		-- other errors, insert into overlow
		WHEN OTHERS THEN
			-- if this faled, throw it into the overflow table (so we don't loose anything)
			INSERT INTO edit_log_overflow VALUES (NEW.*);
		END;
	ELSE
		-- if outside valid date, insert into overflow
		INSERT INTO edit_log_overflow VALUES (NEW.*);
	END IF;
	RETURN NULL;
END
$$
LANGUAGE 'plpgsql'
