DROP TRIGGER IF EXISTS trg_edit_access_data ON edit_access_data;
CREATE TRIGGER trg_edit_access_data
BEFORE INSERT OR UPDATE ON edit_access_data
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
