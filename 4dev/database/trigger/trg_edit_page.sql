-- DROP TRIGGER trg_edit_page ON edit_page;
CREATE TRIGGER trg_edit_page
BEFORE INSERT OR UPDATE ON edit_page
FOR EACH ROW EXECUTE PROCEDURE set_generic();
