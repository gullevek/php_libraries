DROP TRIGGER IF EXISTS trg_edit_user ON edit_user;
CREATE TRIGGER trg_edit_user
BEFORE INSERT OR UPDATE ON edit_user
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
