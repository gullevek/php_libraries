-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, postgres SQL statements for the mysql definitions
-- TABLE: edit_visible_group
-- HISTORY

-- DROP TABLE edit_visible_group;
CREATE TABLE edit_visible_group (
	edit_visible_group_id	SERIAL PRIMARY KEY,
	name	VARCHAR(255),
	flag	VARCHAR(50)
) INHERITS (edit_generic) WITHOUT OIDS;

DELETE FROM edit_visible_group;
INSERT INTO edit_visible_group (name, flag) VALUES ('Main Menu', 'main');
INSERT INTO edit_visible_group (name, flag) VALUES ('Data popup Menu', 'datapopup');
