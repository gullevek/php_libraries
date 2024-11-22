-- AUTHOR: Clemens Schwaighofer
-- DATE: 2019/9/9
-- DESCRIPTION:
-- sub content to one page with additional edit access right set
-- can be eg JS content groups on one page
-- TABLE: edit_page_content
-- HISTORY:

-- DROP TABLE edit_page_content;
CREATE TABLE edit_page_content (
    edit_page_content_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    edit_page_id INT NOT NULL,
    FOREIGN KEY (edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    edit_access_right_id INT NOT NULL,
    FOREIGN KEY (edit_access_right_id) REFERENCES edit_access_right (edit_access_right_id) MATCH FULL ON DELETE CASCADE ON UPDATE CASCADE,
    uid VARCHAR UNIQUE,
    name VARCHAR,
    order_number INT NOT NULL,
    online SMALLINT NOT NULL DEFAULT 0
) INHERITS (edit_generic) WITHOUT OIDS;
