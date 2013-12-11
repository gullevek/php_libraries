-- $Id: edit_menu_group.sql 4382 2013-02-18 07:27:24Z gullevek $
-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, groupings for menu
-- TABLE: edit_menu_group
-- HISTORY

-- DROP TABLE edit_menu_group;
CREATE TABLE edit_menu_group (
	edit_menu_group_id	SERIAL PRIMARY KEY,
	name	VARCHAR(255),
	flag	VARCHAR(50),
	order_number	INT NOT NULL
) INHERITS (edit_generic) WITHOUT OIDS;

DELETE FROM edit_menu_group;
INSERT INTO edit_menu_group (name, flag, order_number) VALUES ('Admin Menu', 'admin', 1);
INSERT INTO edit_menu_group (name, flag, order_number) VALUES ('Admin Data Popup Menu', 'AdminDataPopup', 2);
