-- AUTHOR: Clemens Schwaighofer
-- DATE: 2018-07-17
-- DESCRIPTION:
-- partition the edit_log table by year
-- auto creates table if missing, if failure writes to overflow table
-- HISTORY:

CREATE OR REPLACE FUNCTION edit_log_insert_trigger ()
RETURNS TRIGGER AS
$$
DECLARE
	start_date DATE := '2010-01-01';
	end_date DATE;
	timeformat TEXT := 'YYYY';
	selector TEXT := 'year';
	base_table TEXT := 'edit_log';
	_interval INTERVAL := '1 ' || selector;
	_interval_next INTERVAL := '2 ' || selector;
	table_name TEXT;
	-- compare date column
	compare_date DATE := NEW.event_date;
	compare_date_name TEXT := 'event_date';
	-- the create commands
	command_create_table TEXT := 'CREATE TABLE IF NOT EXISTS {TABLE_NAME} (CHECK({COMPARE_DATE_NAME} >= {START_DATE} AND {COMPARE_DATE_NAME} < {END_DATE})) INHERITS ({BASE_NAME})';
	command_create_primary_key TEXT := 'ALTER TABLE {TABLE_NAME} ADD PRIMARY KEY ({BASE_TABLE}_id)';
	command_create_foreign_key_1 TEXT := 'ALTER TABLE {TABLE_NAME} ADD CONSTRAINT {TABLE_NAME}_euid_fkey FOREIGN KEY (euid) REFERENCES edit_user (edit_user_id) MATCH FULL ON UPDATE CASCADE ON DELETE SET NULL';
	command_create_trigger_1 TEXT = 'CREATE TRIGGER trg_{TABLE_NAME} BEFORE INSERT OR UPDATE ON {TABLE_NAME} FOR EACH ROW EXECUTE PROCEDURE set_edit_generic()';
BEGIN
	-- we are in valid start time area
	IF (NEW.event_date >= start_date) THEN
		-- current table name
		table_name := base_table || '_' || to_char(NEW.event_date, timeformat);
		BEGIN
			EXECUTE 'INSERT INTO ' || quote_ident(table_name) || ' SELECT ($1).*' USING NEW;
		-- if insert failed because of missing table, create new below
		EXCEPTION
		WHEN undefined_table THEN
			-- another block, so in case the creation fails here too
			BEGIN
				-- create new table here + all indexes
				start_date := date_trunc(selector, NEW.event_date);
				end_date := date_trunc(selector, NEW.event_date + _interval);
				-- creat table
				EXECUTE format(REPLACE( -- end date
					REPLACE( -- start date
						REPLACE( -- compare date name
							REPLACE( -- base name (inherit)
								REPLACE( -- table name
									command_create_table,
									'{TABLE_NAME}',
									table_name
								),
								'{BASE_NAME}',
								base_table
							),
							'{COMPARE_DATE_NAME}',
							compare_date_name
						),
						'{START_DATE}',
						quote_literal(start_date)
					),
					'{END_DATE}',
					quote_literal(end_date)
				));
				-- create all indexes and triggers
				EXECUTE format(REPLACE(
					REPLACE(
						command_create_primary_key,
						'{TABLE_NAME}',
						table_name
					),
					'{BASE_TABLE}',
					base_table
				));
				-- FK constraints
				EXECUTE format(REPLACE(command_create_foreign_key_1, '{TABLE_NAME}', table_name));
				-- generic trigger
				EXECUTE format(REPLACE(command_create_trigger_1, '{TABLE_NAME}', table_name));

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
		-- main insert run done, check if we have to create next months table
		BEGIN
			-- check if next month table exists
			table_name := base_table || '_' || to_char((SELECT NEW.event_date + _interval)::DATE, timeformat);
			-- RAISE NOTICE 'SEARCH NEXT: %', table_name;
			IF  (SELECT to_regclass(table_name)) IS NULL THEN
				-- move inner interval same
				start_date := date_trunc(selector, NEW.event_date + _interval);
				end_date := date_trunc(selector, NEW.event_date + _interval_next);
				-- RAISE NOTICE 'CREATE NEXT: %', table_name;
				-- create table
				EXECUTE format(REPLACE( -- end date
					REPLACE( -- start date
						REPLACE( -- compare date name
							REPLACE( -- base name (inherit)
								REPLACE( -- table name
									command_create_table,
									'{TABLE_NAME}',
									table_name
								),
								'{BASE_NAME}',
								base_table
							),
							'{COMPARE_DATE_NAME}',
							compare_date_name
						),
						'{START_DATE}',
						quote_literal(start_date)
					),
					'{END_DATE}',
					quote_literal(end_date)
				));
				-- create all indexes and triggers
				EXECUTE format(REPLACE(
					REPLACE(
						command_create_primary_key,
						'{TABLE_NAME}',
						table_name
					),
					'{BASE_TABLE}',
					base_table
				));
				-- FK constraints
				EXECUTE format(REPLACE(command_create_foreign_key_1, '{TABLE_NAME}', table_name));
				-- generic trigger
				EXECUTE format(REPLACE(command_create_trigger_1, '{TABLE_NAME}', table_name));
			END IF;
		EXCEPTION
		WHEN OTHERS THEN
			RAISE NOTICE 'Failed to create next table: %', table_name;
		END;
	ELSE
		-- if outside valid date, insert into overflow
		INSERT INTO edit_log_overflow VALUES (NEW.*);
	END IF;
	RETURN NULL;
END
$$
LANGUAGE 'plpgsql';
