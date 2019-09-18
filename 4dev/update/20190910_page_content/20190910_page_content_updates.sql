-- 2019/9/10 update edit_page with reference and additional ACLs, update core functions

-- * random_string function
-- * add cuid column in edit_generic
-- * update generic trigger function
-- * edit_page_content table/trigger
-- * edit_* additional_acl entries
-- * edit_page content alias link
-- * update any missing cuid entries

-- create random string with length X
CREATE FUNCTION random_string(randomLength int)
RETURNS text AS $$
SELECT array_to_string(
    ARRAY(
        SELECT substring(
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            trunc(random() * 62)::int + 1,
            1
        )
        FROM generate_series(1, randomLength) AS gs(x)
    ),
    ''
)
$$ LANGUAGE SQL
RETURNS NULL ON NULL INPUT
VOLATILE;
-- edit_gneric update
ALTER TABLE edit_generic ADD cuid VARCHAR;
-- adds the created or updated date tags
CREATE OR REPLACE FUNCTION set_edit_generic() RETURNS TRIGGER AS '
    DECLARE
        random_length INT = 12; -- that should be long enough
    BEGIN
        IF TG_OP = ''INSERT'' THEN
            NEW.date_created := ''now'';
            NEW.cuid := random_string(random_length);
        ELSIF TG_OP = ''UPDATE'' THEN
            NEW.date_updated := ''now'';
        END IF;
        RETURN NEW;
    END;
' LANGUAGE 'plpgsql';

-- DROP TABLE edit_page_content;
CREATE TABLE edit_page_content (
    edit_page_content_id    SERIAL PRIMARY KEY,
    edit_page_id    INT NOT NULL,
    edit_access_right_id    INT NOT NULL,
    name    VARCHAR,
    uid VARCHAR UNIQUE,
    order_number INT NOT NULL,
    online  SMALLINT NOT NULL DEFAULT 0,
    FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
) INHERITS (edit_generic) WITHOUT OIDS;
DROP TRIGGER trg_edit_page_content ON edit_page_content;
CREATE TRIGGER trg_edit_page_content
BEFORE INSERT OR UPDATE ON edit_page_content
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();

-- INSERT new list entry
INSERT INTO edit_access_right (name, level, type) VALUES ('List', 10, 'list');

-- UPDATE
ALTER TABLE edit_user ADD additional_acl JSONB;
ALTER TABLE edit_group ADD additional_acl JSONB;
ALTER TABLE edit_access ADD additional_acl JSONB;

-- page content reference settings
ALTER TABLE edit_page ADD content_alias_edit_page_id INT;
ALTER TABLE edit_page ADD CONSTRAINT edit_page_content_alias_edit_page_id_fkey FOREIGN KEY (content_alias_edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE;


-- 2019/9/10 UPDATE missing cuid in edit_* tables
UPDATE edit_access SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_access_data SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_access_right SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_access_user SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_group SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_language SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_log SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_menu_group SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_page SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_page_access SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_page_content SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_query_string SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_scheme SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_user SET cuid = random_string(12) WHERE cuid IS NULL;
UPDATE edit_visible_group SET cuid = random_string(12) WHERE cuid IS NULL;
