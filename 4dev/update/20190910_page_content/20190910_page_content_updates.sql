-- 2019/9/10 update edit_page with reference

-- page content reference settings

-- UPDATE
ALTER TABLE edit_page ADD content_alias_edit_page_id INT;
ALTER TABLE edit_page ADD CONSTRAINT edit_page_content_alias_edit_page_id_fkey FOREIGN KEY (content_alias_edit_page_id) REFERENCES edit_page (edit_page_id) MATCH FULL ON DELETE RESTRICT ON UPDATE CASCADE;
