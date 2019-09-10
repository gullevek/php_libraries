DROP TRIGGER trg_edit_page_content ON edit_page_content;
CREATE TRIGGER trg_edit_page_content
BEFORE INSERT OR UPDATE ON edit_page_content
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
