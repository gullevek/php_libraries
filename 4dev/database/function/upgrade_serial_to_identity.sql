-- Upgrae serial to identity type
--
-- @param reclass tbl The table where the column is located
-- @param name col The column to be changed
-- @param varchar identity_type [default=a] Allowed a, d, assigned, default
-- @param varchar col_type [default=''] Allowed smallint, int, bigint, int2, int4, int8
-- @raises EXCEPTON on column not found, no linked sequence, more than one linked sequence found
--
CREATE OR REPLACE FUNCTION upgrade_serial_to_identity(
    tbl regclass,
    col name,
    identity_type varchar = 'a',
    col_type varchar = ''
)
RETURNS void
LANGUAGE plpgsql
AS $$
DECLARE
colnum smallint;
seqid oid;
count int;
col_type_oid int;
col_type_len int;
current_col_atttypid oid;
current_col_attlen int;
BEGIN
    -- switch between always (default) or default identiy type
    IF identity_type NOT IN ('a', 'd', 'assigned', 'default') THEN
        identity_type := 'a';
    ELSE
        IF identity_type = 'default' THEN
            identity_type := 'd';
        ELSIF identity_type = 'assigned' THEN
            identity_type := 'a';
        END IF;
    END IF;
    -- find column number, attribute oid and attribute len
    SELECT attnum, atttypid, attlen
        INTO colnum, current_col_atttypid, current_col_attlen
        FROM pg_attribute
        WHERE attrelid = tbl AND attname = col;
    IF NOT FOUND THEN
        RAISE EXCEPTION 'column does not exist';
    END IF;

    -- find sequence
    SELECT INTO seqid objid
        FROM pg_depend
        WHERE (refclassid, refobjid, refobjsubid) = ('pg_class'::regclass, tbl, colnum)
            AND classid = 'pg_class'::regclass AND objsubid = 0
            AND deptype = 'a';

    GET DIAGNOSTICS count = ROW_COUNT;
    IF count < 1 THEN
        RAISE EXCEPTION 'no linked sequence found';
    ELSIF count > 1 THEN
        RAISE EXCEPTION 'more than one linked sequence found';
    END IF;

    -- drop the default
    EXECUTE 'ALTER TABLE ' || tbl || ' ALTER COLUMN ' || quote_ident(col) || ' DROP DEFAULT';

    -- change the dependency between column and sequence to internal
    UPDATE pg_depend
        SET deptype = 'i'
        WHERE (classid, objid, objsubid) = ('pg_class'::regclass, seqid, 0)
            AND deptype = 'a';

    -- mark the column as identity column
    UPDATE pg_attribute
        -- set to 'd' for default
        SET attidentity = identity_type
        WHERE attrelid = tbl
            AND attname = col;
    RAISE NOTICE 'Update to identity for table "%" and columen "%" with type "%"', tbl, col, identity_type;

    -- set type if requested and not empty
    IF col_type <> '' THEN
        IF col_type IN ('smallint', 'int', 'bigint', 'int2', 'int4', 'int8') THEN
            -- rewrite smallint, int, bigint
            IF col_type = 'smallint' THEN
                col_type := 'int2';
            ELSIF col_type = 'int' THEN
                col_type := 'int4';
            ELSIF col_type = 'bigint' THEN
                col_type := 'int8';
            END IF;
            -- get the length and oid for selected
            SELECT oid, typlen INTO col_type_oid, col_type_len FROM pg_type WHERE typname = col_type;
            -- set only if diff or hight
            IF current_col_atttypid <> col_type_oid AND col_type_len > current_col_attlen THEN
                RAISE NOTICE 'Change col type: %', col_type;
                -- update type
                UPDATE pg_attribute
                    SET
                        atttypid = col_type_oid, attlen = col_type_len
                    WHERE attrelid = tbl
                        AND attname = col;
            END IF;
        ELSE
            RAISE NOTICE 'Invalid col type: %', col_type;
        END IF;
    END IF;
END;
$$;
