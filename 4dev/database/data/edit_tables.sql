-- edit tables insert data in order

-- edit visible group
DELETE FROM edit_visible_group;
INSERT INTO edit_visible_group (name, flag) VALUES ('Main Menu', 'main');
INSERT INTO edit_visible_group (name, flag) VALUES ('Data popup Menu', 'datapopup');

-- edit menu group
DELETE FROM edit_menu_group;
INSERT INTO edit_menu_group (name, flag, order_number) VALUES ('Admin Menu', 'admin', 1);
INSERT INTO edit_menu_group (name, flag, order_number) VALUES ('Admin Data Popup Menu', 'AdminDataPopup', 2);

-- edit page
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

-- edit visible group
DELETE FROM edit_page_visible_group;
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Pages'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Users'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Languages'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Schemes'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Groups'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Visible Groups'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Menu Groups'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Access'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));
-- INSERT INTO edit_page_visible_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Order'), (SELECT edit_visible_group_id FROM edit_visible_group WHERE flag = 'main'));

-- edit page menu group
DELETE FROM edit_page_menu_group;
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Pages'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Users'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Languages'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Schemes'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Groups'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Visible Groups'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Menu Groups'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Access'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));
-- INSERT INTO edit_page_menu_group VALUES ((SELECT edit_page_id FROM edit_page WHERE name = 'Edit Order'), (SELECT edit_menu_group_id FROM edit_menu_group WHERE flag = 'admin'));


-- edit access right
DELETE FROM edit_access_right;
INSERT INTO edit_access_right (name, level, type) VALUES ('Default', -1, 'default');
INSERT INTO edit_access_right (name, level, type) VALUES ('No Access', 0, 'none');
INSERT INTO edit_access_right (name, level, type) VALUES ('List', 10, 'list');
INSERT INTO edit_access_right (name, level, type) VALUES ('Read', 20, 'read');
INSERT INTO edit_access_right (name, level, type) VALUES ('Translator', 30, 'mod_trans');
INSERT INTO edit_access_right (name, level, type) VALUES ('Modify', 40, 'mod');
INSERT INTO edit_access_right (name, level, type) VALUES ('Create/Write', 60, 'write');
INSERT INTO edit_access_right (name, level, type) VALUES ('Delete', 80, 'del');
INSERT INTO edit_access_right (name, level, type) VALUES ('Site Admin', 90, 'siteadmin');
INSERT INTO edit_access_right (name, level, type) VALUES ('Admin', 100, 'admin');

-- edit scheme
DELETE FROM edit_scheme;
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Default Scheme', 'E0E2FF', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Admin', 'CC7E7E', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('Visitor', 'B0C4B3', 1);
INSERT INTO edit_scheme (name, header_color, enabled) VALUES ('User', '1E789E', 1);

-- edit language
DELETE FROM edit_language;
INSERT INTO edit_language (short_name, long_name, iso_name, order_number, enabled, lang_default) VALUES ('en', 'English', 'UTF-8', 1, 1, 1);
INSERT INTO edit_language (short_name, long_name, iso_name, order_number, enabled, lang_default) VALUES ('ja', 'Japanese', 'UTF-8', 2, 1, 0);

-- edit group
DELETE FROM edit_group;
INSERT INTO edit_group (name, enabled, edit_scheme_id, edit_access_right_id) VALUES ('Admin', 1, (SELECT edit_scheme_id FROM edit_scheme WHERE name = 'Admin'), (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin'));
INSERT INTO edit_group (name, enabled, edit_scheme_id, edit_access_right_id) VALUES ('User', 1, (SELECT edit_scheme_id FROM edit_scheme WHERE name = 'User'), (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'write'));

-- edit page access
DELETE FROM edit_page_access;
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Pages'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Users'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Languages'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Schemes'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Groups'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Visible Groups'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Menu Groups'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Access'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
INSERT INTO edit_page_access (enabled, edit_group_id, edit_page_id, edit_access_right_id) VALUES (1,
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_page_id FROM edit_page WHERE name = 'Edit Order'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);

-- edit user
-- inserts admin user so basic users can be created
DELETE FROM edit_user;
INSERT INTO edit_user (username, password, enabled, debug, db_debug, email, protected, admin, edit_language_id, edit_group_id, edit_scheme_id, edit_access_right_id) VALUES ('admin', 'admin', 1, 1, 1, '', 1, 1,
    (SELECT edit_language_id FROM edit_language WHERE short_name = 'en'),
    (SELECT edit_group_id FROM edit_group WHERE name = 'Admin'),
    (SELECT edit_scheme_id FROM edit_scheme WHERE name = 'Admin'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);

-- edit access
DELETE FROM edit_access;
INSERT INTO edit_access (name, enabled, protected) VALUES ('Admin Access', 1, 1);

-- edit access user
DELETE FROM edit_access_user;
INSERT INTO edit_access_user (edit_default, enabled, edit_access_id, edit_user_id, edit_access_right_id) VALUES (1, 1,
    (SELECT edit_access_id FROM edit_access WHERE uid = 'AdminAccess'),
    (SELECT edit_user_id FROM edit_user WHERE username = 'admin'),
    (SELECT edit_access_right_id FROM edit_access_right WHERE type = 'admin')
);
