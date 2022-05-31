-- DROP TRIGGER IF EXISTS trg_edit_language ON edit_language;
CREATE TRIGGER trg_edit_language
BEFORE INSERT OR UPDATE ON edit_language
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
