DROP TRIGGER trg_edit_access ON edit_access;
CREATE TRIGGER trg_edit_access
BEFORE INSERT OR UPDATE ON edit_access
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
