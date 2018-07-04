DROP TRIGGER trg_edit_log ON edit_log;
CREATE TRIGGER trg_edit_log
BEFORE INSERT OR UPDATE ON edit_log
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
