DROP TRIGGER IF EXISTS trg_edit_scheme ON edit_scheme;
CREATE TRIGGER trg_edit_scheme
BEFORE INSERT OR UPDATE ON edit_scheme
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
