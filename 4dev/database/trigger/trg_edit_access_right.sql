DROP TRIGGER trg_edit_access_right ON edit_access_right;
CREATE TRIGGER trg_edit_access_right
BEFORE INSERT OR UPDATE ON edit_access_right
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
