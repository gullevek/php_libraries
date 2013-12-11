-- $Id: trg_edit_access.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_access ON edit_access;
CREATE TRIGGER trg_edit_access
BEFORE INSERT OR UPDATE ON edit_access
FOR EACH ROW EXECUTE PROCEDURE set_generic();
