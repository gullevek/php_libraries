-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- edit tables, this table contains all pages in the edit interface and allocates rights + values to it
-- TABLE: edit_table
-- HISTORY:

-- DROP TABLE edit_page;
CREATE TABLE edit_page (
	edit_page_id	SERIAL PRIMARY KEY,
	filename	VARCHAR(70),
	name	VARCHAR(255) UNIQUE,
	order_number INT NOT NULL,
	online	SMALLINT NOT NULL DEFAULT 0,
	menu	SMALLINT NOT NULL DEFAULT 0,
	popup	SMALLINT NOT NULL DEFAULT 0,
	popup_x	SMALLINT,
	popup_y SMALLINT
) INHERITS (edit_generic) WITHOUT OIDS;

DELETE FROM edit_page;
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_pages.php', 'Edit Pages', 1, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_users.php', 'Edit Users', 2, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_languages.php', 'Edit Languages', 3, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_schemes.php', 'Edit Schemes', 4, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_groups.php', 'Edit Groups', 5, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_visible_group.php', 'Edit Visible Groups', 6, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_menu_group.php', 'Edit Menu Groups', 7, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_access.php', 'Edit Access', 8, 1, 1);
INSERT INTO edit_page (filename, name, order_number, online, menu) VALUES ('edit_order.php', 'Edit Order', 9, 1, 0);
