-- AUTHOR: Clemens Schwaighofer
-- DATE: 2005/07/05
-- DESCRIPTION:
-- reference table between visible groups and pages
-- TABLE: edit_page_visible_group
-- HISTORY:

-- DROP TABLE edit_page_visible_group;
CREATE TABLE edit_page_visible_group (
	edit_page_id INT NOT NULL,
	edit_visible_group_id INT NOT NULL,
	FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY (edit_visible_group_id) REFERENCES edit_visible_group (edit_visible_group_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE
);
