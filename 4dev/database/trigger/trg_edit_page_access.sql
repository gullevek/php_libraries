-- DROP TRIGGER trg_edit_page_access ON edit_page_access;
CREATE TRIGGER trg_edit_page_access
BEFORE INSERT OR UPDATE ON edit_page_access
FOR EACH ROW EXECUTE PROCEDURE set_generic();
