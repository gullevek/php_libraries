-- DROP TRIGGER IF EXISTS trg_edit_access ON edit_access;
CREATE TRIGGER trg_edit_access
BEFORE INSERT OR UPDATE ON edit_access
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();

-- DROP TRIGGER IF EXISTS trg_set_edit_access_uid ON edit_access;
CREATE TRIGGER trg_set_edit_access_uid
BEFORE INSERT OR UPDATE ON edit_access
FOR EACH ROW EXECUTE PROCEDURE set_edit_access_uid();
