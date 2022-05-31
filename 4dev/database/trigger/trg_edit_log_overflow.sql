-- DROP TRIGGER IF EXISTS trg_edit_log_overflow ON edit_log_overflow;
CREATE TRIGGER trg_edit_log_overflow
BEFORE INSERT OR UPDATE ON edit_log_overflow
FOR EACH ROW EXECUTE PROCEDURE set_edit_generic();
