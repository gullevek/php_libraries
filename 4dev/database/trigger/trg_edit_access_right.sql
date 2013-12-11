-- $Id: trg_edit_access_right.sql 3158 2010-09-02 02:49:00Z gullevek $

-- DROP TRIGGER trg_edit_access_right ON edit_access_right;
CREATE TRIGGER trg_edit_access_right
BEFORE INSERT OR UPDATE ON edit_access_right
FOR EACH ROW EXECUTE PROCEDURE set_generic();
