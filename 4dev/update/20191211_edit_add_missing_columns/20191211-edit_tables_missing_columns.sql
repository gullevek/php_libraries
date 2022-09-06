-- 2019/12/11 update missing edit_* table data

ALTER TABLE edit_generic ADD cuid VARCHAR;

ALTER TABLE edit_access ADD enabled SMALLINT DEFAULT 0;
ALTER TABLE edit_access ADD protected SMALLINT DEFAULT 0;

ALTER TABLE edit_group ADD uid VARCHAR;
ALTER TABLE edit_group ADD deleted SMALLINT DEFAULT 0;

ALTER TABLE temp_files ADD folder VARCHAR;
ALTER TABLE edit_page ADD hostname VARCHAR;

ALTER TABLE edit_user ADD deleted SMALLINT DEFAULT 0;
