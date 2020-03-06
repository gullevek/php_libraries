-- DROP TRIGGER trg_set_edit_access_uid ON edit_access;
CREATE TRIGGER trg_set_edit_access_uid
BEFORE INSERT OR UPDATE ON edit_access
FOR EACH ROW EXECUTE PROCEDURE set_edit_access_uid();
