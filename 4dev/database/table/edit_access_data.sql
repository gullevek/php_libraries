-- AUTHOR: Clemens Schwaighofer
-- DATE: 2016/7/15
-- DESCRIPTION:
-- sub table to edit access, holds additional data for access group
-- TABLE: edit_access_data
-- HISTORY:

-- DROP TABLE edit_access_data;
CREATE TABLE edit_access_data (
	edit_access_data_id	SERIAL PRIMARY KEY,
	edit_access_id INT NOT NULL,
	FOREIGN KEY (edit_access_id) REFERENCES edit_access (edit_access_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	enabled	SMALLINT NOT NULL DEFAULT 0,
	name	VARCHAR,
	value	VARCHAR
) INHERITS (edit_generic) WITHOUT OIDS;

-- create a unique index for each attached data block for each edit access can
-- only have ONE value;
CREATE UNIQUE INDEX edit_access_data_edit_access_id_name_ukey ON edit_access_data (edit_access_id, name);
