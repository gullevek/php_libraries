-- $Id: trg_edit_log.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_log ON edit_log;
CREATE TRIGGER trg_edit_log
BEFORE INSERT OR UPDATE ON edit_log
FOR EACH ROW EXECUTE PROCEDURE set_generic();
