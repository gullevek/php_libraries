-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- reference table between menu groups and pages
-- TABLE: edit_page_menu_group
-- HISTORY:

-- DROP TABLE edit_page_menu_group;
CREATE TABLE edit_page_menu_group (
    edit_page_id INT NOT NULL,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_menu_group_id INT NOT NULL,
    FOREIGN KEY (edit_menu_group_id) REFERENCES edit_menu_group (edit_menu_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
);
