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
